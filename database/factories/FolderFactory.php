<?php

namespace Database\Factories;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class FolderFactory extends Factory
{
  protected $model = \App\Folder::class;

  /**
   * Define the model's default state.
   *
   * @return array
   */
  public function definition()
  {
    return [
      "name" => $this->faker->words(3, true)
    ];
  }


}
