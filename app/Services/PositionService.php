<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\Department;
use App\Models\Position;

final class PositionService
{
    public function __construct(private readonly AuditService $auditService = new AuditService())
    {
    }

    public function list(string $search = ''): array
    {
        $sql = 'SELECT p.*, d.name AS department_name FROM positions p
                LEFT JOIN departments d ON d.id = p.department_id
                WHERE p.deleted_at IS NULL';

        if ($search !== '') {
            $sql .= ' AND p.name LIKE :search';
        }

        $sql .= ' ORDER BY p.name ASC';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($search !== '' ? ['search' => "%{$search}%"] : []);

        return $stmt->fetchAll();
    }

    public function find(int|string $id): array
    {
        $position = Position::find($id);

        if ($position === null) {
            throw new NotFoundException('Data jabatan tidak ditemukan.');
        }

        return $position;
    }

    public function create(array $data): array
    {
        if (!empty($data['department_id']) && Department::find($data['department_id']) === null) {
            throw new ValidationException(['department_id' => ['Departemen tidak valid.']]);
        }

        $this->ensureUniqueCode($data['code']);
        $data['is_active'] = $data['is_active'] ?? 1;
        $data['department_id'] = $data['department_id'] ?: null;

        $id = Position::create($data);
        $this->auditService->log('CREATE', 'position', 'positions', $id, null, $data);

        return Position::findOrFail($id);
    }

    public function update(int|string $id, array $data): array
    {
        $existing = $this->find($id);
        $this->ensureUniqueCode($data['code'], $id);
        $data['department_id'] = $data['department_id'] ?: null;

        Position::update($id, $data);
        $this->auditService->log('UPDATE', 'position', 'positions', $id, $existing, $data);

        return Position::findOrFail($id);
    }

    public function delete(int|string $id): void
    {
        $existing = $this->find($id);

        $dependents = (int) Database::connection()
            ->query('SELECT COUNT(*) FROM employees WHERE position_id = ' . (int) $id . ' AND deleted_at IS NULL')
            ->fetchColumn();

        if ($dependents > 0) {
            throw new ValidationException(['code' => ['Jabatan tidak dapat dihapus karena masih digunakan karyawan aktif.']]);
        }

        Position::delete($id);
        $this->auditService->log('DELETE', 'position', 'positions', $id, $existing, null);
    }

    private function ensureUniqueCode(string $code, int|string|null $exceptId = null): void
    {
        $existing = Position::findBy('code', $code);

        if ($existing !== null && (string) $existing['id'] !== (string) $exceptId) {
            throw new ValidationException(['code' => ['Kode jabatan sudah digunakan.']]);
        }
    }
}
