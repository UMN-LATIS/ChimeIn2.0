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
        $admin = User::where('umndid', 'admin')->firstOrFail();

        // regular chimes
        Chime::factory()
            ->withPresenter($admin)
            ->withContent(['folders' => 3, 'questions' => 3])
            ->create();

        // LTI Chimes
        Chime::factory()
            ->withPresenter($admin)
            ->withLTI()
            ->withContent(['folders' => 3, 'questions' => 3])
            ->create();

        Chime::factory()
            ->withPresenter($admin)
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
