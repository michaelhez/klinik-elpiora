<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class SupplierReturn extends Model
{
    protected static string $table = 'supplier_returns';
    protected static bool $timestamps = true;
    protected static array $fillable = ['return_number', 'supplier_id', 'goods_receipt_id', 'reason', 'status', 'returned_by'];

    public static function nextReturnNumber(): string
    {
        $year = date('Y');
        $prefix = "SR-{$year}-";

        $sql = 'SELECT return_number FROM supplier_returns WHERE return_number LIKE :prefix ORDER BY id DESC LIMIT 1';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['prefix' => $prefix . '%']);
        $last = $stmt->fetchColumn();

        $sequence = $last ? ((int) substr((string) $last, -6)) + 1 : 1;

        return $prefix . str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }

    public static function findWithRelations(int|string $id): ?array
    {
        $sql = "SELECT sr.*, s.name AS supplier_name FROM supplier_returns sr
                INNER JOIN suppliers s ON s.id = sr.supplier_id
                WHERE sr.id = :id";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    public static function list(): array
    {
        $sql = "SELECT sr.*, s.name AS supplier_name FROM supplier_returns sr
                INNER JOIN suppliers s ON s.id = sr.supplier_id
                ORDER BY sr.created_at DESC LIMIT 100";

        return Database::connection()->query($sql)->fetchAll();
    }
}
