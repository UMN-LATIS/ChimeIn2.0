<?php

namespace App\Http\Controllers;

use App\Question;
use Illuminate\Http\Request;
use App\Events\EndSession;


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
    public function update(Request $req)
    {
        
        $user = $req->user();
        $chime = (
            $user
            ->chimes()
            ->where('chime_id', $req->route('chime_id'))
            ->first());
        
        if ($chime != null && $chime->pivot->permission_number >= 300) {
            $currentFolderId = (int) $req->route('folder_id');
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

            return response()->json($question);
        } else {
            return response('Invalid Permissions to Update Question', 403);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $req)
    {
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

    public function reset(Request $req, $chime, $folder, $question) {
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
}
