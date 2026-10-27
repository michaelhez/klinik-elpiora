<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Department extends Model
{
    protected static string $table = 'departments';
    protected static bool $softDeletes = true;
    protected static array $fillable = ['branch_id', 'name', 'code', 'description', 'is_active'];
}
