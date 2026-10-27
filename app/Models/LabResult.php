<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class LabResult extends Model
{
    protected static string $table = 'lab_results';
    protected static array $fillable = ['lab_request_item_id', 'performed_by', 'performed_at', 'validated_by', 'validated_at', 'notes'];

    public static function forItem(int|string $labRequestItemId): ?array
    {
        return static::findBy('lab_request_item_id', $labRequestItemId);
    }
}
