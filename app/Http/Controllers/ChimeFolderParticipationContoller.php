<?php

namespace App\Http\Controllers;

use App\Chime;
use App\Folder;
use App\Question;
use App\Session;
use Error;
use Illuminate\Http\Request;

class ChimeFolderParticipationContoller extends Controller
{
    public function index(Request $request, Chime $chime, Folder $folder)
    {
        $user = $request->user();
        abort_unless($chime->hasPresenter($user), 403);

        $responses = $folder->questions->flatmap(function (Question $question) {
            return $question->sessions;
        })->flatmap(function (Session $session) {
            return $session->responses;
        });

        return [
            'presenters' => $chime->presenters,
            'participants' => $chime->participants,
            // 'user' => $user,
            // 'chime' => $chime,
            // 'folder' => $folder,
            // 'questions' => $folder->questions,
            // // 'sessions' => $sessions,
            'responses' => $responses,
        ];
    }
}
