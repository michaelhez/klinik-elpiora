<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Session;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\Role;
use App\Models\User;

final class UserService
{
    public function __construct(private readonly AuditService $auditService = new AuditService())
    {
    }

    public function list(string $search = ''): array
    {
        $sql = "SELECT u.*, GROUP_CONCAT(r.name SEPARATOR ', ') AS role_names
                FROM users u
                LEFT JOIN user_roles ur ON ur.user_id = u.id
                LEFT JOIN roles r ON r.id = ur.role_id AND r.deleted_at IS NULL
                WHERE u.deleted_at IS NULL";
        $bindings = [];

        if ($search !== '') {
            $sql .= ' AND (u.name LIKE :search1 OR u.email LIKE :search2)';
            $bindings['search1'] = "%{$search}%";
            $bindings['search2'] = "%{$search}%";
        }

        $sql .= ' GROUP BY u.id ORDER BY u.name ASC';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll();
    }

    public function find(int|string $id): array
    {
        $user = User::find($id);

        if ($user === null) {
            throw new NotFoundException('Pengguna tidak ditemukan.');
        }

        $user['roles'] = User::roles($id);
        unset($user['password']);

        return $user;
    }

    public function create(array $data, array $roleIds): array
    {
        $this->ensureUniqueEmail($data['email']);
        if (!empty($data['username'])) {
            $this->ensureUniqueUsername($data['username']);
        }

        $id = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'username' => $data['username'] ?? null,
            'phone' => $data['phone'] ?? null,
            'password' => password_hash($data['password'], PASSWORD_BCRYPT),
            'is_active' => $data['is_active'] ?? 1,
        ]);

        if ($roleIds !== []) {
            User::syncRoles($id, $roleIds);
        }

        $this->auditService->log('CREATE', 'user', 'users', $id, null, ['name' => $data['name'], 'email' => $data['email']]);

        return $this->find($id);
    }

    public function update(int|string $id, array $data, array $roleIds): array
    {
        $existing = $this->find($id);
        $this->ensureUniqueEmail($data['email'], $id);
        if (!empty($data['username'])) {
            $this->ensureUniqueUsername($data['username'], $id);
        }

        $update = [
            'name' => $data['name'],
            'email' => $data['email'],
            'username' => $data['username'] ?? null,
            'phone' => $data['phone'] ?? null,
            'is_active' => $data['is_active'] ?? 1,
        ];

        if (!empty($data['password'])) {
            $update['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        User::update($id, $update);
        User::syncRoles($id, $roleIds);

        $this->auditService->log('UPDATE', 'user', 'users', $id, $existing, $update);

        return $this->find($id);
    }

    public function delete(int|string $id): void
    {
        $existing = $this->find($id);
        $currentUserId = Session::get('auth_user')['id'] ?? null;

        if ((string) $id === (string) $currentUserId) {
            throw new ValidationException(['id' => ['Anda tidak dapat menghapus akun Anda sendiri.']]);
        }

        User::delete($id);
        $this->auditService->log('DELETE', 'user', 'users', $id, $existing, null);
    }

    public function resetPassword(int|string $id, string $password): void
    {
        $existing = $this->find($id);

        User::update($id, ['password' => password_hash($password, PASSWORD_BCRYPT)]);
        $this->auditService->log('RESET_PASSWORD', 'user', 'users', $id, null, ['name' => $existing['name']]);
    }

    public function roleOptions(): array
    {
        return Role::query()->orderBy('name', 'ASC')->get();
    }

    private function ensureUniqueEmail(string $email, int|string|null $exceptId = null): void
    {
        $existing = User::findByEmail($email);

        if ($existing !== null && (string) $existing['id'] !== (string) $exceptId) {
            throw new ValidationException(['email' => ['Email sudah digunakan.']]);
        }
    }

    private function ensureUniqueUsername(string $username, int|string|null $exceptId = null): void
    {
        $existing = User::findByUsername($username);

        if ($existing !== null && (string) $existing['id'] !== (string) $exceptId) {
            throw new ValidationException(['username' => ['Username sudah digunakan.']]);
        }
    }
}
