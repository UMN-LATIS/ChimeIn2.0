<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use \App\Library\LTI13Processor;
class LTITest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'LTISeeder']);
    }
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testLtiGradeCalc()
    {
        
        $chime = \App\Chime::find(4);
        
        $question1 = $chime->folders->first()->questions[0];
        $question2 = $chime->folders->first()->questions[1];

        $globalUsers = [];
        $chime->only_correct_answers_lti = 0;
        LTI13Processor::getPointsForQuestion($question1, $chime, $globalUsers);
        $this->assertEquals(1, $globalUsers["student-one"]["points"]);
        $this->assertEquals(1, $globalUsers["student-two"]["points"]);
        LTI13Processor::getPointsForQuestion($question2, $chime, $globalUsers);
        $this->assertEquals(2, $globalUsers["student-one"]["points"]);
        $this->assertEquals(2, $globalUsers["student-two"]["points"]);

        $globalUsers = [];
        $chime->only_correct_answers_lti = 1;
        LTI13Processor::getPointsForQuestion($question1, $chime, $globalUsers);
        $this->assertEquals(1, $globalUsers["student-one"]["points"]);
        $this->assertArrayNotHasKey("student-two", $globalUsers);
        LTI13Processor::getPointsForQuestion($question2, $chime, $globalUsers);
        $this->assertEquals(2, $globalUsers["student-one"]["points"]);
        $this->assertEquals(1, $globalUsers["student-two"]["points"]);


        $globalUsers = [];
        $chime->only_correct_answers_lti = 2;
        LTI13Processor::getPointsForQuestion($question1, $chime, $globalUsers);
        $this->assertEquals(1, $globalUsers["student-one"]["points"]);
        $this->assertEquals(0.5, $globalUsers["student-two"]["points"]);
        LTI13Processor::getPointsForQuestion($question2, $chime, $globalUsers);
        $this->assertEquals(2, $globalUsers["student-one"]["points"]);
        $this->assertEquals(1.5, $globalUsers["student-two"]["points"]);
    }
}
