<?php

namespace App\Http\Controllers;

use App\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $req)
    {
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

    /**
     * Display the specified resource.
     */
    public function show(Question $question)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Question $question)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Question $question)
    {
        //
    }
}
