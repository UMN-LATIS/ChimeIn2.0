<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\SubmitResponse;
use App\Question;
use DB;

use App\Chime;
use App\Folder;
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
        // ordered by the most recent session first
        $responses = DB::table('responses')
            ->where("user_id", $user->id)
            ->join('sessions', 'responses.session_id', '=', 'sessions.id')
            ->join('questions', 'sessions.question_id', '=', 'questions.id')
            ->join('folders', 'questions.folder_id', '=', 'folders.id')
            ->join('chimes', 'folders.chime_id', '=', 'chimes.id')
            ->where('chimes.id', $req->route('chime_id'))
            ->select('responses.*')
            ->orderBy("responses.updated_at", "desc")
            // in cases of responses with the same updated_at, 
            // use the id to break the tie (makes tests deterministic)
            ->orderBy("responses.id", "desc")
            ->get();

        $responseModels = \App\Response::hydrate($responses->toArray()); 
        $responseModels->load("session.question", "session.question.folder");
        return response()->json($responseModels);
    }

    public function createOrUpdateResponse(Request $request, Chime $chime, Session $session, Response $response = null) {
        $user = Auth::user();

        $request->validate([
            'response_info' => 'required',
            'response_info.question_type' => 'required',
            'response_info.text' => 'max:10000',
        ], [
            'response_info.required' => 'Responses cannot be blank.',
            'response_info.text.max' => 'Response text cannot be longer than 10,000 characters.',
        ]);

        $chime = $user->chimes()->find($chime->id);

        if(!$chime->sessions()->contains($session)) {
            return response()->json(["message"=>'Session not found.'], 403);
        }

        if(!$session->question->current_session || $session->question->current_session->id != $session->id) {
            return response()->json(["message"=>'Session has been closed.'], 403);
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
        
        event(new SubmitResponse(chime: $chime, session: $session, response: $response, user: $user, isEdit: true));

        return response()->json($response->load("session.question", "session.question.folder"));
    }

    public function deleteResponse(Chime $chime, Folder $folder, Response $response, Request $request) {
        $user = Auth::user();

        $chime = $user->chimes()->find($chime->id);
        
        if ($chime != null && $chime->pivot->permission_number >= 300) {
            $response->delete();
            return response('Response Deleted', 200);
        } else {
            return response('Invalid Permissions to Delete Response', 403);
        }


    }

}
