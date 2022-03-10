<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class LTISeedFoldersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('folders')->delete();
        
        \DB::table('folders')->insert(array (
            0 => 
            array (
                'id' => 3,
                'created_at' => '2022-03-10 19:54:34',
                'updated_at' => '2022-03-10 19:54:34',
                'name' => 'Test Folder',
                'chime_id' => 4,
                'resource_link_pk' => NULL,
                'deleted_at' => NULL,
                'order' => 1,
                'lti_lineitem' => NULL,
            ),
        ));
        
        
    }
}