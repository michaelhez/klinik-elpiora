<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class GuestMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, ?string $param = null): void
    {
        if (Session::has('auth_user')) {
            Response::redirect('/dashboard');
        }
    }
}
