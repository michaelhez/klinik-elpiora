<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class Appointment extends Model
{
    protected static string $table = 'appointments';
    protected static bool $timestamps = true;
    protected static array $fillable = [
        'appointment_number', 'patient_id', 'doctor_id', 'polyclinic_id',
        'appointment_date', 'appointment_time', 'status', 'notes', 'created_by',
    ];

    public static function nextAppointmentNumber(): string
    {
        $year = date('Y');
        $prefix = "APT-{$year}-";

        $sql = 'SELECT appointment_number FROM appointments WHERE appointment_number LIKE :prefix ORDER BY id DESC LIMIT 1';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['prefix' => $prefix . '%']);
        $last = $stmt->fetchColumn();

        $sequence = $last ? ((int) substr((string) $last, -6)) + 1 : 1;

        return $prefix . str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }

    public static function forPatient(int|string $patientId): array
    {
        $sql = 'SELECT a.*, e.name AS doctor_name, p.name AS polyclinic_name
                FROM appointments a
                INNER JOIN doctors d ON d.id = a.doctor_id
                INNER JOIN employees e ON e.id = d.employee_id
                INNER JOIN polyclinics p ON p.id = a.polyclinic_id
                WHERE a.patient_id = :patient_id
                ORDER BY a.appointment_date DESC, a.appointment_time DESC';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['patient_id' => $patientId]);
        return $stmt->fetchAll();
    }
}
