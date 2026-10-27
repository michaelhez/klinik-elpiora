<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class LabTest extends Model
{
    protected static string $table = 'lab_tests';
    protected static bool $softDeletes = true;
    protected static array $fillable = ['code', 'name', 'category', 'sample_type', 'price', 'description', 'status'];

    public static function search(string $term, int $limit = 15): array
    {
        $sql = "SELECT id, code, name, price, sample_type FROM lab_tests
                WHERE status = 'ACTIVE' AND deleted_at IS NULL AND (code LIKE :term1 OR name LIKE :term2)
                ORDER BY name ASC LIMIT :limit";

        $stmt = Database::connection()->prepare($sql);
        $needle = "%{$term}%";
        $stmt->bindValue(':term1', $needle);
        $stmt->bindValue(':term2', $needle);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
