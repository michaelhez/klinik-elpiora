<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class MedicineBatch extends Model
{
    protected static string $table = 'medicine_batches';
    protected static array $fillable = ['medicine_id', 'branch_id', 'batch_number', 'expired_date', 'quantity', 'purchase_price'];

    /**
     * Batches eligible for FEFO dispensing: not expired, quantity remaining, ordered by soonest expiry first.
     */
    public static function fefoEligible(int|string $medicineId, int|string $branchId): array
    {
        $sql = "SELECT * FROM medicine_batches
                WHERE medicine_id = :medicine_id AND branch_id = :branch_id
                AND quantity > 0 AND expired_date >= CURDATE()
                ORDER BY expired_date ASC, id ASC";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['medicine_id' => $medicineId, 'branch_id' => $branchId]);
        return $stmt->fetchAll();
    }

    public static function forMedicine(int|string $medicineId): array
    {
        $sql = "SELECT b.*, br.name AS branch_name FROM medicine_batches b
                INNER JOIN branches br ON br.id = b.branch_id
                WHERE b.medicine_id = :medicine_id
                ORDER BY b.expired_date ASC";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['medicine_id' => $medicineId]);
        return $stmt->fetchAll();
    }

    public static function totalStock(int|string $medicineId, ?int $branchId = null): int
    {
        $sql = 'SELECT COALESCE(SUM(quantity), 0) FROM medicine_batches WHERE medicine_id = :medicine_id AND expired_date >= CURDATE()';
        $bindings = ['medicine_id' => $medicineId];

        if ($branchId !== null) {
            $sql .= ' AND branch_id = :branch_id';
            $bindings['branch_id'] = $branchId;
        }

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($bindings);
        return (int) $stmt->fetchColumn();
    }

    public static function expiringSoon(int $days = 90): array
    {
        $sql = "SELECT b.*, m.name AS medicine_name, m.code AS medicine_code, br.name AS branch_name
                FROM medicine_batches b
                INNER JOIN medicines m ON m.id = b.medicine_id
                INNER JOIN branches br ON br.id = b.branch_id
                WHERE b.quantity > 0 AND b.expired_date <= DATE_ADD(CURDATE(), INTERVAL :days DAY)
                ORDER BY b.expired_date ASC";

        $stmt = Database::connection()->prepare($sql);
        $stmt->bindValue(':days', $days, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
