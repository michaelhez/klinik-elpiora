<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class PatientRepository
{
    /**
     * @param array{search?:string, gender?:string, status?:string} $filters
     */
    public function paginate(array $filters, int $page, int $perPage): array
    {
        [$where, $bindings] = $this->buildWhere($filters);

        $countStmt = Database::connection()->prepare("SELECT COUNT(*) FROM patients {$where}");
        $countStmt->execute($bindings);
        $total = (int) $countStmt->fetchColumn();

        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT * FROM patients {$where} ORDER BY name ASC LIMIT {$perPage} OFFSET {$offset}";
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

    /**
     * @return array{0: string, 1: array<string, mixed>}
     */
    private function buildWhere(array $filters): array
    {
        $conditions = ['deleted_at IS NULL'];
        $bindings = [];

        if (!empty($filters['search'])) {
            $conditions[] = '(name LIKE :search1 OR medical_record_number LIKE :search2 OR nik LIKE :search3 OR phone LIKE :search4)';
            $needle = '%' . $filters['search'] . '%';
            $bindings['search1'] = $needle;
            $bindings['search2'] = $needle;
            $bindings['search3'] = $needle;
            $bindings['search4'] = $needle;
        }

        if (!empty($filters['gender'])) {
            $conditions[] = 'gender = :gender';
            $bindings['gender'] = $filters['gender'];
        }

        if (!empty($filters['status'])) {
            $conditions[] = 'status = :status';
            $bindings['status'] = $filters['status'];
        }

        return ['WHERE ' . implode(' AND ', $conditions), $bindings];
    }
}
