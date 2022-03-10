<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class LTISeedUsersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('users')->delete();
        
        \DB::table('users')->insert(array (
            0 => 
            array (
                'id' => 2,
                'name' => 'Admin User',
                'email' => 'chimein+admin@umn.edu',
                'password' => 'shibboleth',
                'permission_number' => 100,
                'remember_token' => 'NVmvwGfqKx',
                'created_at' => '2022-02-06 02:57:15',
                'updated_at' => '2022-02-06 02:57:15',
                'umndid' => 'admin',
                'userType' => NULL,
                'lti_user_id' => 'asdf123',
                'guest_user' => 0,
                'emplid' => NULL,
                'global_admin' => 1,
                'lti13_sub_id' => NULL,
            ),
            1 => 
            array (
                'id' => 13,
                'name' => 'Faculty User',
                'email' => 'chimein+faculty@umn.edu',
                'password' => 'shibboleth',
                'permission_number' => 100,
                'remember_token' => NULL,
                'created_at' => '2022-03-10 19:55:47',
                'updated_at' => '2022-03-10 19:55:47',
                'umndid' => 'faculty',
                'userType' => NULL,
                'lti_user_id' => 'student-one',
                'guest_user' => 0,
                'emplid' => NULL,
                'global_admin' => 0,
                'lti13_sub_id' => NULL,
            ),
            2 => 
            array (
                'id' => 16,
                'name' => 'Student User',
                'email' => 'chimein+student@umn.edu',
                'password' => 'shibboleth',
                'permission_number' => 100,
                'remember_token' => NULL,
                'created_at' => '2022-03-10 20:21:04',
                'updated_at' => '2022-03-10 20:21:04',
                'umndid' => 'student',
                'userType' => NULL,
                'lti_user_id' => 'student-two',
                'guest_user' => 0,
                'emplid' => NULL,
                'global_admin' => 0,
                'lti13_sub_id' => NULL,
            ),
        ));
        
        
    }
}