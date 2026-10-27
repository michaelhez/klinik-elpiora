<?php

declare(strict_types=1);

namespace App\Core;

use Throwable;

final class Application
{
    private Router $router;

    public function __construct(private readonly string $basePath)
    {
        $this->bootstrap();
        $this->router = new Router();
    }

    private function bootstrap(): void
    {
        $dotenvPath = $this->basePath;
        if (file_exists($dotenvPath . '/.env')) {
            $dotenv = \Dotenv\Dotenv::createImmutable($dotenvPath);
            $dotenv->load();
        }

        date_default_timezone_set((string) config('app.timezone', 'Asia/Jakarta'));

        $debug = (bool) config('app.debug', false);
        ini_set('display_errors', $debug ? '1' : '0');
        ini_set('display_startup_errors', $debug ? '1' : '0');
        error_reporting(E_ALL);

        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);

        Session::start();
    }

    public function handleError(int $severity, string $message, string $file, int $line): bool
    {
        if (!(error_reporting() & $severity)) {
            return false;
        }

        Logger::error($message, ['file' => $file, 'line' => $line]);

        if ($severity === E_ERROR || $severity === E_USER_ERROR) {
            $this->renderFatal();
        }

        return true;
    }

    public function handleException(Throwable $e): void
    {
        Logger::error($e->getMessage(), [
            'exception' => $e::class,
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        $this->renderFatal((bool) config('app.debug', false) ? $e : null);
    }

    private function renderFatal(?Throwable $e = null): void
    {
        if (!headers_sent()) {
            http_response_code(500);
        }

        if ($e !== null) {
            echo '<pre style="background:#0f172a;color:#f8fafc;padding:20px;border-radius:8px;white-space:pre-wrap;">';
            echo htmlspecialchars($e::class . ': ' . $e->getMessage() . "\n" . $e->getFile() . ':' . $e->getLine() . "\n\n" . $e->getTraceAsString());
            echo '</pre>';
        } else {
            echo View::render('errors.500');
        }
        exit(1);
    }

    public function router(): Router
    {
        return $this->router;
    }

    public function run(): void
    {
        $request = new Request();
        $this->router->dispatch($request);
    }
}
