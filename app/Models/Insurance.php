<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Insurance extends Model
{
    protected static string $table = 'insurances';
    protected static bool $softDeletes = true;
    protected static array $fillable = ['code', 'name', 'type', 'contact_person', 'phone', 'address', 'status'];
}
