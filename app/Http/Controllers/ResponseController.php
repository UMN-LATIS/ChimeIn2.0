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

}
