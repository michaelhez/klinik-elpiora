<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class PurchaseOrderItem extends Model
{
    protected static string $table = 'purchase_order_items';
    protected static bool $timestamps = false;
    protected static array $fillable = ['purchase_order_id', 'medicine_id', 'quantity_ordered', 'purchase_price', 'subtotal', 'created_at'];

    public static function forOrder(int|string $purchaseOrderId): array
    {
        $sql = "SELECT poi.*, m.name AS medicine_name, m.code AS medicine_code,
                       (SELECT COALESCE(SUM(gri.quantity_received), 0) FROM goods_receipt_items gri WHERE gri.purchase_order_item_id = poi.id) AS quantity_received
                FROM purchase_order_items poi
                INNER JOIN medicines m ON m.id = poi.medicine_id
                WHERE poi.purchase_order_id = :id
                ORDER BY poi.id ASC";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['id' => $purchaseOrderId]);
        return $stmt->fetchAll();
    }
}
