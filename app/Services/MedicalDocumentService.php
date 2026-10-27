<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Session;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\MedicalDocument;
use App\Models\MedicalRecord;

final class MedicalDocumentService
{
    public function __construct(private readonly AuditService $auditService = new AuditService())
    {
    }

    public function forMedicalRecord(int|string $medicalRecordId): array
    {
        return MedicalDocument::forMedicalRecord($medicalRecordId);
    }

    public function find(int|string $id): array
    {
        $document = MedicalDocument::findWithRelations($id);

        if ($document === null) {
            throw new NotFoundException('Dokumen medis tidak ditemukan.');
        }

        $document['content'] = json_decode((string) $document['content'], true) ?? [];

        return $document;
    }

    public function generateSickNote(int|string $medicalRecordId, array $data): array
    {
        $restDays = max(1, (int) $data['rest_days']);
        $startDate = $data['start_date'];
        $endDate = date('Y-m-d', strtotime($startDate . " +" . ($restDays - 1) . " days"));

        return $this->create($medicalRecordId, 'SICK_NOTE', 'Surat Keterangan Sakit', [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'rest_days' => $restDays,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    public function generateReferralLetter(int|string $medicalRecordId, array $data): array
    {
        return $this->create($medicalRecordId, 'REFERRAL_LETTER', 'Surat Rujukan', [
            'destination_facility' => $data['destination_facility'],
            'reason' => $data['reason'],
        ]);
    }

    public function generateMedicalCertificate(int|string $medicalRecordId, array $data): array
    {
        return $this->create($medicalRecordId, 'MEDICAL_CERTIFICATE', 'Surat Keterangan Sehat', [
            'purpose' => $data['purpose'],
            'notes' => $data['notes'] ?? null,
        ]);
    }

    private function create(int|string $medicalRecordId, string $type, string $title, array $content): array
    {
        $record = MedicalRecord::find($medicalRecordId);

        if ($record === null) {
            throw new ValidationException(['medical_record_id' => ['Rekam medis tidak ditemukan.']]);
        }

        if (!in_array($record['status'], ['FINALIZED', 'AMENDED'], true)) {
            throw new ValidationException(['status' => ['Rekam medis harus difinalisasi sebelum dokumen dapat diterbitkan.']]);
        }

        $userId = Session::get('auth_user')['id'] ?? null;

        $id = MedicalDocument::create([
            'document_number' => MedicalDocument::nextDocumentNumber(),
            'medical_record_id' => $medicalRecordId,
            'patient_id' => $record['patient_id'],
            'type' => $type,
            'title' => $title,
            'content' => json_encode($content, JSON_UNESCAPED_UNICODE),
            'issued_by' => $userId,
            'issued_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->auditService->log('CREATE', 'medical_document', 'medical_documents', $id, null, $content);

        return $this->find($id);
    }
}
