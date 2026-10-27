<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE lab_tests (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                code VARCHAR(30) NOT NULL,
                name VARCHAR(150) NOT NULL,
                category VARCHAR(100) NULL,
                sample_type VARCHAR(50) NULL,
                price DECIMAL(12,2) NOT NULL DEFAULT 0,
                description VARCHAR(255) NULL,
                status ENUM('ACTIVE','INACTIVE') NOT NULL DEFAULT 'ACTIVE',
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP NULL DEFAULT NULL,
                UNIQUE KEY uq_lab_tests_code (code),
                INDEX idx_lab_tests_name (name),
                INDEX idx_lab_tests_category (category)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS lab_tests;');
    }
};
