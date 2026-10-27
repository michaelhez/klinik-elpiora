<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class Payment extends Model
{
    protected static string $table = 'payments';
    protected static bool $timestamps = false;
    protected static array $fillable = ['payment_number', 'invoice_id', 'type', 'method', 'amount', 'reference_number', 'notes', 'paid_by', 'paid_at', 'created_at'];

    public static function nextPaymentNumber(): string
    {
        $year = date('Y');
        $prefix = "PAY-{$year}-";

        $sql = 'SELECT payment_number FROM payments WHERE payment_number LIKE :prefix ORDER BY id DESC LIMIT 1';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['prefix' => $prefix . '%']);
        $last = $stmt->fetchColumn();

        $sequence = $last ? ((int) substr((string) $last, -6)) + 1 : 1;

        return $prefix . str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }

    public static function forInvoice(int|string $invoiceId): array
    {
        $sql = "SELECT pay.*, u.name AS paid_by_name
                FROM payments pay
                LEFT JOIN users u ON u.id = pay.paid_by
                WHERE pay.invoice_id = :invoice_id
                ORDER BY pay.id ASC";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['invoice_id' => $invoiceId]);
        return $stmt->fetchAll();
    }

    public static function totalPaid(int|string $invoiceId): float
    {
        $sql = "SELECT COALESCE(SUM(CASE WHEN type = 'PAYMENT' THEN amount ELSE -amount END), 0)
                FROM payments WHERE invoice_id = :invoice_id";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['invoice_id' => $invoiceId]);
        return (float) $stmt->fetchColumn();
    }
}
