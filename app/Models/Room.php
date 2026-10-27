<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Room extends Model
{
    protected static string $table = 'rooms';
    protected static bool $softDeletes = true;
    protected static array $fillable = ['branch_id', 'polyclinic_id', 'name', 'code', 'floor', 'capacity', 'is_active'];
}
