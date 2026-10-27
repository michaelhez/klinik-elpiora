<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class PrescriptionCompoundItem extends Model
{
    protected static string $table = 'prescription_compound_items';
    protected static bool $timestamps = false;
    protected static array $fillable = ['prescription_compound_id', 'medicine_id', 'quantity_per_dose', 'created_at'];

    public static function forCompound(int|string $compoundId): array
    {
        $sql = "SELECT ci.*, m.name AS medicine_name, m.code AS medicine_code
                FROM prescription_compound_items ci
                INNER JOIN medicines m ON m.id = ci.medicine_id
                WHERE ci.prescription_compound_id = :id";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['id' => $compoundId]);
        return $stmt->fetchAll();
    }
}
