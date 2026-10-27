<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class PrescriptionItem extends Model
{
    protected static string $table = 'prescription_items';
    protected static bool $timestamps = false;
    protected static array $fillable = ['prescription_id', 'medicine_id', 'dosage', 'quantity', 'instructions', 'price', 'created_at'];

    public static function forPrescription(int|string $prescriptionId): array
    {
        $sql = "SELECT pi.*, m.name AS medicine_name, m.code AS medicine_code
                FROM prescription_items pi
                INNER JOIN medicines m ON m.id = pi.medicine_id
                WHERE pi.prescription_id = :id
                ORDER BY pi.id ASC";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['id' => $prescriptionId]);
        return $stmt->fetchAll();
    }
}
