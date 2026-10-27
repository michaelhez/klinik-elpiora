<?php

declare(strict_types=1);

return [
    'name' => env('APP_NAME', 'Clinic Management System'),
    'env' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'key' => env('APP_KEY', ''),
    'timezone' => env('APP_TIMEZONE', 'Asia/Jakarta'),
    'asset_version' => env('APP_ASSET_VERSION', '1'),

    'clinic' => [
        'name' => env('CLINIC_NAME', 'Clinic Management System'),
        'currency' => env('CLINIC_CURRENCY', 'IDR'),
        'date_format' => env('CLINIC_DATE_FORMAT', 'd M Y'),
    ],
];
