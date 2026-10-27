<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Logger;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class PermissionMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, ?string $param = null): void
    {
        if ($param === null) {
            throw new \RuntimeException('PermissionMiddleware requires a permission parameter, e.g. Permission:patient.view');
        }

        $permissions = Session::get('auth_permissions', []);

        if (!in_array($param, $permissions, true)) {
            $user = Session::get('auth_user');

            Logger::security('Permission denied', [
                'user_id' => $user['id'] ?? null,
                'permission' => $param,
                'path' => $request->path(),
                'ip' => $request->ip(),
            ]);

            if ($request->wantsJson()) {
                Response::error('Anda tidak memiliki izin untuk mengakses fitur ini.', [], 403);
            }

            Response::abort(403, 'Anda tidak memiliki izin untuk mengakses fitur ini.');
        }
    }
}
