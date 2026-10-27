<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Session;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\MedicalRecord;
use App\Models\Payment;

final class BillingService
{
    public function __construct(private readonly AuditService $auditService = new AuditService())
    {
    }

    public function list(array $filters = []): array
    {
        return Invoice::list($filters);
    }

    public function find(int|string $id): array
    {
        $invoice = Invoice::findWithRelations($id);

        if ($invoice === null) {
            throw new NotFoundException('Tagihan tidak ditemukan.');
        }

        $invoice['items'] = InvoiceItem::forInvoice($id);
        $invoice['payments'] = Payment::forInvoice($id);

        return $invoice;
    }

    /**
     * Builds an invoice for a finalized medical record: consultation fee, medical actions,
     * lab tests, and dispensed prescription items/compounds.
     */
    public function generateFromMedicalRecord(int|string $medicalRecordId): array
    {
        $record = MedicalRecord::find($medicalRecordId);
        if ($record === null) {
            throw new ValidationException(['medical_record_id' => ['Rekam medis tidak ditemukan.']]);
        }

        if (!in_array($record['status'], ['FINALIZED', 'AMENDED'], true)) {
            throw new ValidationException(['status' => ['Rekam medis harus difinalisasi sebelum tagihan dapat dibuat.']]);
        }

        if (Invoice::forRegistration($record['registration_id']) !== null) {
            throw new ValidationException(['registration_id' => ['Tagihan untuk kunjungan ini sudah dibuat.']]);
        }

        $pdo = Database::connection();

        $regStmt = $pdo->prepare(
            'SELECT r.payment_type, r.polyclinic_id, poly.name AS polyclinic_name, poly.consultation_fee
             FROM registrations r INNER JOIN polyclinics poly ON poly.id = r.polyclinic_id
             WHERE r.id = :id'
        );
        $regStmt->execute(['id' => $record['registration_id']]);
        $registration = $regStmt->fetch();

        if ($registration === false) {
            throw new ValidationException(['registration_id' => ['Registrasi tidak valid.']]);
        }

        $items = [];

        $items[] = [
            'item_type' => 'CONSULTATION',
            'reference_id' => $registration['polyclinic_id'],
            'description' => "Konsultasi Dokter - {$registration['polyclinic_name']}",
            'quantity' => 1,
            'price' => (float) $registration['consultation_fee'],
        ];

        $actionStmt = $pdo->prepare(
            'SELECT mra.medical_action_id, mra.quantity, mra.price, ma.name
             FROM medical_record_actions mra INNER JOIN medical_actions ma ON ma.id = mra.medical_action_id
             WHERE mra.medical_record_id = :id'
        );
        $actionStmt->execute(['id' => $medicalRecordId]);
        foreach ($actionStmt->fetchAll() as $action) {
            $items[] = [
                'item_type' => 'MEDICAL_ACTION',
                'reference_id' => $action['medical_action_id'],
                'description' => $action['name'],
                'quantity' => (int) $action['quantity'],
                'price' => (float) $action['price'],
            ];
        }

        $labStmt = $pdo->prepare(
            "SELECT lri.lab_test_id, lri.price, lt.name
             FROM lab_request_items lri
             INNER JOIN lab_requests lr ON lr.id = lri.lab_request_id
             INNER JOIN lab_tests lt ON lt.id = lri.lab_test_id
             WHERE lr.medical_record_id = :id AND lr.status != 'CANCELLED'"
        );
        $labStmt->execute(['id' => $medicalRecordId]);
        foreach ($labStmt->fetchAll() as $lab) {
            $items[] = [
                'item_type' => 'LAB_TEST',
                'reference_id' => $lab['lab_test_id'],
                'description' => $lab['name'],
                'quantity' => 1,
                'price' => (float) $lab['price'],
            ];
        }

        $rxItemStmt = $pdo->prepare(
            "SELECT pi.medicine_id, pi.quantity, pi.price, m.name
             FROM prescription_items pi
             INNER JOIN prescriptions p ON p.id = pi.prescription_id
             INNER JOIN medicines m ON m.id = pi.medicine_id
             WHERE p.medical_record_id = :id AND p.status = 'DISPENSED'"
        );
        $rxItemStmt->execute(['id' => $medicalRecordId]);
        foreach ($rxItemStmt->fetchAll() as $rxItem) {
            $items[] = [
                'item_type' => 'PRESCRIPTION_ITEM',
                'reference_id' => $rxItem['medicine_id'],
                'description' => $rxItem['name'],
                'quantity' => (int) $rxItem['quantity'],
                'price' => (float) $rxItem['price'],
            ];
        }

        $rxCompoundStmt = $pdo->prepare(
            "SELECT pc.compound_name, pc.price
             FROM prescription_compounds pc
             INNER JOIN prescriptions p ON p.id = pc.prescription_id
             WHERE p.medical_record_id = :id AND p.status = 'DISPENSED'"
        );
        $rxCompoundStmt->execute(['id' => $medicalRecordId]);
        foreach ($rxCompoundStmt->fetchAll() as $compound) {
            $items[] = [
                'item_type' => 'PRESCRIPTION_COMPOUND',
                'reference_id' => null,
                'description' => "Racikan: {$compound['compound_name']}",
                'quantity' => 1,
                'price' => (float) $compound['price'],
            ];
        }

        $subtotal = array_reduce($items, fn (float $carry, array $item) => $carry + ($item['quantity'] * $item['price']), 0.0);

        [$insuranceId, $corporateId] = $this->resolvePayer($record['patient_id'], $registration['payment_type']);

        $userId = Session::get('auth_user')['id'] ?? null;

        $pdo->beginTransaction();

        try {
            $invoiceId = Invoice::create([
                'invoice_number' => Invoice::nextInvoiceNumber(),
                'registration_id' => $record['registration_id'],
                'medical_record_id' => $medicalRecordId,
                'patient_id' => $record['patient_id'],
                'payment_type' => $registration['payment_type'],
                'insurance_id' => $insuranceId,
                'corporate_id' => $corporateId,
                'subtotal' => $subtotal,
                'discount' => 0,
                'total' => $subtotal,
                'paid_amount' => 0,
                'status' => 'UNPAID',
                'created_by' => $userId,
            ]);

            foreach ($items as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoiceId,
                    'item_type' => $item['item_type'],
                    'reference_id' => $item['reference_id'],
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['quantity'] * $item['price'],
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        $this->auditService->log('CREATE', 'billing', 'invoices', $invoiceId, null, ['medical_record_id' => $medicalRecordId, 'total' => $subtotal]);

        return $this->find($invoiceId);
    }

    public function cancel(int|string $id): array
    {
        $invoice = $this->find($id);

        if ($invoice['status'] !== 'UNPAID') {
            throw new ValidationException(['status' => ['Tagihan yang sudah memiliki pembayaran tidak dapat dibatalkan.']]);
        }

        Invoice::update($id, ['status' => 'CANCELLED']);
        $this->auditService->log('CANCEL', 'billing', 'invoices', $id, $invoice, ['status' => 'CANCELLED']);

        return $this->find($id);
    }

    /**
     * @return array{0: int|string|null, 1: int|string|null} [insuranceId, corporateId]
     */
    private function resolvePayer(int|string $patientId, string $paymentType): array
    {
        if ($paymentType === 'INSURANCE' || $paymentType === 'BPJS') {
            $stmt = Database::connection()->prepare(
                'SELECT insurance_id FROM patient_insurances WHERE patient_id = :patient_id ORDER BY id DESC LIMIT 1'
            );
            $stmt->execute(['patient_id' => $patientId]);
            $insuranceId = $stmt->fetchColumn();

            return [$insuranceId !== false ? $insuranceId : null, null];
        }

        if ($paymentType === 'CORPORATE') {
            $stmt = Database::connection()->prepare(
                'SELECT corporate_id FROM patient_corporates WHERE patient_id = :patient_id ORDER BY id DESC LIMIT 1'
            );
            $stmt->execute(['patient_id' => $patientId]);
            $corporateId = $stmt->fetchColumn();

            return [null, $corporateId !== false ? $corporateId : null];
        }

        return [null, null];
    }
}
