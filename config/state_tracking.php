<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tracked Models
    |--------------------------------------------------------------------------
    |
    | This array contains a list of models that should have state tracking
    | enabled. Add the fully qualified class name of each model you want to track.
    |
    */
    'tracked_models' => [
        // Example:
        // \App\Models\Transaction::class,
        // \App\Models\TransactionLineItem::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Tracked Events
    |--------------------------------------------------------------------------
    |
    | Specify which events should be tracked. You can disable events you're not
    | interested in to reduce database storage requirements.
    |
    */
    'tracked_events' => [
        'created' => true,
        'updated' => true,
        'deleted' => true,
        'restored' => true,
        'force_deleted' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Tracked Attributes
    |--------------------------------------------------------------------------
    |
    | Specify which model attributes should be tracked. You can either track
    | all attributes ('*') or specify an array of attribute names.
    |
    | You can also specify this per-model by adding a `$trackedAttributes`
    | property to your model.
    |
    */
    'tracked_attributes' => '*',

    /*
    |--------------------------------------------------------------------------
    | Ignored Attributes
    |--------------------------------------------------------------------------
    |
    | Specify which attributes should be ignored when tracking changes.
    | These attributes will not be included in the changes array.
    |
    | You can also specify this per-model by adding a `$ignoredAttributes`
    | property to your model.
    |
    */
    'ignored_attributes' => [
        'created_at',
        'updated_at',
        'deleted_at',
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Settings
    |--------------------------------------------------------------------------
    |
    | Configure the database settings for the state changes table.
    |
    */
    'database' => [
        'connection' => env('DB_CONNECTION', 'mysql'),
        'table' => 'model_state_changes',
    ],
];
