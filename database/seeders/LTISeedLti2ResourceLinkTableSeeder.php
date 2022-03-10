<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class LTISeedLti2ResourceLinkTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('lti2_resource_link')->delete();
        
        \DB::table('lti2_resource_link')->insert(array (
            0 => 
            array (
                'resource_link_pk' => 1,
                'context_pk' => NULL,
                'consumer_pk' => 1,
                'lti_resource_link_id' => 'asdfasdfasdf',
                'settings' => NULL,
                'primary_resource_link_pk' => NULL,
                'share_approved' => NULL,
                'created' => '2022-01-01 00:00:00',
                'updated' => '2022-01-01 00:00:00',
            ),
        ));
        
        
    }
}