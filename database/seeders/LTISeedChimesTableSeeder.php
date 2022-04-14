<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class LTISeedChimesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('chimes')->delete();
        
        \DB::table('chimes')->insert(array (
            0 => 
            array (
                'id' => 4,
                'access_code' => '510750',
                'created_at' => '2022-03-10 19:54:30',
                'updated_at' => '2022-03-10 20:29:49',
                'name' => 'Test Chime',
                'lti_return_url' => NULL,
                'lti_course_title' => NULL,
                'lti_course_id' => NULL,
                'require_login' => 0,
                'deleted_at' => NULL,
                'students_can_view' => 0,
                'join_instructions' => 1,
                'only_correct_answers_lti' => 2,
                'lti_setup_complete' => 0,
                'resource_link_pk' => NULL,
                'lti13_resource_link_id' => NULL,
                'lti_grade_mode' => NULL,
                'show_folder_title_to_participants' => 0,
            ),
        ));
        
        
    }
}