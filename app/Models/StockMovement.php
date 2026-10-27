<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class StockMovement extends Model
{
    protected static string $table = 'stock_movements';
    protected static bool $timestamps = false;
    protected static array $fillable = [
        'medicine_id', 'medicine_batch_id', 'branch_id', 'type', 'quantity_change',
        'reference_type', 'reference_id', 'notes', 'created_by', 'created_at',
    ];

    public static function forMedicine(int|string $medicineId, int $limit = 50): array
    {
        $sql = "SELECT sm.*, b.batch_number, u.name AS user_name
                FROM stock_movements sm
                LEFT JOIN medicine_batches b ON b.id = sm.medicine_batch_id
                LEFT JOIN users u ON u.id = sm.created_by
                WHERE sm.medicine_id = :medicine_id
                ORDER BY sm.created_at DESC, sm.id DESC
                LIMIT :limit";

        $stmt = Database::connection()->prepare($sql);
        $stmt->bindValue(':medicine_id', $medicineId);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
