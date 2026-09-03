<?php

namespace App\Http\Controllers\Office;

use App\Chime;
use App\Constants\OfficeScope;
use App\Folder;
use App\Http\Controllers\Controller;
use App\Library\QuestionSessionManager;
use App\Question;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Read/present endpoints for the PowerPoint content add-in.
 *
 * Chime scope is enforced by the office.scope middleware; these methods only need
 * to confirm that nested folders and questions really belong to the chime.
 */
class OfficeApiController extends Controller
{
    public function me(Request $request): JsonResponse
    {
        /** @var PersonalAccessToken $token */
        $token = $request->user()->currentAccessToken();

        return response()->json([
            'user' => [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
            ],
            'chime_id' => OfficeScope::chimeIdFor($token),
            'abilities' => $token->abilities,
        ]);
    }

    /**
     * Chimes this user can present, most recently used by the add-in first, so the
     * add-in can preselect a chime on a machine it has not seen before.
     */
    public function chimes(Request $request): JsonResponse
    {
        $chimes = $request->user()
            ->chimes()
            ->wherePivot('permission_number', '>=', CHIMEIN_PRESENTER)
            ->get(['chimes.id', 'chimes.name', 'chimes.access_code']);

        $recency = $this->chimeRecency($request);

        return response()->json(
            $chimes
                ->sortByDesc(fn (Chime $chime) => $recency[$chime->id] ?? 0)
                ->values()
        );
    }

    /**
     * Mint the chime-scoped token that gets embedded in the .pptx.
     */
    public function issueChimeToken(Request $request, Chime $chime): JsonResponse
    {
        $membership = $request->user()->chimes()->where('chime_id', $chime->id)->first();

        if ($membership === null || $membership->pivot->permission_number < CHIMEIN_PRESENTER) {
            return response()->json(['message' => 'You are not a presenter for this chime.'], 403);
        }

        $name = OfficeScope::tokenNameForChime($chime);

        // One live token per presenter per chime keeps the revoke story simple.
        $request->user()->tokens()->where('name', $name)->delete();

        $token = $request->user()->createToken(
            $name,
            OfficeScope::allForChime($chime->id),
            now()->addDays(config('office.chime_token_days'))
        );

        return response()->json([
            'token' => $token->plainTextToken,
            'expires_at' => $token->accessToken->expires_at,
            'chime' => $membership->only(['id', 'name', 'access_code']),
        ]);
    }

    public function chime(Request $request, Chime $chime): JsonResponse
    {
        return response()->json(
            $chime->load(['folders' => fn ($query) => $query->orderBy('order')])
        );
    }

    public function folder(Request $request, Chime $chime, Folder $folder): JsonResponse
    {
        $this->assertFolderInChime($chime, $folder);

        $folder->load(['questions' => fn ($query) => $query->orderBy('order')]);

        return response()->json($folder);
    }

    /**
     * A single question with every session and response, which is what the results
     * components render.
     */
    public function question(Request $request, Chime $chime, Question $question): JsonResponse
    {
        $this->assertQuestionInChime($chime, $question);

        return response()->json($question->load('folder', 'sessions.responses'));
    }

    public function open(Request $request, Chime $chime, Question $question): JsonResponse
    {
        $this->assertQuestionInChime($chime, $question);

        QuestionSessionManager::open($chime, $question);

        return response()->json($question->fresh()->load('folder', 'sessions.responses'));
    }

    public function close(Request $request, Chime $chime, Question $question): JsonResponse
    {
        $this->assertQuestionInChime($chime, $question);

        QuestionSessionManager::close($chime, $question);

        return response()->json($question->fresh()->load('folder', 'sessions.responses'));
    }

    /**
     * Names for attributing responses in the results view.
     */
    public function users(Request $request, Chime $chime): JsonResponse
    {
        return response()->json(
            $chime->users()->get(['users.id', 'users.name', 'users.email'])
        );
    }

    private function assertFolderInChime(Chime $chime, Folder $folder): void
    {
        abort_unless($folder->chime_id === $chime->id, 404);
    }

    private function assertQuestionInChime(Chime $chime, Question $question): void
    {
        abort_unless($question->folder?->chime_id === $chime->id, 404);
    }

    /**
     * @return array<int, int>
     */
    private function chimeRecency(Request $request): array
    {
        return $request->user()
            ->tokens()
            ->where('name', 'like', 'office-chime-%')
            ->get()
            ->mapWithKeys(function (PersonalAccessToken $token) {
                $chimeId = OfficeScope::chimeIdFor($token);

                return $chimeId === null
                    ? []
                    : [$chimeId => ($token->last_used_at ?? $token->created_at)?->getTimestamp() ?? 0];
            })
            ->all();
    }
}
