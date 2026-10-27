<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class PatientMedicalHistory extends Model
{
    protected static string $table = 'patient_medical_histories';
    protected static bool $timestamps = false;
    protected static array $fillable = ['patient_id', 'condition_name', 'diagnosed_date', 'status', 'notes', 'created_at'];

    public static function forPatient(int|string $patientId): array
    {
        return static::query()->where('patient_id', '=', $patientId)->orderBy('id', 'DESC')->get();
    }
}
