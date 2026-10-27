<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Clinic extends Model
{
    protected static string $table = 'clinics';
    protected static bool $softDeletes = true;
    protected static array $fillable = ['name', 'code', 'address', 'phone', 'email', 'logo', 'is_active'];
}
