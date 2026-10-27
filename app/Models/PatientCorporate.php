<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class PatientCorporate extends Model
{
    protected static string $table = 'patient_corporates';
    protected static bool $timestamps = false;
    protected static array $fillable = ['patient_id', 'corporate_id', 'employee_number', 'created_at'];

    public static function forPatient(int|string $patientId): array
    {
        $sql = "SELECT pc.*, c.name AS corporate_name
                FROM patient_corporates pc
                INNER JOIN corporates c ON c.id = pc.corporate_id
                WHERE pc.patient_id = :patient_id
                ORDER BY pc.id DESC";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['patient_id' => $patientId]);
        return $stmt->fetchAll();
    }
}
