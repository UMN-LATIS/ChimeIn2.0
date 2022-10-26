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

        // create admin
        User::factory()
            ->create([
                "name" => "Admin User",
                "email" => "chimein+admin@umn.edu",
                "global_admin" => true,
                "umndid" => "admin",
                "password" => "shibboleth",
            ]);

        User::factory(10)->create();
    }
}
