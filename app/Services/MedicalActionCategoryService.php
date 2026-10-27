<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\MedicalActionCategory;

final class MedicalActionCategoryService
{
    public function __construct(private readonly AuditService $auditService = new AuditService())
    {
    }

    public function list(string $search = ''): array
    {
        $query = MedicalActionCategory::query()->orderBy('name', 'ASC');

        if ($search !== '') {
            $query->whereLike('name', $search);
        }

        return $query->get();
    }

    public function find(int|string $id): array
    {
        $category = MedicalActionCategory::find($id);

        if ($category === null) {
            throw new NotFoundException('Kategori tindakan tidak ditemukan.');
        }

        return $category;
    }

    public function create(array $data): array
    {
        $this->ensureUniqueCode($data['code']);
        $data['is_active'] = $data['is_active'] ?? 1;

        $id = MedicalActionCategory::create($data);
        $this->auditService->log('CREATE', 'medical_action_category', 'medical_action_categories', $id, null, $data);

        return MedicalActionCategory::findOrFail($id);
    }

    public function update(int|string $id, array $data): array
    {
        $existing = $this->find($id);
        $this->ensureUniqueCode($data['code'], $id);

        MedicalActionCategory::update($id, $data);
        $this->auditService->log('UPDATE', 'medical_action_category', 'medical_action_categories', $id, $existing, $data);

        return MedicalActionCategory::findOrFail($id);
    }

    public function delete(int|string $id): void
    {
        $existing = $this->find($id);

        $usage = (int) Database::connection()
            ->query('SELECT COUNT(*) FROM medical_actions WHERE category_id = ' . (int) $id . ' AND deleted_at IS NULL')
            ->fetchColumn();

        if ($usage > 0) {
            throw new ValidationException(['code' => ['Kategori tidak dapat dihapus karena masih digunakan oleh tindakan medis.']]);
        }

        MedicalActionCategory::delete($id);
        $this->auditService->log('DELETE', 'medical_action_category', 'medical_action_categories', $id, $existing, null);
    }

    private function ensureUniqueCode(string $code, int|string|null $exceptId = null): void
    {
        $existing = MedicalActionCategory::findBy('code', $code);

        if ($existing !== null && (string) $existing['id'] !== (string) $exceptId) {
            throw new ValidationException(['code' => ['Kode kategori sudah digunakan.']]);
        }
    }
}
