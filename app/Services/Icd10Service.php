<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\Icd10Code;

final class Icd10Service
{
    public function __construct(private readonly AuditService $auditService = new AuditService())
    {
    }

    public function list(string $search = ''): array
    {
        $query = Icd10Code::query()->orderBy('code', 'ASC');

        if ($search !== '') {
            $query->whereLike('code', $search);
        }

        return $query->get();
    }

    public function find(int|string $id): array
    {
        $icd10 = Icd10Code::find($id);

        if ($icd10 === null) {
            throw new NotFoundException('Kode ICD-10 tidak ditemukan.');
        }

        return $icd10;
    }

    public function create(array $data): array
    {
        $this->ensureUniqueCode($data['code']);
        $data['status'] = $data['status'] ?? 'ACTIVE';

        $id = Icd10Code::create($data);
        $this->auditService->log('CREATE', 'icd10', 'icd10_codes', $id, null, $data);

        return Icd10Code::findOrFail($id);
    }

    public function update(int|string $id, array $data): array
    {
        $existing = $this->find($id);
        $this->ensureUniqueCode($data['code'], $id);

        Icd10Code::update($id, $data);
        $this->auditService->log('UPDATE', 'icd10', 'icd10_codes', $id, $existing, $data);

        return Icd10Code::findOrFail($id);
    }

    public function delete(int|string $id): void
    {
        $existing = $this->find($id);

        $usage = (int) Database::connection()
            ->query('SELECT COUNT(*) FROM medical_record_diagnoses WHERE icd10_code_id = ' . (int) $id)
            ->fetchColumn();

        if ($usage > 0) {
            throw new ValidationException(['code' => ['Kode ICD-10 tidak dapat dihapus karena sudah digunakan pada rekam medis.']]);
        }

        Icd10Code::delete($id);
        $this->auditService->log('DELETE', 'icd10', 'icd10_codes', $id, $existing, null);
    }

    private function ensureUniqueCode(string $code, int|string|null $exceptId = null): void
    {
        $existing = Icd10Code::findBy('code', $code);

        if ($existing !== null && (string) $existing['id'] !== (string) $exceptId) {
            throw new ValidationException(['code' => ['Kode ICD-10 sudah digunakan.']]);
        }
    }
}
