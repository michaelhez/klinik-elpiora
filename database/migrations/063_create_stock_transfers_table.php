<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE stock_transfers (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                transfer_number VARCHAR(30) NOT NULL,
                medicine_id BIGINT UNSIGNED NOT NULL,
                medicine_batch_id BIGINT UNSIGNED NOT NULL,
                from_branch_id BIGINT UNSIGNED NOT NULL,
                to_branch_id BIGINT UNSIGNED NOT NULL,
                quantity INT UNSIGNED NOT NULL,
                notes VARCHAR(255) NULL,
                transferred_by BIGINT UNSIGNED NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_stock_transfers_number (transfer_number),
                INDEX idx_stock_transfers_medicine_id (medicine_id),
                CONSTRAINT fk_stock_transfers_medicine FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE RESTRICT,
                CONSTRAINT fk_stock_transfers_batch FOREIGN KEY (medicine_batch_id) REFERENCES medicine_batches(id) ON DELETE RESTRICT,
                CONSTRAINT fk_stock_transfers_from FOREIGN KEY (from_branch_id) REFERENCES branches(id) ON DELETE RESTRICT,
                CONSTRAINT fk_stock_transfers_to FOREIGN KEY (to_branch_id) REFERENCES branches(id) ON DELETE RESTRICT,
                CONSTRAINT fk_stock_transfers_user FOREIGN KEY (transferred_by) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS stock_transfers;');
    }
};
