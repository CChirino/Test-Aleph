<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Aleph API Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for the Aleph API integration.
    | This includes the base URL and API key for authentication.
    |
    */

    'base_url' => env('ALEPH_API_URL', 'https://qa.alephmanager.com'),

    'api_key' => env('ALEPH_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Export/Import Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for the export and import functionality.
    |
    */

    'exports' => [
        'directory' => 'reports',
        'disk' => 'public',
    ],

    'imports' => [
        'allowed_extensions' => ['xlsx', 'xls'],
        'max_file_size' => 5120, // 5MB in kilobytes
    ],
];
