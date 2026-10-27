<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class Doctor extends Model
{
    protected static string $table = 'doctors';
    protected static bool $softDeletes = true;
    protected static array $fillable = [
        'employee_id', 'doctor_number', 'str_number', 'sip_number',
        'consultation_fee', 'bio', 'status',
    ];

    public static function activeWithNames(?int $polyclinicId = null): array
    {
        $sql = "SELECT d.id, d.doctor_number, e.name FROM doctors d
                INNER JOIN employees e ON e.id = d.employee_id";
        $bindings = [];

        if ($polyclinicId !== null) {
            $sql .= ' INNER JOIN doctor_schedules ds ON ds.doctor_id = d.id AND ds.polyclinic_id = :polyclinic_id';
            $bindings['polyclinic_id'] = $polyclinicId;
        }

        $sql .= " WHERE d.status = 'ACTIVE' AND d.deleted_at IS NULL GROUP BY d.id ORDER BY e.name ASC";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll();
    }

    public static function nextDoctorNumber(): string
    {
        $year = date('Y');
        $prefix = "DOC-{$year}-";

        $sql = "SELECT doctor_number FROM doctors WHERE doctor_number LIKE :prefix ORDER BY id DESC LIMIT 1";
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['prefix' => $prefix . '%']);
        $last = $stmt->fetchColumn();

        $sequence = $last ? ((int) substr((string) $last, -6)) + 1 : 1;

        return $prefix . str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }

    public static function specializations(int|string $doctorId): array
    {
        $sql = 'SELECT s.*, ds.is_primary FROM specializations s
                INNER JOIN doctor_specializations ds ON ds.specialization_id = s.id
                WHERE ds.doctor_id = :doctor_id
                ORDER BY ds.is_primary DESC, s.name ASC';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['doctor_id' => $doctorId]);
        return $stmt->fetchAll();
    }

    public static function syncSpecializations(int|string $doctorId, array $specializationIds, int|string|null $primaryId = null): void
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare('DELETE FROM doctor_specializations WHERE doctor_id = :doctor_id');
            $stmt->execute(['doctor_id' => $doctorId]);

            $insert = $pdo->prepare(
                'INSERT INTO doctor_specializations (doctor_id, specialization_id, is_primary, created_at) VALUES (:doctor_id, :specialization_id, :is_primary, NOW())'
            );

            foreach ($specializationIds as $specializationId) {
                $insert->execute([
                    'doctor_id' => $doctorId,
                    'specialization_id' => $specializationId,
                    'is_primary' => ((string) $specializationId === (string) $primaryId) ? 1 : 0,
                ]);
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
