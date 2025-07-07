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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET', 'whsec_297e33f1370a896e7d2ae52bb2a996394a0c2a710d2b6998284f32d67564e64'),
        'currency' => env('STRIPE_CURRENCY', 'usd'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Notification Settings
    |--------------------------------------------------------------------------
    |
    | This is used to send notifications to administrators when important
    | events occur, such as new free trial registrations.
    |
    */
    'admin_notification_emails' => explode(',', env('ADMIN_NOTIFICATION_EMAILS', 'info@faxt.com')),

    'gift_cards' => [
        'min_amount' => env('GIFT_CARD_MIN_AMOUNT', 5), // Minimum gift card amount in currency units
        'max_amount' => env('GIFT_CARD_MAX_AMOUNT', 1000), // Maximum gift card amount in currency units
        'max_validity_years' => env('GIFT_CARD_MAX_VALIDITY_YEARS', 2), // Maximum validity period in years
        'default_validity_days' => env('GIFT_CARD_DEFAULT_VALIDITY_DAYS', 365), // Default validity period in days
    ],

];
