<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class PatientAllergy extends Model
{
    protected static string $table = 'patient_allergies';
    protected static bool $timestamps = false;
    protected static array $fillable = ['patient_id', 'allergen', 'severity', 'reaction', 'notes', 'created_at'];

    public static function forPatient(int|string $patientId): array
    {
        return static::query()->where('patient_id', '=', $patientId)->orderBy('id', 'DESC')->get();
    }
}
