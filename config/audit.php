<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Audit Logging
    |--------------------------------------------------------------------------
    |
    | This file contains configuration for the audit logging system.
    |
    */


    /*
    |--------------------------------------------------------------------------
    | Enable Audit Logging
    |--------------------------------------------------------------------------
    |
    | This option controls if audit logging is enabled.
    |
    */
    'enabled' => env('AUDIT_LOGGING_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Log HTTP Requests
    |--------------------------------------------------------------------------
    |
    | This option controls if HTTP requests should be automatically logged.
    |
    */
    'log_http_requests' => env('AUDIT_LOG_HTTP_REQUESTS', true),

    /*
    |--------------------------------------------------------------------------
    | Log Model Events
    |--------------------------------------------------------------------------
    |
    | This option controls if model events should be automatically logged.
    |
    */
    'log_model_events' => env('AUDIT_LOG_MODEL_EVENTS', true),

    /*
    |--------------------------------------------------------------------------
    | Log User Events
    |--------------------------------------------------------------------------
    |
    | This option controls if user authentication events should be logged.
    |
    */
    'log_user_events' => env('AUDIT_LOG_USER_EVENTS', true),

    /*
    |--------------------------------------------------------------------------
    | Log Console Commands
    |--------------------------------------------------------------------------
    |
    | This option controls if console commands should be logged.
    |
    */
    'log_console_commands' => env('AUDIT_LOG_CONSOLE_COMMANDS', true),

    /*
    |--------------------------------------------------------------------------
    | Log Queued Jobs
    |--------------------------------------------------------------------------
    |
    | This option controls if queued jobs should be logged.
    |
    */
    'log_queued_jobs' => env('AUDIT_LOG_QUEUED_JOBS', true),

    /*
    |--------------------------------------------------------------------------
    | Log Database Transactions
    |--------------------------------------------------------------------------
    |
    | This option controls if database transactions should be logged.
    |
    */
    'log_database_transactions' => env('AUDIT_LOG_DATABASE_TRANSACTIONS', true),

    /*
    |--------------------------------------------------------------------------
    | Sensitive Fields
    |--------------------------------------------------------------------------
    |
    | These fields will be redacted from the audit logs.
    |
    */
    'sensitive_fields' => [
        'password',
        'password_confirmation',
        'current_password',
        'credit_card',
        'cvv',
        'ssn',
        'token',
        'api_key',
        'secret',
    ],

    /*
    |--------------------------------------------------------------------------
    | Excluded Paths
    |--------------------------------------------------------------------------
    |
    | These paths will be excluded from HTTP request logging.
    |
    */
    'excluded_paths' => [
        'horizon*',
        'telescope*',
        'livewire/*',
        '_debugbar/*',
        'api/*',
    ],

    /*
    |--------------------------------------------------------------------------
    | Logged HTTP Methods
    |--------------------------------------------------------------------------
    |
    | Only these HTTP methods will be logged.
    |
    */
    'logged_http_methods' => ['POST', 'PUT', 'PATCH', 'DELETE'],

    /*
    |--------------------------------------------------------------------------
    | Log Channel
    |--------------------------------------------------------------------------
    |
    | The log channel to use for audit logs. Set to null to use the default channel.
    |
    */
    'log_channel' => env('AUDIT_LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how audit logs are processed. Set to true to process logs
    | asynchronously using the queue.
    |
    */
    'queue' => [
        'enabled' => env('AUDIT_QUEUE_ENABLED', true),
        'name' => env('AUDIT_QUEUE_NAME', 'audit'),
        'connection' => env('AUDIT_QUEUE_CONNECTION', 'database'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Pruning
    |--------------------------------------------------------------------------
    |
    | Configure automatic pruning of old audit logs.
    |
    */
    'prune' => [
        'enabled' => env('AUDIT_PRUNE_ENABLED', true),
        'retention_days' => env('AUDIT_PRUNE_RETENTION_DAYS', 365),
    ],
];
