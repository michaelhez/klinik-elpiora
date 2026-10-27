<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE lab_parameters (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                lab_test_id BIGINT UNSIGNED NOT NULL,
                name VARCHAR(150) NOT NULL,
                unit VARCHAR(30) NULL,
                value_type ENUM('NUMERIC','TEXT') NOT NULL DEFAULT 'NUMERIC',
                reference_min DECIMAL(10,2) NULL,
                reference_max DECIMAL(10,2) NULL,
                reference_text VARCHAR(150) NULL,
                sort_order INT UNSIGNED NOT NULL DEFAULT 0,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_lab_parameters_lab_test_id (lab_test_id),
                CONSTRAINT fk_lab_parameters_lab_test FOREIGN KEY (lab_test_id) REFERENCES lab_tests(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS lab_parameters;');
    }
};
