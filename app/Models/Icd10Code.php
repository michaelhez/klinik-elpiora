<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class Icd10Code extends Model
{
    protected static string $table = 'icd10_codes';
    protected static bool $timestamps = true;
    protected static array $fillable = ['code', 'description', 'category', 'status'];

    public static function search(string $term, int $limit = 15): array
    {
        $sql = "SELECT id, code, description, category FROM icd10_codes
                WHERE status = 'ACTIVE' AND (code LIKE :term1 OR description LIKE :term2)
                ORDER BY code ASC LIMIT :limit";

        $stmt = Database::connection()->prepare($sql);
        $needle = "%{$term}%";
        $stmt->bindValue(':term1', $needle);
        $stmt->bindValue(':term2', $needle);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
