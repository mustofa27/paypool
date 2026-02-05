<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Xendit API Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your Xendit API settings. You can obtain your
    | API keys from the Xendit dashboard.
    |
    */

    'api_key' => env('XENDIT_API_KEY'),
    
    'public_key' => env('XENDIT_PUBLIC_KEY'),
    
    'webhook_token' => env('XENDIT_WEBHOOK_TOKEN'),
    
    'base_url' => env('XENDIT_BASE_URL', 'https://api.xendit.co'),
    
    'success_redirect_url' => env('XENDIT_SUCCESS_REDIRECT_URL'),
    
    'failure_redirect_url' => env('XENDIT_FAILURE_REDIRECT_URL'),
    
    /*
    |--------------------------------------------------------------------------
    | Invoice Settings
    |--------------------------------------------------------------------------
    */
    
    'invoice' => [
        'default_currency' => env('XENDIT_DEFAULT_CURRENCY', 'IDR'),
        'expiry_duration' => env('XENDIT_EXPIRY_DURATION', 86400), // 24 hours in seconds
    ],
];
