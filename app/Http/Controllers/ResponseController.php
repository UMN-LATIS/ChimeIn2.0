<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\SubmitResponse;
use App\Question;
use DB;

use App\Chime;
use App\Session;
use App\Response;
use Auth;

class ResponseController extends Controller
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
    public function getResponse(Request $req) {
        $user = $req->user();


        // get all of the user's existing responses for this chime

        $responses = DB::table('responses')->where("user_id", $user->id)->join('sessions', 'responses.session_id', '=', 'sessions.id')->join('questions', 'sessions.question_id', '=', 'questions.id')->join('folders', 'questions.folder_id', '=', 'folders.id')->join('chimes', 'folders.chime_id', '=', 'chimes.id')->where('chimes.id', $req->route('chime_id'))->select('responses.*')->get();

        $responseModels = \App\Response::hydrate($responses->toArray()); 
        $responseModels->load("session.question");
        return response()->json($responseModels);
    }

    public function getQuestion(Request $req) {
        $user = $req->user();
        $chime = $user->chimes()->find($req->route('chime_id'));
        $session = $chime->sessions->find($req->route('session_id'));
        $question = $session->question()->get()->first();
        return response()->json($question);
    }

    public function createOrUpdateResponse(Chime $chime, Session $session, Response $response = null, Request $request) {
        $user = Auth::user();
        $chime = $user->chimes()->find($chime->id)->first();
        if(!$chime
            ->sessions()
            ->contains($session)) {
            // TODO ERROR
            dd($chime
            ->sessions());
            return "nope";
        }
        
        if($response) {
            $response->response_info = $request->get('response_info');
        }
        else {
            $response = $session->responses()->create([
                'response_info' => $request->get('response_info'),
                'user_id' => $user->id
            ]);
        }

        $response->save();
        
        event(new SubmitResponse($chime, $session, $response, $isEdit=true));

        return response()->json($response->load("session.question"));
    }

    public function updateResponse(Request $req) {
        $user = $req->user();
        $chime = $user->chimes()->find($req->route('chime_id'));
        $session = (
            $chime
            ->sessions()
            ->where('in_progress', true)
            ->find($req->route('session_id')));

        $found_response = (
            $session
            ->responses()
            ->where('user_id', $user->id)
            ->first());

        $found_response->update([
            'response_info' => json_encode($req->get('response_info'))
        ]);

        event(new SubmitResponse($chime, $session, $found_response));

        return response()->json($found_response);
    }
}
