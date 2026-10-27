<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\Patient;
use App\Models\PatientAllergy;
use App\Models\PatientContact;
use App\Models\PatientEmergencyContact;
use App\Models\PatientMedicalHistory;
use App\Repositories\PatientRepository;

final class PatientService
{
    public function __construct(
        private readonly PatientRepository $repository = new PatientRepository(),
        private readonly AuditService $auditService = new AuditService(),
        private readonly FileUploadService $fileUploadService = new FileUploadService(),
    ) {
    }

    public function paginate(array $filters, int $page, int $perPage): array
    {
        return $this->repository->paginate($filters, $page, $perPage);
    }

    public function find(int|string $id): array
    {
        $patient = Patient::find($id);

        if ($patient === null) {
            throw new NotFoundException('Data pasien tidak ditemukan.');
        }

        return $patient;
    }

    public function contacts(int|string $patientId): array
    {
        return PatientContact::forPatient($patientId);
    }

    public function emergencyContacts(int|string $patientId): array
    {
        return PatientEmergencyContact::forPatient($patientId);
    }

    public function allergies(int|string $patientId): array
    {
        return PatientAllergy::forPatient($patientId);
    }

    public function medicalHistories(int|string $patientId): array
    {
        return PatientMedicalHistory::forPatient($patientId);
    }

    public function create(array $data, ?array $photoFile): array
    {
        if (!empty($data['nik']) && Patient::findByNik($data['nik']) !== null) {
            throw new ValidationException(['nik' => ['NIK sudah terdaftar untuk pasien lain.']]);
        }

        $data['medical_record_number'] = Patient::nextMedicalRecordNumber();
        $data = $this->cleanFields($data);

        if ($photoFile !== null && $photoFile['error'] !== UPLOAD_ERR_NO_FILE) {
            $data['photo'] = $this->fileUploadService->storePhoto($photoFile, 'patients');
        }

        $id = Patient::create($data);
        $this->auditService->log('CREATE', 'patient', 'patients', $id, null, $data);

        return $this->find($id);
    }

    public function update(int|string $id, array $data, ?array $photoFile): array
    {
        $existing = $this->find($id);

        if (!empty($data['nik'])) {
            $duplicate = Patient::findByNik($data['nik']);
            if ($duplicate !== null && (string) $duplicate['id'] !== (string) $id) {
                throw new ValidationException(['nik' => ['NIK sudah terdaftar untuk pasien lain.']]);
            }
        }

        $data = $this->cleanFields($data);

        if ($photoFile !== null && $photoFile['error'] !== UPLOAD_ERR_NO_FILE) {
            $data['photo'] = $this->fileUploadService->storePhoto($photoFile, 'patients');
            if (!empty($existing['photo'])) {
                $this->fileUploadService->deletePhoto($existing['photo']);
            }
        }

        Patient::update($id, $data);
        $this->auditService->log('UPDATE', 'patient', 'patients', $id, $existing, $data);

        return $this->find($id);
    }

    public function delete(int|string $id): void
    {
        $existing = $this->find($id);
        Patient::delete($id);
        $this->auditService->log('DELETE', 'patient', 'patients', $id, $existing, null);
    }

    public function addAllergy(int|string $patientId, array $data): array
    {
        $this->find($patientId);
        $data['patient_id'] = $patientId;
        $data['created_at'] = date('Y-m-d H:i:s');

        $id = PatientAllergy::create($data);
        $this->auditService->log('CREATE', 'patient', 'patient_allergies', $id, null, $data);

        return PatientAllergy::findOrFail($id);
    }

    public function deleteAllergy(int|string $patientId, int|string $allergyId): void
    {
        $allergy = PatientAllergy::find($allergyId);

        if ($allergy === null || (string) $allergy['patient_id'] !== (string) $patientId) {
            throw new NotFoundException('Data alergi tidak ditemukan.');
        }

        PatientAllergy::delete($allergyId);
        $this->auditService->log('DELETE', 'patient', 'patient_allergies', $allergyId, $allergy, null);
    }

    public function addMedicalHistory(int|string $patientId, array $data): array
    {
        $this->find($patientId);
        $data['patient_id'] = $patientId;
        $data['diagnosed_date'] = $data['diagnosed_date'] ?: null;
        $data['created_at'] = date('Y-m-d H:i:s');

        $id = PatientMedicalHistory::create($data);
        $this->auditService->log('CREATE', 'patient', 'patient_medical_histories', $id, null, $data);

        return PatientMedicalHistory::findOrFail($id);
    }

    public function deleteMedicalHistory(int|string $patientId, int|string $historyId): void
    {
        $history = PatientMedicalHistory::find($historyId);

        if ($history === null || (string) $history['patient_id'] !== (string) $patientId) {
            throw new NotFoundException('Data riwayat medis tidak ditemukan.');
        }

        PatientMedicalHistory::delete($historyId);
        $this->auditService->log('DELETE', 'patient', 'patient_medical_histories', $historyId, $history, null);
    }

    public function addEmergencyContact(int|string $patientId, array $data): array
    {
        $this->find($patientId);
        $data['patient_id'] = $patientId;
        $data['created_at'] = date('Y-m-d H:i:s');

        $id = PatientEmergencyContact::create($data);
        $this->auditService->log('CREATE', 'patient', 'patient_emergency_contacts', $id, null, $data);

        return PatientEmergencyContact::findOrFail($id);
    }

    public function deleteEmergencyContact(int|string $patientId, int|string $contactId): void
    {
        $contact = PatientEmergencyContact::find($contactId);

        if ($contact === null || (string) $contact['patient_id'] !== (string) $patientId) {
            throw new NotFoundException('Kontak darurat tidak ditemukan.');
        }

        PatientEmergencyContact::delete($contactId);
        $this->auditService->log('DELETE', 'patient', 'patient_emergency_contacts', $contactId, $contact, null);
    }

    private function cleanFields(array $data): array
    {
        $data['nik'] = $data['nik'] ?: null;
        $data['birth_date'] = $data['birth_date'] ?: null;
        $data['email'] = $data['email'] ?: null;
        $data['blood_type'] = $data['blood_type'] ?: 'UNKNOWN';
        $data['marital_status'] = $data['marital_status'] ?: null;
        $data['status'] = $data['status'] ?? 'ACTIVE';

        return $data;
    }
}
