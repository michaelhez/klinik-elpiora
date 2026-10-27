<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class RegistrationStatusHistory extends Model
{
    protected static string $table = 'registration_status_histories';
    protected static bool $timestamps = false;
    protected static array $fillable = ['registration_id', 'status', 'notes', 'changed_by', 'created_at'];
}
