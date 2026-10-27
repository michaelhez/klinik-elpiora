<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE supplier_return_items (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                supplier_return_id BIGINT UNSIGNED NOT NULL,
                medicine_batch_id BIGINT UNSIGNED NOT NULL,
                quantity INT UNSIGNED NOT NULL,
                notes VARCHAR(255) NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_supplier_return_items_return_id (supplier_return_id),
                CONSTRAINT fk_supplier_return_items_return FOREIGN KEY (supplier_return_id) REFERENCES supplier_returns(id) ON DELETE CASCADE,
                CONSTRAINT fk_supplier_return_items_batch FOREIGN KEY (medicine_batch_id) REFERENCES medicine_batches(id) ON DELETE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS supplier_return_items;');
    }
};
