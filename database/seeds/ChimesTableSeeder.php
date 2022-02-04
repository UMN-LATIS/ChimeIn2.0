<?php

use Illuminate\Database\Seeder;
use Faker\Generator as Faker;
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
    Chime::factory(3)->create();

    // LTI Chimes
    Chime::factory(3)
      ->withLTI()
      ->create();
  }
}
1;
