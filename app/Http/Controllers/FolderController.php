<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FolderController extends Controller
{

    public function show(Request $req, $chime, $folder, $includeQuestions=false) {
        $user = $req->user();
        $chime = $user->chimes()->where('chime_id', $chime->id)->first();
        
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

            $new_question = \App\Question::create([
                'text' => $req->get('question_text'),
                'order' => $order_num,
                'question_info' => $req->get('question_info'),
                'anonymous'=>$req->get('anonymous'),
                'folder_id'=>$req->get('folder_id'),
                'allow_multiple' => $req->get('allow_multiple')
            ]);
                    
            return response()->json($new_question);
        } else {
            return response('Invalid Permissions to Create Question', 403);
        }
    }

    public function updateQuestion(Request $req) {

        $user = $req->user();
        $chime = (
            $user
            ->chimes()
            ->where('chime_id', $req->route('chime_id'))
            ->first());
        
        if ($chime != null && $chime->pivot->permission_number >= 300) {

            $folder = $chime->folders()->find($req->route('folder_id'));
            $question = $folder->questions()->find($req->route('question_id'));
            
            $question->update([
                'text' => $req->get('question_text'),
                'question_info' => $req->get('question_info'),
                'anonymous'=>$req->get('anonymous'),
                'folder_id'=>$req->get('folder_id'),
                'allow_multiple' => $req->get('allow_multiple')
            ]);

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
            $folder->questions()->find($req->route('question_id'))->delete();
        
            return response('Question Deleted', 200);
        } else {
            return response('Invalid Permissions to Delete Question', 403);
        }
    }
}
