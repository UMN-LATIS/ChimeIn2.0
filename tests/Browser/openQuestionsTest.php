<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class openQuestionsTest extends DuskTestCase
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

        $new_question = \App\Question::create([
                'text' => "<p>test</p>",
                'order' => 0,
                'question_info' => ["question_type"=>"multiple_choice","question_responses"=>["one","two","three"]],
                'anonymous'=>0,
                'folder_id'=>$this->folder->id,
                'allow_multiple' => 0
            ]);
    }

    public function testOpenQuestion() {

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)->visit('/chime/' . $this->chime->id . '/folder/' . $this->folder->id);
            $browser->pause(500)->assertSee("test");
        });

        $this->browse(function (Browser $browser1, Browser $browser2) {
            $browser1->loginAs($this->admin)->visit('/chime/' . $this->chime->id . '/folder/' . $this->folder->id);
            $browser1->pause(500)->assertSee("test");
            $browser2->visit('/join/' . $this->chime->access_code)->pause(500)->assertSee("No Open Questions");
            $browser1->pause(300)->click("@open-all-button");
            $browser2->pause(2000)->assertSee("test");
            $browser1->click("@close-all-button");
            $browser2->pause(1000)->assertSee("No Open Questions");
        });

    }

}
