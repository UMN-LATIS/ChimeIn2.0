<?php

use Illuminate\Database\Seeder;

class ChimesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        factory(App\Chime::class, 5)->create();
    }
}
