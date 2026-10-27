<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE dispensing_items (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                dispensing_id BIGINT UNSIGNED NOT NULL,
                prescription_item_id BIGINT UNSIGNED NULL,
                prescription_compound_id BIGINT UNSIGNED NULL,
                medicine_batch_id BIGINT UNSIGNED NOT NULL,
                quantity_taken INT UNSIGNED NOT NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_dispensing_items_dispensing_id (dispensing_id),
                CONSTRAINT fk_dispensing_items_dispensing FOREIGN KEY (dispensing_id) REFERENCES dispensings(id) ON DELETE CASCADE,
                CONSTRAINT fk_dispensing_items_prescription_item FOREIGN KEY (prescription_item_id) REFERENCES prescription_items(id) ON DELETE SET NULL,
                CONSTRAINT fk_dispensing_items_compound FOREIGN KEY (prescription_compound_id) REFERENCES prescription_compounds(id) ON DELETE SET NULL,
                CONSTRAINT fk_dispensing_items_batch FOREIGN KEY (medicine_batch_id) REFERENCES medicine_batches(id) ON DELETE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS dispensing_items;');
    }
};
