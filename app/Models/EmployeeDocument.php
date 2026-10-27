<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class EmployeeDocument extends Model
{
    protected static string $table = 'employee_documents';
    protected static bool $timestamps = false;
    protected static array $fillable = ['employee_id', 'document_type', 'file_name', 'file_path', 'uploaded_at', 'created_at'];

    public static function forEmployee(int|string $employeeId): array
    {
        return static::query()
            ->where('employee_id', '=', $employeeId)
            ->orderBy('created_at', 'DESC')
            ->get();
    }
}
