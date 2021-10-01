<?php


return array(

    /*
    |--------------------------------------------------------------------------
    | Views / Endpoints
    |--------------------------------------------------------------------------
    |
    | Set your login page, or login routes, here. If you provide a view,
    | that will be rendered. Otherwise, it will redirect to a route.
    |
     */

    'idp_login'     => '/Shibboleth.sso/Login',
    'idp_logout'    => '/Shibboleth.sso/Logout?return=https%3a%2f%2flogin.umn.edu%2fidp%2fLogoutUMN',
    'authenticated' => '/',
    'authfield'     => 'email',
    /*
    |--------------------------------------------------------------------------
    | Emulate an IdP
    |--------------------------------------------------------------------------
    |
    | In case you do not have access to your Shibboleth environment on
    | homestead or your own Vagrant box, you can emulate a Shibboleth
    | environment with the help of Shibalike.
    |
    | Do not use this in production for literally any reason.
    |
     */

    'emulate_idp'       => env('SHIB_EMULATE', false),
    'emulate_idp_users' => array(
        'admin' => array(
            'uid'         => 'admin',
            'displayName' => 'Admin User',
            'givenName'   => 'Admin',
            'sn'          => 'User',
            'eppn'        => 'chimein+admin@umn.edu',
            'umnDID'      => 'admin',
            'umnEmplId'      => '1',
            'eduPersonAffiliation' =>'faculty',
            'https://www.umn.edu/shibboleth/attributes/umnCourse' => ['1051ARTS3604_001sD0040_022239__','1051GDES2351_001sD0030_016625__','1051GDES4131W001_D0040_018297__']
        ),
        'faculty' => array(
            'uid'         => 'faculty',
            'displayName' => 'Faculty User',
            'givenName'   => 'Faculty',
            'sn'          => 'User',
            'eppn'        => 'chimein+faculty@umn.edu',
            'umnDID'      => 'faculty',
            'umnEmplId'      => '2',
            'eduPersonAffiliation' =>'faculty'
        ),
        'student' => array(
            'uid'         => 'student',
            'displayName' => 'Student User',
            'givenName'   => 'Student',
            'sn'          => 'User',
            'eppn'        => 'chimein+student@umn.edu',
            'umnDID'      => 'student',
            'umnEmplId'      => '3',
            'eduPersonAffiliation' =>'student'
        ),
    ),

    /*
    |--------------------------------------------------------------------------
    | Server Variable Mapping
    |--------------------------------------------------------------------------
    |
    | Change these to the proper values for your IdP.
    |
     */

    'entitlement' => 'secret',

    'user' => [
        // fillable user model attribute => server variable
        'email'       => 'eppn',
        'umndid' => 'umnDID',
        'name' => 'displayName',
        // 'enrolled_courses'=>'https://www.umn.edu/shibboleth/attributes/umnCourse',
    ],

    /*
    |--------------------------------------------------------------------------
    | User Creation and Groups Settings
    |--------------------------------------------------------------------------
    |
    | Allows you to change if / how new users are added
    |
     */

    'add_new_users' => true, // Should new users be added automatically if they do not exist?

    /*
    |--------------------------------------------------------------------------
    | JWT Auth
    |--------------------------------------------------------------------------
    |
    | JWTs are for the front end to know it's logged in
    |
    | https://github.com/tymondesigns/jwt-auth
    | https://github.com/StudentAffairsUWM/Laravel-Shibboleth-Service-Provider/issues/24
    |
     */

    'jwtauth' => env('JWTAUTH', false),
);
