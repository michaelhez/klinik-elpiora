<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE purchase_order_items (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                purchase_order_id BIGINT UNSIGNED NOT NULL,
                medicine_id BIGINT UNSIGNED NOT NULL,
                quantity_ordered INT UNSIGNED NOT NULL,
                purchase_price DECIMAL(12,2) NOT NULL DEFAULT 0,
                subtotal DECIMAL(14,2) NOT NULL DEFAULT 0,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_purchase_order_items_po_id (purchase_order_id),
                CONSTRAINT fk_purchase_order_items_po FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id) ON DELETE CASCADE,
                CONSTRAINT fk_purchase_order_items_medicine FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS purchase_order_items;');
    }
};
