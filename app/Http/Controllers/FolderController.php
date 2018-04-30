<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FolderController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function getQuestions(Request $req) {
        $user = $req->user();
        $chime = (
            $user
            ->chimes()
            ->where('chime_id', $req->route('chime_id'))
            ->first());
        
        if ($chime != null && $chime->pivot->permission_number >= 200) {
            $folder = $chime->folders()->find($req->route('folder_id'));
            $questions = $folder->questions()->orderBy('order')->get();
            
            return response()->json([
                'questions' => $questions
            ]);
        } else {
            return response('Invalid Permissions to Get Questions', 403);
        }
    }

    public function createQuestion(Request $req) {
        $user = $req->user();
        $chime = (
            $user
            ->chimes()
            ->where('chime_id', $req->route('chime_id'))
            ->first());
        
        if ($chime != null && $chime->pivot->permission_number >= 200) {
            $folder = $chime->folders()->find($req->route('folder_id'));
            $highest = $folder->questions()->max('order');
            $order_num = 1;

            if ($highest != null) {
                $order_num = $highest + 1;
            }

            $new_question = $folder->questions()->create([
                'text' => $req->get('question_text'),
                'order' => $order_num,
                'question_info' => json_encode($req->get('question_info'))
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
        
        if ($chime != null && $chime->pivot->permission_number >= 200) {
            $folder = $chime->folders()->find($req->route('folder_id'));
            $question = $folder->questions()->find($req->route('question_id'));
            $question->update([
                'text' => $req->get('question_text'),
                'question_info' => json_encode($req->get('question_info'))
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
        
        if ($chime != null && $chime->pivot->permission_number >= 200) {
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
        
        if ($chime != null && $chime->pivot->permission_number >= 200) {
            $folder = $chime->folders()->find($req->route('folder_id'));
            $folder->questions()->find($req->route('question_id'))->delete();
        
            return response('Question Deleted', 200);
        } else {
            return response('Invalid Permissions to Delete Question', 403);
        }
    }
}
