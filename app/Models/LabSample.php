<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class LabSample extends Model
{
    protected static string $table = 'lab_samples';
    protected static bool $timestamps = false;
    protected static array $fillable = ['lab_request_id', 'sample_number', 'sample_type', 'collected_at', 'collected_by', 'notes', 'created_at'];

    public static function nextSampleNumber(): string
    {
        $year = date('Y');
        $prefix = "SPL-{$year}-";

        $sql = 'SELECT sample_number FROM lab_samples WHERE sample_number LIKE :prefix ORDER BY id DESC LIMIT 1';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['prefix' => $prefix . '%']);
        $last = $stmt->fetchColumn();

        $sequence = $last ? ((int) substr((string) $last, -6)) + 1 : 1;

        return $prefix . str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }

    public static function forRequest(int|string $labRequestId): array
    {
        return static::query()->where('lab_request_id', '=', $labRequestId)->orderBy('id', 'ASC')->get();
    }
}
