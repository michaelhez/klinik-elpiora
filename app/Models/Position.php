<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Position extends Model
{
    protected static string $table = 'positions';
    protected static bool $softDeletes = true;
    protected static array $fillable = ['department_id', 'name', 'code', 'level', 'description', 'is_active'];
}
