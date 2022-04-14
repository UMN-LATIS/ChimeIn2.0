<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class LTISeedSessionsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('sessions')->delete();
        
        \DB::table('sessions')->insert(array (
            0 => 
            array (
                'id' => 4,
                'created_at' => '2022-03-10 19:54:55',
                'updated_at' => '2022-03-10 19:54:55',
                'question_id' => 4,
                'deleted_at' => NULL,
            ),
            1 => 
            array (
                'id' => 5,
                'created_at' => '2022-03-10 19:54:56',
                'updated_at' => '2022-03-10 20:20:46',
                'question_id' => 3,
                'deleted_at' => NULL,
            ),
            2 => 
            array (
                'id' => 6,
                'created_at' => '2022-03-10 20:20:47',
                'updated_at' => '2022-03-10 20:20:47',
                'question_id' => 3,
                'deleted_at' => NULL,
            ),
        ));
        
        
    }
}