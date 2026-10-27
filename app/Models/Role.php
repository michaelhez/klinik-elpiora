<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class Role extends Model
{
    protected static string $table = 'roles';
    protected static bool $softDeletes = true;
    protected static array $fillable = ['name', 'slug', 'description', 'is_system'];

    public static function findBySlug(string $slug): ?array
    {
        return static::findBy('slug', $slug);
    }

    public static function syncPermissions(int|string $roleId, array $permissionIds): void
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare('DELETE FROM role_permissions WHERE role_id = :role_id');
            $stmt->execute(['role_id' => $roleId]);

            $insert = $pdo->prepare(
                'INSERT INTO role_permissions (role_id, permission_id, created_at) VALUES (:role_id, :permission_id, NOW())'
            );

            foreach ($permissionIds as $permissionId) {
                $insert->execute(['role_id' => $roleId, 'permission_id' => $permissionId]);
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
