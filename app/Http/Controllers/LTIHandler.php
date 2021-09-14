<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Library\ChimeToolProvider;
use Auth;
use Illuminate\Support\Facades\Cookie;
use \App\Chime;

class LTIHandler extends Controller
{

    private $allowedDomains = ["umnscratch.instructure.com", "canvas.umn.edu", "umn.instructure.com", "scratch.canvas.umn.edu"];

    public function __construct() {
        if(app()->getProvider('debugbar')) {
            app('debugbar')->disable();
        }
        
    }

    public function index() {

    }

    public function configInfo() {
        $host = request()->getSchemeAndHttpHost();
        return view("ltiConfig", ["host"=>$host]);
    }


    public function launch() {
        
        $tool = new ChimeToolProvider();
        $tool->handleRequest();
        $launchDomain = $tool->resourceLink->getSetting("custom_canvas_api_domain");
        if(!\App::environment('local') && !in_array($launchDomain, $this->allowedDomains)) {
            abort(401, 'LTI Launch from an invalid domain');
        }
// $tool->user->sourceId = 2328381;
// $tool->user->email = "mcfa0086@umn.edu";

        if(!$tool->user->sourceId) {
            return view("errors.emplid");    
        }
        
        if(Auth::attempt(["emplid"=>$tool->user->sourceId]) || Auth::attempt(["email"=>$tool->user->email])) {
            Auth::user()->lti_user_id = $tool->user->ltiUserId;
            Auth::user()->save();
        }
        else {
            $user = new \App\User;
            // $user->first_name = $tool->user->firstname;
            // $user->last_name = $tool->user->lastname;
            $user->email = $tool->user->email;
            $user->name = $tool->user->firstname . " ". $tool->user->lastname;
            $user->emplid = $tool->user->sourceId;    
            $user->lti_user_id = $tool->user->ltiUserId;
            $user->save();
            Auth::login($user);
        }

        if($tool->user->isStaff() || $tool->user->isAdmin()) {

            // it's an instructor, let's check if this assingment exists
            if($folder = \App\Folder::where("resource_link_pk", $tool->resourceLink->getRecordId())->first()) {             
                $chime = $folder->chime;
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
                    // if(!$chime->single_chime_for_lti) {
                        $folder = new \App\Folder;
                        $folder->chime()->associate($chime);
                        $folder->name = $tool->resourceLink->title;
                        $folder->resource_link_pk = $tool->resourceLink->getRecordId();
                        $folder->save();
                    // }
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
                    // $chime->single_chime_for_lti = false;
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
}
