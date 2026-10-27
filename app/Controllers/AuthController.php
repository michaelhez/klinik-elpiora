<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Services\AuthService;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService = new AuthService())
    {
    }

    public function showLogin(Request $request): never
    {
        $this->view('auth.login', [
            'title' => 'Login',
        ]);
    }

    public function login(Request $request): never
    {
        $data = $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $email = (string) $data['email'];
        $ip = $request->ip();

        if ($this->authService->tooManyAttempts($email, $ip)) {
            $minutes = $this->authService->lockoutMinutes();
            Session::flash('_errors', ['email' => ["Terlalu banyak percobaan login. Coba lagi dalam {$minutes} menit."]]);
            Session::flash('_old_input', ['email' => $email]);

            if ($request->wantsJson()) {
                $this->fail("Terlalu banyak percobaan login. Coba lagi dalam {$minutes} menit.", [], 429);
            }

            $this->redirect('/login');
        }

        $result = $this->authService->attempt($email, (string) $data['password'], $ip, $request->userAgent());

        if (!$result['success']) {
            Session::flash('_errors', ['email' => [$result['message']]]);
            Session::flash('_old_input', ['email' => $email]);

            if ($request->wantsJson()) {
                $this->fail($result['message'], [], 401);
            }

            $this->redirect('/login');
        }

        $intended = Session::getFlash('_intended_url') ?? '/dashboard';

        if ($request->wantsJson()) {
            $this->success('Login berhasil.', ['redirect' => $intended]);
        }

        $this->redirect($intended);
    }

    public function logout(Request $request): never
    {
        $this->authService->logout();
        $this->redirect('/login');
    }
}
