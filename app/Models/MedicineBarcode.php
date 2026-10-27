<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class MedicineBarcode extends Model
{
    protected static string $table = 'medicine_barcodes';
    protected static bool $timestamps = false;
    protected static array $fillable = ['medicine_id', 'barcode', 'is_primary', 'created_at'];

    public static function forMedicine(int|string $medicineId): array
    {
        return static::query()->where('medicine_id', '=', $medicineId)->orderBy('is_primary', 'DESC')->get();
    }
}
