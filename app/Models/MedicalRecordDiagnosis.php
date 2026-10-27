<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class MedicalRecordDiagnosis extends Model
{
    protected static string $table = 'medical_record_diagnoses';
    protected static bool $timestamps = false;
    protected static array $fillable = ['medical_record_id', 'icd10_code_id', 'type', 'notes', 'created_by', 'created_at'];

    public static function forRecord(int|string $medicalRecordId): array
    {
        $sql = "SELECT mrd.*, i.code AS icd10_code, i.description AS icd10_description
                FROM medical_record_diagnoses mrd
                INNER JOIN icd10_codes i ON i.id = mrd.icd10_code_id
                WHERE mrd.medical_record_id = :id
                ORDER BY mrd.type ASC, mrd.id ASC";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['id' => $medicalRecordId]);
        return $stmt->fetchAll();
    }
}
