<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'lm_studio' => [
        'url' => env('LM_STUDIO_URL', 'http://127.0.0.1:1234/v1'),
        'model' => env('LM_STUDIO_MODEL'),
        'token' => env('LM_STUDIO_API_TOKEN'),
        'timeout' => env('LM_STUDIO_TIMEOUT', 300),
    ],

    'groq' => [
        'url' => env('GROQ_API_URL', 'https://api.groq.com/openai/v1'),
        'model' => env('GROQ_MODEL', 'openai/gpt-oss-120b'),
        'key' => env('GROQ_API_KEY'),
        'timeout' => env('GROQ_TIMEOUT', 300),
    ],

    'institution_directory' => [
        'url' => env('GIATEP_DIRECTORY_API_URL', 'http://127.0.0.1:8091/api/v1'),
        'timeout' => env('GIATEP_DIRECTORY_API_TIMEOUT', 3),
    ],

];
