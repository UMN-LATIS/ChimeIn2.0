<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LTITest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testLtiGradeCalc()
    {
        $chime = \App\Chime::factory(1)->withLTI()->create();
        $folder = \App\Folder::factory(1)->make();
        $folder->chime_id = $chime->id;
        $folder->save();
        $question = \App\Question::factory(1)->make();
        $question->folder_id = $folder->id;
        $question->save();
        
        
        $this->assertTrue(true);
    }
}
