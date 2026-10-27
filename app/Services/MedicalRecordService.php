<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Session;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\Icd10Code;
use App\Models\MedicalAction;
use App\Models\MedicalRecord;
use App\Models\MedicalRecordAction;
use App\Models\MedicalRecordAnamnesis;
use App\Models\MedicalRecordDiagnosis;
use App\Models\MedicalRecordHistory;
use App\Models\MedicalRecordNote;
use App\Models\MedicalRecordPhysicalExamination;
use App\Models\MedicalRecordVital;
use App\Models\Registration;

final class MedicalRecordService
{
    private const LOCKED_STATUSES = ['FINALIZED', 'AMENDED'];

    public function __construct(private readonly AuditService $auditService = new AuditService())
    {
    }

    public function find(int|string $id): array
    {
        $record = MedicalRecord::findWithRelations($id);

        if ($record === null) {
            throw new NotFoundException('Rekam medis tidak ditemukan.');
        }

        return $record;
    }

    public function findByRegistration(int|string $registrationId): ?array
    {
        $record = MedicalRecord::findByRegistration($registrationId);

        return $record !== null ? $this->find($record['id']) : null;
    }

    public function forPatient(int|string $patientId): array
    {
        return MedicalRecord::forPatient($patientId);
    }

    public function vitals(int|string $medicalRecordId): ?array
    {
        return MedicalRecordVital::forRecord($medicalRecordId);
    }

    public function anamnesis(int|string $medicalRecordId): ?array
    {
        return MedicalRecordAnamnesis::forRecord($medicalRecordId);
    }

    public function physicalExamination(int|string $medicalRecordId): ?array
    {
        return MedicalRecordPhysicalExamination::forRecord($medicalRecordId);
    }

    public function diagnoses(int|string $medicalRecordId): array
    {
        return MedicalRecordDiagnosis::forRecord($medicalRecordId);
    }

    public function actions(int|string $medicalRecordId): array
    {
        return MedicalRecordAction::forRecord($medicalRecordId);
    }

    public function notes(int|string $medicalRecordId): array
    {
        return MedicalRecordNote::forRecord($medicalRecordId);
    }

    public function histories(int|string $medicalRecordId): array
    {
        return MedicalRecordHistory::forRecord($medicalRecordId);
    }

    public function createFromRegistration(int|string $registrationId): array
    {
        if (MedicalRecord::findByRegistration($registrationId) !== null) {
            throw new ValidationException(['registration_id' => ['Rekam medis untuk registrasi ini sudah dibuat.']]);
        }

        $registration = Registration::find($registrationId);
        if ($registration === null) {
            throw new ValidationException(['registration_id' => ['Registrasi tidak valid.']]);
        }

        if ($registration['status'] === 'CANCELLED') {
            throw new ValidationException(['registration_id' => ['Registrasi ini sudah dibatalkan.']]);
        }

        if (empty($registration['doctor_id'])) {
            throw new ValidationException(['doctor_id' => ['Registrasi ini belum memiliki dokter penanggung jawab.']]);
        }

        $id = MedicalRecord::create([
            'record_number' => MedicalRecord::nextRecordNumber(),
            'registration_id' => $registrationId,
            'patient_id' => $registration['patient_id'],
            'doctor_id' => $registration['doctor_id'],
            'status' => 'DRAFT',
        ]);

        $this->auditService->log('CREATE', 'medical_record', 'medical_records', $id, null, ['registration_id' => $registrationId]);

        return $this->find($id);
    }

    public function upsertVitals(int|string $id, array $data): array
    {
        $record = $this->find($id);
        $this->ensureEditable($record);

        $bmi = null;
        if (!empty($data['weight']) && !empty($data['height'])) {
            $heightMeter = ((float) $data['height']) / 100;
            $bmi = $heightMeter > 0 ? round(((float) $data['weight']) / ($heightMeter ** 2), 2) : null;
        }

        $payload = array_merge($data, ['bmi' => $bmi, 'recorded_by' => Session::get('auth_user')['id'] ?? null]);

        $existing = MedicalRecordVital::forRecord($id);
        if ($existing !== null) {
            MedicalRecordVital::update($existing['id'], $payload);
        } else {
            $payload['medical_record_id'] = $id;
            MedicalRecordVital::create($payload);
        }

        $this->progressStatus($id);
        $this->auditService->log('UPDATE', 'medical_record', 'medical_record_vitals', $id, $existing, $payload);

        return $this->vitals($id);
    }

    public function upsertAnamnesis(int|string $id, array $data): array
    {
        $record = $this->find($id);
        $this->ensureEditable($record);

        $payload = array_merge($data, ['created_by' => Session::get('auth_user')['id'] ?? null]);

        $existing = MedicalRecordAnamnesis::forRecord($id);
        if ($existing !== null) {
            MedicalRecordAnamnesis::update($existing['id'], $payload);
        } else {
            $payload['medical_record_id'] = $id;
            MedicalRecordAnamnesis::create($payload);
        }

        if (!empty($data['chief_complaint'])) {
            MedicalRecord::update($id, ['chief_complaint' => $data['chief_complaint']]);
        }

        $this->progressStatus($id);
        $this->auditService->log('UPDATE', 'medical_record', 'medical_record_anamnesis', $id, $existing, $payload);

        return $this->anamnesis($id);
    }

    public function upsertPhysicalExamination(int|string $id, array $data): array
    {
        $record = $this->find($id);
        $this->ensureEditable($record);

        $payload = array_merge($data, ['created_by' => Session::get('auth_user')['id'] ?? null]);

        $existing = MedicalRecordPhysicalExamination::forRecord($id);
        if ($existing !== null) {
            MedicalRecordPhysicalExamination::update($existing['id'], $payload);
        } else {
            $payload['medical_record_id'] = $id;
            MedicalRecordPhysicalExamination::create($payload);
        }

        $this->progressStatus($id);
        $this->auditService->log('UPDATE', 'medical_record', 'medical_record_physical_examinations', $id, $existing, $payload);

        return $this->physicalExamination($id);
    }

    public function addDiagnosis(int|string $id, array $data): array
    {
        $record = $this->find($id);
        $this->ensureEditable($record);

        if (Icd10Code::find($data['icd10_code_id']) === null) {
            throw new ValidationException(['icd10_code_id' => ['Kode ICD-10 tidak valid.']]);
        }

        $diagnosisId = MedicalRecordDiagnosis::create([
            'medical_record_id' => $id,
            'icd10_code_id' => $data['icd10_code_id'],
            'type' => $data['type'] ?? 'PRIMARY',
            'notes' => $data['notes'] ?? null,
            'created_by' => Session::get('auth_user')['id'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->progressStatus($id);
        $this->auditService->log('CREATE', 'medical_record', 'medical_record_diagnoses', $diagnosisId, null, $data);

        return $this->diagnoses($id);
    }

    public function removeDiagnosis(int|string $id, int|string $diagnosisId): array
    {
        $record = $this->find($id);
        $this->ensureEditable($record);

        $diagnosis = MedicalRecordDiagnosis::find($diagnosisId);
        if ($diagnosis === null || (string) $diagnosis['medical_record_id'] !== (string) $id) {
            throw new NotFoundException('Diagnosis tidak ditemukan.');
        }

        MedicalRecordDiagnosis::delete($diagnosisId);
        $this->auditService->log('DELETE', 'medical_record', 'medical_record_diagnoses', $diagnosisId, $diagnosis, null);

        return $this->diagnoses($id);
    }

    public function addAction(int|string $id, array $data): array
    {
        $record = $this->find($id);
        $this->ensureEditable($record);

        $medicalAction = MedicalAction::find($data['medical_action_id']);
        if ($medicalAction === null) {
            throw new ValidationException(['medical_action_id' => ['Tindakan medis tidak valid.']]);
        }

        $quantity = max(1, (int) ($data['quantity'] ?? 1));

        $actionId = MedicalRecordAction::create([
            'medical_record_id' => $id,
            'medical_action_id' => $data['medical_action_id'],
            'quantity' => $quantity,
            'price' => $medicalAction['price'],
            'notes' => $data['notes'] ?? null,
            'created_by' => Session::get('auth_user')['id'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->progressStatus($id);
        $this->auditService->log('CREATE', 'medical_record', 'medical_record_actions', $actionId, null, $data);

        return $this->actions($id);
    }

    public function removeAction(int|string $id, int|string $actionId): array
    {
        $record = $this->find($id);
        $this->ensureEditable($record);

        $action = MedicalRecordAction::find($actionId);
        if ($action === null || (string) $action['medical_record_id'] !== (string) $id) {
            throw new NotFoundException('Tindakan tidak ditemukan.');
        }

        MedicalRecordAction::delete($actionId);
        $this->auditService->log('DELETE', 'medical_record', 'medical_record_actions', $actionId, $action, null);

        return $this->actions($id);
    }

    public function addNote(int|string $id, string $note): array
    {
        $record = $this->find($id);
        $this->ensureEditable($record);

        $noteId = MedicalRecordNote::create([
            'medical_record_id' => $id,
            'note' => $note,
            'created_by' => Session::get('auth_user')['id'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->auditService->log('CREATE', 'medical_record', 'medical_record_notes', $noteId, null, ['note' => $note]);

        return $this->notes($id);
    }

    public function finalize(int|string $id): array
    {
        $record = $this->find($id);
        $this->ensureEditable($record);

        $anamnesis = $this->anamnesis($id);
        if ($anamnesis === null) {
            throw new ValidationException(['anamnesis' => ['Anamnesis wajib diisi sebelum rekam medis difinalisasi.']]);
        }

        $diagnoses = $this->diagnoses($id);
        if ($diagnoses === []) {
            throw new ValidationException(['diagnosis' => ['Minimal satu diagnosis wajib ditambahkan sebelum finalisasi.']]);
        }

        $hasPrimary = false;
        foreach ($diagnoses as $diagnosis) {
            if ($diagnosis['type'] === 'PRIMARY') {
                $hasPrimary = true;
                break;
            }
        }
        if (!$hasPrimary) {
            throw new ValidationException(['diagnosis' => ['Wajib memiliki satu diagnosis utama (PRIMARY).']]);
        }

        $userId = Session::get('auth_user')['id'] ?? null;

        MedicalRecord::update($id, [
            'status' => 'FINALIZED',
            'finalized_at' => date('Y-m-d H:i:s'),
            'finalized_by' => $userId,
        ]);

        MedicalRecordHistory::create([
            'medical_record_id' => $id,
            'action_type' => 'FINALIZE',
            'section' => null,
            'old_data' => json_encode(['status' => $record['status']]),
            'new_data' => json_encode(['status' => 'FINALIZED']),
            'reason' => null,
            'changed_by' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->auditService->log('FINALIZE', 'medical_record', 'medical_records', $id, $record, ['status' => 'FINALIZED']);

        return $this->find($id);
    }

    /**
     * Applies a controlled amendment to a finalized medical record.
     * Only 'anamnesis' and 'physical_examination' sections are supported in this phase.
     */
    public function amend(int|string $id, string $section, array $newData, string $reason): array
    {
        $record = $this->find($id);

        if (!in_array($record['status'], self::LOCKED_STATUSES, true)) {
            throw new ValidationException(['status' => ['Amendemen hanya berlaku untuk rekam medis yang sudah difinalisasi.']]);
        }

        $allowedSections = ['anamnesis', 'physical_examination'];
        if (!in_array($section, $allowedSections, true)) {
            throw new ValidationException(['section' => ['Bagian rekam medis tidak valid untuk amendemen.']]);
        }

        $userId = Session::get('auth_user')['id'] ?? null;

        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            if ($section === 'anamnesis') {
                $oldData = $this->anamnesis($id);
                $payload = array_merge($newData, ['created_by' => $userId]);
                if ($oldData !== null) {
                    MedicalRecordAnamnesis::update($oldData['id'], $payload);
                } else {
                    $payload['medical_record_id'] = $id;
                    MedicalRecordAnamnesis::create($payload);
                }
            } else {
                $oldData = $this->physicalExamination($id);
                $payload = array_merge($newData, ['created_by' => $userId]);
                if ($oldData !== null) {
                    MedicalRecordPhysicalExamination::update($oldData['id'], $payload);
                } else {
                    $payload['medical_record_id'] = $id;
                    MedicalRecordPhysicalExamination::create($payload);
                }
            }

            MedicalRecordHistory::create([
                'medical_record_id' => $id,
                'action_type' => 'AMEND',
                'section' => $section,
                'old_data' => json_encode($oldData),
                'new_data' => json_encode($newData),
                'reason' => $reason,
                'changed_by' => $userId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            MedicalRecord::update($id, ['status' => 'AMENDED']);

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        $this->auditService->log('AMEND', 'medical_record', 'medical_records', $id, $record, ['section' => $section, 'reason' => $reason]);

        return $this->find($id);
    }

    private function ensureEditable(array $record): void
    {
        if (in_array($record['status'], self::LOCKED_STATUSES, true)) {
            throw new ValidationException(['status' => ['Rekam medis sudah difinalisasi dan tidak dapat diubah langsung. Gunakan amendemen.']]);
        }
    }

    private function progressStatus(int|string $id): void
    {
        $record = MedicalRecord::find($id);
        if ($record !== null && $record['status'] === 'DRAFT') {
            MedicalRecord::update($id, ['status' => 'IN_PROGRESS']);
        }
    }
}
