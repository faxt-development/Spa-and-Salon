<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Revenue Recognition Settings
    |--------------------------------------------------------------------------
    |
    | This file contains configuration for the revenue recognition system.
    |
    */


    /*
    |--------------------------------------------------------------------------
    | Default Recognition Methods
    |--------------------------------------------------------------------------
    |
    | Default recognition methods for different line item types.
    | Supported methods: immediately, upon_completion, over_time
    |
    */
    'default_recognition_methods' => [
        'service' => 'upon_completion',
        'product' => 'immediately',
        'membership' => 'over_time',
        'package' => 'over_time',
        'gift_card' => 'upon_redemption',
        'tax' => 'immediately',
        'tip' => 'immediately',
        'discount' => 'immediately',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Recognition Periods
    |--------------------------------------------------------------------------
    |
    | Default recognition periods (in days) for different line item types
    | when using the 'over_time' recognition method.
    |
    */
    'default_recognition_periods' => [
        'membership' => 30, // 30 days
        'package' => 90,    // 90 days
    ],

    /*
    |--------------------------------------------------------------------------
    | Batch Processing
    |--------------------------------------------------------------------------
    |
    | Settings for batch processing of revenue recognition.
    |
    */
    'batch_processing' => [
        'enabled' => env('REVENUE_RECOGNITION_BATCH_ENABLED', true),
        'batch_size' => env('REVENUE_RECOGNITION_BATCH_SIZE', 100),
        'schedule' => env('REVENUE_RECOGNITION_SCHEDULE', 'daily'), // daily, hourly, weekly
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Configure logging for revenue recognition.
    |
    */
    'logging' => [
        'enabled' => env('REVENUE_RECOGNITION_LOGGING', true),
        'level' => env('REVENUE_RECOGNITION_LOG_LEVEL', 'info'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue
    |--------------------------------------------------------------------------
    |
    | Queue configuration for revenue recognition jobs.
    |
    */
    'queue' => [
        'connection' => env('REVENUE_RECOGNITION_QUEUE_CONNECTION', env('QUEUE_CONNECTION', 'sync')),
        'queue' => env('REVENUE_RECOGNITION_QUEUE', 'default'),
        'delay' => env('REVENUE_RECOGNITION_QUEUE_DELAY', 0),
        'tries' => env('REVENUE_RECOGNITION_QUEUE_TRIES', 3),
        'timeout' => env('REVENUE_RECOGNITION_QUEUE_TIMEOUT', 300),
    ],

    /*
    |--------------------------------------------------------------------------
    | Revenue Recognition Rules
    |--------------------------------------------------------------------------
    |
    | Custom recognition rules that override the default behavior.
    | Format: 'line_item_type' => [
    |     'method' => 'recognition_method',
    |     'period' => 30, // days (for over_time method)
    |     'handler' => 'Fully\Qualified\HandlerClass@method', // Optional custom handler
    | ]
    |
    */
    'rules' => [
        // Example:
        // 'custom_service' => [
        //     'method' => 'over_time',
        //     'period' => 60,
        //     'handler' => 'App\\Services\\CustomRecognitionHandler@handle',
        // ],
    ],
];
