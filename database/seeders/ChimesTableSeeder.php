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
            ->withContent(['folders' => 3, 'questions' => 3])
            ->create();

        // LTI Chimes
        Chime::factory()
            ->withPresenter(User::find(1))
            ->withLTI()
            ->withContent(['folders' => 3, 'questions' => 3])
            ->create();

        Chime::factory()
            ->withPresenter(User::find(1))
            ->withParticipants(40)
            ->withContent([
                'folders' => 3, 'questions' => 3,
                'sessions' => 1, 'responses' => 20
            ])
            ->create([
                'name' => 'A chime with Responses',
            ]);
    }
}
