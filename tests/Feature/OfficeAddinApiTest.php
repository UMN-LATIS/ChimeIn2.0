<?php

use App\Chime;
use App\Constants\OfficeScope;
use App\Folder;
use App\Question;
use App\User;

function presenterOf(Chime $chime): User
{
    $presenter = User::factory()->create();
    $chime->users()->attach($presenter, ['permission_number' => CHIMEIN_PRESENTER]);

    return $presenter;
}

function browseToken(User $user): string
{
    return $user->createToken(OfficeScope::BROWSE_TOKEN_NAME, [OfficeScope::BROWSE])->plainTextToken;
}

function chimeToken(User $user, Chime $chime): string
{
    return $user->createToken(
        OfficeScope::tokenNameForChime($chime),
        OfficeScope::allForChime($chime->id)
    )->plainTextToken;
}

function questionIn(Chime $chime): Question
{
    $folder = Folder::factory()->create(['chime_id' => $chime->id]);

    return Question::factory()->create(['folder_id' => $folder->id]);
}

it('rejects an unauthenticated request instead of creating a guest user', function () {
    $before = User::count();

    $this->getJson('/api/office/chimes')->assertStatus(401);

    expect(User::count())->toBe($before);
});

it('lists only chimes the user presents', function () {
    $chime = Chime::factory()->create();
    $presenter = presenterOf($chime);

    $participantChime = Chime::factory()->create();
    $participantChime->users()->attach($presenter, ['permission_number' => CHIMEIN_PARTICIPANT]);

    $response = $this->withToken(browseToken($presenter))
        ->getJson('/api/office/chimes')
        ->assertOk();

    expect(collect($response->json())->pluck('id')->all())->toBe([$chime->id]);
});

it('will not let a participant mint a chime token', function () {
    $chime = Chime::factory()->create();
    $user = User::factory()->create();
    $chime->users()->attach($user, ['permission_number' => CHIMEIN_PARTICIPANT]);

    $this->withToken(browseToken($user))
        ->postJson("/api/office/chimes/{$chime->id}/token")
        ->assertStatus(403);
});

it('issues a chime token scoped to a single chime', function () {
    $chime = Chime::factory()->create();
    $presenter = presenterOf($chime);

    $response = $this->withToken(browseToken($presenter))
        ->postJson("/api/office/chimes/{$chime->id}/token")
        ->assertOk();

    $token = Laravel\Sanctum\PersonalAccessToken::findToken($response->json('token'));

    expect($token->abilities)->toBe(OfficeScope::allForChime($chime->id));
});

it('refuses a chime token used against a different chime', function () {
    $chime = Chime::factory()->create();
    $otherChime = Chime::factory()->create();
    $presenter = presenterOf($chime);
    $otherChime->users()->attach($presenter, ['permission_number' => CHIMEIN_PRESENTER]);

    $this->withToken(chimeToken($presenter, $chime))
        ->getJson("/api/office/chimes/{$otherChime->id}")
        ->assertStatus(403);
});

it('refuses a browse token for presenter actions', function () {
    $chime = Chime::factory()->create();
    $presenter = presenterOf($chime);
    $question = questionIn($chime);

    $this->withToken(browseToken($presenter))
        ->postJson("/api/office/chimes/{$chime->id}/questions/{$question->id}/open")
        ->assertStatus(403);
});

it('stops honoring a token once the user is no longer a presenter', function () {
    $chime = Chime::factory()->create();
    $presenter = presenterOf($chime);
    $token = chimeToken($presenter, $chime);

    $chime->users()->updateExistingPivot($presenter->id, ['permission_number' => CHIMEIN_PARTICIPANT]);

    $this->withToken($token)
        ->getJson("/api/office/chimes/{$chime->id}")
        ->assertStatus(403);
});

it('opens and closes a question', function () {
    $chime = Chime::factory()->create();
    $presenter = presenterOf($chime);
    $question = questionIn($chime);
    $token = chimeToken($presenter, $chime);

    $this->withToken($token)
        ->postJson("/api/office/chimes/{$chime->id}/questions/{$question->id}/open")
        ->assertOk();

    expect($question->fresh()->current_session_id)->not->toBeNull();

    $this->withToken($token)
        ->postJson("/api/office/chimes/{$chime->id}/questions/{$question->id}/close")
        ->assertOk();

    expect($question->fresh()->current_session_id)->toBeNull();
});

it('does not leak a question belonging to another chime', function () {
    $chime = Chime::factory()->create();
    $presenter = presenterOf($chime);

    $otherChime = Chime::factory()->create();
    $otherQuestion = questionIn($otherChime);

    $this->withToken(chimeToken($presenter, $chime))
        ->getJson("/api/office/chimes/{$chime->id}/questions/{$otherQuestion->id}")
        ->assertStatus(404);
});
