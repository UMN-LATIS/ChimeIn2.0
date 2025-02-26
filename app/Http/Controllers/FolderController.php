<?php

namespace App\Http\Controllers;

use App\Chime;
use Illuminate\Http\Request;
use App\Events\EndSession;
use App\Folder;
use Illuminate\Support\Facades\Auth;

class FolderController extends Controller
{

    public function show(Request $req, $chime, $folder, $includeQuestions = false) {
        $user = $req->user();
        $chime = $user->chimes()->where('chime_id', $chime->id)->first();
        
        // allow `?includeQuestions=true` to be passed as a query string
        $includeQuestions = $includeQuestions || $req->query('include_questions');

        if ($chime != null && ($chime->pivot->permission_number >= 300 || $chime->students_can_view)) {
            if($includeQuestions) {
                // this is spendy!
                if($chime->students_can_view && $chime->pivot->permission_number < 300) {
                    $folder->student_view = true;
                }
                // $folder->load("questions");
                $folder->load("questions.folder");
                // $folder->load("questions.sessions");
                $folder->load("questions.sessions.responses");
                $folder->load("questions.sessions.responses.user");
            }
            return response()->json($folder);
        }
        else {
            return response('Invalid Permissions to Get Folder', 403);
        }

        
    }

    public function importQuestions(Request $req, Chime $chime, Folder $folder)
    {
        $validated = $req->validate([
            'folder_id' => 'required|integer|exists:folders,id'
        ]);

        $user = Auth::user();
        $sourceFolder = Folder::find($validated['folder_id']);
        $sourceChime = $sourceFolder->chime;

        // verify that the user has edit permissions on
        // both the current folder and the source folder
        abort_unless($user->canEditChime($chime->id) && $user->canEditChime($sourceChime->id), 403, 'Invalid Permissions to Import Questions');

        $currentOrderIndex = $folder->questions->max('order') ?? 0;

        foreach ($sourceFolder->questions->sortBy('order') as $question) {
            $currentOrderIndex += 1;

            $newQuestion = $question->replicate();
            $newQuestion->folder()->associate($folder);
            $newQuestion->current_session_id = null;
            $newQuestion->order = $currentOrderIndex;
            $newQuestion->save();
        }

        return response()->json(["success" => true]);
    }
    
    public function saveOrder(Request $req) {
        $user = $req->user();
        $chime = (
            $user
            ->chimes()
            ->where('chime_id', $req->route('chime_id'))
            ->first());
        
        if ($chime != null && $chime->pivot->permission_number >= 300) {
            $folder = $chime->folders()->find($req->route('folder_id'));
            /*
            $folder = $chime->folders()->find($req->route('folder_id'));

            $q1 = $folder->questions->find($req->get('q1'));
            $q1Order = $q1->order;

            $q2 = $folder->questions->find($req->get('q2'));
            $q2Order = $q2->order;

            $q1->update(['order' => $q2Order]);
            $q2->update(['order' => $q1Order]);

            return response('Question Ordering Saved', 200);
            */
            $question_orders = $req->get('question_order');
            
            foreach($question_orders as $qo) {
                $question = $folder->questions()->find($qo['id']);
                $question->update(['order' => (int)$qo['order']]);
            }

            return response('Question Ordering Saved', 200);
        } else {
            return response('Invalid Permissions to Move Question', 403);
        }
    }

    public function resetQuestion(Request $req, $chime, $folder, $question) {
        $user = $req->user();
        $chime = (
            $user
            ->chimes()
            ->where('chime_id', $chime->id)
            ->first());
        
        if ($chime != null && $chime->pivot->permission_number >= 300) {
            
            $question->sessions()->delete();
            $question->current_session_id = null;
            $question->save();


            return response('Question Reset', 200);
        } else {
            return response('Invalid Permissions to Reset Question', 403);
        }
    }

    public function forceSync(Request $req, $chime, $folder) {
        if($folder->resource_link_pk > 0) {
            if(\App\Library\LTIProcessor::syncFolder($folder)) {
                return response()->json(["success"=>"success"]);
            }
        }
        else if($folder->lti_lineitem) {
            if(\App\Library\LTI13Processor::syncFolder($folder)) {
                return response()->json(["success"=>"success"]);
            }
        }
        return response('Failed to sync', 403);
        
    }
}
