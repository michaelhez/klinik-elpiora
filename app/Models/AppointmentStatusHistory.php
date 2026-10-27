<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class AppointmentStatusHistory extends Model
{
    protected static string $table = 'appointment_status_histories';
    protected static bool $timestamps = false;
    protected static array $fillable = ['appointment_id', 'status', 'notes', 'changed_by', 'created_at'];
}
