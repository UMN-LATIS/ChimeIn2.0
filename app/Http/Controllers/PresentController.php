<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\StartSession;
use App\Events\EndSession;
use App\Events\ChangeSessionStatus;

class PresentController extends Controller
{


    public function startSession(Request $req) {
        $user = $req->user();
        $chime = (
            $user
            ->chimes()
            ->where('chime_id', $req->route('chime_id'))
            ->first());
        
        if ($chime != null && $chime->pivot->permission_number >= 200) {
            $folder = $chime->folders()->find($req->route('folder_id'));
            $question = $folder->questions()->find($req->route('question_id'));

            $new_session = $question->sessions()->create();

            $question->current_session()->associate($new_session);
            $question->save();

            // $question->save();
            event(new StartSession($chime, $new_session));

            return response()->json($new_session);
        } else {
            return response('Invalid Permissions to Start Session', 403);
        }
    }

    public function stopSession(Request $req) {
        $user = $req->user();
        $chime = (
            $user
            ->chimes()
            ->where('chime_id', $req->route('chime_id'))
            ->first());
        
        if ($chime != null && $chime->pivot->permission_number >= 200) {
            $folder = $chime->folders()->find($req->route('folder_id'));
            
            $question = $folder->questions()->find($req->route('question_id'));
            $currentSession = $question->current_session;
            $question->current_session()->dissociate();
            $question->save();
            event(new EndSession($chime, $currentSession));

            return response()->json($question);
        } else {
            return response('Invalid Permissions to Stop Session', 403);
        }
    }

}