<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class MedicineUnit extends Model
{
    protected static string $table = 'medicine_units';
    protected static bool $softDeletes = true;
    protected static array $fillable = ['name', 'code', 'is_active'];
}
