<?php

namespace App\Http\Controllers;

use App\Chime;
use App\Folder;
use App\Question;
use App\Session;
use Illuminate\Http\Request;
use App\Response;

class FolderParticipationContoller extends Controller
{
    public function index(Request $request, Chime $chime, Folder $folder)
    {
        $user = $request->user();
        abort_unless($chime->hasPresenter($user), 403);

        $responses = $folder->questions
            ->flatmap(function (Question $question) {
                return $question->sessions;
            })
            ->flatmap(function (Session $session) {
                return $session->responses;
            })
            ->map(function (Response $response) use ($folder) {
                return [
                    ...$response->toArray(),
                    'is_correct' => $response->isCorrect(),
                    'question_id' => $response->getQuestion()->id,
                ];
            });

        return [
            'presenters' => $chime->presenters,
            'participants' => $chime->participants,
            'responses' => $responses,
        ];
    }
}
