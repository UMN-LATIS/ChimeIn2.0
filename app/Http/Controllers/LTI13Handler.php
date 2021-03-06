<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Library\ChimeToolProvider;
use Auth;
use Illuminate\Support\Facades\Cookie;
use \App\Chime;
use Packback\Lti1p3\LtiOidcLogin;
use Packback\Lti1p3\LtiMessageLaunch;
use Packback\Lti1p3\LtiException;
use \App\LTI13ResourceLink;
use \App\Library\LTI13Processor;

class LTI13Handler extends Controller
{

    private $staffRoles = ["http://purl.imsglobal.org/vocab/lis/v2/institution/person#Faculty",
    "http://purl.imsglobal.org/vocab/lis/v2/institution/person#Staff",
    "http://purl.imsglobal.org/vocab/lis/v2/institution/person#Instructor"];

    public function __construct() {
        if(app()->getProvider('debugbar')) {
            app('debugbar')->disable();
        }
    }

    public function index() {

    }

     public function login() {
        return LtiOidcLogin::new(new \App\Library\LTI13Database, new \App\Library\LTI13Cache, new \App\Library\LTI13Cookie)
            ->doOidcLoginRedirect(url("lti13/launch"))
            ->doRedirect();
    }


    public function launch() {

         try {
            $launch = LtiMessageLaunch::new(new \App\Library\LTI13Database, new \App\Library\LTI13Cache, new \App\Library\LTI13Cookie)
            ->validate();
        }
        catch (LtiException $e) {

            // canvas needs to update for new window to work https://github.com/instructure/canvas-lms/commit/811a1194cabccc1b3fb22aa3d13d64cde547116d#diff-79b6cd1bab1e82354966238b3d72cfa8fffb6357a61d2454bf4aba1c85b96a5e
//             echo '<script>
//             window.parent.postMessage(
//   {
//     messageType: "requestFullWindowLaunch",
//     data: {
//       url: "https://cla-chimein-dev.oit.umn.edu/lti13/launch",
//       launchType: "new_window",
//     }
//   },
//   "*"
// )           
            // </script>';
            echo '<script>
            window.parent.postMessage(
  {
    messageType: "requestFullWindowLaunch",
    data: "' . url("lti13/launch") . '"
  },
  "*"
)           
            </script>
            <h1>Canvas Launch Error</h1>
            <p>' . $e->getMessage() . "</p>";
            return;
        }

        $launchData = $launch->getLaunchData();
        
        $lisData = $launchData["https://purl.imsglobal.org/spec/lti/claim/lis"];
        $rolesData = $launchData["https://purl.imsglobal.org/spec/lti/claim/roles"];
        $contextData = $launchData["https://purl.imsglobal.org/spec/lti/claim/context"];
        $endpointData = $launchData["https://purl.imsglobal.org/spec/lti-ags/claim/endpoint"];
        $resourceData = $launchData["https://purl.imsglobal.org/spec/lti/claim/resource_link"];

        $resourceLinks = LTI13ResourceLink::where("resource_link", $resourceData["id"])->get();    
        if($resourceLinks->count() > 0) {
            $resourceLink = $resourceLinks->first();
        }
        else {

            $deploymentId = $launchData['https://purl.imsglobal.org/spec/lti/claim/deployment_id'];
            try {
                $deployment = \App\LTI13Deployment::where("deployment_id", $deploymentId)->firstOrFail();
            }
            catch (ModelNotFoundException $ex) {
                Log::error("Model not found launching from Canvas", $ex);
                return view("errors.500", ["exception"=>$ex]);
            }
            

            $resourceLink = new Lti13ResourceLink;
            $resourceLink->resource_link = $resourceData["id"];
            $resourceLink->endpoint = $endpointData;
            $resourceLink->deployment_id = $deployment->id;
            $resourceLink->save();
        }
        
     

        

        if(!$lisData["person_sourcedid"] || !is_numeric($lisData["person_sourcedid"])) {
            return view("errors.emplid");
        }
        
        

        if(Auth::attempt(["emplid"=>$lisData["person_sourcedid"]]) || Auth::attempt(["email"=>$launchData["email"]])) {
            Auth::user()->lti13_sub_id = $launchData["sub"];
            Auth::user()->save();
        }
        else {
            $user = new \App\User;
            $user->email =$launchData["email"];
            $user->name =$launchData["name"];
            $user->emplid = $lisData["person_sourcedid"];
            $user->lti13_sub_id = $launchData["sub"];
            $user->save();
            Auth::login($user);
        }

        if(count(array_intersect($rolesData, $this->staffRoles)) > 0) {
            // update our resoruceLink 

            $chime = \App\Chime::where('lti_course_id', $contextData["id"])->first();
            // it's an instructor, let's check if this assingment exists
            if($chime && $chime->lti_setup_complete) {
                
                $chime->users()->syncWithoutDetaching([Auth::user()->id=> ['permission_number' => 300]]);
                $chime->save();

                // if they've launched from an assignment, we can get them to the right folder
                if(isset($endpointData["lineitem"]) && $chime->lti_grade_mode == LTI13ResourceLink::LTI_GRADE_MODE_MULTIPLE_GRADES) {

                    // check if the lineitem attached matches a folder
                    $folder = \App\Folder::where("lti_lineitem", $endpointData["lineitem"])->first();
                    if($folder) {
                        return \Redirect::to("/chime/" . $chime->id. "/folder/" . $folder->id);
                    }

                    // we need to make a new folder
                    $folder = new \App\Folder;
                    $folder->chime()->associate($chime);
                    $folder->name = $resourceData["title"];
                    $folder->lti_lineitem = $endpointData["lineitem"];
                    $folder->save();
                    return \Redirect::to("/chime/" . $chime->id. "/folder/" . $folder->id);
                }
                return \Redirect::to("/chime/" . $chime->id);
                
            }
            else if($chime && !$chime->lti_setup_complete) {
                $chime->lti13_resource_link_id = $resourceLink->id;
                $chime->save();
                $chime->users()->syncWithoutDetaching([Auth::user()->id=> ['permission_number' => 300]]);
                return view("ltiSelectionPrompt", ["chime"=>$chime, "haveLineItem"=>isset($endpointData["lineitem"]), "lti_resource_title"=>$resourceData["title"]]);
            }
            else {
                $chime = new \App\Chime;
                $chime->lti_course_title = $contextData["title"];
                $chime->lti_course_id = $contextData["id"];
                $chime->lti13_resource_link_id = $resourceLink->id;
                $chime->name = $contextData["title"];
                $chime->require_login = true;
                $chime->access_code = $chime->getUniqueCode();
                $chime->save();
                $chime->users()->attach(Auth::user(), ['permission_number' => 300]);
;
                // return \Redirect::to("/chime/" . $chime->id. "/folder/" . $folder->id);
                return view("ltiSelectionPrompt", ["chime"=>$chime, "haveLineItem"=>isset($endpointData["lineitem"]), "lti_resource_title"=>$resourceData["title"]]);
            }                
        }
        else {
            if($chime = \App\Chime::where("lti_course_id",$contextData["id"])->first()) {
                if(!Auth::user()->chimes->contains($chime)) {
                    Auth::user()->chimes()->attach($chime, [
                        'permission_number' => 100
                    ]);
                }

                $folderId = null;
                $folder = null;
                $courseHasNonLTIFolders = false;
                if(isset($endpointData["lineitem"]) && $folder = \App\Folder::where("lti_lineitem", $endpointData["lineitem"])->first()) {
                    $folderId = $folder->id;
                    if($folder->chime->folders->filter(function($folder) { return !$folder->lti_lineitem;})->count() > 0) {
                        $folderId = null;
                        $courseHasNonLTIFolders = true;
                    }
                }
                $response = \Redirect::to("/chimeParticipant/" . $chime->id . "/" . $folderId);
                if(isset($endpointData["lineitem"]) && !$folder && !$courseHasNonLTIFolders) {
                    $response = $response->with('lti_error', 'There are no questions created for this assignment, so we\'re showing you all of the open questions for this course. This could be because your instructor hasn\'t added any questions yet. Check with them if you\'re not sure.');
                }
                return $response;

            }
            else {
                return view("errors.not_setup");
            }
        }

    }

    public function saveLTISettings(Request $req, Chime $chime) {
        
        $chime->fill($req->all());
        $chime->lti_setup_complete = true;
        $chime->save();
        $resourceLink = $chime->lti13_resource_link;
        
        if($chime->lti_grade_mode == LTI13ResourceLink::LTI_GRADE_MODE_MULTIPLE_GRADES) {
            $folder = new \App\Folder;
            $folder->chime()->associate($chime);
            $folder->name = $req->get("lti_resource_title");
            $folder->lti_lineitem = $resourceLink->endpoint["lineitem"];
            $folder->save();
            return \Redirect::to("/chime/" . $chime->id. "/folder/" . $folder->id);
        }
        else if($chime->lti_grade_mode == LTI13ResourceLink::LTI_GRADE_MODE_ONE_GRADE && !isset($resourceLink->endpoint["lineitem"])) {
            // we don't have a gradebook entry - they must not be accessing via an assignment. We'll push an entry and they can modify it.

            $ags = LTI13Processor::getAGS($chime);     
            $score_lineitem = \Packback\Lti1p3\LtiLineitem::new()
            ->setTag('chimein_grade')
            ->setScoreMaximum(10)
            ->setLabel('ChimeIn')
            ->setResourceId($resourceLink->resource_link);
            $result = $ags->findOrCreateLineitem($score_lineitem);
            $resourceLink->created_line_item = $result->getId();
            $resourceLink->save();
            $req->session()->flash('lti_notice', 'ChimeIn created a new gradebook entry in your Canvas course. By default, the ChimeIn entry is worth 10 points. Feel free to adjust this in Canvas.');
            
        }

        return \Redirect::to("/chime/" . $chime->id);
        
        
        
    }


    public function config(Request $request) {
        // key generated with https://mkjwk.org
        $configArray = [
            "title" => "ChimeIn",
            "description" => "ChimeIn Student Response Tool",
            "oidc_initiation_url" => url("lti13/login"),
            "target_link_uri" => url("lti13/launch"),
            "scopes" => [
                "https://purl.imsglobal.org/spec/lti-ags/scope/lineitem",
                "https://purl.imsglobal.org/spec/lti-ags/scope/result.readonly",
                "https://purl.imsglobal.org/spec/lti-ags/scope/score"
            ],
            "extensions" => [
                [
                    "domain" => $request->getHost(),
                    "tool_id" => "chimein",
                    "platform" => "canvas.instructure.com",
                    "settings" => [
                        "privacy_level" => "public",
                        "text" => "Launch ChimeIn",
                        "icon_url" => url("/library/images/home/record-icon.png"),
                        "placements" => [
                                [
                                    "text" => "ChimeIn",
                                    "enabled" => true,
                                    "placement" => "assignment_selection",
                                    "message_type" => "LtiResourceLinkRequest",
                                    "target_link_uri" => url("lti13/launch"),
                                    "canvas_icon_class" => "icon-lti"
                                ],
                                [
                                    "text"=>"ChimeIn",
                                    "enabled"=>true,
                                    "placement"=>"course_navigation",
                                    "message_type"=>"LtiResourceLinkRequest",
                                    "target_link_uri"=>url("lti13/launch"),
                                    "canvas_icon_class"=>"icon-lti",
                                    "windowTarget"=> "_blank"
                                ],
                                [
                                    "text"=>"ChimeIn",
                                    "enabled"=>true,
                                    "placement"=>"link_selection",
                                    "message_type"=>"LtiResourceLinkRequest",
                                    "target_link_uri"=>url("lti13/launch"),
                                    "canvas_icon_class"=>"icon-lti",
                                    "windowTarget"=> "_blank"
                                ]
                            ]
                        ]
                    ]
            ],
            "public_jwk" => [
                "kty" => "RSA",
                "alg" => "RS256",
                "use" => "sig",
                "e" => "AQAB",
                "n" => "ken8EjBiMrvjQSKCdZfVH_d1gsVe8iwZMJ1xJoB4UpWK2WxphMCiJ3XYQfwaR87EdfAOIkdneUL2mFHdSzKMVnXPHzapMeHNhMdtSZZc3jheA-kg-zQWhFhYI6uyaYpqtEprMGlBuBUxoWhj9o34v6lK4SSCmGa_ZMzm9Mtlop32k_AIAC1DnOe1KFMQ4PL5Fi09tSjuiZb1KjxdwM12ADQI0k98XcW5OrzDuv4eSNPs3mdAPq_7dI9AjcrmhlM6o7KLeUp9-jV5Cf5ipSq19hm1-nErf62PWITK6XoHc19V6ucfoYmwaVs8X8CXEyWsYjBPaWmNy0lIG7sNw46H4Q",
                "kid" => "sig-1615993966"
            ],
            "custom_fields" => [  
                "canvas_integration_id" =>'$Canvas.user.sisSourceId',
                "user_username" => '$User.username',
                "canvas_user_id" => '$Canvas.user.id',
                "canvas_course_id" => '$Canvas.course.id'
            ]
        ];

        return response()->json($configArray);
    }
}
