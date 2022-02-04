<?php

// use Faker\Generator as Faker;

// $factory->define(App\Chime::class, function (Faker $faker) {
//     $temp = new \App\Chime;
//     return [
//         'access_code' => $temp->getUniqueCode(),
//         'name' => $faker->words(3, true),
//     ];
// });

namespace Database\Factories;

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
}
