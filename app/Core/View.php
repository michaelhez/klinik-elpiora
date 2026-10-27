<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    private static ?string $layout = null;
    private static array $sections = [];
    private static array $sectionStack = [];

    public static function render(string $view, array $data = []): string
    {
        self::$layout = null;
        self::$sections = [];

        $content = self::renderFile($view, $data);

        if (self::$layout !== null) {
            $data['content'] = $content;
            return self::renderFile(self::$layout, $data);
        }

        return $content;
    }

    private static function renderFile(string $view, array $data): string
    {
        $path = base_path('resources/views/' . str_replace('.', '/', $view) . '.php');

        if (!file_exists($path)) {
            throw new \App\Exceptions\NotFoundException("View [{$view}] not found.");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $path;
        return ob_get_clean();
    }

    public static function extend(string $layout): void
    {
        self::$layout = $layout;
    }

    public static function section(string $name): void
    {
        self::$sectionStack[] = $name;
        ob_start();
    }

    public static function endSection(): void
    {
        $name = array_pop(self::$sectionStack);
        self::$sections[$name] = ob_get_clean();
    }

    public static function yield(string $name, string $default = ''): string
    {
        return self::$sections[$name] ?? $default;
    }

    public static function include(string $view, array $data = []): string
    {
        return self::renderFile($view, $data);
    }
}
