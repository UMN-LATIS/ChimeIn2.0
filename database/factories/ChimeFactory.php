<?php

namespace Database\Factories;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChimeFactory extends Factory
{
  protected $model = \App\Chime::class;

  /**
   * Define the model's default state.
   *
   * @return array
   */
  public function definition()
  {
    return [
      "name" => $this->faker->words(3, true),
      "access_code" => $this->faker->unique()->randomNumber(6),
      "require_login" => $this->faker->boolean(),
      "students_can_view" => $this->faker->boolean(),
      "join_instructions" => 1,
      "show_folder_title_to_participants" => $this->faker->boolean(),
    ];
  }

  public function withLTI()
  {
    return $this->state(function () {
      return [
        "lti_return_url" => "https://scratch.canvas.umn.edu/courses/" . random_int(1, 9999). "/assignments",
        "lti_course_title" => $this->faker->unique()->words(3, true),
        "lti_course_id" => Str::random(10),
        "require_login" => 1,
        "only_correct_answers_lti" => 2,
        "lti_setup_complete" => 1,
        "lti_grade_mode" => "multiple_grades",
      ];
    });
  }
}
