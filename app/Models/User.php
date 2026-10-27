<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class User extends Model
{
    protected static string $table = 'users';
    protected static bool $softDeletes = true;
    protected static array $fillable = [
        'employee_id', 'name', 'email', 'username', 'password', 'avatar',
        'phone', 'is_active', 'last_login_at', 'last_login_ip',
    ];

    public static function findByEmail(string $email): ?array
    {
        return static::findBy('email', $email);
    }

    public static function roles(int|string $userId): array
    {
        $sql = 'SELECT r.* FROM roles r
                INNER JOIN user_roles ur ON ur.role_id = r.id
                WHERE ur.user_id = :user_id AND r.deleted_at IS NULL';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public static function permissions(int|string $userId): array
    {
        $sql = "SELECT DISTINCT p.name FROM permissions p
                INNER JOIN role_permissions rp ON rp.permission_id = p.id
                INNER JOIN user_roles ur ON ur.role_id = rp.role_id
                WHERE ur.user_id = :user_id";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    public static function assignRole(int|string $userId, int|string $roleId): void
    {
        $sql = 'INSERT IGNORE INTO user_roles (user_id, role_id, created_at) VALUES (:user_id, :role_id, NOW())';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['user_id' => $userId, 'role_id' => $roleId]);
    }

    /**
     * @param array<int, int|string> $roleIds
     */
    public static function syncRoles(int|string $userId, array $roleIds): void
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare('DELETE FROM user_roles WHERE user_id = :user_id');
            $stmt->execute(['user_id' => $userId]);

            $insert = $pdo->prepare('INSERT INTO user_roles (user_id, role_id, created_at) VALUES (:user_id, :role_id, NOW())');

            foreach ($roleIds as $roleId) {
                $insert->execute(['user_id' => $userId, 'role_id' => $roleId]);
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function findByUsername(string $username): ?array
    {
        return static::findBy('username', $username);
    }
}
