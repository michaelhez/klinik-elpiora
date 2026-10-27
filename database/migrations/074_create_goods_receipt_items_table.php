<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE goods_receipt_items (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                goods_receipt_id BIGINT UNSIGNED NOT NULL,
                purchase_order_item_id BIGINT UNSIGNED NOT NULL,
                medicine_id BIGINT UNSIGNED NOT NULL,
                medicine_batch_id BIGINT UNSIGNED NULL,
                quantity_received INT UNSIGNED NOT NULL,
                batch_number VARCHAR(50) NOT NULL,
                expired_date DATE NOT NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_goods_receipt_items_receipt_id (goods_receipt_id),
                CONSTRAINT fk_goods_receipt_items_receipt FOREIGN KEY (goods_receipt_id) REFERENCES goods_receipts(id) ON DELETE CASCADE,
                CONSTRAINT fk_goods_receipt_items_po_item FOREIGN KEY (purchase_order_item_id) REFERENCES purchase_order_items(id) ON DELETE RESTRICT,
                CONSTRAINT fk_goods_receipt_items_medicine FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE RESTRICT,
                CONSTRAINT fk_goods_receipt_items_batch FOREIGN KEY (medicine_batch_id) REFERENCES medicine_batches(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS goods_receipt_items;');
    }
};
