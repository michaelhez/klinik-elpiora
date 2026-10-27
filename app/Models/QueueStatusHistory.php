<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class QueueStatusHistory extends Model
{
    protected static string $table = 'queue_status_histories';
    protected static bool $timestamps = false;
    protected static array $fillable = ['queue_id', 'status', 'notes', 'changed_by', 'created_at'];
}
