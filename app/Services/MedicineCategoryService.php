<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\MedicineCategory;

final class MedicineCategoryService
{
    public function __construct(private readonly AuditService $auditService = new AuditService())
    {
    }

    public function list(string $search = ''): array
    {
        $query = MedicineCategory::query()->orderBy('name', 'ASC');

        if ($search !== '') {
            $query->whereLike('name', $search);
        }

        return $query->get();
    }

    public function find(int|string $id): array
    {
        $category = MedicineCategory::find($id);

        if ($category === null) {
            throw new NotFoundException('Kategori obat tidak ditemukan.');
        }

        return $category;
    }

    public function create(array $data): array
    {
        $this->ensureUniqueCode($data['code']);
        $data['is_active'] = $data['is_active'] ?? 1;

        $id = MedicineCategory::create($data);
        $this->auditService->log('CREATE', 'medicine_category', 'medicine_categories', $id, null, $data);

        return MedicineCategory::findOrFail($id);
    }

    public function update(int|string $id, array $data): array
    {
        $existing = $this->find($id);
        $this->ensureUniqueCode($data['code'], $id);

        MedicineCategory::update($id, $data);
        $this->auditService->log('UPDATE', 'medicine_category', 'medicine_categories', $id, $existing, $data);

        return MedicineCategory::findOrFail($id);
    }

    public function delete(int|string $id): void
    {
        $existing = $this->find($id);

        $usage = (int) Database::connection()
            ->query('SELECT COUNT(*) FROM medicines WHERE category_id = ' . (int) $id . ' AND deleted_at IS NULL')
            ->fetchColumn();

        if ($usage > 0) {
            throw new ValidationException(['code' => ['Kategori tidak dapat dihapus karena masih digunakan oleh obat.']]);
        }

        MedicineCategory::delete($id);
        $this->auditService->log('DELETE', 'medicine_category', 'medicine_categories', $id, $existing, null);
    }

    private function ensureUniqueCode(string $code, int|string|null $exceptId = null): void
    {
        $existing = MedicineCategory::findBy('code', $code);

        if ($existing !== null && (string) $existing['id'] !== (string) $exceptId) {
            throw new ValidationException(['code' => ['Kode kategori sudah digunakan.']]);
        }
    }
}
