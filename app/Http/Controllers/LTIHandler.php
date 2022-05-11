<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Library\ChimeToolProvider;
use Auth;
use Illuminate\Support\Facades\Cookie;
use \App\Chime;

class LTIHandler extends Controller
{

    private $allowedDomains = ["umnscratch.instructure.com", "canvas.umn.edu", "umn.instructure.com", "scratch.canvas.umn.edu", "umndevelopment.instructure.com"];

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


    public function launch(Request $request) {
        if($request->get("id_token")) {
            // this is an LTI1.3 launch. Forward over to that controller.
            $lti13Handler = new LTI13Handler();
            return $lti13Handler->launch();
        }

        $tool = new ChimeToolProvider();
        $tool->handleRequest();
        
        session(['lti_launch' => true]);
        $launchDomain = $tool->resourceLink->getSetting("custom_canvas_api_domain");
        if(!\App::environment('local') && !in_array($launchDomain, $this->allowedDomains)) {
            abort(401, 'LTI Launch from an invalid domain');
        }
        
        // our dev instance sends this value. need to make it a real emplid
        if($tool->user->sourceId == "SISIDformcfa0086") {
            $tool->user->sourceId = 2328381;
        }
        // our dev instance sends this value. need to make it a real emplid
        if($tool->user->sourceId == "SISID4elevator") {
            $tool->user->sourceId = 1111111;
        }
        // our dev instance sends this value. need to make it a real emplid
        if($tool->user->sourceId == "emplidFORjohnsojr") {
            $tool->user->sourceId = 1111112;
        }

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


        $resource_link_pk = $tool->resourceLink->getRecordId();
        $resource_link_title = $tool->resourceLink->title;

        if($tool->user->isStaff() || $tool->user->isAdmin()) {

            // it's an instructor, let's check if this assignment exists
            $folder = \App\Folder::where("resource_link_pk", $resource_link_pk)->first();
            $chime = \App\Chime::where('lti_course_id', $tool->context->ltiContextId)->first();
            
            if(!$folder && $chime) {
                // let's check if the chime has this folder created without a resource link
                $folder = $this->relinkSimilarFolder($chime, $resource_link_title, $resource_link_pk);
            }

            if($folder) {             
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
                
                if($chime && $chime->lti_setup_complete) {
                    $chime->users()->syncWithoutDetaching([Auth::user()->id=> ['permission_number' => 300]]);
                    if($chime->lti_grade_mode == \App\LTI13ResourceLink::LTI_GRADE_MODE_MULTIPLE_GRADES) {
                        $folder = new \App\Folder;
                        $folder->chime()->associate($chime);
                        $folder->name = $resource_link_title;
                        $folder->resource_link_pk = $resource_link_pk;
                        $folder->save();
                        return \Redirect::to("/chime/" . $chime->id . "/folder/" . $folder->id);
                    }
                    else {
                        return \Redirect::to("/chime/" . $chime->id);
                    }
                }
                else if($chime && !$chime->lti_setup_complete) {
                    
                    $similarChimes = $this->getSimilarChimes($chime);

                    $chime->users()->syncWithoutDetaching([Auth::user()->id=> ['permission_number' => 300]]);
                    return view("ltiSelectionPrompt", ["ltiLaunch"=>["similar_chimes"=>$similarChimes], "lti_resource_title"=>$resource_link_title, "resource_link_pk"=>$resource_link_pk, "chime"=>$chime]);
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
                    
                    $similarChimes = $this->getSimilarChimes($chime);



                    return view("ltiSelectionPrompt", ["lti_resource_title"=>$resource_link_title, "resource_link_pk"=>$resource_link_pk, "ltiLaunch"=>["similar_chimes"=>$similarChimes], "chime"=>$chime]);
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
                $folder = \App\Folder::where("resource_link_pk", $resource_link_pk)->first();
                
                if(!$folder && $chime) {
                    // let's check if the chime has this folder created without a resource link
                    $folder = $this->relinkSimilarFolder($chime, $resource_link_title, $resource_link_pk);
                }

                if($folder) {
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

    private function relinkSimilarFolder($chime, $folderTitle, $resourceLink) {
        $folder = $chime->folders->where("name", $folderTitle)->where("resource_link_pk", null)->first();
        // this is an imported folder, update it
        if($folder && $chime->lti_grade_mode == \App\LTI13ResourceLink::LTI_GRADE_MODE_MULTIPLE_GRADES) {
            $folder->resource_link_pk = $resourceLink;
            $folder->save();
            return $folder;
        }
        return false;
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

    public function saveLTISettings(Request $req, Chime $chime) {
        $chime->fill($req->all());
        $chime->lti_setup_complete = true;
        $chime->save();
        $resource_link_title = $req->get("lti_resource_title");
        $resource_link_pk = $req->get("resource_link_pk");
        $targetFolder = false;
        if($req->get("import_chime") && is_numeric($req->get("import_chime"))) {
            $oldChime = \App\Chime::find($req->get("import_chime"));
            $chime->lti_grade_mode = $oldChime->lti_grade_mode;
            $chime->students_can_view = $oldChime->students_can_view;
            $chime->join_instructions= $oldChime->join_instructions;
            $chime->only_correct_answers_lti= $oldChime->only_correct_answers_lti;
            if($chime->lti_grade_mode == \App\LTI13ResourceLink::LTI_GRADE_MODE_ONE_GRADE) {
                $chime->resource_link_pk = $resource_link_pk;
            }
            foreach($oldChime->folders as $sourceFolder) {
                $folder = new \App\Folder;
                $folder->chime()->associate($chime);
                $folder->name = $sourceFolder->name;
                $folder->order = $sourceFolder->order;
                if($folder->name == $resource_link_title && $chime->lti_grade_mode == \App\LTI13ResourceLink::LTI_GRADE_MODE_MULTIPLE_GRADES) {
                    $folder->resource_link_pk = $resource_link_pk;
                    $targetFolder = $folder;
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
            $folder = new \App\Folder;
            $folder->chime()->associate($chime);
            $folder->name = $resource_link_title;
            if($chime->lti_grade_mode == \App\LTI13ResourceLink::LTI_GRADE_MODE_MULTIPLE_GRADES) {
                $folder->resource_link_pk = $resource_link_pk;
                $chime->resource_link_pk = null;
                $chime->save();
            }
            else {
                $chime->resource_link_pk = $resource_link_pk;
                $chime->save();
            }
            $folder->save();
            $targetFolder = $folder;
        }
        return \Redirect::to("/chime/" . $chime->id . ($targetFolder?("/folder/" . $targetFolder->id):null));
        
    }
}
