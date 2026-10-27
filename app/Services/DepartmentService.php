<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\Branch;
use App\Models\Department;

final class DepartmentService
{
    public function __construct(private readonly AuditService $auditService = new AuditService())
    {
    }

    public function list(string $search = ''): array
    {
        $sql = 'SELECT d.*, b.name AS branch_name FROM departments d
                INNER JOIN branches b ON b.id = d.branch_id
                WHERE d.deleted_at IS NULL';

        if ($search !== '') {
            $sql .= ' AND d.name LIKE :search';
        }

        $sql .= ' ORDER BY d.name ASC';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($search !== '' ? ['search' => "%{$search}%"] : []);

        return $stmt->fetchAll();
    }

    public function find(int|string $id): array
    {
        $department = Department::find($id);

        if ($department === null) {
            throw new NotFoundException('Data departemen tidak ditemukan.');
        }

        return $department;
    }

    public function create(array $data): array
    {
        if (Branch::find($data['branch_id']) === null) {
            throw new ValidationException(['branch_id' => ['Cabang tidak valid.']]);
        }

        $this->ensureUniqueCode($data['code']);
        $data['is_active'] = $data['is_active'] ?? 1;

        $id = Department::create($data);
        $this->auditService->log('CREATE', 'department', 'departments', $id, null, $data);

        return Department::findOrFail($id);
    }

    public function update(int|string $id, array $data): array
    {
        $existing = $this->find($id);

        if (Branch::find($data['branch_id']) === null) {
            throw new ValidationException(['branch_id' => ['Cabang tidak valid.']]);
        }

        $this->ensureUniqueCode($data['code'], $id);

        Department::update($id, $data);
        $this->auditService->log('UPDATE', 'department', 'departments', $id, $existing, $data);

        return Department::findOrFail($id);
    }

    public function delete(int|string $id): void
    {
        $existing = $this->find($id);

        $dependents = (int) Database::connection()
            ->query('SELECT COUNT(*) FROM employees WHERE department_id = ' . (int) $id . ' AND deleted_at IS NULL')
            ->fetchColumn();

        if ($dependents > 0) {
            throw new ValidationException(['code' => ['Departemen tidak dapat dihapus karena masih memiliki karyawan aktif.']]);
        }

        Department::delete($id);
        $this->auditService->log('DELETE', 'department', 'departments', $id, $existing, null);
    }

    private function ensureUniqueCode(string $code, int|string|null $exceptId = null): void
    {
        $existing = Department::findBy('code', $code);

        if ($existing !== null && (string) $existing['id'] !== (string) $exceptId) {
            throw new ValidationException(['code' => ['Kode departemen sudah digunakan.']]);
        }
    }
}
