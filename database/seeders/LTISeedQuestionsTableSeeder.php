<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class LTISeedQuestionsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('questions')->delete();
        
        \DB::table('questions')->insert(array (
            0 => 
            array (
                'id' => 3,
                'created_at' => '2022-03-10 19:54:44',
                'updated_at' => '2022-03-10 20:20:47',
                'text' => '<p>Question One</p>',
                'folder_id' => 3,
                'order' => 1,
                'question_info' => '{"question_type":"multiple_choice","question_responses":[{"text":"<p>one<\\/p>","correct":false},{"text":"<p>two<\\/p>","correct":false},{"text":"<p>three<\\/p>","correct":true}]}',
                'current_session_id' => 6,
                'deleted_at' => NULL,
                'anonymous' => 0,
                'allow_multiple' => 0,
            ),
            1 => 
            array (
                'id' => 4,
                'created_at' => '2022-03-10 19:54:53',
                'updated_at' => '2022-03-10 19:54:55',
                'text' => '<p>Free Response Questions</p>',
                'folder_id' => 3,
                'order' => 2,
                'question_info' => '{"question_type":"free_response","question_responses":[]}',
                'current_session_id' => 4,
                'deleted_at' => NULL,
                'anonymous' => 0,
                'allow_multiple' => 0,
            ),
        ));
        
        
    }
}