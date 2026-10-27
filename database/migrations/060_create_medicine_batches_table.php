<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE medicine_batches (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                medicine_id BIGINT UNSIGNED NOT NULL,
                branch_id BIGINT UNSIGNED NOT NULL,
                batch_number VARCHAR(50) NOT NULL,
                expired_date DATE NOT NULL,
                quantity INT NOT NULL DEFAULT 0,
                purchase_price DECIMAL(12,2) NOT NULL DEFAULT 0,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_medicine_batches_medicine_id (medicine_id),
                INDEX idx_medicine_batches_branch_id (branch_id),
                INDEX idx_medicine_batches_expired_date (expired_date),
                UNIQUE KEY uq_medicine_batches (medicine_id, branch_id, batch_number),
                CONSTRAINT fk_medicine_batches_medicine FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE RESTRICT,
                CONSTRAINT fk_medicine_batches_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS medicine_batches;');
    }
};
