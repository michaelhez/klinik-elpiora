<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\Branch;
use App\Models\Polyclinic;

final class PolyclinicService
{
    public function __construct(private readonly AuditService $auditService = new AuditService())
    {
    }

    public function list(string $search = ''): array
    {
        $sql = 'SELECT p.*, b.name AS branch_name FROM polyclinics p
                INNER JOIN branches b ON b.id = p.branch_id
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
        $polyclinic = Polyclinic::find($id);

        if ($polyclinic === null) {
            throw new NotFoundException('Data poliklinik tidak ditemukan.');
        }

        return $polyclinic;
    }

    public function all(): array
    {
        return Polyclinic::query()->where('is_active', '=', 1)->orderBy('name', 'ASC')->get();
    }

    public function create(array $data): array
    {
        if (Branch::find($data['branch_id']) === null) {
            throw new ValidationException(['branch_id' => ['Cabang tidak valid.']]);
        }

        $this->ensureUniqueCode($data['code']);
        $data['is_active'] = $data['is_active'] ?? 1;

        $id = Polyclinic::create($data);
        $this->auditService->log('CREATE', 'polyclinic', 'polyclinics', $id, null, $data);

        return Polyclinic::findOrFail($id);
    }

    public function update(int|string $id, array $data): array
    {
        $existing = $this->find($id);

        if (Branch::find($data['branch_id']) === null) {
            throw new ValidationException(['branch_id' => ['Cabang tidak valid.']]);
        }

        $this->ensureUniqueCode($data['code'], $id);

        Polyclinic::update($id, $data);
        $this->auditService->log('UPDATE', 'polyclinic', 'polyclinics', $id, $existing, $data);

        return Polyclinic::findOrFail($id);
    }

    public function delete(int|string $id): void
    {
        $existing = $this->find($id);

        $dependents = (int) Database::connection()
            ->query('SELECT COUNT(*) FROM doctor_schedules WHERE polyclinic_id = ' . (int) $id)
            ->fetchColumn();

        if ($dependents > 0) {
            throw new ValidationException(['code' => ['Poliklinik tidak dapat dihapus karena masih memiliki jadwal dokter.']]);
        }

        Polyclinic::delete($id);
        $this->auditService->log('DELETE', 'polyclinic', 'polyclinics', $id, $existing, null);
    }

    private function ensureUniqueCode(string $code, int|string|null $exceptId = null): void
    {
        $existing = Polyclinic::findBy('code', $code);

        if ($existing !== null && (string) $existing['id'] !== (string) $exceptId) {
            throw new ValidationException(['code' => ['Kode poliklinik sudah digunakan.']]);
        }
    }
}
