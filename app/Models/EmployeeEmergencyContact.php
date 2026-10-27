<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class EmployeeEmergencyContact extends Model
{
    protected static string $table = 'employee_emergency_contacts';
    protected static array $fillable = ['employee_id', 'name', 'relationship', 'phone', 'address'];

    public static function forEmployee(int|string $employeeId): array
    {
        return static::query()->where('employee_id', '=', $employeeId)->orderBy('id', 'ASC')->get();
    }
}
