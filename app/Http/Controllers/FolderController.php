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



    public function createQuestion(Request $req) {
        $user = $req->user();
        $chime = (
            $user
            ->chimes()
            ->where('chime_id', $req->route('chime_id'))
            ->first());
        
        if ($chime != null && $chime->pivot->permission_number >= 300) {
            $folder = $chime->folders()->find($req->route('folder_id'));
            $highest = $folder->questions()->max('order');
            $order_num = 1;

            if ($highest != null) {
                $order_num = $highest + 1;
            }

            if(!$req->get('question_text')) {
                return response('Question Text Cannot be Blank', 500);
            }

            $new_question = \App\Question::create([
                'text' => $req->get('question_text'),
                'order' => $order_num,
                'question_info' => $req->get('question_info'),
                'anonymous'=>$req->get('anonymous')?$req->get('anonymous'):0,
                'folder_id'=>$req->get('folder_id'),
                'allow_multiple' => $req->get('allow_multiple')?$req->get('allow_multiple'):0
            ]);
                    
            return response()->json($new_question);
        } else {
            return response('Invalid Permissions to Create Question', 403);
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

    public function updateQuestion(Request $req) {

        $user = $req->user();
        $chime = (
            $user
            ->chimes()
            ->where('chime_id', $req->route('chime_id'))
            ->first());
        
        if ($chime != null && $chime->pivot->permission_number >= 300) {
            $currentFolderId = $req->route('folder_id');
            $destFolderId = $req->get('folder_id');
            $currentFolder = $chime->folders()->find($currentFolderId);
            $question = $currentFolder->questions()->find($req->route('question_id'));

            // if moving folders, we need to update the order
            $questionOrder = $question->order;
            if ($currentFolderId !== $destFolderId) {
                $destFolder = $chime->folders()->find($destFolderId);
                $questionOrder = $destFolder->questions()->max('order') + 1;
            }

            $question->update([
                'text' => $req->get('question_text'),
                'question_info' => $req->get('question_info'),
                'anonymous'=>$req->get('anonymous')?$req->get('anonymous'):0,
                'folder_id'=>$req->get('folder_id'),
                'allow_multiple' => $req->get('allow_multiple')?$req->get('allow_multiple'):0,
                'order' => $questionOrder,
            ]);

            $question->refresh();

            return response()->json($question);
        } else {
            return response('Invalid Permissions to Update Question', 403);
        }
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

    public function deleteQuestion(Request $req) {
        $user = $req->user();
        $chime = (
            $user
            ->chimes()
            ->where('chime_id', $req->route('chime_id'))
            ->first());
        
        if ($chime != null && $chime->pivot->permission_number >= 300) {
            $folder = $chime->folders()->find($req->route('folder_id'));
            
            
            $question = $folder->questions()->find($req->route('question_id'));

            $currentSession = $question->current_session;
            if($currentSession) {
                $currentSession->touch();
                $question->current_session()->dissociate();
                $question->save();
                event(new EndSession($chime, $currentSession));
            }
            
            $question->delete();
            
            $i = 1;

            foreach($folder->questions as $question) {
                $question->order = $i;
                $question->save();
                $i++;
            }


            return response('Question Deleted', 200);
        } else {
            return response('Invalid Permissions to Delete Question', 403);
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
