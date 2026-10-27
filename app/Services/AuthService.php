<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Logger;
use App\Core\Session;
use App\Models\LoginAttempt;
use App\Models\User;
use App\Services\AuditService;

final class AuthService
{
    public function __construct(private readonly AuditService $auditService = new AuditService())
    {
    }

    public function tooManyAttempts(string $email, string $ip): bool
    {
        $maxAttempts = (int) config('auth.login.max_attempts', 5);
        $lockoutMinutes = (int) config('auth.login.lockout_minutes', 15);

        return LoginAttempt::recentFailedCount($email, $ip, $lockoutMinutes) >= $maxAttempts;
    }

    public function lockoutMinutes(): int
    {
        return (int) config('auth.login.lockout_minutes', 15);
    }

    /**
     * @return array{success: bool, message: string}
     */
    public function attempt(string $email, string $password, string $ip, string $userAgent): array
    {
        $user = User::findByEmail($email);

        if ($user === null || !password_verify($password, $user['password'])) {
            LoginAttempt::record($email, $ip, false);
            Logger::security('Failed login attempt', ['email' => $email, 'ip' => $ip]);

            return ['success' => false, 'message' => 'Email atau kata sandi salah.'];
        }

        if ((int) $user['is_active'] !== 1) {
            LoginAttempt::record($email, $ip, false);

            return ['success' => false, 'message' => 'Akun Anda dinonaktifkan. Hubungi administrator.'];
        }

        LoginAttempt::record($email, $ip, true);

        $this->login($user, $ip);

        return ['success' => true, 'message' => 'Login berhasil.'];
    }

    private function login(array $user, string $ip): void
    {
        Session::regenerate();

        $roles = User::roles($user['id']);
        $permissions = User::permissions($user['id']);

        unset($user['password']);

        Session::set('auth_user', $user);
        Session::set('auth_roles', array_column($roles, 'slug'));
        Session::set('auth_permissions', $permissions);

        User::update($user['id'], [
            'last_login_at' => date('Y-m-d H:i:s'),
            'last_login_ip' => $ip,
        ]);

        $this->auditService->log('LOGIN', 'auth', null, null, null, null);
    }

    public function logout(): void
    {
        $this->auditService->log('LOGOUT', 'auth', null, null, null, null);

        Session::destroy();
        Session::start();
        Session::regenerate();
    }
}
