<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class LTISeedLti2ConsumerTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('lti2_consumer')->delete();
        
        \DB::table('lti2_consumer')->insert(array (
            0 => 
            array (
                'consumer_pk' => 1,
                'name' => '',
                'consumer_key256' => 'fda',
                'consumer_key' => 'fda',
                'secret' => '',
                'lti_version' => '1.1',
                'consumer_name' => 'fdaf',
                'consumer_version' => NULL,
                'consumer_guid' => NULL,
                'profile' => NULL,
                'tool_proxy' => NULL,
                'settings' => NULL,
                'protected' => 0,
                'enabled' => 0,
                'enable_from' => NULL,
                'enable_until' => NULL,
                'last_access' => NULL,
                'created' => '2022-01-01 00:00:00',
                'updated' => '2022-01-01 00:00:00',
            ),
        ));
        
        
    }
}