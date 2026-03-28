<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
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

    'sms' => [
        'provider' => env('SMS_PROVIDER', 'log'),
        'url'      => env('SMS_API_URL'),
        'token'    => env('SMS_API_TOKEN'),
        'from'     => env('SMS_FROM', 'KHAYMA'),
    ],

    'paydunya' => [
        'mode'          => env('PAYDUNYA_MODE', 'log'),        // log|fake|api
        'paydunya_mode' => env('PAYDUNYA_ENV', 'test'),         // test|live
        'master_key'    => env('PAYDUNYA_MASTER_KEY', ''),
        'private_key'   => env('PAYDUNYA_PRIVATE_KEY', ''),
        'token'         => env('PAYDUNYA_TOKEN', ''),
    ],

];
