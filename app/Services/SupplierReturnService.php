<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Session;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\MedicineBatch;
use App\Models\Supplier;
use App\Models\SupplierReturn;
use App\Models\SupplierReturnItem;

final class SupplierReturnService
{
    public function __construct(
        private readonly InventoryService $inventoryService = new InventoryService(),
        private readonly AuditService $auditService = new AuditService(),
    ) {
    }

    public function list(): array
    {
        return SupplierReturn::list();
    }

    public function find(int|string $id): array
    {
        $return = SupplierReturn::findWithRelations($id);

        if ($return === null) {
            throw new NotFoundException('Retur pemasok tidak ditemukan.');
        }

        $return['items'] = SupplierReturnItem::forReturn($id);

        return $return;
    }

    /**
     * @param array<int, array{medicine_batch_id:int|string, quantity:int, notes?:string}> $items
     */
    public function create(array $data, array $items): array
    {
        if ($items === []) {
            throw new ValidationException(['items' => ['Tambahkan minimal satu obat yang diretur.']]);
        }

        if (Supplier::find($data['supplier_id']) === null) {
            throw new ValidationException(['supplier_id' => ['Pemasok tidak valid.']]);
        }

        $userId = Session::get('auth_user')['id'] ?? null;

        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $returnId = SupplierReturn::create([
                'return_number' => SupplierReturn::nextReturnNumber(),
                'supplier_id' => $data['supplier_id'],
                'goods_receipt_id' => $data['goods_receipt_id'] ?? null,
                'reason' => $data['reason'],
                'status' => 'COMPLETED',
                'returned_by' => $userId,
            ]);

            foreach ($items as $item) {
                $batch = MedicineBatch::find($item['medicine_batch_id']);
                if ($batch === null) {
                    throw new ValidationException(['items' => ['Salah satu batch obat tidak valid.']]);
                }

                $quantity = max(1, (int) $item['quantity']);

                $this->inventoryService->returnToSupplier(
                    $batch['medicine_id'],
                    $batch['id'],
                    $batch['branch_id'],
                    $quantity,
                    'supplier_return',
                    $returnId,
                    $item['notes'] ?? $data['reason'],
                );

                SupplierReturnItem::create([
                    'supplier_return_id' => $returnId,
                    'medicine_batch_id' => $batch['id'],
                    'quantity' => $quantity,
                    'notes' => $item['notes'] ?? null,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        $this->auditService->log('CREATE', 'supplier_return', 'supplier_returns', $returnId, null, $data);

        return $this->find($returnId);
    }
}
