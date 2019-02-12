<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Library\ChimeToolProvider;
use Auth;

class LTIHandler extends Controller
{

    public function index() {

    }

    public function configInfo() {
        return view("ltiConfig");
    }


    public function launch() {

        $tool = new ChimeToolProvider();
        $tool->handleRequest();
        if(!$tool->user->sourceId || !is_numeric($tool->user->sourceId)) {
            return view("errors.emplid");    
        }
        
        if(Auth::attempt(["emplid"=>$tool->user->sourceId])) {
            Auth::user()->lti_user_id = $tool->user->ltiUserId;
            Auth::user()->save();
        }
        else {
            $user = new \App\User;
            // $user->first_name = $tool->user->firstname;
            // $user->last_name = $tool->user->lastname;
            $user->email = $tool->user->email;
            $user->name = $user->first_name . " ". $user->last_name;
            $user->emplid = $tool->user->sourceId;    
            $user->lti_user_id = $tool->user->ltiUserId;
            $user->save();
            Auth::login($user);
        }

        if($tool->user->isStaff()) {
            // it's an instructor, let's check if this assingment exists
            if($folder = \App\Folder::where("resource_link_pk", $tool->resourceLink->getRecordId())->first()) {             
                $chime = $folder->chime;
                $chime->users()->syncWithoutDetaching([Auth::user()->id=> ['permission_number' => 300]]);
                $chime->save();
                return \Redirect::to("/chime/" . $chime->id. "/folder/" . $folder->id);
            }
            else {
                if($chime = \App\Chime::where('lti_course_id', $tool->context->ltiContextId)->first()) {

                }
                else {
                    $chime = new \App\Chime;
                    $chime->lti_return_url = $tool->returnUrl;
                    $chime->lti_course_title = $tool->context->title;
                    $chime->lti_course_id = $tool->context->ltiContextId;
                    $chime->name = $tool->context->title;
                    $chime->require_login = true;
                    $chime->access_code = strtolower(str_random(6));
                    $chime->save();
                    $chime->users()->attach(Auth::user(), ['permission_number' => 300]);
                }

                $folder = new \App\Folder;
                $folder->chime()->associate($chime);
                $folder->name = $tool->resourceLink->title;
                $folder->resource_link_pk = $tool->resourceLink->getRecordId();
                $folder->save();
                
                return \Redirect::to("/chime/" . $chime->id. "/folder/" . $folder->id);
            }
        }
        else {
            // Auth::logout();
            // we'll force shib
            if($chime = \App\Chime::where("lti_course_id",$tool->context->ltiContextId)->first()) {
                return \Redirect::to("/chimeParticipant/" . $chime->id);
            }
            else {

                // TODO??
            }
        }

    }
}
