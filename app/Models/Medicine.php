<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class Medicine extends Model
{
    protected static string $table = 'medicines';
    protected static bool $softDeletes = true;
    protected static array $fillable = [
        'category_id', 'unit_id', 'code', 'name', 'generic_name', 'brand_name',
        'purchase_price', 'selling_price', 'minimum_stock', 'description', 'status',
    ];

    public static function search(string $term, int $limit = 15): array
    {
        $sql = "SELECT id, code, name, generic_name, selling_price, purchase_price FROM medicines
                WHERE status = 'ACTIVE' AND deleted_at IS NULL AND (code LIKE :term1 OR name LIKE :term2 OR generic_name LIKE :term3)
                ORDER BY name ASC LIMIT :limit";

        $stmt = Database::connection()->prepare($sql);
        $needle = "%{$term}%";
        $stmt->bindValue(':term1', $needle);
        $stmt->bindValue(':term2', $needle);
        $stmt->bindValue(':term3', $needle);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function findWithRelations(int|string $id): ?array
    {
        $sql = "SELECT m.*, c.name AS category_name, u.name AS unit_name
                FROM medicines m
                LEFT JOIN medicine_categories c ON c.id = m.category_id
                LEFT JOIN medicine_units u ON u.id = m.unit_id
                WHERE m.id = :id";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();

        return $result ?: null;
    }
}
