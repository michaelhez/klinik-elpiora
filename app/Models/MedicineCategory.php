<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class MedicineCategory extends Model
{
    protected static string $table = 'medicine_categories';
    protected static bool $softDeletes = true;
    protected static array $fillable = ['name', 'code', 'description', 'is_active'];
}
