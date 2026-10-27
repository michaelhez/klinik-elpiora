<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class LabParameter extends Model
{
    protected static string $table = 'lab_parameters';
    protected static array $fillable = [
        'lab_test_id', 'name', 'unit', 'value_type', 'reference_min', 'reference_max', 'reference_text', 'sort_order',
    ];

    public static function forTest(int|string $labTestId): array
    {
        return static::query()->where('lab_test_id', '=', $labTestId)->orderBy('sort_order', 'ASC')->get();
    }
}
