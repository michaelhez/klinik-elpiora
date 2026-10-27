<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class PatientEmergencyContact extends Model
{
    protected static string $table = 'patient_emergency_contacts';
    protected static bool $timestamps = false;
    protected static array $fillable = ['patient_id', 'name', 'relationship', 'phone', 'address', 'created_at'];

    public static function forPatient(int|string $patientId): array
    {
        return static::query()->where('patient_id', '=', $patientId)->orderBy('id', 'ASC')->get();
    }
}
