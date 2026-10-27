<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE medical_actions (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                category_id BIGINT UNSIGNED NULL,
                code VARCHAR(30) NOT NULL,
                name VARCHAR(150) NOT NULL,
                price DECIMAL(12,2) NOT NULL DEFAULT 0,
                duration INT UNSIGNED NULL COMMENT 'minutes',
                description VARCHAR(255) NULL,
                status ENUM('ACTIVE','INACTIVE') NOT NULL DEFAULT 'ACTIVE',
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP NULL DEFAULT NULL,
                UNIQUE KEY uq_medical_actions_code (code),
                INDEX idx_medical_actions_category_id (category_id),
                CONSTRAINT fk_medical_actions_category FOREIGN KEY (category_id) REFERENCES medical_action_categories(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS medical_actions;');
    }
};
