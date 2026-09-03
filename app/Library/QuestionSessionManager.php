<?php

namespace App\Library;

use App\Chime;
use App\Events\EndSession;
use App\Events\StartSession;
use App\Question;
use App\Session;

/**
 * Opening and closing a question, shared by the web presenter and the Office add-in.
 */
class QuestionSessionManager
{
    /**
     * Returns the existing session if the question is already open.
     */
    public static function open(Chime $chime, Question $question): Session
    {
        if ($question->current_session) {
            return $question->current_session;
        }

        $session = $question->sessions()->create();

        $question->current_session()->associate($session);
        $question->save();

        event(new StartSession($chime, $session));

        return $session;
    }

    public static function close(Chime $chime, Question $question): ?Session
    {
        $session = $question->current_session;

        if (! $session) {
            return null;
        }

        $session->touch();
        $question->current_session()->dissociate();
        $question->save();

        event(new EndSession($chime, $session));

        return $session;
    }
}
