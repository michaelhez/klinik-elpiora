<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class PurchaseOrder extends Model
{
    protected static string $table = 'purchase_orders';
    protected static bool $timestamps = true;
    protected static array $fillable = [
        'po_number', 'supplier_id', 'branch_id', 'status', 'order_date', 'expected_date',
        'notes', 'created_by', 'approved_by', 'approved_at',
    ];

    public static function nextPoNumber(): string
    {
        $year = date('Y');
        $prefix = "PO-{$year}-";

        $sql = 'SELECT po_number FROM purchase_orders WHERE po_number LIKE :prefix ORDER BY id DESC LIMIT 1';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['prefix' => $prefix . '%']);
        $last = $stmt->fetchColumn();

        $sequence = $last ? ((int) substr((string) $last, -6)) + 1 : 1;

        return $prefix . str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }

    public static function findWithRelations(int|string $id): ?array
    {
        $sql = "SELECT po.*, s.name AS supplier_name, s.code AS supplier_code, b.name AS branch_name
                FROM purchase_orders po
                INNER JOIN suppliers s ON s.id = po.supplier_id
                INNER JOIN branches b ON b.id = po.branch_id
                WHERE po.id = :id";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    public static function list(array $filters = []): array
    {
        $conditions = ['1 = 1'];
        $bindings = [];

        if (!empty($filters['status'])) {
            $conditions[] = 'po.status = :status';
            $bindings['status'] = $filters['status'];
        }

        $sql = 'SELECT po.*, s.name AS supplier_name FROM purchase_orders po
                INNER JOIN suppliers s ON s.id = po.supplier_id
                WHERE ' . implode(' AND ', $conditions) . '
                ORDER BY po.created_at DESC LIMIT 100';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll();
    }
}
