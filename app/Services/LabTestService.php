<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\LabParameter;
use App\Models\LabTest;

final class LabTestService
{
    public function __construct(private readonly AuditService $auditService = new AuditService())
    {
    }

    public function list(string $search = ''): array
    {
        $query = LabTest::query()->orderBy('name', 'ASC');

        if ($search !== '') {
            $query->whereLike('name', $search);
        }

        return $query->get();
    }

    public function find(int|string $id): array
    {
        $test = LabTest::find($id);

        if ($test === null) {
            throw new NotFoundException('Jenis pemeriksaan lab tidak ditemukan.');
        }

        return $test;
    }

    public function create(array $data): array
    {
        $this->ensureUniqueCode($data['code']);
        $data['status'] = $data['status'] ?? 'ACTIVE';

        $id = LabTest::create($data);
        $this->auditService->log('CREATE', 'lab_test', 'lab_tests', $id, null, $data);

        return LabTest::findOrFail($id);
    }

    public function update(int|string $id, array $data): array
    {
        $existing = $this->find($id);
        $this->ensureUniqueCode($data['code'], $id);

        LabTest::update($id, $data);
        $this->auditService->log('UPDATE', 'lab_test', 'lab_tests', $id, $existing, $data);

        return LabTest::findOrFail($id);
    }

    public function delete(int|string $id): void
    {
        $existing = $this->find($id);

        $usage = (int) Database::connection()
            ->query('SELECT COUNT(*) FROM lab_request_items WHERE lab_test_id = ' . (int) $id)
            ->fetchColumn();

        if ($usage > 0) {
            throw new ValidationException(['code' => ['Jenis pemeriksaan tidak dapat dihapus karena sudah pernah digunakan.']]);
        }

        LabTest::delete($id);
        $this->auditService->log('DELETE', 'lab_test', 'lab_tests', $id, $existing, null);
    }

    public function parameters(int|string $labTestId): array
    {
        return LabParameter::forTest($labTestId);
    }

    public function addParameter(int|string $labTestId, array $data): array
    {
        $this->find($labTestId);

        $data['lab_test_id'] = $labTestId;
        $data['reference_min'] = $data['reference_min'] !== '' ? $data['reference_min'] : null;
        $data['reference_max'] = $data['reference_max'] !== '' ? $data['reference_max'] : null;
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $id = LabParameter::create($data);
        $this->auditService->log('CREATE', 'lab_test', 'lab_parameters', $id, null, $data);

        return $this->parameters($labTestId);
    }

    public function updateParameter(int|string $labTestId, int|string $parameterId, array $data): array
    {
        $this->find($labTestId);

        $parameter = LabParameter::find($parameterId);
        if ($parameter === null || (string) $parameter['lab_test_id'] !== (string) $labTestId) {
            throw new NotFoundException('Parameter tidak ditemukan.');
        }

        $data['reference_min'] = $data['reference_min'] !== '' ? $data['reference_min'] : null;
        $data['reference_max'] = $data['reference_max'] !== '' ? $data['reference_max'] : null;

        LabParameter::update($parameterId, $data);
        $this->auditService->log('UPDATE', 'lab_test', 'lab_parameters', $parameterId, $parameter, $data);

        return $this->parameters($labTestId);
    }

    public function deleteParameter(int|string $labTestId, int|string $parameterId): array
    {
        $this->find($labTestId);

        $parameter = LabParameter::find($parameterId);
        if ($parameter === null || (string) $parameter['lab_test_id'] !== (string) $labTestId) {
            throw new NotFoundException('Parameter tidak ditemukan.');
        }

        $usage = (int) Database::connection()
            ->query('SELECT COUNT(*) FROM lab_result_values WHERE lab_parameter_id = ' . (int) $parameterId)
            ->fetchColumn();

        if ($usage > 0) {
            throw new ValidationException(['name' => ['Parameter tidak dapat dihapus karena sudah memiliki data hasil.']]);
        }

        LabParameter::delete($parameterId);
        $this->auditService->log('DELETE', 'lab_test', 'lab_parameters', $parameterId, $parameter, null);

        return $this->parameters($labTestId);
    }

    private function ensureUniqueCode(string $code, int|string|null $exceptId = null): void
    {
        $existing = LabTest::findBy('code', $code);

        if ($existing !== null && (string) $existing['id'] !== (string) $exceptId) {
            throw new ValidationException(['code' => ['Kode pemeriksaan sudah digunakan.']]);
        }
    }
}
