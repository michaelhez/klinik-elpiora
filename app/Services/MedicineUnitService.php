<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\MedicineUnit;

final class MedicineUnitService
{
    public function __construct(private readonly AuditService $auditService = new AuditService())
    {
    }

    public function list(string $search = ''): array
    {
        $query = MedicineUnit::query()->orderBy('name', 'ASC');

        if ($search !== '') {
            $query->whereLike('name', $search);
        }

        return $query->get();
    }

    public function find(int|string $id): array
    {
        $unit = MedicineUnit::find($id);

        if ($unit === null) {
            throw new NotFoundException('Satuan obat tidak ditemukan.');
        }

        return $unit;
    }

    public function create(array $data): array
    {
        $this->ensureUniqueCode($data['code']);
        $data['is_active'] = $data['is_active'] ?? 1;

        $id = MedicineUnit::create($data);
        $this->auditService->log('CREATE', 'medicine_unit', 'medicine_units', $id, null, $data);

        return MedicineUnit::findOrFail($id);
    }

    public function update(int|string $id, array $data): array
    {
        $existing = $this->find($id);
        $this->ensureUniqueCode($data['code'], $id);

        MedicineUnit::update($id, $data);
        $this->auditService->log('UPDATE', 'medicine_unit', 'medicine_units', $id, $existing, $data);

        return MedicineUnit::findOrFail($id);
    }

    public function delete(int|string $id): void
    {
        $existing = $this->find($id);

        $usage = (int) Database::connection()
            ->query('SELECT COUNT(*) FROM medicines WHERE unit_id = ' . (int) $id . ' AND deleted_at IS NULL')
            ->fetchColumn();

        if ($usage > 0) {
            throw new ValidationException(['code' => ['Satuan tidak dapat dihapus karena masih digunakan oleh obat.']]);
        }

        MedicineUnit::delete($id);
        $this->auditService->log('DELETE', 'medicine_unit', 'medicine_units', $id, $existing, null);
    }

    private function ensureUniqueCode(string $code, int|string|null $exceptId = null): void
    {
        $existing = MedicineUnit::findBy('code', $code);

        if ($existing !== null && (string) $existing['id'] !== (string) $exceptId) {
            throw new ValidationException(['code' => ['Kode satuan sudah digunakan.']]);
        }
    }
}
