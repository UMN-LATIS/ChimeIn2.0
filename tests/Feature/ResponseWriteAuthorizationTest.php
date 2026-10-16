<?php

use App\Chime;
use App\Events\SubmitResponse;
use App\Http\Middleware\PreventRequestForgery;
use App\Question;
use App\Response;
use App\Session;
use App\User;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->withoutMiddleware(PreventRequestForgery::class);
    Event::fake([SubmitResponse::class]);

    $this->author = User::factory()->create();
    $this->otherParticipant = User::factory()->create();
    $this->nonMember = User::factory()->create();

    $this->chime = Chime::factory()
        ->withPresenter(User::factory()->create())
        ->hasAttached($this->author, ['permission_number' => CHIMEIN_PARTICIPANT])
        ->hasAttached($this->otherParticipant, ['permission_number' => CHIMEIN_PARTICIPANT])
        ->withContent(['folders' => 1, 'questions' => 2])
        ->create();

    [$openQuestion, $closedQuestion] = $this->chime->folders->first()->questions;

    $this->openSession = Session::factory()->create(['question_id' => $openQuestion->id]);
    $openQuestion->forceFill(['current_session_id' => $this->openSession->id])->save();

    $closedSession = Session::factory()->create(['question_id' => $closedQuestion->id]);

    $this->openResponse = Response::factory()->create([
        'session_id' => $this->openSession->id,
        'user_id' => $this->author->id,
        'response_info' => ['question_type' => Question::MULTIPLE_CHOICE_TYPE, 'choice' => 'original'],
    ]);
    $this->closedResponse = Response::factory()->create([
        'session_id' => $closedSession->id,
        'user_id' => $this->author->id,
        'response_info' => ['question_type' => Question::MULTIPLE_CHOICE_TYPE, 'choice' => 'original'],
    ]);
});

function responseUrl(Chime $chime, Session $session, ?Response $response = null): string {
    return "/api/chime/{$chime->id}/session/{$session->id}/response" . ($response ? "/{$response->id}" : '');
}

function changedAnswer(): array {
    return ['response_info' => ['question_type' => Question::MULTIPLE_CHOICE_TYPE, 'choice' => 'changed']];
}

it("rejects a participant overwriting another participant's response", function () {
    $this->actingAs($this->otherParticipant)
        ->putJson(responseUrl($this->chime, $this->openSession, $this->openResponse), changedAnswer())
        ->assertForbidden();

    expect($this->openResponse->fresh()->response_info['choice'])->toBe('original');
});

it('rejects editing a closed-session response through an open session', function () {
    $this->actingAs($this->author)
        ->putJson(responseUrl($this->chime, $this->openSession, $this->closedResponse), changedAnswer())
        ->assertForbidden();

    expect($this->closedResponse->fresh()->response_info['choice'])->toBe('original');
});

it('rejects a non-member submitting a response', function () {
    $this->actingAs($this->nonMember)
        ->putJson(responseUrl($this->chime, $this->openSession), changedAnswer())
        ->assertForbidden();
});

it('lets a participant edit their own response in an open session', function () {
    $this->actingAs($this->author)
        ->putJson(responseUrl($this->chime, $this->openSession, $this->openResponse), changedAnswer())
        ->assertOk();

    expect($this->openResponse->fresh()->response_info['choice'])->toBe('changed');
});

it('lets a participant create a response in an open session', function () {
    $this->actingAs($this->otherParticipant)
        ->putJson(responseUrl($this->chime, $this->openSession), changedAnswer())
        ->assertOk();

    expect($this->openSession->responses()->where('user_id', $this->otherParticipant->id)->exists())->toBeTrue();
});
