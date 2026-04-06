<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
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
        return redirect()->away('https://umn-latis.github.io/ChimeIn2.0/lti11-migration.html');
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
