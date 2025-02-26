<?php

namespace App\Http\Controllers;

use App\Chime;
use App\Events\EndSession;
use App\Folder;
use App\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Chime $chime, Folder $folder)
    {
        $user = $request->user();

        abort_unless($user->canEditChime($chime->id), 403, 'Invalid Permissions to Create Question');

        $validated = $request->validate([
            'question_text' => ['required', 'string'],
            'question_info' => ['required', 'array'],
            'anonymous' => ['nullable', 'boolean'],
            'allow_multiple' => ['nullable', 'boolean'],
        ]);

        $maxQuestionOrder = $folder->questions()->max('order') ?? 0;

        $question = Question::create([
            'text' => $validated['question_text'],
            'order' => $maxQuestionOrder + 1,
            'question_info' => $validated['question_info'],
            'anonymous' => $validated['anonymous'] ?? false,
            'folder_id' => $folder->id,
            'allow_multiple' => $validated['allow_multiple'] ?? false,
        ]);

        return response()->json($question);
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
                'anonymous' => $req->get('anonymous') ? $req->get('anonymous') : 0,
                'folder_id' => $req->get('folder_id'),
                'allow_multiple' => $req->get('allow_multiple') ? $req->get('allow_multiple') : 0,
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
            if ($currentSession) {
                $currentSession->touch();
                $question->current_session()->dissociate();
                $question->save();
                event(new EndSession($chime, $currentSession));
            }

            $question->delete();

            $i = 1;

            foreach ($folder->questions as $question) {
                $question->order = $i;
                $question->save();
                $i++;
            }

            return response('Question Deleted', 200);
        } else {
            return response('Invalid Permissions to Delete Question', 403);
        }
    }

    public function reset(Request $req, $chime, $folder, $question)
    {
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
