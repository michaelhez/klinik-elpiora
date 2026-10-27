<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class Registration extends Model
{
    protected static string $table = 'registrations';
    protected static bool $timestamps = true;
    protected static array $fillable = [
        'registration_number', 'patient_id', 'doctor_id', 'polyclinic_id', 'room_id',
        'appointment_id', 'queue_id', 'payment_type', 'status', 'notes', 'registered_by', 'registered_at',
    ];

    public static function nextRegistrationNumber(): string
    {
        $year = date('Y');
        $prefix = "REG-{$year}-";

        $sql = 'SELECT registration_number FROM registrations WHERE registration_number LIKE :prefix ORDER BY id DESC LIMIT 1';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['prefix' => $prefix . '%']);
        $last = $stmt->fetchColumn();

        $sequence = $last ? ((int) substr((string) $last, -6)) + 1 : 1;

        return $prefix . str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }

    public static function findWithRelations(int|string $id): ?array
    {
        $sql = "SELECT r.*, p.name AS patient_name, p.medical_record_number,
                       e.name AS doctor_name, poly.name AS polyclinic_name, rm.name AS room_name,
                       q.queue_number, q.status AS queue_status
                FROM registrations r
                INNER JOIN patients p ON p.id = r.patient_id
                LEFT JOIN doctors d ON d.id = r.doctor_id
                LEFT JOIN employees e ON e.id = d.employee_id
                INNER JOIN polyclinics poly ON poly.id = r.polyclinic_id
                LEFT JOIN rooms rm ON rm.id = r.room_id
                LEFT JOIN queues q ON q.id = r.queue_id
                WHERE r.id = :id";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    public static function forPatient(int|string $patientId): array
    {
        $sql = "SELECT r.*, e.name AS doctor_name, poly.name AS polyclinic_name, q.queue_number, q.status AS queue_status
                FROM registrations r
                LEFT JOIN doctors d ON d.id = r.doctor_id
                LEFT JOIN employees e ON e.id = d.employee_id
                INNER JOIN polyclinics poly ON poly.id = r.polyclinic_id
                LEFT JOIN queues q ON q.id = r.queue_id
                WHERE r.patient_id = :patient_id
                ORDER BY r.registered_at DESC";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['patient_id' => $patientId]);
        return $stmt->fetchAll();
    }
}
