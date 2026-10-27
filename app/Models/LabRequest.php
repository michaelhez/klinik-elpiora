<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class LabRequest extends Model
{
    protected static string $table = 'lab_requests';
    protected static bool $timestamps = true;
    protected static array $fillable = [
        'request_number', 'medical_record_id', 'patient_id', 'doctor_id', 'status', 'notes', 'requested_by', 'requested_at',
    ];

    public static function nextRequestNumber(): string
    {
        $year = date('Y');
        $prefix = "LAB-{$year}-";

        $sql = 'SELECT request_number FROM lab_requests WHERE request_number LIKE :prefix ORDER BY id DESC LIMIT 1';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['prefix' => $prefix . '%']);
        $last = $stmt->fetchColumn();

        $sequence = $last ? ((int) substr((string) $last, -6)) + 1 : 1;

        return $prefix . str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }

    public static function findWithRelations(int|string $id): ?array
    {
        $sql = "SELECT lr.*, p.name AS patient_name, p.medical_record_number, e.name AS doctor_name, mr.record_number
                FROM lab_requests lr
                INNER JOIN patients p ON p.id = lr.patient_id
                INNER JOIN doctors d ON d.id = lr.doctor_id
                INNER JOIN employees e ON e.id = d.employee_id
                INNER JOIN medical_records mr ON mr.id = lr.medical_record_id
                WHERE lr.id = :id";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    public static function forMedicalRecord(int|string $medicalRecordId): array
    {
        $sql = "SELECT lr.* FROM lab_requests lr WHERE lr.medical_record_id = :id ORDER BY lr.created_at DESC";
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['id' => $medicalRecordId]);
        return $stmt->fetchAll();
    }

    public static function queue(array $statuses = []): array
    {
        $sql = "SELECT lr.*, p.name AS patient_name, p.medical_record_number, e.name AS doctor_name
                FROM lab_requests lr
                INNER JOIN patients p ON p.id = lr.patient_id
                INNER JOIN doctors d ON d.id = lr.doctor_id
                INNER JOIN employees e ON e.id = d.employee_id";

        $bindings = [];
        if ($statuses !== []) {
            $placeholders = [];
            foreach ($statuses as $i => $status) {
                $key = "status{$i}";
                $placeholders[] = ":{$key}";
                $bindings[$key] = $status;
            }
            $sql .= ' WHERE lr.status IN (' . implode(', ', $placeholders) . ')';
        }

        $sql .= ' ORDER BY lr.requested_at DESC LIMIT 100';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll();
    }
}
