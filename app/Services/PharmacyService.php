<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Session;
use App\Exceptions\ValidationException;
use App\Models\Dispensing;
use App\Models\DispensingItem;
use App\Models\Prescription;

final class PharmacyService
{
    public function __construct(
        private readonly PrescriptionService $prescriptionService = new PrescriptionService(),
        private readonly InventoryService $inventoryService = new InventoryService(),
        private readonly AuditService $auditService = new AuditService(),
    ) {
    }

    public function verify(int|string $prescriptionId): array
    {
        $prescription = $this->prescriptionService->find($prescriptionId);

        if ($prescription['status'] !== 'WAITING_PHARMACY') {
            throw new ValidationException(['status' => ['Resep ini tidak dalam status menunggu verifikasi.']]);
        }

        Prescription::update($prescriptionId, ['status' => 'PROCESSING']);
        $this->auditService->log('UPDATE', 'prescription', 'prescriptions', $prescriptionId, $prescription, ['status' => 'PROCESSING']);

        return $this->prescriptionService->find($prescriptionId);
    }

    /**
     * Performs FEFO stock deduction for every item/compound and marks the prescription READY.
     */
    public function prepare(int|string $prescriptionId): array
    {
        $prescription = $this->prescriptionService->find($prescriptionId);

        if ($prescription['status'] !== 'PROCESSING') {
            throw new ValidationException(['status' => ['Resep harus diverifikasi terlebih dahulu sebelum disiapkan.']]);
        }

        $branchId = $prescription['branch_id'];
        $userId = Session::get('auth_user')['id'] ?? null;

        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $dispensingId = Dispensing::create([
                'prescription_id' => $prescriptionId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            foreach ($prescription['items'] as $item) {
                $used = $this->inventoryService->deductFefo(
                    $item['medicine_id'],
                    $branchId,
                    (int) $item['quantity'],
                    'DISPENSING',
                    'prescription',
                    $prescriptionId,
                    "Resep {$prescription['prescription_number']}"
                );

                foreach ($used as $batchUsage) {
                    DispensingItem::create([
                        'dispensing_id' => $dispensingId,
                        'prescription_item_id' => $item['id'],
                        'prescription_compound_id' => null,
                        'medicine_batch_id' => $batchUsage['batch_id'],
                        'quantity_taken' => $batchUsage['quantity'],
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            }

            foreach ($prescription['compounds'] as $compound) {
                foreach ($compound['items'] as $compoundItem) {
                    $totalNeeded = (int) ceil(((float) $compoundItem['quantity_per_dose']) * ((float) $compound['quantity']));

                    if ($totalNeeded <= 0) {
                        continue;
                    }

                    $used = $this->inventoryService->deductFefo(
                        $compoundItem['medicine_id'],
                        $branchId,
                        $totalNeeded,
                        'DISPENSING',
                        'prescription_compound',
                        $compound['id'],
                        "Racikan {$compound['compound_name']} - Resep {$prescription['prescription_number']}"
                    );

                    foreach ($used as $batchUsage) {
                        DispensingItem::create([
                            'dispensing_id' => $dispensingId,
                            'prescription_item_id' => null,
                            'prescription_compound_id' => $compound['id'],
                            'medicine_batch_id' => $batchUsage['batch_id'],
                            'quantity_taken' => $batchUsage['quantity'],
                            'created_at' => date('Y-m-d H:i:s'),
                        ]);
                    }
                }
            }

            Prescription::update($prescriptionId, ['status' => 'READY']);

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        $this->auditService->log('UPDATE', 'prescription', 'prescriptions', $prescriptionId, $prescription, ['status' => 'READY']);

        return $this->prescriptionService->find($prescriptionId);
    }

    public function handover(int|string $prescriptionId): array
    {
        $prescription = $this->prescriptionService->find($prescriptionId);

        if ($prescription['status'] !== 'READY') {
            throw new ValidationException(['status' => ['Resep belum siap untuk diserahkan.']]);
        }

        $userId = Session::get('auth_user')['id'] ?? null;

        Prescription::update($prescriptionId, ['status' => 'DISPENSED']);
        Dispensing::update($prescription['dispensing']['id'], [
            'dispensed_by' => $userId,
            'dispensed_at' => date('Y-m-d H:i:s'),
        ]);

        $this->auditService->log('DISPENSE', 'prescription', 'prescriptions', $prescriptionId, $prescription, ['status' => 'DISPENSED']);

        return $this->prescriptionService->find($prescriptionId);
    }
}
