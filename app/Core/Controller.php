<?php

declare(strict_types=1);

namespace App\Core;

use App\Exceptions\AuthorizationException;
use App\Exceptions\ValidationException;

abstract class Controller
{
    protected function view(string $view, array $data = []): never
    {
        Response::view($view, $data);
    }

    protected function json(mixed $data, int $status = 200): never
    {
        Response::json($data, $status);
    }

    protected function success(string $message = 'Operation successful', mixed $data = [], int $status = 200): never
    {
        Response::success($message, $data, $status);
    }

    protected function fail(string $message = 'Operation failed', mixed $data = [], int $status = 400): never
    {
        Response::error($message, $data, $status);
    }

    protected function redirect(string $path): never
    {
        Response::redirect($path);
    }

    protected function downloadFile(string $content, string $filename, string $contentType): never
    {
        Response::download($content, $filename, $contentType);
    }

    protected function back(Request $request, string $fallback = '/'): never
    {
        $referer = $request->header('Referer');
        Response::redirect($referer ?: $fallback);
    }

    /**
     * @param array<string, string> $rules
     */
    protected function validate(Request $request, array $rules, array $messages = []): array
    {
        try {
            return Validator::make($request->all(), $rules, $messages)->validate();
        } catch (ValidationException $e) {
            if ($request->wantsJson()) {
                Response::error('Validasi gagal.', ['errors' => $e->errors()], 422);
            }

            Session::flash('_errors', $e->errors());
            Session::flash('_old_input', $request->all());
            $this->back($request);
        }
    }

    protected function authorize(bool $condition, string $message = 'Anda tidak memiliki akses untuk aksi ini.'): void
    {
        if (!$condition) {
            throw new AuthorizationException($message);
        }
    }

    protected function can(string $permission): bool
    {
        return can($permission);
    }
}
