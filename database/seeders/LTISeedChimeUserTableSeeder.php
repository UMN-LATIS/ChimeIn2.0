<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class LTISeedChimeUserTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('chime_user')->delete();
        
        \DB::table('chime_user')->insert(array (
            0 => 
            array (
                'id' => 5,
                'created_at' => '2022-03-10 19:54:30',
                'updated_at' => '2022-03-10 19:54:30',
                'user_id' => 2,
                'chime_id' => 4,
                'permission_number' => 300,
            ),
            1 => 
            array (
                'id' => 7,
                'created_at' => '2022-03-10 19:55:49',
                'updated_at' => '2022-03-10 19:55:49',
                'user_id' => 13,
                'chime_id' => 4,
                'permission_number' => 100,
            ),
            2 => 
            array (
                'id' => 10,
                'created_at' => '2022-03-10 20:21:07',
                'updated_at' => '2022-03-10 20:21:07',
                'user_id' => 16,
                'chime_id' => 4,
                'permission_number' => 100,
            ),
        ));
        
        
    }
}