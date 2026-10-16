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

    // GUIDs for the emulated SharePoint add-in catalog Windows clients read from.
    'catalog_web_id' => env('OFFICE_CATALOG_WEB_ID', 'c0f1b7e4-2a64-4f0e-9c3d-7b5a1e2d8f40'),
    'catalog_list_id' => env('OFFICE_CATALOG_LIST_ID', 'd6a2f1c8-5b39-4e27-8f10-3c94ad6e2b55'),
    'catalog_file_id' => env('OFFICE_CATALOG_FILE_ID', 'b4e73d91-8c25-4a6f-9d08-1f52c7ab3e60'),

];
