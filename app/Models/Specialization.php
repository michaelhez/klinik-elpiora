<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Specialization extends Model
{
    protected static string $table = 'specializations';
    protected static bool $softDeletes = true;
    protected static array $fillable = ['name', 'code', 'description', 'is_active'];
}
