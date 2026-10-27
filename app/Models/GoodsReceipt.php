<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class GoodsReceipt extends Model
{
    protected static string $table = 'goods_receipts';
    protected static bool $timestamps = false;
    protected static array $fillable = ['receipt_number', 'purchase_order_id', 'received_by', 'received_at', 'notes', 'created_at'];

    public static function nextReceiptNumber(): string
    {
        $year = date('Y');
        $prefix = "GR-{$year}-";

        $sql = 'SELECT receipt_number FROM goods_receipts WHERE receipt_number LIKE :prefix ORDER BY id DESC LIMIT 1';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['prefix' => $prefix . '%']);
        $last = $stmt->fetchColumn();

        $sequence = $last ? ((int) substr((string) $last, -6)) + 1 : 1;

        return $prefix . str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }

    public static function forOrder(int|string $purchaseOrderId): array
    {
        $sql = 'SELECT * FROM goods_receipts WHERE purchase_order_id = :id ORDER BY id ASC';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['id' => $purchaseOrderId]);
        return $stmt->fetchAll();
    }
}
