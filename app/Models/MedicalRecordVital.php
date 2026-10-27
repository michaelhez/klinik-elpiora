<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class MedicalRecordVital extends Model
{
    protected static string $table = 'medical_record_vitals';
    protected static array $fillable = [
        'medical_record_id', 'systolic', 'diastolic', 'heart_rate', 'temperature',
        'respiratory_rate', 'oxygen_saturation', 'weight', 'height', 'bmi', 'recorded_by',
    ];

    public static function forRecord(int|string $medicalRecordId): ?array
    {
        return static::findBy('medical_record_id', $medicalRecordId);
    }
}
