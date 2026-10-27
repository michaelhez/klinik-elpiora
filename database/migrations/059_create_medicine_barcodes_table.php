<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE medicine_barcodes (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                medicine_id BIGINT UNSIGNED NOT NULL,
                barcode VARCHAR(50) NOT NULL,
                is_primary TINYINT(1) NOT NULL DEFAULT 0,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_medicine_barcodes_barcode (barcode),
                INDEX idx_medicine_barcodes_medicine_id (medicine_id),
                CONSTRAINT fk_medicine_barcodes_medicine FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS medicine_barcodes;');
    }
};
