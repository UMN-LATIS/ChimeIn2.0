<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;
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
            "text" => fake()->sentence(),
            "order" => fake()->unique()->numberBetween(1, 100),
            "question_info" => [
                "question_type" => Question::MULTIPLE_CHOICE_TYPE,
                "question_responses" => [
                    [
                        'text' => fake()->words(5, true),
                        'correct' => fake()->boolean(),
                    ],
                    [
                        'text' => fake()->words(5, true),
                        'correct' => fake()->boolean(),
                    ],
                    [
                        'text' => fake()->words(5, true),
                        'correct' => fake()->boolean(),
                    ],                    [
                        'text' => fake()->words(5, true),
                        'correct' => fake()->boolean(),
                    ]
                ],
            ],
        ];
    }
}
