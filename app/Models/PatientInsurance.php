<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class PatientInsurance extends Model
{
    protected static string $table = 'patient_insurances';
    protected static bool $timestamps = false;
    protected static array $fillable = ['patient_id', 'insurance_id', 'member_number', 'valid_until', 'created_at'];

    public static function forPatient(int|string $patientId): array
    {
        $sql = "SELECT pi.*, i.name AS insurance_name, i.type AS insurance_type
                FROM patient_insurances pi
                INNER JOIN insurances i ON i.id = pi.insurance_id
                WHERE pi.patient_id = :patient_id
                ORDER BY pi.id DESC";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['patient_id' => $patientId]);
        return $stmt->fetchAll();
    }
}
