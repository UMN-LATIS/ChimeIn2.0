<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class TestCreateCourseAndQuestions extends DuskTestCase
{
    /**
     * A Dusk test example.
     *
     * @return void
     */
    public function testAuthLogin()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSee('guest');
            $browser->loginAs(\App\User::find(2))->visit('/')->assertSee('Admin');
            $browser->click('@add-chime-button');
            $browser->pause(500)->assertSee("Chime Name");
            $browser->type("#chime_name_input", "Test Chime");
            $browser->click('@create-chime-button');
            $browser->assertSee('Test Chime');
            $browser->pause(1200)->click('.card-title');
            $browser->assertSee('No Folders Yet!');
            $browser->type("#createFolder", "Test Folder");
            $browser->click('@create-folder');
            $browser->pause(100)->click('h4');
            $browser->pause(100)->assertSee('Test Folder');
            $browser->click('@new-question-button');
            $browser->pause(100)->assertSee('Add a Question');

        });
    }
}
