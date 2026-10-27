<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Exceptions\NotFoundException;

final class AuditLogService
{
    public function paginate(array $filters, int $page, int $perPage): array
    {
        $conditions = ['1 = 1'];
        $bindings = [];

        if (!empty($filters['module'])) {
            $conditions[] = 'al.module = :module';
            $bindings['module'] = $filters['module'];
        }

        if (!empty($filters['action'])) {
            $conditions[] = 'al.action = :action';
            $bindings['action'] = $filters['action'];
        }

        if (!empty($filters['user_id'])) {
            $conditions[] = 'al.user_id = :user_id';
            $bindings['user_id'] = $filters['user_id'];
        }

        if (!empty($filters['date_from'])) {
            $conditions[] = 'al.created_at >= :date_from';
            $bindings['date_from'] = $filters['date_from'] . ' 00:00:00';
        }

        if (!empty($filters['date_to'])) {
            $conditions[] = 'al.created_at <= :date_to';
            $bindings['date_to'] = $filters['date_to'] . ' 23:59:59';
        }

        if (!empty($filters['search'])) {
            $conditions[] = '(al.table_name LIKE :search1 OR al.record_id LIKE :search2)';
            $needle = '%' . $filters['search'] . '%';
            $bindings['search1'] = $needle;
            $bindings['search2'] = $needle;
        }

        $where = 'WHERE ' . implode(' AND ', $conditions);

        $countStmt = Database::connection()->prepare("SELECT COUNT(*) FROM audit_logs al {$where}");
        $countStmt->execute($bindings);
        $total = (int) $countStmt->fetchColumn();

        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT al.*, u.name AS user_name
                FROM audit_logs al
                LEFT JOIN users u ON u.id = al.user_id
                {$where}
                ORDER BY al.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($bindings);

        return [
            'data' => $stmt->fetchAll(),
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int) max(1, ceil($total / $perPage)),
        ];
    }

    public function find(int|string $id): array
    {
        $sql = "SELECT al.*, u.name AS user_name FROM audit_logs al LEFT JOIN users u ON u.id = al.user_id WHERE al.id = :id";
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $log = $stmt->fetch();

        if ($log === false) {
            throw new NotFoundException('Log audit tidak ditemukan.');
        }

        return $log;
    }

    public function moduleOptions(): array
    {
        $stmt = Database::connection()->query('SELECT DISTINCT module FROM audit_logs ORDER BY module ASC');
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function actionOptions(): array
    {
        $stmt = Database::connection()->query('SELECT DISTINCT action FROM audit_logs ORDER BY action ASC');
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }
}
