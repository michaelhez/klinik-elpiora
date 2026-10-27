<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Session;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\LabParameter;
use App\Models\LabRequest;
use App\Models\LabRequestItem;
use App\Models\LabResult;
use App\Models\LabResultValue;
use App\Models\LabSample;
use App\Models\LabTest;
use App\Models\MedicalRecord;

final class LabRequestService
{
    private const ACTIVE_STATUSES = ['REQUESTED', 'SAMPLE_COLLECTED', 'PROCESSING'];

    public function __construct(private readonly AuditService $auditService = new AuditService())
    {
    }

    public function queue(array $statuses = []): array
    {
        return LabRequest::queue($statuses);
    }

    public function find(int|string $id): array
    {
        $request = LabRequest::findWithRelations($id);

        if ($request === null) {
            throw new NotFoundException('Permintaan lab tidak ditemukan.');
        }

        $items = LabRequestItem::forRequest($id);

        foreach ($items as &$item) {
            $item['parameters'] = LabParameter::forTest($item['lab_test_id']);
            $item['result_values'] = !empty($item['result_id']) ? LabResultValue::forResult($item['result_id']) : [];
        }
        unset($item);

        $request['items'] = $items;
        $request['samples'] = LabSample::forRequest($id);

        return $request;
    }

    public function forMedicalRecord(int|string $medicalRecordId): array
    {
        $requests = LabRequest::forMedicalRecord($medicalRecordId);

        foreach ($requests as &$request) {
            $request['items'] = LabRequestItem::forRequest($request['id']);
        }

        return $requests;
    }

    public function create(array $data): array
    {
        $medicalRecord = MedicalRecord::find($data['medical_record_id']);
        if ($medicalRecord === null) {
            throw new ValidationException(['medical_record_id' => ['Rekam medis tidak valid.']]);
        }

        $testIds = (array) $data['lab_test_ids'];
        if ($testIds === []) {
            throw new ValidationException(['lab_test_ids' => ['Pilih minimal satu jenis pemeriksaan.']]);
        }

        $userId = Session::get('auth_user')['id'] ?? null;

        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $requestId = LabRequest::create([
                'request_number' => LabRequest::nextRequestNumber(),
                'medical_record_id' => $data['medical_record_id'],
                'patient_id' => $medicalRecord['patient_id'],
                'doctor_id' => $medicalRecord['doctor_id'],
                'status' => 'REQUESTED',
                'notes' => $data['notes'] ?? null,
                'requested_by' => $userId,
                'requested_at' => date('Y-m-d H:i:s'),
            ]);

            foreach ($testIds as $testId) {
                $test = LabTest::find($testId);
                if ($test === null) {
                    throw new ValidationException(['lab_test_ids' => ['Salah satu jenis pemeriksaan tidak valid.']]);
                }

                LabRequestItem::create([
                    'lab_request_id' => $requestId,
                    'lab_test_id' => $testId,
                    'price' => $test['price'],
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        $this->auditService->log('CREATE', 'laboratory', 'lab_requests', $requestId, null, $data);

        return $this->find($requestId);
    }

    public function collectSample(int|string $id, array $data): array
    {
        $request = $this->find($id);

        if ($request['status'] !== 'REQUESTED') {
            throw new ValidationException(['status' => ['Sampel hanya dapat diambil untuk permintaan berstatus Diminta.']]);
        }

        $userId = Session::get('auth_user')['id'] ?? null;

        $sampleId = LabSample::create([
            'lab_request_id' => $id,
            'sample_number' => LabSample::nextSampleNumber(),
            'sample_type' => $data['sample_type'],
            'collected_at' => date('Y-m-d H:i:s'),
            'collected_by' => $userId,
            'notes' => $data['notes'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        LabRequest::update($id, ['status' => 'SAMPLE_COLLECTED']);
        $this->auditService->log('UPDATE', 'laboratory', 'lab_requests', $id, $request, ['status' => 'SAMPLE_COLLECTED']);

        return $this->find($id);
    }

    /**
     * @param array<int|string, string> $values Keyed by lab_parameter_id.
     */
    public function submitResult(int|string $id, int|string $itemId, array $values): array
    {
        $request = $this->find($id);

        if (!in_array($request['status'], ['SAMPLE_COLLECTED', 'PROCESSING'], true)) {
            throw new ValidationException(['status' => ['Sampel belum diambil untuk permintaan ini.']]);
        }

        $item = LabRequestItem::find($itemId);
        if ($item === null || (string) $item['lab_request_id'] !== (string) $id) {
            throw new NotFoundException('Item pemeriksaan tidak ditemukan.');
        }

        if ($values === []) {
            throw new ValidationException(['values' => ['Isi minimal satu nilai parameter.']]);
        }

        $userId = Session::get('auth_user')['id'] ?? null;
        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $existingResult = LabResult::forItem($itemId);
            $resultId = $existingResult['id'] ?? LabResult::create([
                'lab_request_item_id' => $itemId,
                'performed_by' => $userId,
                'performed_at' => date('Y-m-d H:i:s'),
            ]);

            if ($existingResult !== null) {
                LabResult::update($resultId, ['performed_by' => $userId, 'performed_at' => date('Y-m-d H:i:s')]);
            }

            foreach ($values as $parameterId => $value) {
                if ($value === '') {
                    continue;
                }

                $parameter = LabParameter::find($parameterId);
                if ($parameter === null || (string) $parameter['lab_test_id'] !== (string) $item['lab_test_id']) {
                    continue;
                }

                $flag = $this->calculateFlag($parameter, $value);

                $existingValue = LabResultValue::query()
                    ->where('lab_result_id', '=', $resultId)
                    ->where('lab_parameter_id', '=', $parameterId)
                    ->first();

                if ($existingValue !== null) {
                    LabResultValue::update($existingValue['id'], ['value' => $value, 'flag' => $flag]);
                } else {
                    LabResultValue::create([
                        'lab_result_id' => $resultId,
                        'lab_parameter_id' => $parameterId,
                        'value' => $value,
                        'flag' => $flag,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            }

            if ($request['status'] === 'SAMPLE_COLLECTED') {
                LabRequest::update($id, ['status' => 'PROCESSING']);
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        $this->autoCompleteIfReady($id);
        $this->auditService->log('UPDATE', 'laboratory', 'lab_results', $resultId, null, $values);

        return $this->find($id);
    }

    public function validateRequest(int|string $id): array
    {
        $request = $this->find($id);

        if ($request['status'] !== 'COMPLETED') {
            throw new ValidationException(['status' => ['Hanya permintaan yang sudah selesai diproses yang dapat divalidasi.']]);
        }

        $userId = Session::get('auth_user')['id'] ?? null;

        foreach ($request['items'] as $item) {
            if (!empty($item['result_id'])) {
                LabResult::update($item['result_id'], [
                    'validated_by' => $userId,
                    'validated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        LabRequest::update($id, ['status' => 'VALIDATED']);
        $this->auditService->log('VALIDATE', 'laboratory', 'lab_requests', $id, $request, ['status' => 'VALIDATED']);

        return $this->find($id);
    }

    public function cancel(int|string $id): array
    {
        $request = $this->find($id);

        if (in_array($request['status'], ['COMPLETED', 'VALIDATED', 'CANCELLED'], true)) {
            throw new ValidationException(['status' => ['Permintaan ini tidak dapat dibatalkan.']]);
        }

        LabRequest::update($id, ['status' => 'CANCELLED']);
        $this->auditService->log('CANCEL', 'laboratory', 'lab_requests', $id, $request, ['status' => 'CANCELLED']);

        return $this->find($id);
    }

    public function resultDetail(int|string $itemId): array
    {
        $result = LabResult::forItem($itemId);

        if ($result === null) {
            return ['result' => null, 'values' => []];
        }

        return ['result' => $result, 'values' => LabResultValue::forResult($result['id'])];
    }

    private function autoCompleteIfReady(int|string $id): void
    {
        $items = LabRequestItem::forRequest($id);

        foreach ($items as $item) {
            if (empty($item['performed_at'])) {
                return;
            }
        }

        LabRequest::update($id, ['status' => 'COMPLETED']);
    }

    private function calculateFlag(array $parameter, string $value): ?string
    {
        if ($parameter['value_type'] !== 'NUMERIC' || !is_numeric($value)) {
            return null;
        }

        if ($parameter['reference_min'] === null && $parameter['reference_max'] === null) {
            return null;
        }

        $numericValue = (float) $value;
        $min = $parameter['reference_min'] !== null ? (float) $parameter['reference_min'] : null;
        $max = $parameter['reference_max'] !== null ? (float) $parameter['reference_max'] : null;

        if ($min !== null && $numericValue < $min * 0.7) {
            return 'CRITICAL';
        }

        if ($max !== null && $numericValue > $max * 1.3) {
            return 'CRITICAL';
        }

        if ($min !== null && $numericValue < $min) {
            return 'LOW';
        }

        if ($max !== null && $numericValue > $max) {
            return 'HIGH';
        }

        return 'NORMAL';
    }
}
