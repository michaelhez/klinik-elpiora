<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE stock_movements (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                medicine_id BIGINT UNSIGNED NOT NULL,
                medicine_batch_id BIGINT UNSIGNED NULL,
                branch_id BIGINT UNSIGNED NOT NULL,
                type ENUM('PURCHASE','DISPENSING','SALE','RETURN','ADJUSTMENT','EXPIRED','DAMAGED','TRANSFER') NOT NULL,
                quantity_change INT NOT NULL,
                reference_type VARCHAR(50) NULL,
                reference_id BIGINT UNSIGNED NULL,
                notes VARCHAR(255) NULL,
                created_by BIGINT UNSIGNED NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_stock_movements_medicine_id (medicine_id),
                INDEX idx_stock_movements_batch_id (medicine_batch_id),
                INDEX idx_stock_movements_branch_id (branch_id),
                INDEX idx_stock_movements_type (type),
                INDEX idx_stock_movements_reference (reference_type, reference_id),
                CONSTRAINT fk_stock_movements_medicine FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE RESTRICT,
                CONSTRAINT fk_stock_movements_batch FOREIGN KEY (medicine_batch_id) REFERENCES medicine_batches(id) ON DELETE SET NULL,
                CONSTRAINT fk_stock_movements_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE RESTRICT,
                CONSTRAINT fk_stock_movements_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS stock_movements;');
    }
};
