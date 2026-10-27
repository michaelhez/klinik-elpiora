<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Session;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Medicine;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;

final class PurchaseOrderService
{
    public function __construct(
        private readonly InventoryService $inventoryService = new InventoryService(),
        private readonly AuditService $auditService = new AuditService(),
    ) {
    }

    public function list(array $filters = []): array
    {
        return PurchaseOrder::list($filters);
    }

    public function find(int|string $id): array
    {
        $order = PurchaseOrder::findWithRelations($id);

        if ($order === null) {
            throw new NotFoundException('Purchase order tidak ditemukan.');
        }

        $order['items'] = PurchaseOrderItem::forOrder($id);
        $order['receipts'] = GoodsReceipt::forOrder($id);

        foreach ($order['receipts'] as &$receipt) {
            $receipt['items'] = GoodsReceiptItem::forReceipt($receipt['id']);
        }
        unset($receipt);

        return $order;
    }

    /**
     * @param array<int, array{medicine_id:int|string, quantity_ordered:int, purchase_price:float}> $items
     */
    public function create(array $data, array $items): array
    {
        if ($items === []) {
            throw new ValidationException(['items' => ['Tambahkan minimal satu obat.']]);
        }

        if (Supplier::find($data['supplier_id']) === null) {
            throw new ValidationException(['supplier_id' => ['Pemasok tidak valid.']]);
        }

        $userId = Session::get('auth_user')['id'] ?? null;

        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $orderId = PurchaseOrder::create([
                'po_number' => PurchaseOrder::nextPoNumber(),
                'supplier_id' => $data['supplier_id'],
                'branch_id' => $data['branch_id'],
                'status' => 'DRAFT',
                'order_date' => ($data['order_date'] ?? '') !== '' ? $data['order_date'] : null,
                'expected_date' => ($data['expected_date'] ?? '') !== '' ? $data['expected_date'] : null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
            ]);

            foreach ($items as $item) {
                $medicine = Medicine::find($item['medicine_id']);
                if ($medicine === null) {
                    throw new ValidationException(['items' => ['Salah satu obat tidak valid.']]);
                }

                $quantity = max(1, (int) $item['quantity_ordered']);
                $price = (float) $item['purchase_price'];

                PurchaseOrderItem::create([
                    'purchase_order_id' => $orderId,
                    'medicine_id' => $item['medicine_id'],
                    'quantity_ordered' => $quantity,
                    'purchase_price' => $price,
                    'subtotal' => $quantity * $price,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        $this->auditService->log('CREATE', 'purchase_order', 'purchase_orders', $orderId, null, $data);

        return $this->find($orderId);
    }

    public function submit(int|string $id): array
    {
        return $this->transition($id, ['DRAFT'], 'SUBMITTED');
    }

    public function approve(int|string $id): array
    {
        $order = $this->find($id);
        $this->assertStatus($order, ['SUBMITTED']);

        PurchaseOrder::update($id, [
            'status' => 'APPROVED',
            'approved_by' => Session::get('auth_user')['id'] ?? null,
            'approved_at' => date('Y-m-d H:i:s'),
        ]);

        $this->auditService->log('APPROVE', 'purchase_order', 'purchase_orders', $id, $order, ['status' => 'APPROVED']);

        return $this->find($id);
    }

    public function markOrdered(int|string $id): array
    {
        return $this->transition($id, ['APPROVED'], 'ORDERED');
    }

    public function cancel(int|string $id): array
    {
        return $this->transition($id, ['DRAFT', 'SUBMITTED', 'APPROVED', 'ORDERED'], 'CANCELLED');
    }

    /**
     * Records a goods receipt against a purchase order, receiving stock via FEFO-compatible ledger,
     * and updates the order status to RECEIVED (partial) or COMPLETED (fully received).
     *
     * @param array<int, array{purchase_order_item_id:int|string, quantity_received:int, batch_number:string, expired_date:string}> $receivedItems
     */
    public function receive(int|string $id, array $receivedItems, ?string $notes = null): array
    {
        $order = $this->find($id);
        $this->assertStatus($order, ['ORDERED', 'RECEIVED']);

        if ($receivedItems === []) {
            throw new ValidationException(['items' => ['Tambahkan minimal satu item yang diterima.']]);
        }

        $itemsById = [];
        foreach ($order['items'] as $item) {
            $itemsById[$item['id']] = $item;
        }

        $userId = Session::get('auth_user')['id'] ?? null;

        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $receiptId = GoodsReceipt::create([
                'receipt_number' => GoodsReceipt::nextReceiptNumber(),
                'purchase_order_id' => $id,
                'received_by' => $userId,
                'received_at' => date('Y-m-d H:i:s'),
                'notes' => $notes,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            foreach ($receivedItems as $received) {
                $poItem = $itemsById[$received['purchase_order_item_id']] ?? null;
                if ($poItem === null) {
                    throw new ValidationException(['items' => ['Item purchase order tidak valid.']]);
                }

                $quantity = max(1, (int) $received['quantity_received']);
                $alreadyReceived = (int) $poItem['quantity_received'];

                if ($alreadyReceived + $quantity > (int) $poItem['quantity_ordered']) {
                    throw new ValidationException(['items' => ["Jumlah diterima untuk {$poItem['medicine_name']} melebihi jumlah yang dipesan."]]);
                }

                $batch = $this->inventoryService->receiveStock(
                    $poItem['medicine_id'],
                    $order['branch_id'],
                    $received['batch_number'],
                    $received['expired_date'],
                    $quantity,
                    (float) $poItem['purchase_price'],
                    'PURCHASE',
                    'purchase_order',
                    $id,
                    "Penerimaan {$order['po_number']}",
                );

                GoodsReceiptItem::create([
                    'goods_receipt_id' => $receiptId,
                    'purchase_order_item_id' => $poItem['id'],
                    'medicine_id' => $poItem['medicine_id'],
                    'medicine_batch_id' => $batch['id'],
                    'quantity_received' => $quantity,
                    'batch_number' => $received['batch_number'],
                    'expired_date' => $received['expired_date'],
                    'created_at' => date('Y-m-d H:i:s'),
                ]);

                $itemsById[$received['purchase_order_item_id']]['quantity_received'] = $alreadyReceived + $quantity;
            }

            $fullyReceived = array_reduce(
                $itemsById,
                fn (bool $carry, array $item) => $carry && (int) $item['quantity_received'] >= (int) $item['quantity_ordered'],
                true,
            );

            PurchaseOrder::update($id, ['status' => $fullyReceived ? 'COMPLETED' : 'RECEIVED']);

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        $this->auditService->log('RECEIVE', 'purchase_order', 'goods_receipts', $receiptId, null, ['purchase_order_id' => $id, 'items' => $receivedItems]);

        return $this->find($id);
    }

    private function transition(int|string $id, array $allowedFrom, string $to): array
    {
        $order = $this->find($id);
        $this->assertStatus($order, $allowedFrom);

        PurchaseOrder::update($id, ['status' => $to]);
        $this->auditService->log('UPDATE', 'purchase_order', 'purchase_orders', $id, $order, ['status' => $to]);

        return $this->find($id);
    }

    private function assertStatus(array $order, array $allowed): void
    {
        if (!in_array($order['status'], $allowed, true)) {
            throw new ValidationException(['status' => ['Status purchase order saat ini tidak mengizinkan aksi ini.']]);
        }
    }
}
