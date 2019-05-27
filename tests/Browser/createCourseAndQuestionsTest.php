<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class TestCreateCourseAndQuestions extends DuskTestCase
{
    use DatabaseMigrations;
    
    public function setUp(): void {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'UsersTableSeeder']);
    }

    /**
     *
     * @return void
     */
    public function testAuthLogin()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSee('guest');
            $browser->loginAs(\App\User::where('name',"Admin")->first())->visit('/')->assertSee('Admin');

        });
    }

    public function testCreateCourse() {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(\App\User::where('name',"Admin")->first())->visit('/');
            $browser->click('@add-chime-button');
            $browser->pause(500)->assertSee("Chime Name");
            $browser->type("#chime_name_input", "Test Chime");
            $browser->click('@create-chime-button');
            $browser->pause(100)->assertSee('Test Chime');
            $browser->pause(1200)->click('.card-title');
            $browser->assertSee("You don't have any folders");
            $browser->type("#createFolder", "Test Folder");
            $browser->click('@create-folder');
            $browser->pause(200)->click('.h4');
            $browser->pause(100)->assertSee('Test Folder');
            $browser->click('@new-question-button');
            $browser->pause(200)->assertSee('Add a Question');
        });
    }
}
