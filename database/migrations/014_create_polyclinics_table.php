<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE polyclinics (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                branch_id BIGINT UNSIGNED NOT NULL,
                name VARCHAR(150) NOT NULL,
                code VARCHAR(30) NOT NULL,
                description VARCHAR(255) NULL,
                icon VARCHAR(50) NULL,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP NULL DEFAULT NULL,
                UNIQUE KEY uq_polyclinics_code (code),
                INDEX idx_polyclinics_branch_id (branch_id),
                CONSTRAINT fk_polyclinics_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS polyclinics;');
    }
};
