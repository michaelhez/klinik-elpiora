<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\Clinic;

final class ClinicService
{
    public function __construct(private readonly AuditService $auditService = new AuditService())
    {
    }

    public function list(string $search = ''): array
    {
        $query = Clinic::query()->orderBy('name', 'ASC');

        if ($search !== '') {
            $query->whereLike('name', $search);
        }

        return $query->get();
    }

    public function find(int|string $id): array
    {
        $clinic = Clinic::find($id);

        if ($clinic === null) {
            throw new NotFoundException('Data klinik tidak ditemukan.');
        }

        return $clinic;
    }

    public function create(array $data): array
    {
        $this->ensureUniqueCode($data['code']);

        $data['is_active'] = $data['is_active'] ?? 1;
        $id = Clinic::create($data);

        $this->auditService->log('CREATE', 'clinic', 'clinics', $id, null, $data);

        return Clinic::findOrFail($id);
    }

    public function update(int|string $id, array $data): array
    {
        $existing = $this->find($id);
        $this->ensureUniqueCode($data['code'], $id);

        Clinic::update($id, $data);
        $this->auditService->log('UPDATE', 'clinic', 'clinics', $id, $existing, $data);

        return Clinic::findOrFail($id);
    }

    public function delete(int|string $id): void
    {
        $existing = $this->find($id);

        $branchCount = (int) Database::connection()
            ->query('SELECT COUNT(*) FROM branches WHERE clinic_id = ' . (int) $id . ' AND deleted_at IS NULL')
            ->fetchColumn();

        if ($branchCount > 0) {
            throw new ValidationException(['code' => ['Klinik tidak dapat dihapus karena masih memiliki cabang aktif.']]);
        }

        Clinic::delete($id);
        $this->auditService->log('DELETE', 'clinic', 'clinics', $id, $existing, null);
    }

    private function ensureUniqueCode(string $code, int|string|null $exceptId = null): void
    {
        $existing = Clinic::findBy('code', $code);

        if ($existing !== null && (string) $existing['id'] !== (string) $exceptId) {
            throw new ValidationException(['code' => ['Kode klinik sudah digunakan.']]);
        }
    }
}
