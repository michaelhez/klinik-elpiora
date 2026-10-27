<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class MedicalRecordNote extends Model
{
    protected static string $table = 'medical_record_notes';
    protected static bool $timestamps = false;
    protected static array $fillable = ['medical_record_id', 'note', 'created_by', 'created_at'];

    public static function forRecord(int|string $medicalRecordId): array
    {
        $sql = "SELECT n.*, u.name AS author_name
                FROM medical_record_notes n
                LEFT JOIN users u ON u.id = n.created_by
                WHERE n.medical_record_id = :id
                ORDER BY n.created_at DESC";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['id' => $medicalRecordId]);
        return $stmt->fetchAll();
    }
}
