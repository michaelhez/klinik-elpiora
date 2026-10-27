<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class MedicalRecordAnamnesis extends Model
{
    protected static string $table = 'medical_record_anamnesis';
    protected static array $fillable = [
        'medical_record_id', 'chief_complaint', 'present_illness_history', 'past_medical_history', 'notes', 'created_by',
    ];

    public static function forRecord(int|string $medicalRecordId): ?array
    {
        return static::findBy('medical_record_id', $medicalRecordId);
    }
}
