<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\Permission;
use App\Models\Role;

final class RoleService
{
    public function __construct(private readonly AuditService $auditService = new AuditService())
    {
    }

    public function list(): array
    {
        $sql = "SELECT r.*, COUNT(DISTINCT ur.user_id) AS user_count, COUNT(DISTINCT rp.permission_id) AS permission_count
                FROM roles r
                LEFT JOIN user_roles ur ON ur.role_id = r.id
                LEFT JOIN role_permissions rp ON rp.role_id = r.id
                WHERE r.deleted_at IS NULL
                GROUP BY r.id
                ORDER BY r.name ASC";

        return Database::connection()->query($sql)->fetchAll();
    }

    public function find(int|string $id): array
    {
        $role = Role::find($id);

        if ($role === null) {
            throw new NotFoundException('Peran tidak ditemukan.');
        }

        $role['permission_ids'] = $this->assignedPermissionIds($id);

        return $role;
    }

    public function permissionCatalogue(): array
    {
        $permissions = Permission::query()->orderBy('module', 'ASC')->orderBy('action', 'ASC')->get();

        $grouped = [];
        foreach ($permissions as $permission) {
            $grouped[$permission['module']][] = $permission;
        }

        return $grouped;
    }

    public function create(array $data, array $permissionIds): array
    {
        $data['slug'] = $this->slugify($data['name']);
        $this->ensureUniqueSlug($data['slug']);

        $id = Role::create([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? null,
            'is_system' => 0,
        ]);

        if ($permissionIds !== []) {
            Role::syncPermissions($id, $permissionIds);
        }

        $this->auditService->log('CREATE', 'role', 'roles', $id, null, $data);

        return $this->find($id);
    }

    public function update(int|string $id, array $data, array $permissionIds): array
    {
        $existing = $this->find($id);

        if ((bool) $existing['is_system']) {
            throw new ValidationException(['name' => ['Peran sistem tidak dapat diubah namanya, hanya izin aksesnya.']]);
        }

        $slug = $this->slugify($data['name']);
        $this->ensureUniqueSlug($slug, $id);

        Role::update($id, [
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
        ]);

        Role::syncPermissions($id, $permissionIds);

        $this->auditService->log('UPDATE', 'role', 'roles', $id, $existing, $data);

        return $this->find($id);
    }

    public function updatePermissionsOnly(int|string $id, array $permissionIds): array
    {
        $existing = $this->find($id);

        Role::syncPermissions($id, $permissionIds);

        $this->auditService->log('UPDATE', 'role', 'roles', $id, $existing, ['permission_ids' => $permissionIds]);

        return $this->find($id);
    }

    public function delete(int|string $id): void
    {
        $existing = $this->find($id);

        if ((bool) $existing['is_system']) {
            throw new ValidationException(['name' => ['Peran sistem tidak dapat dihapus.']]);
        }

        $stmt = Database::connection()->prepare('SELECT COUNT(*) FROM user_roles WHERE role_id = :id');
        $stmt->execute(['id' => $id]);

        if ((int) $stmt->fetchColumn() > 0) {
            throw new ValidationException(['name' => ['Peran tidak dapat dihapus karena masih digunakan oleh pengguna.']]);
        }

        Role::delete($id);
        $this->auditService->log('DELETE', 'role', 'roles', $id, $existing, null);
    }

    private function assignedPermissionIds(int|string $roleId): array
    {
        $stmt = Database::connection()->prepare('SELECT permission_id FROM role_permissions WHERE role_id = :role_id');
        $stmt->execute(['role_id' => $roleId]);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    private function slugify(string $name): string
    {
        return strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '_', $name), '_'));
    }

    private function ensureUniqueSlug(string $slug, int|string|null $exceptId = null): void
    {
        $existing = Role::findBySlug($slug);

        if ($existing !== null && (string) $existing['id'] !== (string) $exceptId) {
            throw new ValidationException(['name' => ['Nama peran menghasilkan slug yang sudah digunakan, gunakan nama lain.']]);
        }
    }
}
