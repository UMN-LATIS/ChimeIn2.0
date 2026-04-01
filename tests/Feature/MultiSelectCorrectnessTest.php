<?php

use App\Chime;
use App\Folder;
use App\Question;
use App\Response;
use App\Session;
use App\User;

function createMultiSelectQuestion(Folder $folder, array $choices): Question {
    return Question::factory()->create([
        'folder_id' => $folder->id,
        'question_info' => [
            'question_type' => Question::MULTIPLE_CHOICE_TYPE,
            'question_responses' => $choices,
        ],
    ]);
}

function createResponseForQuestion(Question $question, User $user, mixed $choice): Response {
    $session = Session::factory()->create([
        'question_id' => $question->id,
    ]);

    return Response::factory()->create([
        'session_id' => $session->id,
        'user_id' => $user->id,
        'response_info' => [
            'question_type' => Question::MULTIPLE_CHOICE_TYPE,
            'choice' => $choice,
        ],
    ]);
}

it('marks a multi-select response as correct when it includes at least one correct answer', function () {
    $presenter = User::factory()->create();
    $participant = User::factory()->create();

    $chime = Chime::factory()
        ->withPresenter($presenter)
        ->hasAttached($participant, ['permission_number' => 100])
        ->create();

    $folder = Folder::factory()->create(['chime_id' => $chime->id]);

    $question = createMultiSelectQuestion($folder, [
        ['text' => 'Option A', 'correct' => true],
        ['text' => 'Option B', 'correct' => false],
        ['text' => 'Option C', 'correct' => true],
    ]);

    // Multi-select: user chose Option A and Option B (array format)
    $response = createResponseForQuestion($question, $participant, ['Option A', 'Option B']);

    expect($response->isCorrect())->toBeTrue();
});

it('marks a multi-select response as incorrect when it includes zero correct answers', function () {
    $presenter = User::factory()->create();
    $participant = User::factory()->create();

    $chime = Chime::factory()
        ->withPresenter($presenter)
        ->hasAttached($participant, ['permission_number' => 100])
        ->create();

    $folder = Folder::factory()->create(['chime_id' => $chime->id]);

    $question = createMultiSelectQuestion($folder, [
        ['text' => 'Option A', 'correct' => true],
        ['text' => 'Option B', 'correct' => false],
        ['text' => 'Option C', 'correct' => false],
    ]);

    // Multi-select: user chose only incorrect answers
    $response = createResponseForQuestion($question, $participant, ['Option B', 'Option C']);

    expect($response->isCorrect())->toBeFalse();
});

it('marks a single-select correct response as correct (regression)', function () {
    $presenter = User::factory()->create();
    $participant = User::factory()->create();

    $chime = Chime::factory()
        ->withPresenter($presenter)
        ->hasAttached($participant, ['permission_number' => 100])
        ->create();

    $folder = Folder::factory()->create(['chime_id' => $chime->id]);

    $question = createMultiSelectQuestion($folder, [
        ['text' => 'Option A', 'correct' => true],
        ['text' => 'Option B', 'correct' => false],
    ]);

    // Single-select: user chose the correct answer (string format)
    $response = createResponseForQuestion($question, $participant, 'Option A');

    expect($response->isCorrect())->toBeTrue();
});

it('treats non-multiple-choice responses as always correct', function (string $questionType, array $responseInfo) {
    $participant = User::factory()->create();
    $chime = Chime::factory()
        ->hasAttached($participant, ['permission_number' => 100])
        ->create();

    $folder = Folder::factory()->create(['chime_id' => $chime->id]);

    $question = Question::factory()->create([
        'folder_id' => $folder->id,
        'question_info' => ['question_type' => $questionType, 'question_responses' => []],
    ]);

    $session = Session::factory()->create(['question_id' => $question->id]);
    $response = Response::factory()->create([
        'session_id' => $session->id,
        'user_id' => $participant->id,
        'response_info' => $responseInfo,
    ]);

    expect($response->isCorrect())->toBeTrue();
})->with([
    'free response' => [
        Question::FREE_RESPONSE_TYPE,
        ['question_type' => Question::FREE_RESPONSE_TYPE, 'text' => 'Some answer'],
    ],
    'slider' => [
        Question::SLIDER_TYPE,
        ['question_type' => Question::SLIDER_TYPE, 'choice' => '75'],
    ],
    'image response' => [
        Question::IMAGE_RESPONSE_TYPE,
        ['question_type' => Question::IMAGE_RESPONSE_TYPE, 'image_name' => 'photo.jpg'],
    ],
    'heatmap' => [
        Question::HEATMAP_RESPONSE_TYPE,
        ['question_type' => Question::HEATMAP_RESPONSE_TYPE, 'image_coordinates' => ['coordinate_x' => 10, 'coordinate_y' => 20]],
    ],
    'text heatmap' => [
        Question::TEXT_HEATMAP_RESPONSE_TYPE,
        ['question_type' => Question::TEXT_HEATMAP_RESPONSE_TYPE, 'startOffset' => 0, 'endOffset' => 5],
    ],
]);

it('treats any response as correct when no correct answers are flagged', function () {
    $presenter = User::factory()->create();
    $participant = User::factory()->create();

    $chime = Chime::factory()
        ->withPresenter($presenter)
        ->hasAttached($participant, ['permission_number' => 100])
        ->create();

    $folder = Folder::factory()->create(['chime_id' => $chime->id]);

    $question = createMultiSelectQuestion($folder, [
        ['text' => 'Option A', 'correct' => false],
        ['text' => 'Option B', 'correct' => false],
    ]);

    $response = createResponseForQuestion($question, $participant, ['Option A']);

    expect($response->isCorrect())->toBeTrue();
});
