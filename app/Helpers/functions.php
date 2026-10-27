<?php

declare(strict_types=1);

use App\Core\Session;

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        static $cache = [];

        [$file, $path] = array_pad(explode('.', $key, 2), 2, null);

        if (!isset($cache[$file])) {
            $configPath = dirname(__DIR__, 2) . '/config/' . $file . '.php';
            $cache[$file] = file_exists($configPath) ? require $configPath : [];
        }

        if ($path === null) {
            return $cache[$file] ?: $default;
        }

        $value = $cache[$file];
        foreach (explode('.', $path) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($value === false) {
            return $default;
        }

        return match (strtolower((string) $value)) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'null', '(null)' => null,
            'empty', '(empty)' => '',
            default => $value,
        };
    }
}

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        return dirname(__DIR__, 2) . ($path ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('storage_path')) {
    function storage_path(string $path = ''): string
    {
        return base_path('storage') . ($path ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('public_path')) {
    function public_path(string $path = ''): string
    {
        return base_path('public') . ($path ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        $baseUrl = rtrim(config('app.url', ''), '/');
        return $baseUrl . '/assets/' . ltrim($path, '/') . '?v=' . config('app.asset_version', '1');
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        if (preg_match('#^https?://#i', $path) === 1) {
            return $path;
        }

        $baseUrl = rtrim((string) config('app.url', ''), '/');
        return $baseUrl . '/' . ltrim($path, '/');
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): mixed
    {
        $old = Session::getFlash('_old_input') ?? [];
        return $old[$key] ?? $default;
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return Session::csrfToken();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('method_field')) {
    function method_field(string $method): string
    {
        return '<input type="hidden" name="_method" value="' . e(strtoupper($method)) . '">';
    }
}

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('flash')) {
    function flash(string $type, string $message): void
    {
        Session::flash('_flash_' . $type, $message);
    }
}

if (!function_exists('get_flash')) {
    function get_flash(string $type): ?string
    {
        return Session::getFlash('_flash_' . $type);
    }
}

if (!function_exists('auth_user')) {
    function auth_user(): ?array
    {
        return Session::get('auth_user');
    }
}

if (!function_exists('can')) {
    function can(string $permission): bool
    {
        $permissions = Session::get('auth_permissions', []);
        return in_array($permission, $permissions, true);
    }
}

if (!function_exists('format_currency')) {
    function format_currency(float|int $amount): string
    {
        return 'Rp' . number_format((float) $amount, 0, ',', '.');
    }
}

if (!function_exists('format_date')) {
    function format_date(?string $date, string $format = 'd M Y'): string
    {
        if (!$date) {
            return '-';
        }
        try {
            return (new DateTime($date))->format($format);
        } catch (Exception) {
            return '-';
        }
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path): never
    {
        header('Location: ' . url($path));
        exit;
    }
}

if (!function_exists('dd')) {
    function dd(mixed ...$vars): never
    {
        echo '<pre style="background:#0f172a;color:#f8fafc;padding:16px;border-radius:8px;">';
        foreach ($vars as $var) {
            print_r($var);
            echo "\n";
        }
        echo '</pre>';
        exit;
    }
}
