<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\Specialization;

final class SpecializationService
{
    public function __construct(private readonly AuditService $auditService = new AuditService())
    {
    }

    public function list(string $search = ''): array
    {
        $query = Specialization::query()->orderBy('name', 'ASC');

        if ($search !== '') {
            $query->whereLike('name', $search);
        }

        return $query->get();
    }

    public function find(int|string $id): array
    {
        $specialization = Specialization::find($id);

        if ($specialization === null) {
            throw new NotFoundException('Data spesialisasi tidak ditemukan.');
        }

        return $specialization;
    }

    public function all(): array
    {
        return Specialization::query()->where('is_active', '=', 1)->orderBy('name', 'ASC')->get();
    }

    public function create(array $data): array
    {
        $this->ensureUniqueCode($data['code']);
        $data['is_active'] = $data['is_active'] ?? 1;

        $id = Specialization::create($data);
        $this->auditService->log('CREATE', 'specialization', 'specializations', $id, null, $data);

        return Specialization::findOrFail($id);
    }

    public function update(int|string $id, array $data): array
    {
        $existing = $this->find($id);
        $this->ensureUniqueCode($data['code'], $id);

        Specialization::update($id, $data);
        $this->auditService->log('UPDATE', 'specialization', 'specializations', $id, $existing, $data);

        return Specialization::findOrFail($id);
    }

    public function delete(int|string $id): void
    {
        $existing = $this->find($id);

        $dependents = (int) Database::connection()
            ->query('SELECT COUNT(*) FROM doctor_specializations WHERE specialization_id = ' . (int) $id)
            ->fetchColumn();

        if ($dependents > 0) {
            throw new ValidationException(['code' => ['Spesialisasi tidak dapat dihapus karena masih digunakan oleh dokter.']]);
        }

        Specialization::delete($id);
        $this->auditService->log('DELETE', 'specialization', 'specializations', $id, $existing, null);
    }

    private function ensureUniqueCode(string $code, int|string|null $exceptId = null): void
    {
        $existing = Specialization::findBy('code', $code);

        if ($existing !== null && (string) $existing['id'] !== (string) $exceptId) {
            throw new ValidationException(['code' => ['Kode spesialisasi sudah digunakan.']]);
        }
    }
}
