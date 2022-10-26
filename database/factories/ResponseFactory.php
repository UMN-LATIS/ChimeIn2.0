<?php

namespace Database\Factories;

use App\Question;
use App\Session;
use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;



/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Response>
 */
class ResponseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $allUserIds = User::all()->map(fn ($u) => $u->id)->toArray();

        return [
            'user_id' => fake()->randomElement($allUserIds),
            'response_info' => function (array $responseAttrs) {
                // look up associated question and it's choices
                // then pick one at random
                $session = Session::find($responseAttrs['session_id']);
                $question = Question::find($session->question_id);
                $choices = $question->question_info['question_responses'];
                $randomIndex = rand(0, count($choices));
                $choice = $choices[$randomIndex];

                return [
                    'question_type' => Question::MULTIPLE_CHOICE_TYPE,
                    'choice' => $choice['text'],
                ];
            },
        ];
    }
}
