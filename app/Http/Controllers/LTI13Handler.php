<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Library\ChimeToolProvider;
use Auth;
use Illuminate\Support\Facades\Cookie;
use \App\Chime;
use Packback\Lti1p3\LtiOidcLogin;
use Packback\Lti1p3\LtiMessageLaunch;
use \App\LTI13ResourceLink;

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
        $launch = LtiMessageLaunch::new(new \App\Library\LTI13Database, new \App\Library\LTI13Cache, new \App\Library\LTI13Cookie)
        ->validate();
        $launchData = $launch->getLaunchData();
        dd($launchData);
        $userData = $launchData["https://purl.imsglobal.org/spec/lti/claim/custom"];
        $resourceData = $launchData["https://purl.imsglobal.org/spec/lti/claim/resource_link"];
        $contextData = $launchData["https://purl.imsglobal.org/spec/lti/claim/context"];
        $launchPresentationData = $launchData["https://purl.imsglobal.org/spec/lti/claim/launch_presentation"];
        $rolesData = $launchData["https://purl.imsglobal.org/spec/lti/claim/roles"];
        $lisData = $launchData["https://purl.imsglobal.org/spec/lti/claim/lis"];
        
        // TODO: Need to track deployment
        $resourceLinks = LTI13ResourceLink::where("resource_link", $resourceData["id"])->get();
        if($resourceLinks->count() > 0) {
            $resourceLink = $resourceLinks->first();
        }
        else {
            $resourceLink = new Lti13ResourceLink;
            $resourceLink->resource_link = $resourceData["id"];
            $resourceLink->launch_data = $launchData;
            $resourceLink->save();
        }

        // TODO: strip after debugging
        if($lisData["person_sourcedid"] == "SISIDformcfa0086") {
            $lisData["person_sourcedid"] = 2328381;
        }
        if($lisData["person_sourcedid"] == "SISID4elevator") {
            $lisData["person_sourcedid"] = 2328384;
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

            // it's an instructor, let's check if this assingment exists
            if($chime = $resourceLink->chime) {
                if($folder && !$chime) {
                    $chime = $folder->chime;
                }
                
                $chime->users()->syncWithoutDetaching([Auth::user()->id=> ['permission_number' => 300]]);
                $chime->save();

                if($folder->chime->folders->filter(function($folder) { return !$folder->resource_link_pk;})->count() > 0) {
                    return \Redirect::to("/chime/" . $chime->id);
                }
                else {
                    return \Redirect::to("/chime/" . $chime->id. "/folder/" . $folder->id);
                }
                
            }
            else {
                $chime = \App\Chime::where('lti_course_id', $tool->context->ltiContextId)->first();
                if($chime && $chime->lti_setup_complete) {
                    $chime->users()->syncWithoutDetaching([Auth::user()->id=> ['permission_number' => 300]]);
                    if(!$chime->single_chime_for_lti) {
                        $folder = new \App\Folder;
                        $folder->chime()->associate($chime);
                        $folder->name = $tool->resourceLink->title;
                        $folder->resource_link_pk = $tool->resourceLink->getRecordId();
                        $folder->save();
                    }
                    return \Redirect::to("/chime/" . $chime->id);
                }
                else if($chime && !$chime->lti_setup_complete) {
                    $chime->users()->syncWithoutDetaching([Auth::user()->id=> ['permission_number' => 300]]);
                    return view("ltiSelectionPrompt", ["resource_link_title"=>$tool->resourceLink->title, "resource_link_pk"=>$tool->resourceLink->getRecordId(), "chime"=>$chime]);
                }
                else {
                    $chime = new \App\Chime;
                    $chime->lti_return_url = $tool->returnUrl;
                    $chime->lti_course_title = $tool->context->title;
                    $chime->lti_course_id = $tool->context->ltiContextId;
                    $chime->name = $tool->context->title;
                    $chime->require_login = true;
                    // $chime->single_chime_for_lti = true;
                    $chime->access_code = $chime->getUniqueCode();
                    $chime->save();
                    $chime->users()->attach(Auth::user(), ['permission_number' => 300]);

                    // temporary while we figure out our LTI future
                    $chime->lti_setup_complete = true;
                    $chime->single_chime_for_lti = false;
                    $folder = new \App\Folder;
                    $folder->chime()->associate($chime);
                    $folder->name = $tool->resourceLink->title;
                    $folder->resource_link_pk = $tool->resourceLink->getRecordId();
                    $chime->save();
                    $folder->save();
                    return \Redirect::to("/chime/" . $chime->id. "/folder/" . $folder->id);
                    // return view("ltiSelectionPrompt", ["resource_link_title"=>$tool->resourceLink->title, "resource_link_pk"=>$tool->resourceLink->getRecordId(), "chime"=>$chime]);
                }                
            }
        }
        else {
            if($chime = \App\Chime::where("lti_course_id",$tool->context->ltiContextId)->first()) {
                if(!Auth::user()->chimes->contains($chime)) {
                    Auth::user()->chimes()->attach($chime, [
                        'permission_number' => 100
                    ]);
                }

                $folderId = null;
                if($folder = \App\Folder::where("resource_link_pk", $tool->resourceLink->getRecordId())->first()) {
                    $folderId = $folder->id;
                    if($folder->chime->folders->filter(function($folder) { return !$folder->resource_link_pk;})->count() > 0) {
                        $folderId = null;
                    }
                }
                return \Redirect::to("/chimeParticipant/" . $chime->id . "/" . $folderId);
            }
            else {

                // TODO??
            }
        }

    }

    public function saveLTISettings(Request $req, Chime $chime) {
        $chime->fill($req->all());
        $chime->lti_setup_complete = true;
        $chime->save();
        $resource_link_title = $req->get("resource_link_title");
        $resource_link_pk = $req->get("resource_link_pk");
        $folder = new \App\Folder;
        $folder->chime()->associate($chime);
        $folder->name = $resource_link_title;
        if(!$chime->single_chime_for_lti) {
            $folder->resource_link_pk = $resource_link_pk;
            $chime->resource_link_pk = null;
            $chime->save();
        }
        else {
            $chime->resource_link_pk = $resource_link_pk;
            $chime->save();
        }
        $folder->save();
        return \Redirect::to("/chime/" . $chime->id. "/folder/" . $folder->id);
        
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
