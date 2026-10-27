<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Session;
use App\Exceptions\ValidationException;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Models\StockTransfer;

final class InventoryService
{
    public function __construct(private readonly AuditService $auditService = new AuditService())
    {
    }

    public function currentStock(int|string $medicineId, ?int $branchId = null): int
    {
        return MedicineBatch::totalStock($medicineId, $branchId);
    }

    public function batches(int|string $medicineId): array
    {
        return MedicineBatch::forMedicine($medicineId);
    }

    public function movements(int|string $medicineId): array
    {
        return StockMovement::forMedicine($medicineId);
    }

    public function expiringSoon(int $days = 90): array
    {
        return MedicineBatch::expiringSoon($days);
    }

    public function lowStock(): array
    {
        $sql = "SELECT m.id, m.code, m.name, m.minimum_stock,
                       COALESCE((SELECT SUM(b.quantity) FROM medicine_batches b WHERE b.medicine_id = m.id AND b.expired_date >= CURDATE()), 0) AS current_stock
                FROM medicines m
                WHERE m.status = 'ACTIVE' AND m.deleted_at IS NULL
                HAVING current_stock <= m.minimum_stock
                ORDER BY current_stock ASC";

        return Database::connection()->query($sql)->fetchAll();
    }

    /**
     * Records an inbound stock movement (e.g. purchase/goods receipt) and creates/updates the batch.
     */
    public function receiveStock(
        int|string $medicineId,
        int|string $branchId,
        string $batchNumber,
        string $expiredDate,
        int $quantity,
        float $purchasePrice,
        string $type = 'PURCHASE',
        ?string $referenceType = null,
        int|string|null $referenceId = null,
        ?string $notes = null,
    ): array {
        if ($quantity <= 0) {
            throw new ValidationException(['quantity' => ['Jumlah harus lebih dari 0.']]);
        }

        $existingBatch = $this->findBatch($medicineId, $branchId, $batchNumber);

        $batchId = $this->runInTransaction(function () use ($existingBatch, $medicineId, $branchId, $batchNumber, $expiredDate, $quantity, $purchasePrice, $type, $referenceType, $referenceId, $notes) {
            if ($existingBatch !== null) {
                $batchId = $existingBatch['id'];
                MedicineBatch::update($batchId, ['quantity' => $existingBatch['quantity'] + $quantity]);
            } else {
                $batchId = MedicineBatch::create([
                    'medicine_id' => $medicineId,
                    'branch_id' => $branchId,
                    'batch_number' => $batchNumber,
                    'expired_date' => $expiredDate,
                    'quantity' => $quantity,
                    'purchase_price' => $purchasePrice,
                ]);
            }

            StockMovement::create([
                'medicine_id' => $medicineId,
                'medicine_batch_id' => $batchId,
                'branch_id' => $branchId,
                'type' => $type,
                'quantity_change' => $quantity,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $notes,
                'created_by' => Session::get('auth_user')['id'] ?? null,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            return $batchId;
        });

        return MedicineBatch::find($batchId);
    }

    /**
     * Deducts stock using First Expired First Out across eligible batches.
     *
     * @return array<int, array{batch_id: int|string, quantity: int}> Batches used and quantities taken.
     */
    public function deductFefo(
        int|string $medicineId,
        int|string $branchId,
        int $quantity,
        string $type,
        ?string $referenceType = null,
        int|string|null $referenceId = null,
        ?string $notes = null,
    ): array {
        if ($quantity <= 0) {
            throw new ValidationException(['quantity' => ['Jumlah harus lebih dari 0.']]);
        }

        $batches = MedicineBatch::fefoEligible($medicineId, $branchId);
        $available = array_sum(array_column($batches, 'quantity'));

        if ($available < $quantity) {
            $medicine = Medicine::find($medicineId);
            throw new ValidationException(['quantity' => ["Stok {$medicine['name']} tidak mencukupi. Tersedia: {$available}, dibutuhkan: {$quantity}."]]);
        }

        $remaining = $quantity;
        $used = [];
        $userId = Session::get('auth_user')['id'] ?? null;

        $this->runInTransaction(function () use ($batches, &$remaining, &$used, $medicineId, $branchId, $type, $referenceType, $referenceId, $notes, $userId) {
            foreach ($batches as $batch) {
                if ($remaining <= 0) {
                    break;
                }

                $take = min($remaining, (int) $batch['quantity']);

                MedicineBatch::update($batch['id'], ['quantity' => $batch['quantity'] - $take]);

                StockMovement::create([
                    'medicine_id' => $medicineId,
                    'medicine_batch_id' => $batch['id'],
                    'branch_id' => $branchId,
                    'type' => $type,
                    'quantity_change' => -$take,
                    'reference_type' => $referenceType,
                    'reference_id' => $referenceId,
                    'notes' => $notes,
                    'created_by' => $userId,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);

                $used[] = ['batch_id' => $batch['id'], 'quantity' => $take];
                $remaining -= $take;
            }
        });

        return $used;
    }

    public function adjust(int|string $medicineId, int|string $batchId, int|string $branchId, string $adjustmentType, int $quantity, string $reason): array
    {
        if ($quantity <= 0) {
            throw new ValidationException(['quantity' => ['Jumlah harus lebih dari 0.']]);
        }

        $batch = MedicineBatch::find($batchId);
        if ($batch === null) {
            throw new ValidationException(['medicine_batch_id' => ['Batch tidak ditemukan.']]);
        }

        if ($adjustmentType === 'REMOVE' && $batch['quantity'] < $quantity) {
            throw new ValidationException(['quantity' => ['Jumlah pengurangan melebihi stok batch saat ini.']]);
        }

        $userId = Session::get('auth_user')['id'] ?? null;
        $delta = $adjustmentType === 'ADD' ? $quantity : -$quantity;

        $adjustmentId = $this->runInTransaction(function () use ($batch, $medicineId, $batchId, $branchId, $adjustmentType, $quantity, $reason, $userId, $delta) {
            MedicineBatch::update($batchId, ['quantity' => $batch['quantity'] + $delta]);

            $movementId = StockMovement::create([
                'medicine_id' => $medicineId,
                'medicine_batch_id' => $batchId,
                'branch_id' => $branchId,
                'type' => 'ADJUSTMENT',
                'quantity_change' => $delta,
                'reference_type' => 'stock_adjustment',
                'reference_id' => null,
                'notes' => $reason,
                'created_by' => $userId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $adjustmentId = StockAdjustment::create([
                'medicine_id' => $medicineId,
                'medicine_batch_id' => $batchId,
                'branch_id' => $branchId,
                'adjustment_type' => $adjustmentType,
                'quantity' => $quantity,
                'reason' => $reason,
                'stock_movement_id' => $movementId,
                'adjusted_by' => $userId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            StockMovement::update($movementId, ['reference_id' => $adjustmentId]);

            return $adjustmentId;
        });

        $this->auditService->log('CREATE', 'inventory', 'stock_adjustments', $adjustmentId, null, [
            'medicine_id' => $medicineId, 'type' => $adjustmentType, 'quantity' => $quantity, 'reason' => $reason,
        ]);

        return StockAdjustment::find($adjustmentId);
    }

    public function transfer(int|string $medicineId, int|string $batchId, int|string $fromBranchId, int|string $toBranchId, int $quantity, ?string $notes = null): array
    {
        if ($quantity <= 0) {
            throw new ValidationException(['quantity' => ['Jumlah harus lebih dari 0.']]);
        }

        if ((string) $fromBranchId === (string) $toBranchId) {
            throw new ValidationException(['to_branch_id' => ['Cabang tujuan harus berbeda dari cabang asal.']]);
        }

        $batch = MedicineBatch::find($batchId);
        if ($batch === null || (string) $batch['branch_id'] !== (string) $fromBranchId) {
            throw new ValidationException(['medicine_batch_id' => ['Batch tidak ditemukan di cabang asal.']]);
        }

        if ($batch['quantity'] < $quantity) {
            throw new ValidationException(['quantity' => ['Jumlah transfer melebihi stok batch saat ini.']]);
        }

        $userId = Session::get('auth_user')['id'] ?? null;

        $transferId = $this->runInTransaction(function () use ($batch, $medicineId, $batchId, $fromBranchId, $toBranchId, $quantity, $notes, $userId) {
            MedicineBatch::update($batchId, ['quantity' => $batch['quantity'] - $quantity]);

            StockMovement::create([
                'medicine_id' => $medicineId,
                'medicine_batch_id' => $batchId,
                'branch_id' => $fromBranchId,
                'type' => 'TRANSFER',
                'quantity_change' => -$quantity,
                'reference_type' => 'stock_transfer',
                'notes' => $notes,
                'created_by' => $userId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $destBatch = $this->findBatch($medicineId, $toBranchId, $batch['batch_number']);

            if ($destBatch !== null) {
                $destBatchId = $destBatch['id'];
                MedicineBatch::update($destBatchId, ['quantity' => $destBatch['quantity'] + $quantity]);
            } else {
                $destBatchId = MedicineBatch::create([
                    'medicine_id' => $medicineId,
                    'branch_id' => $toBranchId,
                    'batch_number' => $batch['batch_number'],
                    'expired_date' => $batch['expired_date'],
                    'quantity' => $quantity,
                    'purchase_price' => $batch['purchase_price'],
                ]);
            }

            StockMovement::create([
                'medicine_id' => $medicineId,
                'medicine_batch_id' => $destBatchId,
                'branch_id' => $toBranchId,
                'type' => 'TRANSFER',
                'quantity_change' => $quantity,
                'reference_type' => 'stock_transfer',
                'notes' => $notes,
                'created_by' => $userId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            return StockTransfer::create([
                'transfer_number' => StockTransfer::nextTransferNumber(),
                'medicine_id' => $medicineId,
                'medicine_batch_id' => $batchId,
                'from_branch_id' => $fromBranchId,
                'to_branch_id' => $toBranchId,
                'quantity' => $quantity,
                'notes' => $notes,
                'transferred_by' => $userId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        });

        $this->auditService->log('CREATE', 'inventory', 'stock_transfers', $transferId, null, [
            'medicine_id' => $medicineId, 'quantity' => $quantity, 'from' => $fromBranchId, 'to' => $toBranchId,
        ]);

        return StockTransfer::find($transferId);
    }

    private function findBatch(int|string $medicineId, int|string $branchId, string $batchNumber): ?array
    {
        return MedicineBatch::query()
            ->where('medicine_id', '=', $medicineId)
            ->where('branch_id', '=', $branchId)
            ->where('batch_number', '=', $batchNumber)
            ->first();
    }

    /**
     * Deducts stock from a specific batch for a return to the supplier.
     */
    public function returnToSupplier(int|string $medicineId, int|string $batchId, int|string $branchId, int $quantity, ?string $referenceType = null, int|string|null $referenceId = null, ?string $notes = null): void
    {
        if ($quantity <= 0) {
            throw new ValidationException(['quantity' => ['Jumlah harus lebih dari 0.']]);
        }

        $batch = MedicineBatch::find($batchId);
        if ($batch === null || (string) $batch['branch_id'] !== (string) $branchId) {
            throw new ValidationException(['medicine_batch_id' => ['Batch tidak ditemukan di cabang ini.']]);
        }

        if ($batch['quantity'] < $quantity) {
            throw new ValidationException(['quantity' => ['Jumlah retur melebihi stok batch saat ini.']]);
        }

        $userId = Session::get('auth_user')['id'] ?? null;

        $this->runInTransaction(function () use ($batch, $medicineId, $batchId, $branchId, $quantity, $referenceType, $referenceId, $notes, $userId) {
            MedicineBatch::update($batchId, ['quantity' => $batch['quantity'] - $quantity]);

            StockMovement::create([
                'medicine_id' => $medicineId,
                'medicine_batch_id' => $batchId,
                'branch_id' => $branchId,
                'type' => 'RETURN',
                'quantity_change' => -$quantity,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $notes,
                'created_by' => $userId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        });
    }

    /**
     * Runs the callback inside a database transaction, reusing an already-active outer transaction
     * (started by the caller) instead of nesting, since the underlying PDO connection does not
     * support nested transactions.
     */
    private function runInTransaction(callable $callback): mixed
    {
        $pdo = Database::connection();
        $ownsTransaction = !$pdo->inTransaction();

        if ($ownsTransaction) {
            $pdo->beginTransaction();
        }

        try {
            $result = $callback();

            if ($ownsTransaction) {
                $pdo->commit();
            }

            return $result;
        } catch (\Throwable $e) {
            if ($ownsTransaction) {
                $pdo->rollBack();
            }

            throw $e;
        }
    }
}
