<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class PatientContact extends Model
{
    protected static string $table = 'patient_contacts';
    protected static bool $timestamps = false;
    protected static array $fillable = ['patient_id', 'type', 'value', 'label', 'is_primary', 'created_at'];

    public static function forPatient(int|string $patientId): array
    {
        return static::query()->where('patient_id', '=', $patientId)->orderBy('is_primary', 'DESC')->get();
    }
}
