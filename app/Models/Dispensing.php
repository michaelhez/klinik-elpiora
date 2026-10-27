<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Dispensing extends Model
{
    protected static string $table = 'dispensings';
    protected static bool $timestamps = false;
    protected static array $fillable = ['prescription_id', 'dispensed_by', 'dispensed_at', 'notes', 'created_at'];

    public static function forPrescription(int|string $prescriptionId): ?array
    {
        return static::findBy('prescription_id', $prescriptionId);
    }
}
