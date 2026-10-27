<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class GoodsReceiptItem extends Model
{
    protected static string $table = 'goods_receipt_items';
    protected static bool $timestamps = false;
    protected static array $fillable = [
        'goods_receipt_id', 'purchase_order_item_id', 'medicine_id', 'medicine_batch_id',
        'quantity_received', 'batch_number', 'expired_date', 'created_at',
    ];

    public static function forReceipt(int|string $goodsReceiptId): array
    {
        $sql = "SELECT gri.*, m.name AS medicine_name, m.code AS medicine_code
                FROM goods_receipt_items gri
                INNER JOIN medicines m ON m.id = gri.medicine_id
                WHERE gri.goods_receipt_id = :id";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['id' => $goodsReceiptId]);
        return $stmt->fetchAll();
    }
}
