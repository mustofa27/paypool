<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Midtrans API Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your Midtrans API settings. Obtain keys from Midtrans dashboard.
    */

    'server_key' => env('MIDTRANS_SERVER_KEY'),
    'client_key' => env('MIDTRANS_CLIENT_KEY'),
    'base_url' => env('MIDTRANS_BASE_URL', 'https://app.sandbox.midtrans.com/snap'),
    'success_redirect_url' => env('MIDTRANS_SUCCESS_REDIRECT_URL'),
    'failure_redirect_url' => env('MIDTRANS_FAILURE_REDIRECT_URL'),

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
