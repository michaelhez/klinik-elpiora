<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE users (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                employee_id BIGINT UNSIGNED NULL,
                name VARCHAR(150) NOT NULL,
                email VARCHAR(150) NOT NULL,
                username VARCHAR(100) NULL,
                password VARCHAR(255) NOT NULL,
                avatar VARCHAR(255) NULL,
                phone VARCHAR(30) NULL,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                last_login_at TIMESTAMP NULL DEFAULT NULL,
                last_login_ip VARCHAR(45) NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP NULL DEFAULT NULL,
                UNIQUE KEY uq_users_email (email),
                UNIQUE KEY uq_users_username (username),
                INDEX idx_users_is_active (is_active),
                INDEX idx_users_deleted_at (deleted_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS users;');
    }
};
