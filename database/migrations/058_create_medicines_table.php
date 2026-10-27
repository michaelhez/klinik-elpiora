<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE medicines (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                category_id BIGINT UNSIGNED NULL,
                unit_id BIGINT UNSIGNED NULL,
                code VARCHAR(30) NOT NULL,
                name VARCHAR(150) NOT NULL,
                generic_name VARCHAR(150) NULL,
                brand_name VARCHAR(150) NULL,
                purchase_price DECIMAL(12,2) NOT NULL DEFAULT 0,
                selling_price DECIMAL(12,2) NOT NULL DEFAULT 0,
                minimum_stock INT UNSIGNED NOT NULL DEFAULT 0,
                description VARCHAR(255) NULL,
                status ENUM('ACTIVE','INACTIVE') NOT NULL DEFAULT 'ACTIVE',
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP NULL DEFAULT NULL,
                UNIQUE KEY uq_medicines_code (code),
                INDEX idx_medicines_name (name),
                INDEX idx_medicines_category_id (category_id),
                CONSTRAINT fk_medicines_category FOREIGN KEY (category_id) REFERENCES medicine_categories(id) ON DELETE SET NULL,
                CONSTRAINT fk_medicines_unit FOREIGN KEY (unit_id) REFERENCES medicine_units(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS medicines;');
    }
};
