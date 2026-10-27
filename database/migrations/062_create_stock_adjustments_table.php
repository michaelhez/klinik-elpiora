<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE stock_adjustments (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                medicine_id BIGINT UNSIGNED NOT NULL,
                medicine_batch_id BIGINT UNSIGNED NOT NULL,
                branch_id BIGINT UNSIGNED NOT NULL,
                adjustment_type ENUM('ADD','REMOVE') NOT NULL,
                quantity INT UNSIGNED NOT NULL,
                reason VARCHAR(255) NOT NULL,
                stock_movement_id BIGINT UNSIGNED NULL,
                adjusted_by BIGINT UNSIGNED NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_stock_adjustments_medicine_id (medicine_id),
                CONSTRAINT fk_stock_adjustments_medicine FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE RESTRICT,
                CONSTRAINT fk_stock_adjustments_batch FOREIGN KEY (medicine_batch_id) REFERENCES medicine_batches(id) ON DELETE RESTRICT,
                CONSTRAINT fk_stock_adjustments_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE RESTRICT,
                CONSTRAINT fk_stock_adjustments_movement FOREIGN KEY (stock_movement_id) REFERENCES stock_movements(id) ON DELETE SET NULL,
                CONSTRAINT fk_stock_adjustments_user FOREIGN KEY (adjusted_by) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS stock_adjustments;');
    }
};
