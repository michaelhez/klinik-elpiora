<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class Employee extends Model
{
    protected static string $table = 'employees';
    protected static bool $softDeletes = true;
    protected static array $fillable = [
        'user_id', 'branch_id', 'department_id', 'position_id', 'employee_number',
        'name', 'gender', 'nik', 'phone', 'email', 'address', 'birth_place',
        'birth_date', 'join_date', 'photo', 'status',
    ];

    public static function nextEmployeeNumber(): string
    {
        $year = date('Y');
        $prefix = "EMP-{$year}-";

        $sql = "SELECT employee_number FROM employees WHERE employee_number LIKE :prefix ORDER BY id DESC LIMIT 1";
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['prefix' => $prefix . '%']);
        $last = $stmt->fetchColumn();

        $sequence = $last ? ((int) substr((string) $last, -6)) + 1 : 1;

        return $prefix . str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }

    public static function findByNik(string $nik): ?array
    {
        return static::findBy('nik', $nik);
    }

    public static function withoutDoctorRecord(): array
    {
        $sql = "SELECT e.id, e.employee_number, e.name FROM employees e
                LEFT JOIN doctors d ON d.employee_id = e.id
                WHERE d.id IS NULL AND e.deleted_at IS NULL AND e.status = 'ACTIVE'
                ORDER BY e.name ASC";

        return Database::connection()->query($sql)->fetchAll();
    }
}
