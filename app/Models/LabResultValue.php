<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class LabResultValue extends Model
{
    protected static string $table = 'lab_result_values';
    protected static bool $timestamps = false;
    protected static array $fillable = ['lab_result_id', 'lab_parameter_id', 'value', 'flag', 'created_at'];

    public static function forResult(int|string $labResultId): array
    {
        $sql = "SELECT v.*, p.name AS parameter_name, p.unit, p.reference_min, p.reference_max, p.reference_text, p.value_type
                FROM lab_result_values v
                INNER JOIN lab_parameters p ON p.id = v.lab_parameter_id
                WHERE v.lab_result_id = :id
                ORDER BY p.sort_order ASC";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['id' => $labResultId]);
        return $stmt->fetchAll();
    }
}
