<?php

declare(strict_types=1);

return [
    'session_key' => 'auth_user',
    'session_lifetime' => (int) env('SESSION_LIFETIME', 120),
    'secure_cookie' => (bool) env('SESSION_SECURE_COOKIE', false),
    'password_min_length' => 8,
    'login' => [
        'max_attempts' => (int) env('LOGIN_MAX_ATTEMPTS', 5),
        'lockout_minutes' => (int) env('LOGIN_LOCKOUT_MINUTES', 15),
    ],
];
