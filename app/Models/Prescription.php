<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class Prescription extends Model
{
    protected static string $table = 'prescriptions';
    protected static bool $timestamps = true;
    protected static array $fillable = [
        'prescription_number', 'medical_record_id', 'patient_id', 'doctor_id',
        'branch_id', 'status', 'notes', 'created_by',
    ];

    public static function nextPrescriptionNumber(): string
    {
        $year = date('Y');
        $prefix = "RX-{$year}-";

        $sql = 'SELECT prescription_number FROM prescriptions WHERE prescription_number LIKE :prefix ORDER BY id DESC LIMIT 1';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['prefix' => $prefix . '%']);
        $last = $stmt->fetchColumn();

        $sequence = $last ? ((int) substr((string) $last, -6)) + 1 : 1;

        return $prefix . str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }

    public static function findWithRelations(int|string $id): ?array
    {
        $sql = "SELECT p.*, pt.name AS patient_name, pt.medical_record_number, e.name AS doctor_name, mr.record_number, b.name AS branch_name
                FROM prescriptions p
                INNER JOIN patients pt ON pt.id = p.patient_id
                INNER JOIN doctors d ON d.id = p.doctor_id
                INNER JOIN employees e ON e.id = d.employee_id
                INNER JOIN medical_records mr ON mr.id = p.medical_record_id
                INNER JOIN branches b ON b.id = p.branch_id
                WHERE p.id = :id";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    public static function forMedicalRecord(int|string $medicalRecordId): array
    {
        $sql = 'SELECT * FROM prescriptions WHERE medical_record_id = :id ORDER BY created_at DESC';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['id' => $medicalRecordId]);
        return $stmt->fetchAll();
    }

    public static function queue(array $statuses = []): array
    {
        $sql = "SELECT p.*, pt.name AS patient_name, pt.medical_record_number, e.name AS doctor_name
                FROM prescriptions p
                INNER JOIN patients pt ON pt.id = p.patient_id
                INNER JOIN doctors d ON d.id = p.doctor_id
                INNER JOIN employees e ON e.id = d.employee_id";

        $bindings = [];
        if ($statuses !== []) {
            $placeholders = [];
            foreach ($statuses as $i => $status) {
                $key = "status{$i}";
                $placeholders[] = ":{$key}";
                $bindings[$key] = $status;
            }
            $sql .= ' WHERE p.status IN (' . implode(', ', $placeholders) . ')';
        }

        $sql .= ' ORDER BY p.created_at DESC LIMIT 100';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll();
    }
}
