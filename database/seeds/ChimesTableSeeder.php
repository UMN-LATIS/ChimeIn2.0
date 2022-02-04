<?php

use Illuminate\Database\Seeder;
use Faker\Generator as Faker;
use Illuminate\Support\Str;
use App\Chime;

class ChimesTableSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run(Faker $faker)
  {
    // regular chimes
    Chime::factory(1)->create();

    // LTI Chimes
    Chime::factory(1)
      ->state(function () use ($faker) {
        return [
          "lti_return_url" => $faker->url() . "\/courses\/" . Str::random(5),
          "lti_course_title" => $faker->unique()->words(3, true),
          "lti_course_id" => Str::random(10),
          "require_login" => 1,
          "only_correct_answers_lti" => 2,
          "lti_setup_complete" => 1,
          "lti_grade_mode" => "multiple_grades",
        ];
      })
      ->create();
  }
}
1;
