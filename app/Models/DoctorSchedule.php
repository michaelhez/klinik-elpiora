<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class DoctorSchedule extends Model
{
    protected static string $table = 'doctor_schedules';
    protected static array $fillable = [
        'doctor_id', 'polyclinic_id', 'room_id', 'day_of_week',
        'start_time', 'end_time', 'quota', 'is_active',
    ];

    public static function forDoctor(int|string $doctorId): array
    {
        $sql = 'SELECT ds.*, p.name AS polyclinic_name, r.name AS room_name
                FROM doctor_schedules ds
                INNER JOIN polyclinics p ON p.id = ds.polyclinic_id
                LEFT JOIN rooms r ON r.id = ds.room_id
                WHERE ds.doctor_id = :doctor_id
                ORDER BY ds.day_of_week ASC, ds.start_time ASC';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['doctor_id' => $doctorId]);
        return $stmt->fetchAll();
    }
}
