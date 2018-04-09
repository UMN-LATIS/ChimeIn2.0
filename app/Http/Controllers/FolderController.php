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
        
        if ($chime->pivot->permission_number >= 200) {
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
        
        if ($chime->pivot->permission_number >= 200) {
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
        
        if ($chime->pivot->permission_number >= 200) {
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
    
    public function moveQuestionDown(Request $req) {
        $user = $req->user();
        $chime = (
            $user
            ->chimes()
            ->where('chime_id', $req->route('chime_id'))
            ->first());
        
        if ($chime->pivot->permission_number >= 200) {
            $folder = $chime->folders()->find($req->route('folder_id'));
            $question = $folder->questions()->find($req->route('question_id'));
            $temp = $question->order;
            $next_question = (
                $folder
                ->questions()
                ->where('order', '>', $question->order)
                ->first()
            );

            if ($next_question == null) {
                $next_question = $folder->questions()->orderBy('order')->first();
            }

            $question->update(['order' => $next_question->order]);
            $next_question->update(['order' => $temp]);
                    
            return response()->json([$question, $next_question]);
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
        
        if ($chime->pivot->permission_number >= 200) {
            $folder = $chime->folders()->find($req->route('folder_id'));
            $folder->questions()->find($req->route('question_id'))->delete();
        
            return response('Question Deleted', 200);
        } else {
            return response('Invalid Permissions to Delete Question', 403);
        }
    }
}
