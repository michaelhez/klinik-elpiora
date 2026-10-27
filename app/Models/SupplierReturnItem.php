<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class SupplierReturnItem extends Model
{
    protected static string $table = 'supplier_return_items';
    protected static bool $timestamps = false;
    protected static array $fillable = ['supplier_return_id', 'medicine_batch_id', 'quantity', 'notes', 'created_at'];

    public static function forReturn(int|string $supplierReturnId): array
    {
        $sql = "SELECT sri.*, b.batch_number, m.name AS medicine_name
                FROM supplier_return_items sri
                INNER JOIN medicine_batches b ON b.id = sri.medicine_batch_id
                INNER JOIN medicines m ON m.id = b.medicine_id
                WHERE sri.supplier_return_id = :id";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['id' => $supplierReturnId]);
        return $stmt->fetchAll();
    }
}
