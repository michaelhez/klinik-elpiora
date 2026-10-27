<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class DoctorRepository
{
    /**
     * @param array{search?:string, specialization_id?:string, status?:string} $filters
     */
    public function paginate(array $filters, int $page, int $perPage): array
    {
        [$joins, $where, $bindings] = $this->buildWhere($filters);

        $countSql = "SELECT COUNT(DISTINCT d.id) FROM doctors d
                     INNER JOIN employees e ON e.id = d.employee_id
                     {$joins} {$where}";
        $countStmt = Database::connection()->prepare($countSql);
        $countStmt->execute($bindings);
        $total = (int) $countStmt->fetchColumn();

        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT DISTINCT d.*, e.name, e.photo, e.phone, e.email, b.name AS branch_name
                FROM doctors d
                INNER JOIN employees e ON e.id = d.employee_id
                INNER JOIN branches b ON b.id = e.branch_id
                {$joins} {$where}
                ORDER BY e.name ASC
                LIMIT {$perPage} OFFSET {$offset}";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($bindings);

        return [
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int) max(1, ceil($total / $perPage)),
        ];
    }

    public function findWithRelations(int|string $id): ?array
    {
        $sql = "SELECT d.*, e.name, e.photo, e.phone, e.email, e.gender, e.id AS employee_id,
                       b.name AS branch_name
                FROM doctors d
                INNER JOIN employees e ON e.id = d.employee_id
                INNER JOIN branches b ON b.id = e.branch_id
                WHERE d.id = :id AND d.deleted_at IS NULL";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    /**
     * @return array{0: string, 1: string, 2: array<string, mixed>}
     */
    private function buildWhere(array $filters): array
    {
        $conditions = ['d.deleted_at IS NULL'];
        $joins = '';
        $bindings = [];

        if (!empty($filters['search'])) {
            $conditions[] = '(e.name LIKE :search1 OR d.doctor_number LIKE :search2)';
            $needle = '%' . $filters['search'] . '%';
            $bindings['search1'] = $needle;
            $bindings['search2'] = $needle;
        }

        if (!empty($filters['status'])) {
            $conditions[] = 'd.status = :status';
            $bindings['status'] = $filters['status'];
        }

        if (!empty($filters['specialization_id'])) {
            $joins = 'INNER JOIN doctor_specializations ds ON ds.doctor_id = d.id';
            $conditions[] = 'ds.specialization_id = :specialization_id';
            $bindings['specialization_id'] = $filters['specialization_id'];
        }

        return [$joins, 'WHERE ' . implode(' AND ', $conditions), $bindings];
    }
}
