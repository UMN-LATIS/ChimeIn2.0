<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Question;

class QuestionFactory extends Factory
{
    protected $model = Question::class;

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
            "question_info" => json_encode([
                "question_type" => "multiple_choice",
                "question_responses" => [
                    [
                        "text" => "Yes",
                        "correct" => false,
                    ], [
                        "text" => "No",
                        "correct" => false,
                    ]
                ],
            ]),
        ];
    }
}
