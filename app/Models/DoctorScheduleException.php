<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class DoctorScheduleException extends Model
{
    protected static string $table = 'doctor_schedule_exceptions';
    protected static bool $timestamps = false;
    protected static array $fillable = ['doctor_id', 'exception_date', 'type', 'reason', 'start_time', 'end_time', 'created_at'];
}
