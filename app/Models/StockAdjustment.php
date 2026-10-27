<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class StockAdjustment extends Model
{
    protected static string $table = 'stock_adjustments';
    protected static bool $timestamps = false;
    protected static array $fillable = [
        'medicine_id', 'medicine_batch_id', 'branch_id', 'adjustment_type',
        'quantity', 'reason', 'stock_movement_id', 'adjusted_by', 'created_at',
    ];

    public static function recent(int $limit = 50): array
    {
        $sql = "SELECT sa.*, m.name AS medicine_name, b.batch_number, u.name AS user_name
                FROM stock_adjustments sa
                INNER JOIN medicines m ON m.id = sa.medicine_id
                INNER JOIN medicine_batches b ON b.id = sa.medicine_batch_id
                LEFT JOIN users u ON u.id = sa.adjusted_by
                ORDER BY sa.created_at DESC
                LIMIT :limit";

        $stmt = Database::connection()->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
