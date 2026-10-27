<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    public static function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function success(string $message = 'Operation successful', mixed $data = [], int $status = 200): never
    {
        self::json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    public static function error(string $message = 'Operation failed', mixed $data = [], int $status = 400): never
    {
        self::json([
            'success' => false,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    public static function redirect(string $path, int $status = 302): never
    {
        http_response_code($status);
        header('Location: ' . url($path));
        exit;
    }

    public static function view(string $view, array $data = [], int $status = 200): never
    {
        http_response_code($status);
        echo View::render($view, $data);
        exit;
    }

    public static function download(string $content, string $filename, string $contentType): never
    {
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($content));
        echo $content;
        exit;
    }

    public static function abort(int $status, string $message = ''): never
    {
        http_response_code($status);
        $view = match ($status) {
            403 => 'errors.403',
            404 => 'errors.404',
            419 => 'errors.419',
            429 => 'errors.429',
            default => 'errors.500',
        };
        echo View::render($view, ['message' => $message]);
        exit;
    }
}
