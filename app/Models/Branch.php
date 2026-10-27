<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Branch extends Model
{
    protected static string $table = 'branches';
    protected static bool $softDeletes = true;
    protected static array $fillable = ['clinic_id', 'name', 'code', 'address', 'phone', 'email', 'is_main', 'is_active'];
}
