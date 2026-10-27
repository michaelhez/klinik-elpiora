<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class EmployeeRepository
{
    /**
     * @param array{search?:string, branch_id?:string, department_id?:string, status?:string} $filters
     */
    public function paginate(array $filters, int $page, int $perPage): array
    {
        [$where, $bindings] = $this->buildWhere($filters);

        $countSql = "SELECT COUNT(*) FROM employees e {$where}";
        $countStmt = Database::connection()->prepare($countSql);
        $countStmt->execute($bindings);
        $total = (int) $countStmt->fetchColumn();

        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT e.*, b.name AS branch_name, d.name AS department_name, p.name AS position_name
                FROM employees e
                INNER JOIN branches b ON b.id = e.branch_id
                LEFT JOIN departments d ON d.id = e.department_id
                LEFT JOIN positions p ON p.id = e.position_id
                {$where}
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
        $sql = "SELECT e.*, b.name AS branch_name, d.name AS department_name, p.name AS position_name
                FROM employees e
                INNER JOIN branches b ON b.id = e.branch_id
                LEFT JOIN departments d ON d.id = e.department_id
                LEFT JOIN positions p ON p.id = e.position_id
                WHERE e.id = :id AND e.deleted_at IS NULL";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    /**
     * @return array{0: string, 1: array<string, mixed>}
     */
    private function buildWhere(array $filters): array
    {
        $conditions = ['e.deleted_at IS NULL'];
        $bindings = [];

        if (!empty($filters['search'])) {
            $conditions[] = '(e.name LIKE :search1 OR e.employee_number LIKE :search2 OR e.nik LIKE :search3)';
            $needle = '%' . $filters['search'] . '%';
            $bindings['search1'] = $needle;
            $bindings['search2'] = $needle;
            $bindings['search3'] = $needle;
        }

        if (!empty($filters['branch_id'])) {
            $conditions[] = 'e.branch_id = :branch_id';
            $bindings['branch_id'] = $filters['branch_id'];
        }

        if (!empty($filters['department_id'])) {
            $conditions[] = 'e.department_id = :department_id';
            $bindings['department_id'] = $filters['department_id'];
        }

        if (!empty($filters['status'])) {
            $conditions[] = 'e.status = :status';
            $bindings['status'] = $filters['status'];
        }

        return ['WHERE ' . implode(' AND ', $conditions), $bindings];
    }
}
