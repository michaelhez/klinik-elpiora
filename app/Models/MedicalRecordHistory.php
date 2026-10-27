<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class MedicalRecordHistory extends Model
{
    protected static string $table = 'medical_record_histories';
    protected static bool $timestamps = false;
    protected static array $fillable = ['medical_record_id', 'action_type', 'section', 'old_data', 'new_data', 'reason', 'changed_by', 'created_at'];

    public static function forRecord(int|string $medicalRecordId): array
    {
        $sql = "SELECT h.*, u.name AS changed_by_name
                FROM medical_record_histories h
                LEFT JOIN users u ON u.id = h.changed_by
                WHERE h.medical_record_id = :id
                ORDER BY h.created_at DESC";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['id' => $medicalRecordId]);
        return $stmt->fetchAll();
    }
}
