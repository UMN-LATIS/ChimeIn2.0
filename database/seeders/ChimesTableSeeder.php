<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Chime;
use App\User;

class ChimesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // regular chimes
        Chime::factory()
            ->withPresenter(User::find(1))
            ->create();

        // LTI Chimes
        Chime::factory()
            ->withPresenter(User::find(1))
            ->withLTI()
            ->create();

        Chime::factory()
            ->withResponses()
            ->withPresenter(User::find(1))
            ->create([
                'name' => 'A chime with Responses',
            ]);
    }
}
