<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class PrescriptionCompound extends Model
{
    protected static string $table = 'prescription_compounds';
    protected static bool $timestamps = false;
    protected static array $fillable = ['prescription_id', 'compound_name', 'dosage_instructions', 'quantity', 'price', 'created_at'];

    public static function forPrescription(int|string $prescriptionId): array
    {
        $sql = 'SELECT * FROM prescription_compounds WHERE prescription_id = :id ORDER BY id ASC';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['id' => $prescriptionId]);
        return $stmt->fetchAll();
    }
}
