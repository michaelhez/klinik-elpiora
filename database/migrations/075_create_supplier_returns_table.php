<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE supplier_returns (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                return_number VARCHAR(30) NOT NULL,
                supplier_id BIGINT UNSIGNED NOT NULL,
                goods_receipt_id BIGINT UNSIGNED NULL,
                reason VARCHAR(255) NOT NULL,
                status ENUM('DRAFT','SUBMITTED','COMPLETED') NOT NULL DEFAULT 'DRAFT',
                returned_by BIGINT UNSIGNED NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_supplier_returns_number (return_number),
                INDEX idx_supplier_returns_supplier_id (supplier_id),
                CONSTRAINT fk_supplier_returns_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE RESTRICT,
                CONSTRAINT fk_supplier_returns_receipt FOREIGN KEY (goods_receipt_id) REFERENCES goods_receipts(id) ON DELETE SET NULL,
                CONSTRAINT fk_supplier_returns_user FOREIGN KEY (returned_by) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS supplier_returns;');
    }
};
