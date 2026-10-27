<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class Queue extends Model
{
    protected static string $table = 'queues';
    protected static bool $timestamps = true;
    protected static array $fillable = [
        'registration_id', 'polyclinic_id', 'queue_number', 'queue_date',
        'status', 'called_at', 'started_at', 'completed_at',
    ];

    public static function todayForPolyclinic(int|string $polyclinicId): array
    {
        $sql = "SELECT q.*, p.name AS patient_name, poly.name AS polyclinic_name
                FROM queues q
                INNER JOIN registrations r ON r.id = q.registration_id
                INNER JOIN patients p ON p.id = r.patient_id
                INNER JOIN polyclinics poly ON poly.id = q.polyclinic_id
                WHERE q.polyclinic_id = :polyclinic_id AND q.queue_date = CURDATE()
                ORDER BY q.id ASC";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['polyclinic_id' => $polyclinicId]);
        return $stmt->fetchAll();
    }

    public static function todayAll(): array
    {
        $sql = "SELECT q.*, p.name AS patient_name, poly.name AS polyclinic_name
                FROM queues q
                INNER JOIN registrations r ON r.id = q.registration_id
                INNER JOIN patients p ON p.id = r.patient_id
                INNER JOIN polyclinics poly ON poly.id = q.polyclinic_id
                WHERE q.queue_date = CURDATE()
                ORDER BY poly.name ASC, q.id ASC";

        return Database::connection()->query($sql)->fetchAll();
    }

    public static function currentlyServingByPolyclinic(): array
    {
        $sql = "SELECT q.*, poly.name AS polyclinic_name
                FROM queues q
                INNER JOIN polyclinics poly ON poly.id = q.polyclinic_id
                WHERE q.queue_date = CURDATE() AND q.status = 'CALLED'
                ORDER BY q.called_at DESC";

        return Database::connection()->query($sql)->fetchAll();
    }

    public static function waitingCount(int|string $polyclinicId): int
    {
        $sql = "SELECT COUNT(*) FROM queues WHERE polyclinic_id = :polyclinic_id AND queue_date = CURDATE() AND status = 'WAITING'";
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['polyclinic_id' => $polyclinicId]);
        return (int) $stmt->fetchColumn();
    }
}
