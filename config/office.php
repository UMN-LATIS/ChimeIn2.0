<?php

return [

    /*
    |--------------------------------------------------------------------------
    | PowerPoint Add-in
    |--------------------------------------------------------------------------
    |
    | Settings for the ChimeIn content add-in. Chime tokens stay in Office client
    | storage and are never written to the presentation file.
    |
    */

    'chime_token_days' => (int) env('OFFICE_CHIME_TOKEN_DAYS', 365),

    'browse_token_days' => (int) env('OFFICE_BROWSE_TOKEN_DAYS', 30),

    // GUIDs for the add-in manifest. Must stay stable once deployed.
    'content_addin_id' => env('OFFICE_CONTENT_ADDIN_ID', '5e8b3d2a-9c14-4f7b-8a61-2d0f4c6e7b93'),

];
