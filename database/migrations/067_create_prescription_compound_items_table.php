<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE prescription_compound_items (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                prescription_compound_id BIGINT UNSIGNED NOT NULL,
                medicine_id BIGINT UNSIGNED NOT NULL,
                quantity_per_dose DECIMAL(10,2) NOT NULL DEFAULT 1,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_prescription_compound_items_compound_id (prescription_compound_id),
                CONSTRAINT fk_prescription_compound_items_compound FOREIGN KEY (prescription_compound_id) REFERENCES prescription_compounds(id) ON DELETE CASCADE,
                CONSTRAINT fk_prescription_compound_items_medicine FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS prescription_compound_items;');
    }
};
