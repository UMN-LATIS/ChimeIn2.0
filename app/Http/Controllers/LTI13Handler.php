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

    private $staffRoles = [
    "http://purl.imsglobal.org/vocab/lis/v2/membership#ContentDeveloper",
    "http://purl.imsglobal.org/vocab/lis/v2/membership#Instructor"];

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

        if(isset($_REQUEST['error']) && $_REQUEST['error'] == 'launch_no_longer_valid') {
            $exception = new \Exception($_REQUEST['error_description']);
            if (app()->bound('sentry')) {
                app('sentry')->captureException($exception);
            }
            return view("errors.500", ["exception"=>$exception]);
        }

         try {
            $launch = LtiMessageLaunch::new(
                new \App\Library\LTI13Database, 
                new \App\Library\LTI13Cache, 
                new \App\Library\LTI13Cookie, 
                new \Packback\Lti1p3\LtiServiceConnector(
                    new \App\Library\LTI13Cache, 
                    new \GuzzleHttp\Client([
                        'timeout' => 30,
                    ])
                )
            )
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
        session(['lti_launch' => true]);
        $launchData = $launch->getLaunchData();

        $lisData = $launchData["https://purl.imsglobal.org/spec/lti/claim/lis"];
        $rolesData = $launchData["https://purl.imsglobal.org/spec/lti/claim/roles"];
        $contextData = $launchData["https://purl.imsglobal.org/spec/lti/claim/context"];
        $endpointData = $launchData["https://purl.imsglobal.org/spec/lti-ags/claim/endpoint"];
        $resourceData = $launchData["https://purl.imsglobal.org/spec/lti/claim/resource_link"];
        $presentationData = $launchData["https://purl.imsglobal.org/spec/lti/claim/launch_presentation"];

        $returnURL = explode("external_content", $presentationData["return_url"])[0];
        // LTI1.3 always passes us "instructure.com" domained urls. We don't really want that because it 
        // creates cookie issues potentially if users access ChimeIn in an iframe from a umn.instructure url.
        // This isn't a great universal fix obviously - the real fix is a change in the U's Canvas config
        $returnURL = str_replace("umn.instructure.com", "canvas.umn.edu", $returnURL);
        
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
        
        $lisData = $this->mungeLisData($lisData);

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
                // update our resourceLink in case this is a migrated lti1.1
                $chime->lti13_resource_link_id = $resourceLink->id;
                $chime->lti_return_url = $returnURL;
                $chime->users()->syncWithoutDetaching([Auth::user()->id=> ['permission_number' => 300]]);
                $chime->save();

                // if they've launched from an assignment, we can get them to the right folder
                if(isset($endpointData["lineitem"]) && $chime->lti_grade_mode == LTI13ResourceLink::LTI_GRADE_MODE_MULTIPLE_GRADES) {

                    // check if the lineitem attached matches a folder
                    $folder = \App\Folder::where("lti_lineitem", $endpointData["lineitem"])->first();
                    
                    if(!$folder && $chime) {
                        // let's check if the chime has this folder created without a resource link
                        $folder = $this->relinkSimilarFolder($chime, $resourceData["title"], $endpointData["lineitem"]);
                    }
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

                $similarChimes = $this->getSimilarChimes($chime);

                $chime->users()->syncWithoutDetaching([Auth::user()->id=> ['permission_number' => 300]]);
                return view("ltiSelectionPrompt", ["ltiLaunch"=>["similar_chimes"=>$similarChimes], "chime"=>$chime, "resource_link_pk"=>null, "lti_resource_title"=>$resourceData["title"], "saveTarget"=>'ltisettings13.update']);
            }
            else {
                $chime = new \App\Chime;
                $chime->lti_course_title = $contextData["title"];
                $chime->lti_course_id = $contextData["id"];
                $chime->lti_return_url = $returnURL;
                $chime->lti13_resource_link_id = $resourceLink->id;
                $chime->name = $contextData["title"];
                $chime->require_login = true;
                $chime->access_code = $chime->getUniqueCode();
                $chime->save();
                $chime->users()->attach(Auth::user(), ['permission_number' => 300]);
;       
                $similarChimes = $this->getSimilarChimes($chime);

                // return \Redirect::to("/chime/" . $chime->id. "/folder/" . $folder->id);
                return view("ltiSelectionPrompt", ["ltiLaunch"=>["similar_chimes"=>$similarChimes], "chime"=>$chime , "resource_link_pk"=>null, "lti_resource_title"=>$resourceData["title"], "saveTarget"=>'ltisettings13.update']);
            }                
        }
        else {
            if($chime = \App\Chime::where("lti_course_id",$contextData["id"])->first()) {
                if(!Auth::user()->chimes->contains($chime)) {
                    Auth::user()->chimes()->attach($chime, [
                        'permission_number' => 100
                    ]);
                }
                else {
                    $chime->users()->syncWithoutDetaching([Auth::user()->id=> ['permission_number' => 100]]);
                }

                $folderId = null;
                $folder = null;
                $courseHasNonLTIFolders = false;
                if(isset($endpointData["lineitem"])) {
                    $folder = \App\Folder::where("lti_lineitem", $endpointData["lineitem"])->first();
                    
                    if(!$folder && $chime) {
                        // let's check if the chime has this folder created without a resource link
                        $folder = $this->relinkSimilarFolder($chime, $resourceData["title"], $endpointData["lineitem"]);
                    }
                    if($folder) {
                        $folderId = $folder->id;
                        if($folder->chime->folders->filter(function($folder) { return !$folder->lti_lineitem;})->count() > 0) {
                            $folderId = null;
                            $courseHasNonLTIFolders = true;
                        }
                    }
                }
                $response = \Redirect::to("/chimeParticipant/" . $chime->id . "/" . $folderId);
                if(isset($endpointData["lineitem"]) && !$folder && !$courseHasNonLTIFolders && $chime->lti_grade_mode == LTI13ResourceLink::LTI_GRADE_MODE_MULTIPLE_GRADES) {
                    $response = $response->with('lti_error', "There are no questions created for this assignment, so we're showing you all of the open questions for this course. This could be because your instructor hasn't added any questions yet. Check with them if you're not sure.");
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
        $ags = LTI13Processor::getAGS($chime);
        $resourceLink = $chime->lti13_resource_link;
        $resource_link_title  = $req->get("lti_resource_title");
        $lineitem = $resourceLink->endpoint["lineitem"];
        if($req->get("import_chime") && is_numeric($req->get("import_chime"))) {
            $oldChime = \App\Chime::find($req->get("import_chime"));
            $chime->lti_grade_mode = $oldChime->lti_grade_mode;
            $chime->students_can_view = $oldChime->students_can_view;
            $chime->join_instructions= $oldChime->join_instructions;
            $chime->only_correct_answers_lti= $oldChime->only_correct_answers_lti;
 
            foreach($oldChime->folders as $sourceFolder) {
                $folder = new \App\Folder;
                $folder->chime()->associate($chime);
                $folder->name = $sourceFolder->name;
                $folder->order = $sourceFolder->order;
                if($chime->lti_grade_mode == \App\LTI13ResourceLink::LTI_GRADE_MODE_MULTIPLE_GRADES) {
                    foreach($ags as $lineitem) {
                        if($lineitem["label"] == $sourceFolder->name) {
                            $folder->lti_lineitem = $lineitem["resource_link_id"];
                        }
                    }
                }
                
                
                $folder->save();
                foreach($sourceFolder->questions as $question) {
                    $newQuestion = $question->replicate();
                    $newQuestion->folder()->associate($folder);
                    $newQuestion->current_session_id = null;
                    $newQuestion->save();
                }

            }
            $chime->save();

        }
        else {
            if($chime->lti_grade_mode == LTI13ResourceLink::LTI_GRADE_MODE_MULTIPLE_GRADES) {
                $folder = new \App\Folder;
                $folder->chime()->associate($chime);
                $folder->name = $req->get("lti_resource_title");
                $folder->lti_lineitem = $resourceLink->endpoint["lineitem"];
                $folder->save();
                return \Redirect::to("/chime/" . $chime->id. "/folder/" . $folder->id);
            }
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
                                // [
                                //     "text"=>"ChimeIn",
                                //     "enabled"=>true,
                                //     "placement"=>"course_navigation",
                                //     "message_type"=>"LtiResourceLinkRequest",
                                //     "target_link_uri"=>url("lti13/launch"),
                                //     "canvas_icon_class"=>"icon-lti",
                                //     "windowTarget"=> "_blank"
                                // ],
                                // [
                                //     "text"=>"ChimeIn",
                                //     "enabled"=>true,
                                //     "placement"=>"link_selection",
                                //     "message_type"=>"LtiResourceLinkRequest",
                                //     "target_link_uri"=>url("lti13/launch"),
                                //     "canvas_icon_class"=>"icon-lti",
                                //     "windowTarget"=> "_blank"
                                // ]
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

    private function relinkSimilarFolder($chime, $folderTitle, $resourceLink) {
        $folder = $chime->folders->where("name", $folderTitle)->where("lti_lineitem", null)->first();
        // this is an imported folder, update it
        if($folder && $chime->lti_grade_mode == \App\LTI13ResourceLink::LTI_GRADE_MODE_MULTIPLE_GRADES) {
            $folder->lti_lineitem = $resourceLink;
            $folder->resource_link_pk = null;
            $folder->save();
            return $folder;
        }
        return false;
    }

    private function mungeLisData($lisData) {
        if($lisData["person_sourcedid"]== "SISIDformcfa0086") {
            $lisData["person_sourcedid"] = 2328381;
        }
        // our dev instance sends this value. need to make it a real emplid
        if($lisData["person_sourcedid"] == "SISID4elevator" || $lisData["person_sourcedid"]  == "Dx7a7sg9zz") {
            $lisData["person_sourcedid"] = 1111111;
        }
        // our dev instance sends this value. need to make it a real emplid
        if($lisData["person_sourcedid"] == "emplidFORjohnsojr") {
            $lisData["person_sourcedid"] = 1111112;
        }
        return $lisData;
    }

    private function getSimilarChimes($chime) {
        $explodedName = explode(" ", $chime->name);
        $courseName = $explodedName[0] . " " . $explodedName[1] . "%";
        $similarChimes = Auth::user()->chimes()->where("name", "like", $courseName)->where("chimes.id", "!=", $chime->id)->get();
        if($similarChimes->count() == 0) {
            return false;
        }
        return $similarChimes;
    }
}
