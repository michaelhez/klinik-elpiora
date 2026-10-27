<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class MedicalRecord extends Model
{
    protected static string $table = 'medical_records';
    protected static bool $timestamps = true;
    protected static array $fillable = [
        'record_number', 'registration_id', 'patient_id', 'doctor_id', 'status',
        'chief_complaint', 'finalized_at', 'finalized_by',
    ];

    public static function nextRecordNumber(): string
    {
        $year = date('Y');
        $prefix = "MREC-{$year}-";

        $sql = 'SELECT record_number FROM medical_records WHERE record_number LIKE :prefix ORDER BY id DESC LIMIT 1';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['prefix' => $prefix . '%']);
        $last = $stmt->fetchColumn();

        $sequence = $last ? ((int) substr((string) $last, -6)) + 1 : 1;

        return $prefix . str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }

    public static function findByRegistration(int|string $registrationId): ?array
    {
        return static::findBy('registration_id', $registrationId);
    }

    public static function findWithRelations(int|string $id): ?array
    {
        $sql = "SELECT mr.*, p.name AS patient_name, p.medical_record_number, p.gender, p.birth_date, p.blood_type,
                       e.name AS doctor_name, r.registration_number
                FROM medical_records mr
                INNER JOIN patients p ON p.id = mr.patient_id
                INNER JOIN doctors d ON d.id = mr.doctor_id
                INNER JOIN employees e ON e.id = d.employee_id
                INNER JOIN registrations r ON r.id = mr.registration_id
                WHERE mr.id = :id";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    public static function forPatient(int|string $patientId): array
    {
        $sql = "SELECT mr.*, e.name AS doctor_name
                FROM medical_records mr
                INNER JOIN doctors d ON d.id = mr.doctor_id
                INNER JOIN employees e ON e.id = d.employee_id
                WHERE mr.patient_id = :patient_id
                ORDER BY mr.created_at DESC";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['patient_id' => $patientId]);
        return $stmt->fetchAll();
    }
}
