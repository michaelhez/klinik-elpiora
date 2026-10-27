<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE branches (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                clinic_id BIGINT UNSIGNED NOT NULL,
                name VARCHAR(150) NOT NULL,
                code VARCHAR(30) NOT NULL,
                address VARCHAR(255) NULL,
                phone VARCHAR(30) NULL,
                email VARCHAR(150) NULL,
                is_main TINYINT(1) NOT NULL DEFAULT 0,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP NULL DEFAULT NULL,
                UNIQUE KEY uq_branches_code (code),
                INDEX idx_branches_clinic_id (clinic_id),
                CONSTRAINT fk_branches_clinic FOREIGN KEY (clinic_id) REFERENCES clinics(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS branches;');
    }
};
