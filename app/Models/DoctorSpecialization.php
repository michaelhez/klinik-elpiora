<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class DoctorSpecialization extends Model
{
    protected static string $table = 'doctor_specializations';
    protected static bool $timestamps = false;
    protected static array $fillable = ['doctor_id', 'specialization_id', 'is_primary'];
}
