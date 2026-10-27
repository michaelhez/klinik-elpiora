<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class MedicalRecordPhysicalExamination extends Model
{
    protected static string $table = 'medical_record_physical_examinations';
    protected static array $fillable = [
        'medical_record_id', 'general_condition', 'consciousness', 'head_neck',
        'chest', 'abdomen', 'extremities', 'notes', 'created_by',
    ];

    public static function forRecord(int|string $medicalRecordId): ?array
    {
        return static::findBy('medical_record_id', $medicalRecordId);
    }
}
