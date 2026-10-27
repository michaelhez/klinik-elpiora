<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class AuthMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, ?string $param = null): void
    {
        if (!Session::has('auth_user')) {
            if ($request->wantsJson()) {
                Response::error('Sesi Anda telah berakhir. Silakan login kembali.', [], 401);
            }

            Session::flash('_intended_url', $request->path());
            Response::redirect('/login');
        }
    }
}
