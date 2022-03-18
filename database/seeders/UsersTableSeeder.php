<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\User;
use App\Chime;

class UsersTableSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    User::factory()
      // chimes
      ->hasAttached(Chime::factory()->count(1), ["permission_number" => 300])
      // canvas chimes
      ->hasAttached(
        Chime::factory()
          ->withLTI()
          ->count(1),
        ["permission_number" => 300]
      )
      ->create([
        "name" => "Admin User",
        "email" => "chimein+admin@umn.edu",
        "global_admin" => true,
        "umndid" => "admin",
        "password" => "shibboleth",
      ]);
  }
}
