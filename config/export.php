<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Export Settings
    |--------------------------------------------------------------------------
    |
    | This file contains the default settings for exports in the application.
    | You can override these values in your .env file or in the respective
    | export classes.
    |
    */


    /*
    |--------------------------------------------------------------------------
    | PDF Export Settings
    |--------------------------------------------------------------------------
    |
    | Configure the default PDF export settings.
    |
    */
    'pdf' => [
        // Paper size (a4, letter, legal, etc.)
        'paper' => env('PDF_PAPER_SIZE', 'a4'),
        
        // Page orientation (portrait or landscape)
        'orientation' => env('PDF_ORIENTATION', 'portrait'),
        
        // Default font family
        'font' => env('PDF_FONT_FAMILY', 'sans-serif'),
        
        // Font size in pt
        'font_size' => 10,
        
        // Document margins in mm
        'margin' => [
            'top' => 15,
            'right' => 15,
            'bottom' => 15,
            'left' => 15,
        ],
        
        // Header settings
        'header' => [
            'enabled' => true,
            'html' => null,
            'spacing' => 10,
        ],
        
        // Footer settings
        'footer' => [
            'enabled' => true,
            'html' => '<div style="text-align: center; font-size: 8pt; color: #666;">Page {PAGE_NUM} of {PAGE_COUNT}</div>',
            'spacing' => 10,
        ],
        
        // Image DPI
        'dpi' => 96,
        
        // Enable/disable image compression
        'image_compression' => true,
        
        // Enable/disable remote images
        'enable_remote' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Excel Export Settings
    |--------------------------------------------------------------------------
    |
    | Configure the default Excel export settings.
    |
    */
    'excel' => [
        // Default format (Xlsx, Xls, Csv, Ods, Html, etc.)
        'format' => env('EXCEL_FORMAT', 'Xlsx'),
        
        // CSV settings
        'csv' => [
            'delimiter' => ',',
            'enclosure' => '"',
            'line_ending' => "\n",
            'use_bom' => true,
            'include_separator_line' => false,
            'excel_compatibility' => true,
        ],
        
        // Default column width
        'default_column_width' => 15,
        
        // Auto-size columns
        'auto_size' => true,
        
        // Enable/disable pre-calculate formulas
        'calculate' => true,
        
        // Default date format
        'date_format' => 'yyyy-mm-dd',
        
        // Default datetime format
        'datetime_format' => 'yyyy-mm-dd hh:mm:ss',
    ],

    /*
    |--------------------------------------------------------------------------
    | Exportable Models
    |--------------------------------------------------------------------------
    |
    | Define which models are exportable and their corresponding export classes.
    |
    */
    'exportables' => [
        'appointments' => \App\Exports\AppointmentsExport::class,
        'services' => \App\Exports\ServicesExport::class,
        'orders' => \App\Exports\OrdersExport::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Chunking
    |--------------------------------------------------------------------------
    |
    | Configure chunking for large exports to prevent memory issues.
    |
    */
    'chunk_size' => 1000,

    /*
    |--------------------------------------------------------------------------
    | Queue
    |--------------------------------------------------------------------------
    |
    | Configure queue settings for large exports.
    |
    */
    'queue' => [
        'enabled' => env('EXPORT_QUEUE_ENABLED', false),
        'connection' => env('EXPORT_QUEUE_CONNECTION', env('QUEUE_CONNECTION', 'sync')),
        'queue' => env('EXPORT_QUEUE', 'exports'),
        'timeout' => 60 * 10, // 10 minutes
        'tries' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | Configure notifications for completed exports.
    |
    */
    'notifications' => [
        'enabled' => env('EXPORT_NOTIFICATIONS_ENABLED', true),
        'email' => [
            'enabled' => env('EXPORT_EMAIL_NOTIFICATIONS', true),
            'to' => env('EXPORT_EMAIL_TO', env('MAIL_FROM_ADDRESS')),
            'subject' => 'Export Completed',
        ],
        'database' => [
            'enabled' => env('EXPORT_DATABASE_NOTIFICATIONS', true),
            'via_web' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage
    |--------------------------------------------------------------------------
    |
    | Configure storage settings for exported files.
    |
    */
    'storage' => [
        'disk' => env('EXPORT_STORAGE_DISK', 'local'),
        'path' => 'exports',
        'retention_days' => 7, // Number of days to keep exported files
    ],

    /*
    |--------------------------------------------------------------------------
    | Date & Time Formats
    |--------------------------------------------------------------------------
    |
    | Configure default date and time formats for exports.
    |
    */
    'formats' => [
        'date' => 'Y-m-d',
        'time' => 'H:i:s',
        'datetime' => 'Y-m-d H:i:s',
        'timestamp' => 'Y-m-d H:i:s',
    ]
];
