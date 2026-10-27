<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class InvoiceItem extends Model
{
    protected static string $table = 'invoice_items';
    protected static bool $timestamps = false;
    protected static array $fillable = ['invoice_id', 'item_type', 'reference_id', 'description', 'quantity', 'price', 'subtotal', 'created_at'];

    public static function forInvoice(int|string $invoiceId): array
    {
        $sql = 'SELECT * FROM invoice_items WHERE invoice_id = :invoice_id ORDER BY id ASC';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['invoice_id' => $invoiceId]);
        return $stmt->fetchAll();
    }
}
