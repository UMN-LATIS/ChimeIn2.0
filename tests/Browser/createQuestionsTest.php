<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class createQuestionsTest extends DuskTestCase
{
    // use DatabaseMigrations;
    public $admin = null;
    public $chime = null;
    public function setUp(): void {
        parent::setUp();

        $this->admin = factory(\App\User::class)->create();
        $this->chime = factory(\App\Chime::class)->create();
        $this->folder = factory(\App\Folder::class)->make();
        $this->chime->folders()->save($this->folder);
        $this->admin->chimes()->attach($this->chime, [
            'permission_number' => 300
        ]);
    }

     public function testQuestionWindow() {

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)->visit('/chime/' . $this->chime->id . '/folder/' . $this->folder->id);
            $browser->pause(500)->click('@new-question-button');
            $browser->pause(2000)->assertSee("Add a Question");
        });

    }


}
