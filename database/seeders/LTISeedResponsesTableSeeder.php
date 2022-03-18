<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class LTISeedResponsesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('responses')->delete();
        
        \DB::table('responses')->insert(array (
            0 => 
            array (
                'id' => 3,
                'created_at' => '2022-03-10 20:00:51',
                'updated_at' => '2022-03-10 20:01:03',
                'session_id' => 5,
                'user_id' => 13,
                'response_info' => '{"question_type":"multiple_choice","choice":"<p>two<\\/p>"}',
                'deleted_at' => NULL,
            ),
            1 => 
            array (
                'id' => 4,
                'created_at' => '2022-03-10 20:20:34',
                'updated_at' => '2022-03-10 20:20:34',
                'session_id' => 4,
                'user_id' => 13,
                'response_info' => '{"question_type":"free_response","text":"fdafdsaf"}',
                'deleted_at' => NULL,
            ),
            2 => 
            array (
                'id' => 5,
                'created_at' => '2022-03-10 20:20:49',
                'updated_at' => '2022-03-10 20:20:49',
                'session_id' => 6,
                'user_id' => 13,
                'response_info' => '{"question_type":"multiple_choice","choice":"<p>three<\\/p>"}',
                'deleted_at' => NULL,
            ),
            3 => 
            array (
                'id' => 6,
                'created_at' => '2022-03-10 20:21:09',
                'updated_at' => '2022-03-10 20:21:09',
                'session_id' => 6,
                'user_id' => 16,
                'response_info' => '{"question_type":"multiple_choice","choice":"<p>two<\\/p>"}',
                'deleted_at' => NULL,
            ),
            4 => 
            array (
                'id' => 7,
                'created_at' => '2022-03-10 20:21:11',
                'updated_at' => '2022-03-10 20:21:11',
                'session_id' => 4,
                'user_id' => 16,
                'response_info' => '{"question_type":"free_response","text":"fdafs"}',
                'deleted_at' => NULL,
            ),
        ));
        
        
    }
}