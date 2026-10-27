<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Request;
use App\Core\Session;
use App\Models\AuditLog;

final class AuditService
{
    public function log(
        string $action,
        string $module,
        ?string $tableName = null,
        int|string|null $recordId = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        $user = Session::get('auth_user');
        $server = $_SERVER;

        AuditLog::create([
            'user_id' => $user['id'] ?? null,
            'action' => $action,
            'module' => $module,
            'table_name' => $tableName,
            'record_id' => $recordId !== null ? (string) $recordId : null,
            'old_values' => $oldValues !== null ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null,
            'new_values' => $newValues !== null ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null,
            'ip_address' => $server['REMOTE_ADDR'] ?? '0.0.0.0',
            'user_agent' => $server['HTTP_USER_AGENT'] ?? 'unknown',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
