<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class LabRequestItem extends Model
{
    protected static string $table = 'lab_request_items';
    protected static bool $timestamps = false;
    protected static array $fillable = ['lab_request_id', 'lab_test_id', 'price', 'created_at'];

    public static function forRequest(int|string $labRequestId): array
    {
        $sql = "SELECT lri.*, lt.code AS test_code, lt.name AS test_name, lt.sample_type,
                       lres.id AS result_id, lres.performed_at, lres.validated_at
                FROM lab_request_items lri
                INNER JOIN lab_tests lt ON lt.id = lri.lab_test_id
                LEFT JOIN lab_results lres ON lres.lab_request_item_id = lri.id
                WHERE lri.lab_request_id = :id
                ORDER BY lri.id ASC";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['id' => $labRequestId]);
        return $stmt->fetchAll();
    }
}
