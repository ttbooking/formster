<?php

use TTBooking\Formster\Handlers\BooleanHandler;
use TTBooking\Formster\Handlers\ColorHandler;
use TTBooking\Formster\Handlers\DateTimeHandler;
use TTBooking\Formster\Handlers\DateTimeZoneHandler;
use TTBooking\Formster\Handlers\EnumHandler;
use TTBooking\Formster\Handlers\FileHandler;
use TTBooking\Formster\Handlers\FloatHandler;
use TTBooking\Formster\Handlers\ImageHandler;
use TTBooking\Formster\Handlers\IntegerHandler;
use TTBooking\Formster\Handlers\StringHandler;

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
        BooleanHandler::class,
        IntegerHandler::class,
        FloatHandler::class,
        StringHandler::class,
        EnumHandler::class,
        DateTimeHandler::class,
        DateTimeZoneHandler::class,
        ColorHandler::class,
        ImageHandler::class,
        FileHandler::class,
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
