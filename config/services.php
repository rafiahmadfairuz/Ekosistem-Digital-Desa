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

    'xendit' => [
        'marketplace_enabled' => env('MARKETPLACE_XENDIT_ENABLED', true),
        'mode' => env('MARKETPLACE_PAYMENT_MODE', 'simulated'),
        'secret_key' => env('XENDIT_SECRET_KEY'),
        'webhook_token' => env('XENDIT_WEBHOOK_TOKEN'),
        'payouts_enabled' => env('XENDIT_PAYOUTS_ENABLED', false),
        'payout_webhook_token' => env('XENDIT_PAYOUT_WEBHOOK_TOKEN'),
        'payout_source_of_fund' => env('XENDIT_PAYOUT_SOURCE_OF_FUND', 'BUSINESS_REVENUE'),
        'payout_purpose_code' => env('XENDIT_PAYOUT_PURPOSE_CODE', 'TRADES'),
        'base_url' => env('XENDIT_BASE_URL', 'https://api.xendit.co'),
        'timeout' => (int) env('XENDIT_TIMEOUT', 10),
    ],

];
