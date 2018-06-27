<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\StartSession;
use App\Events\EndSession;
use App\Events\ChangeSessionStatus;

class PresentController extends Controller
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
    public function index(Request $req) {
        $user = $req->user();
        $chime = (
            $user
            ->chimes()
            ->where('chime_id', $req->route('chime_id'))
            ->first());
        
        if ($chime != null && $chime->pivot->permission_number >= 200) {
            return view('present', ['user' => $user, 'chime'=>$chime]);
        } else {
            return Response('Invalid permissions to view present page', 405);
        }
    }

    public function getSessions(Request $req) {
        $user = $req->user();
        $chime = (
            $user
            ->chimes()
            ->where('chime_id', $req->route('chime_id'))
            ->first());
        
        if ($chime != null && $chime->pivot->permission_number >= 200) {

            $folder = $chime->folders()->find($req->route('folder_id'));
            $question = $folder->questions()->where("id", $req->route('question_id'))->with('sessions.responses')->first();


            return response()->json($question);
        } else {
            return response('Invalid Permissions to Get Sessions', 403);
        }
    }

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

    public function getResponses(Request $req) {
        $user = $req->user();

        $chime = (
            $user
            ->chimes()
            ->where('chime_id', $req->route('chime_id'))
            ->first());

        if ($chime != null && $chime->pivot->permission_number >= 200) {
            $folder = $chime->folders()->find($req->route('folder_id'));
            $question = $folder->questions()->find($req->route('question_id'));
            $session = $question->sessions()->find($req->route('session_id'));

            return response()->json($session->responses()->with('user')->get());
        } else {
            return response('Invalid Permissions to get responses', 403);
        }
    }
}