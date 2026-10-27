<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class StockTransfer extends Model
{
    protected static string $table = 'stock_transfers';
    protected static bool $timestamps = false;
    protected static array $fillable = [
        'transfer_number', 'medicine_id', 'medicine_batch_id', 'from_branch_id',
        'to_branch_id', 'quantity', 'notes', 'transferred_by', 'created_at',
    ];

    public static function nextTransferNumber(): string
    {
        $year = date('Y');
        $prefix = "TRF-{$year}-";

        $sql = 'SELECT transfer_number FROM stock_transfers WHERE transfer_number LIKE :prefix ORDER BY id DESC LIMIT 1';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['prefix' => $prefix . '%']);
        $last = $stmt->fetchColumn();

        $sequence = $last ? ((int) substr((string) $last, -6)) + 1 : 1;

        return $prefix . str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }

    public static function recent(int $limit = 50): array
    {
        $sql = "SELECT st.*, m.name AS medicine_name, fb.name AS from_branch_name, tb.name AS to_branch_name, u.name AS user_name
                FROM stock_transfers st
                INNER JOIN medicines m ON m.id = st.medicine_id
                INNER JOIN branches fb ON fb.id = st.from_branch_id
                INNER JOIN branches tb ON tb.id = st.to_branch_id
                LEFT JOIN users u ON u.id = st.transferred_by
                ORDER BY st.created_at DESC
                LIMIT :limit";

        $stmt = Database::connection()->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
