<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE rooms (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                branch_id BIGINT UNSIGNED NOT NULL,
                polyclinic_id BIGINT UNSIGNED NULL,
                name VARCHAR(150) NOT NULL,
                code VARCHAR(30) NOT NULL,
                floor VARCHAR(30) NULL,
                capacity INT UNSIGNED NOT NULL DEFAULT 1,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP NULL DEFAULT NULL,
                UNIQUE KEY uq_rooms_code (code),
                INDEX idx_rooms_branch_id (branch_id),
                INDEX idx_rooms_polyclinic_id (polyclinic_id),
                CONSTRAINT fk_rooms_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
                CONSTRAINT fk_rooms_polyclinic FOREIGN KEY (polyclinic_id) REFERENCES polyclinics(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS rooms;');
    }
};
