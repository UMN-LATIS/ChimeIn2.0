<?php

use App\Chime;
use App\Http\Middleware\PreventRequestForgery;
use App\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->withoutMiddleware(PreventRequestForgery::class);

    $this->presenter = User::factory()->create();
    $this->participant = User::factory()->create();
    $this->nonMember = User::factory()->create();

    $this->chime = Chime::factory()
        ->withPresenter($this->presenter)
        ->hasAttached($this->participant, ['permission_number' => CHIMEIN_PARTICIPANT])
        ->withContent(['folders' => 2])
        ->create(['require_login' => true]);
});

function folderOrderSwap(Chime $chime): array {
    [$first, $second] = $chime->folders;

    return ['folders' => [
        ['id' => $first->id, 'order' => 1],
        ['id' => $second->id, 'order' => 0],
    ]];
}

function linkToLti13(Chime $chime): void {
    $resourceLinkId = DB::table('lti13_resource_links')->insertGetId([
        'resource_link' => 'test-resource-link',
        'endpoint' => '{}',
    ]);
    $chime->forceFill(['lti13_resource_link_id' => $resourceLinkId])->save();
    $chime->folders->first()->forceFill(['lti_lineitem' => 'https://lms.test/lineitem/1'])->save();
}

it('rejects a participant changing chime settings', function () {
    $this->actingAs($this->participant)
        ->patchJson("/api/chime/{$this->chime->id}", ['require_login' => false])
        ->assertForbidden();

    expect($this->chime->fresh()->require_login)->toBeTrue();
});

it('rejects a non-member changing chime settings', function () {
    $this->actingAs($this->nonMember)
        ->patchJson("/api/chime/{$this->chime->id}", ['require_login' => false])
        ->assertNotFound();

    expect($this->chime->fresh()->require_login)->toBeTrue();
});

it('lets a presenter change chime settings', function () {
    $this->actingAs($this->presenter)
        ->patchJson("/api/chime/{$this->chime->id}", ['require_login' => false])
        ->assertOk();

    expect($this->chime->fresh()->require_login)->toBeFalse();
});

it('rejects a participant reordering folders', function () {
    $ordersBefore = $this->chime->folders->pluck('order', 'id');

    $this->actingAs($this->participant)
        ->putJson("/api/chime/{$this->chime->id}", folderOrderSwap($this->chime))
        ->assertForbidden();

    expect($this->chime->fresh()->folders->pluck('order', 'id'))->toEqual($ordersBefore);
});

it('lets a presenter reorder folders', function () {
    $this->actingAs($this->presenter)
        ->putJson("/api/chime/{$this->chime->id}", folderOrderSwap($this->chime))
        ->assertOk();

    [$first, $second] = $this->chime->fresh()->folders;
    expect($first->order)->toEqual(1)
        ->and($second->order)->toEqual(0);
});

it('rejects a participant syncing chime grades to the LMS', function () {
    linkToLti13($this->chime);

    $this->actingAs($this->participant)
        ->postJson("/api/chime/{$this->chime->id}/sync")
        ->assertForbidden();
});

it('rejects a participant syncing folder grades to the LMS', function () {
    linkToLti13($this->chime);
    $folder = $this->chime->folders->first();

    $this->actingAs($this->participant)
        ->postJson("/api/chime/{$this->chime->id}/folder/{$folder->id}/sync")
        ->assertForbidden();
});
