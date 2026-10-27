<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Session;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\Dispensing;
use App\Models\DispensingItem;
use App\Models\Medicine;
use App\Models\MedicalRecord;
use App\Models\Prescription;
use App\Models\PrescriptionCompound;
use App\Models\PrescriptionCompoundItem;
use App\Models\PrescriptionItem;

final class PrescriptionService
{
    public function __construct(private readonly AuditService $auditService = new AuditService())
    {
    }

    public function find(int|string $id): array
    {
        $prescription = Prescription::findWithRelations($id);

        if ($prescription === null) {
            throw new NotFoundException('Resep tidak ditemukan.');
        }

        $prescription['items'] = PrescriptionItem::forPrescription($id);
        $prescription['compounds'] = PrescriptionCompound::forPrescription($id);

        foreach ($prescription['compounds'] as &$compound) {
            $compound['items'] = PrescriptionCompoundItem::forCompound($compound['id']);
        }
        unset($compound);

        $dispensing = Dispensing::forPrescription($id);
        $prescription['dispensing'] = $dispensing;
        $prescription['dispensing_items'] = $dispensing !== null ? DispensingItem::forDispensing($dispensing['id']) : [];

        return $prescription;
    }

    public function forMedicalRecord(int|string $medicalRecordId): array
    {
        return Prescription::forMedicalRecord($medicalRecordId);
    }

    public function queue(array $statuses = []): array
    {
        return Prescription::queue($statuses);
    }

    /**
     * Creates a full prescription (non-compound items + compound recipes) in a single transaction.
     *
     * @param array<int, array{medicine_id:int|string, dosage?:string, quantity:int, instructions?:string}> $items
     * @param array<int, array{compound_name:string, dosage_instructions?:string, quantity:int, items: array<int, array{medicine_id:int|string, quantity_per_dose:float}>}> $compounds
     */
    public function create(array $data, array $items, array $compounds): array
    {
        $medicalRecord = MedicalRecord::find($data['medical_record_id']);
        if ($medicalRecord === null) {
            throw new ValidationException(['medical_record_id' => ['Rekam medis tidak valid.']]);
        }

        if ($items === [] && $compounds === []) {
            throw new ValidationException(['items' => ['Tambahkan minimal satu obat atau racikan.']]);
        }

        $branchId = $this->resolveBranch($medicalRecord['registration_id']);
        $userId = Session::get('auth_user')['id'] ?? null;

        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $prescriptionId = Prescription::create([
                'prescription_number' => Prescription::nextPrescriptionNumber(),
                'medical_record_id' => $data['medical_record_id'],
                'patient_id' => $medicalRecord['patient_id'],
                'doctor_id' => $medicalRecord['doctor_id'],
                'branch_id' => $branchId,
                'status' => 'WAITING_PHARMACY',
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
            ]);

            foreach ($items as $item) {
                $medicine = Medicine::find($item['medicine_id']);
                if ($medicine === null) {
                    throw new ValidationException(['items' => ['Salah satu obat tidak valid.']]);
                }

                PrescriptionItem::create([
                    'prescription_id' => $prescriptionId,
                    'medicine_id' => $item['medicine_id'],
                    'dosage' => $item['dosage'] ?? null,
                    'quantity' => max(1, (int) $item['quantity']),
                    'instructions' => $item['instructions'] ?? null,
                    'price' => $medicine['selling_price'],
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            foreach ($compounds as $compound) {
                if (empty($compound['items'])) {
                    throw new ValidationException(['compounds' => ['Racikan harus memiliki minimal satu komposisi obat.']]);
                }

                $quantity = max(1, (int) $compound['quantity']);
                $totalPrice = 0;

                foreach ($compound['items'] as $compoundItem) {
                    $medicine = Medicine::find($compoundItem['medicine_id']);
                    if ($medicine === null) {
                        throw new ValidationException(['compounds' => ['Salah satu obat dalam racikan tidak valid.']]);
                    }
                    $totalPrice += ((float) $medicine['selling_price']) * ((float) $compoundItem['quantity_per_dose']) * $quantity;
                }

                $compoundId = PrescriptionCompound::create([
                    'prescription_id' => $prescriptionId,
                    'compound_name' => $compound['compound_name'],
                    'dosage_instructions' => $compound['dosage_instructions'] ?? null,
                    'quantity' => $quantity,
                    'price' => $totalPrice,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);

                foreach ($compound['items'] as $compoundItem) {
                    PrescriptionCompoundItem::create([
                        'prescription_compound_id' => $compoundId,
                        'medicine_id' => $compoundItem['medicine_id'],
                        'quantity_per_dose' => $compoundItem['quantity_per_dose'],
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        $this->auditService->log('CREATE', 'prescription', 'prescriptions', $prescriptionId, null, $data);

        return $this->find($prescriptionId);
    }

    public function cancel(int|string $id): array
    {
        $existing = $this->find($id);

        if (in_array($existing['status'], ['DISPENSED', 'CANCELLED'], true)) {
            throw new ValidationException(['status' => ['Resep ini tidak dapat dibatalkan.']]);
        }

        Prescription::update($id, ['status' => 'CANCELLED']);
        $this->auditService->log('CANCEL', 'prescription', 'prescriptions', $id, $existing, ['status' => 'CANCELLED']);

        return $this->find($id);
    }

    private function resolveBranch(int|string $registrationId): int|string
    {
        $sql = 'SELECT p.branch_id FROM registrations r INNER JOIN polyclinics p ON p.id = r.polyclinic_id WHERE r.id = :id';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['id' => $registrationId]);
        $branchId = $stmt->fetchColumn();

        if ($branchId === false) {
            throw new ValidationException(['medical_record_id' => ['Cabang tidak dapat ditentukan dari registrasi ini.']]);
        }

        return $branchId;
    }
}
