<?php

use App\Chime;
use App\User;

beforeEach(function () {
    $this->presenter = User::factory()->create();
    $this->participant = User::factory()->create();

    $this->chime = Chime::factory()
        ->withPresenter($this->presenter)
        ->hasAttached($this->participant, ['permission_number' => CHIMEIN_PARTICIPANT])
        ->create(['require_login' => true, 'access_code' => 'secret-join-code']);
});

dataset('lti settings routes', ['lti/saveLTISettings', 'lti13/saveLTISettings']);

it('rejects an anonymous visitor saving LTI settings', function (string $route) {
    $this->putJson("/{$route}/{$this->chime->id}", ['require_login' => false])
        ->assertNotFound();

    expect($this->chime->fresh()->require_login)->toBeTrue();
})->with('lti settings routes');

it('rejects a participant saving LTI settings', function (string $route) {
    $this->actingAs($this->participant)
        ->putJson("/{$route}/{$this->chime->id}", ['require_login' => false])
        ->assertForbidden();

    expect($this->chime->fresh()->require_login)->toBeTrue();
})->with('lti settings routes');

it("rejects importing questions from a chime the presenter doesn't present", function (string $route) {
    $otherChime = Chime::factory()
        ->withPresenter(User::factory()->create())
        ->withContent()
        ->create();

    $this->actingAs($this->presenter)
        ->putJson("/{$route}/{$this->chime->id}", ['import_chime' => $otherChime->id])
        ->assertNotFound();

    expect($this->chime->folders()->count())->toBe(0);
})->with('lti settings routes');

it("doesn't show other chimes' join codes on the LTI selection demo page", function () {
    $this->get('/ltiSelectionPromptDemo')
        ->assertDontSee('secret-join-code');
});
