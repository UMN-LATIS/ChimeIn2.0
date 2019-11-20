<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;



class TestCreateCourseAndQuestions extends DuskTestCase
{
    use DatabaseMigrations;
    public $admin = null;
    public $chime = null;
    public function setUp(): void {
        parent::setUp();

        $this->admin = factory(\App\User::class)->create();
        $this->chime = factory(\App\Chime::class)->create();
        $this->admin->chimes()->attach($this->chime, [
            'permission_number' => 300
        ]);
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
            $browser->loginAs($this->admin)->visit('/')->assertSee($this->admin->name);

        });

    }

    public function testCreateCourse() {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)->visit('/');
            $browser->click('@add-chime-button');
            $browser->pause(500)->assertSee("Chime Name");
            $browser->type("#chime_name_input", "Test Chime");
            $browser->click('@create-chime-button');
            $browser->pause(1000)->assertSee('Test Chime');
            $browser->assertSee("any folders");
        });
    }

    public function testCreateFolder() {
        
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)->visit('/chime/' . $this->chime->id);
            $browser->assertSee("any folders");
            $browser->type("#createFolder", "Test Folder");
            $browser->click('@create-folder');
            $browser->pause(200)->click('.h4');
            $browser->pause(1000)->assertSee('Test Folder');
        });
    }

    public function testManageSettings() {
        $this->assertNull($this->chime->require_login);
        $this->assertNull($this->chime->students_can_view);
        $this->assertNull($this->chime->join_instructions);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)->visit('/chime/' . $this->chime->id);
            $browser->pause(500)->click('@manage-button');
            $browser->pause(2000)->assertSee("Access Code");
            $browser->check('requireLogin');
            $browser->check('studentView');
            $browser->check('joinInstructions');
            $browser->pause(500);
        });
        $this->chime->refresh();
        $this->assertEquals($this->chime->require_login, 1);
        $this->assertEquals($this->chime->students_can_view, 1);
        $this->assertEquals($this->chime->join_instructions, 1);

    }

}
