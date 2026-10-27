<?php

declare(strict_types=1);

namespace App\Core;

final class Logger
{
    public static function info(string $message, array $context = []): void
    {
        self::write('app.log', 'INFO', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::write('error.log', 'ERROR', $message, $context);
    }

    public static function security(string $message, array $context = []): void
    {
        self::write('security.log', 'SECURITY', $message, $context);
    }

    private static function write(string $file, string $level, string $message, array $context): void
    {
        $path = storage_path('logs/' . $file);

        $line = sprintf(
            '[%s] %s: %s %s%s',
            date('Y-m-d H:i:s'),
            $level,
            $message,
            $context !== [] ? json_encode($context, JSON_UNESCAPED_SLASHES) : '',
            PHP_EOL
        );

        error_log($line, 3, $path);
    }
}
