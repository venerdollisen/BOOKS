<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure CORS settings for your application. This
    | configuration is used by the CORS middleware to handle preflight
    | requests and add proper CORS headers to responses.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:5173',  // Vite development server
        'http://localhost:3000',  // Alternative port
        'http://localhost',       // Production fallback
        'http://127.0.0.1:5173',
    ],

    'allowed_origins_patterns' => [
        // Allow all localhost variations during development
        '/^http:\/\/localhost(:\d+)?$/',
        '/^http:\/\/127\.0\.0\.1(:\d+)?$/',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [
        'Content-Length',
        'X-JSON-Response',
        'Authorization',
    ],

    'max_age' => 86400,

    'supports_credentials' => true,

];
