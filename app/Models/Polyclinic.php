<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Polyclinic extends Model
{
    protected static string $table = 'polyclinics';
    protected static bool $softDeletes = true;
    protected static array $fillable = ['branch_id', 'name', 'code', 'description', 'icon', 'queue_prefix', 'consultation_fee', 'is_active'];
}
