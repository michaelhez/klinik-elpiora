<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Permission extends Model
{
    protected static string $table = 'permissions';
    protected static bool $timestamps = true;
    protected static array $fillable = ['name', 'module', 'action', 'description'];
}
