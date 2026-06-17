<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WhatsApp Provider
    |--------------------------------------------------------------------------
    |
    | Supported: "fonnte"
    |
    */
    'provider' => env('WHATSAPP_PROVIDER', 'fonnte'),

    /*
    |--------------------------------------------------------------------------
    | Fonnte Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Fonnte WhatsApp API
    | Sign up at: https://fonnte.com
    |
    */
    'fonnte' => [
        'base_url' => env('FONNTE_BASE_URL', 'https://api.fonnte.com'),
        'token' => env('FONNTE_TOKEN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | From Configuration
    |--------------------------------------------------------------------------
    |
    | Default sender information
    |
    */
    'from' => [
        'name' => env('WHATSAPP_FROM_NAME', 'BundaGaya'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Message Settings
    |--------------------------------------------------------------------------
    |
    | General message settings
    |
    */
    'settings' => [
        'retry_attempts' => 3,
        'retry_delay' => 5, // seconds
        'log_messages' => true,
    ],
];
