<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class LTISeedLti2UserResultTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('lti2_user_result')->delete();
        
        \DB::table('lti2_user_result')->insert(array (
            0 => 
            array (
                'user_pk' => 1,
                'resource_link_pk' => 1,
                'lti_user_id' => 'asdf123',
                'lti_result_sourcedid' => 'dfsdafdaf',
                'created' => '2022-01-01 00:00:00',
                'updated' => '2022-01-01 00:00:00',
            ),
            1 => 
            array (
                'user_pk' => 2,
                'resource_link_pk' => 1,
                'lti_user_id' => 'student-one',
                'lti_result_sourcedid' => 'dsfsajdkf',
                'created' => '2022-01-01 00:00:00',
                'updated' => '2022-01-01 00:00:00',
            ),
            2 => 
            array (
                'user_pk' => 3,
                'resource_link_pk' => 1,
                'lti_user_id' => 'student-two',
                'lti_result_sourcedid' => 'dsdfdfsalj',
                'created' => '2022-01-01 00:00:00',
                'updated' => '2022-01-01 00:00:00',
            ),
        ));
        
        
    }
}