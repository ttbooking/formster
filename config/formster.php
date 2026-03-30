<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Property Parser
    |--------------------------------------------------------------------------
    */

    'property_parser' => env('FORMSTER_PROPERTY_PARSER', 'phpstan,reflection'),

    /*
    |--------------------------------------------------------------------------
    | Property Cache Options
    |--------------------------------------------------------------------------
    */

    'property_cache' => [
        'store' => env('FORMSTER_PROPERTY_CACHE_STORE'),
        'ttl' => (int) env('FORMSTER_PROPERTY_CACHE_TTL') ?: null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Active Property Handlers
    |--------------------------------------------------------------------------
    */

    'property_handlers' => [
        TTBooking\Formster\Handlers\BooleanHandler::class,
        TTBooking\Formster\Handlers\IntegerHandler::class,
        TTBooking\Formster\Handlers\FloatHandler::class,
        TTBooking\Formster\Handlers\StringHandler::class,
        TTBooking\Formster\Handlers\EnumHandler::class,
        TTBooking\Formster\Handlers\DateTimeHandler::class,
        TTBooking\Formster\Handlers\DateTimeZoneHandler::class,
        TTBooking\Formster\Handlers\ColorHandler::class,
        TTBooking\Formster\Handlers\ImageHandler::class,
        TTBooking\Formster\Handlers\FileHandler::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | File Pseudotype Configuration
    |--------------------------------------------------------------------------
    */

    'file' => [
        'disk' => env('FORMSTER_DISK'),
        'static_disk' => env('FORMSTER_STATIC_DISK', env('FORMSTER_DISK')),
        'content_disposition' => env('FORMSTER_CONTENT_DISPOSITION', 'attachment'),
        'show_uploaded_name' => (bool) env('FORMSTER_SHOW_FILENAME', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Pseudotype Preview Options
    |--------------------------------------------------------------------------
    */

    'preview' => [
        'width' => (int) env('FORMSTER_PREVIEW_WIDTH', 100),
        'height' => (int) env('FORMSTER_PREVIEW_HEIGHT', 100),
        'scale_down_threshold' => (int) env('FORMSTER_PREVIEW_SCALE_DOWN_THRESHOLD', 10_240),
    ],

];
