<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE invoice_items (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                invoice_id BIGINT UNSIGNED NOT NULL,
                item_type ENUM('CONSULTATION','MEDICAL_ACTION','LAB_TEST','PRESCRIPTION_ITEM','PRESCRIPTION_COMPOUND','OTHER') NOT NULL,
                reference_id BIGINT UNSIGNED NULL,
                description VARCHAR(255) NOT NULL,
                quantity INT UNSIGNED NOT NULL DEFAULT 1,
                price DECIMAL(12,2) NOT NULL DEFAULT 0,
                subtotal DECIMAL(14,2) NOT NULL DEFAULT 0,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_invoice_items_invoice_id (invoice_id),
                CONSTRAINT fk_invoice_items_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS invoice_items;');
    }
};
