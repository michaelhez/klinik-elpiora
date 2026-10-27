<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Logger;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class CsrfMiddleware implements MiddlewareInterface
{
    private const SAFE_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    public function handle(Request $request, ?string $param = null): void
    {
        if (in_array($request->method(), self::SAFE_METHODS, true)) {
            return;
        }

        $token = $request->token() ?? $request->header('X-CSRF-TOKEN');

        if (!Session::verifyCsrfToken($token)) {
            Logger::security('CSRF token mismatch', [
                'path' => $request->path(),
                'ip' => $request->ip(),
            ]);

            if ($request->wantsJson()) {
                Response::error('Token keamanan tidak valid. Silakan muat ulang halaman.', [], 419);
            }

            Response::abort(419, 'Token keamanan tidak valid atau kedaluwarsa. Silakan muat ulang halaman.');
        }
    }
}
