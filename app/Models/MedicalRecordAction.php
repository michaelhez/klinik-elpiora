<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class MedicalRecordAction extends Model
{
    protected static string $table = 'medical_record_actions';
    protected static bool $timestamps = false;
    protected static array $fillable = ['medical_record_id', 'medical_action_id', 'quantity', 'price', 'notes', 'created_by', 'created_at'];

    public static function forRecord(int|string $medicalRecordId): array
    {
        $sql = "SELECT mra.*, ma.code AS action_code, ma.name AS action_name
                FROM medical_record_actions mra
                INNER JOIN medical_actions ma ON ma.id = mra.medical_action_id
                WHERE mra.medical_record_id = :id
                ORDER BY mra.id ASC";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['id' => $medicalRecordId]);
        return $stmt->fetchAll();
    }
}
