<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Corporate extends Model
{
    protected static string $table = 'corporates';
    protected static bool $softDeletes = true;
    protected static array $fillable = ['code', 'name', 'pic_name', 'phone', 'email', 'address', 'credit_limit', 'status'];
}
