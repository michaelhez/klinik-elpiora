<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\Branch;
use App\Models\Clinic;

final class BranchService
{
    public function __construct(private readonly AuditService $auditService = new AuditService())
    {
    }

    public function list(string $search = ''): array
    {
        $sql = 'SELECT b.*, c.name AS clinic_name FROM branches b
                INNER JOIN clinics c ON c.id = b.clinic_id
                WHERE b.deleted_at IS NULL';

        if ($search !== '') {
            $sql .= ' AND b.name LIKE :search';
        }

        $sql .= ' ORDER BY b.name ASC';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($search !== '' ? ['search' => "%{$search}%"] : []);

        return $stmt->fetchAll();
    }

    public function find(int|string $id): array
    {
        $branch = Branch::find($id);

        if ($branch === null) {
            throw new NotFoundException('Data cabang tidak ditemukan.');
        }

        return $branch;
    }

    public function create(array $data): array
    {
        if (Clinic::find($data['clinic_id']) === null) {
            throw new ValidationException(['clinic_id' => ['Klinik tidak valid.']]);
        }

        $this->ensureUniqueCode($data['code']);
        $data['is_main'] = $data['is_main'] ?? 0;
        $data['is_active'] = $data['is_active'] ?? 1;

        $id = Branch::create($data);
        $this->auditService->log('CREATE', 'branch', 'branches', $id, null, $data);

        return Branch::findOrFail($id);
    }

    public function update(int|string $id, array $data): array
    {
        $existing = $this->find($id);

        if (Clinic::find($data['clinic_id']) === null) {
            throw new ValidationException(['clinic_id' => ['Klinik tidak valid.']]);
        }

        $this->ensureUniqueCode($data['code'], $id);

        Branch::update($id, $data);
        $this->auditService->log('UPDATE', 'branch', 'branches', $id, $existing, $data);

        return Branch::findOrFail($id);
    }

    public function delete(int|string $id): void
    {
        $existing = $this->find($id);

        $dependents = (int) Database::connection()
            ->query('SELECT COUNT(*) FROM employees WHERE branch_id = ' . (int) $id . ' AND deleted_at IS NULL')
            ->fetchColumn();

        if ($dependents > 0) {
            throw new ValidationException(['code' => ['Cabang tidak dapat dihapus karena masih memiliki karyawan aktif.']]);
        }

        Branch::delete($id);
        $this->auditService->log('DELETE', 'branch', 'branches', $id, $existing, null);
    }

    private function ensureUniqueCode(string $code, int|string|null $exceptId = null): void
    {
        $existing = Branch::findBy('code', $code);

        if ($existing !== null && (string) $existing['id'] !== (string) $exceptId) {
            throw new ValidationException(['code' => ['Kode cabang sudah digunakan.']]);
        }
    }
}
