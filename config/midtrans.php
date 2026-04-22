<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Midtrans API Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your Midtrans API settings. Obtain keys from Midtrans dashboard.
    */

    'default_environment' => env('MIDTRANS_DEFAULT_ENVIRONMENT', 'sandbox'),

    // Keep all essential environment-specific values in one place.
    'environments' => [
        'sandbox' => [
            'server_key' => env('MIDTRANS_SANDBOX_SERVER_KEY', env('MIDTRANS_SERVER_KEY')),
            'client_key' => env('MIDTRANS_SANDBOX_CLIENT_KEY', env('MIDTRANS_CLIENT_KEY')),
            'snap_base_url' => env('MIDTRANS_SANDBOX_SNAP_BASE_URL', 'https://app.sandbox.midtrans.com/snap'),
            'api_base_url' => env('MIDTRANS_SANDBOX_API_BASE_URL', 'https://api.sandbox.midtrans.com'),
        ],
        'production' => [
            'server_key' => env('MIDTRANS_PRODUCTION_SERVER_KEY'),
            'client_key' => env('MIDTRANS_PRODUCTION_CLIENT_KEY'),
            'snap_base_url' => env('MIDTRANS_PRODUCTION_SNAP_BASE_URL', 'https://app.midtrans.com/snap'),
            'api_base_url' => env('MIDTRANS_PRODUCTION_API_BASE_URL', 'https://api.midtrans.com'),
        ],
    ],

    'redirects' => [
        'success_url' => env('MIDTRANS_SUCCESS_REDIRECT_URL'),
        'failure_url' => env('MIDTRANS_FAILURE_REDIRECT_URL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Transaction Settings
    |--------------------------------------------------------------------------
    */
    'transaction' => [
        'default_currency' => env('MIDTRANS_DEFAULT_CURRENCY', 'IDR'),
        'expiry_duration' => env('MIDTRANS_EXPIRY_DURATION', 86400), // 24 hours in seconds
    ],
];
