<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE goods_receipts (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                receipt_number VARCHAR(30) NOT NULL,
                purchase_order_id BIGINT UNSIGNED NOT NULL,
                received_by BIGINT UNSIGNED NULL,
                received_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                notes VARCHAR(255) NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_goods_receipts_number (receipt_number),
                INDEX idx_goods_receipts_po_id (purchase_order_id),
                CONSTRAINT fk_goods_receipts_po FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id) ON DELETE RESTRICT,
                CONSTRAINT fk_goods_receipts_user FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS goods_receipts;');
    }
};
