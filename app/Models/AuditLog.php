<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class AuditLog extends Model
{
    protected static string $table = 'audit_logs';
    protected static bool $timestamps = false;
    protected static array $fillable = [
        'user_id', 'action', 'module', 'table_name', 'record_id',
        'old_values', 'new_values', 'ip_address', 'user_agent', 'created_at',
    ];
}
