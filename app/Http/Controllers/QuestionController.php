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
    public function update(Request $request, Chime $chime, Folder $folder, Question $question)
    {
        $user = $request->user();

        abort_unless($user->canEditChime($chime->id), 403, 'Invalid Permissions to Update Question');

        $validated = $request->validate([
            'question_text' => ['required', 'string'],
            'question_info' => ['required', 'array'],
            'anonymous' => ['nullable', 'boolean'],
            'allow_multiple' => ['nullable', 'boolean'],
            'folder_id' => ['required', 'integer', 'exists:folders,id'],
        ]);

        $currentFolderId = $folder->id;
        $destFolderId = $validated['folder_id'];
        $isMovingFolders = $currentFolderId !== $destFolderId;
        $destFolder = $isMovingFolders
            ? $chime->folders()->find($destFolderId)
            : $folder;

        abort_unless($destFolder, 400, 'Valid destination folder not found');

        // If moving folders, moved to the end of the dest folder
        $questionOrder = $isMovingFolders
            ? ($destFolder->questions()->max('order') ?? 0) + 1
            : $question->order;

        $question->update([
            'text' => $validated['question_text'],
            'question_info' => $validated['question_info'],
            'anonymous' => $validated['anonymous'] ?? false,
            'folder_id' => $destFolderId,
            'allow_multiple' => $validated['allow_multiple'] ?? false,
            'order' => $questionOrder,
        ]);

        return response()->json($question);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Chime $chime, Folder $folder, Question $question)
    {
        $user = $request->user();

        abort_unless($user->canEditChime($chime->id), 403, 'Invalid Permissions to Delete Question');

        $currentSession = $question->current_session;
        if ($currentSession) {
            $currentSession->touch();
            $question->current_session()->dissociate();
            $question->save();
            event(new EndSession($chime, $currentSession));
        }

        $question->delete();

        // Reorder remaining questions in the folder
        $folder->questions->each(function ($q, $i) {
            $q->order = $i + 1;
            $q->save();
        });

        return response('Question Deleted', 200);
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
