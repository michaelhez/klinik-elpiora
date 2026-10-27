<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class DispensingItem extends Model
{
    protected static string $table = 'dispensing_items';
    protected static bool $timestamps = false;
    protected static array $fillable = [
        'dispensing_id', 'prescription_item_id', 'prescription_compound_id', 'medicine_batch_id', 'quantity_taken', 'created_at',
    ];

    public static function forDispensing(int|string $dispensingId): array
    {
        $sql = "SELECT di.*, b.batch_number, b.expired_date
                FROM dispensing_items di
                INNER JOIN medicine_batches b ON b.id = di.medicine_batch_id
                WHERE di.dispensing_id = :id
                ORDER BY di.id ASC";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['id' => $dispensingId]);
        return $stmt->fetchAll();
    }
}
