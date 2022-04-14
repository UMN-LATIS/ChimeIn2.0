<?php

namespace Database\Factories;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionFactory extends Factory
{
  protected $model = \App\Question::class;

  /**
   * Define the model's default state.
   *
   * @return array
   */
  public function definition()
  {
    return [
      "text" => $this->faker->words(3, true),
      "order" => 1,
      "question_info" => '{
	"question_type": "multiple_choice",
	"question_responses": [
            {
                "text": "Yes",
                "correct": false
            },
            {
                "text": "No",
                "correct": false
            }
        ]
    }',
    ];
  }
s}
