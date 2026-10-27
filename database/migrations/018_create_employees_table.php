<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE employees (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NULL,
                branch_id BIGINT UNSIGNED NOT NULL,
                department_id BIGINT UNSIGNED NULL,
                position_id BIGINT UNSIGNED NULL,
                employee_number VARCHAR(30) NOT NULL,
                name VARCHAR(150) NOT NULL,
                gender ENUM('MALE','FEMALE') NOT NULL,
                nik VARCHAR(20) NULL,
                phone VARCHAR(30) NULL,
                email VARCHAR(150) NULL,
                address VARCHAR(255) NULL,
                birth_place VARCHAR(100) NULL,
                birth_date DATE NULL,
                join_date DATE NULL,
                photo VARCHAR(255) NULL,
                status ENUM('ACTIVE','INACTIVE','RESIGNED') NOT NULL DEFAULT 'ACTIVE',
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP NULL DEFAULT NULL,
                UNIQUE KEY uq_employees_number (employee_number),
                UNIQUE KEY uq_employees_nik (nik),
                UNIQUE KEY uq_employees_user_id (user_id),
                INDEX idx_employees_branch_id (branch_id),
                INDEX idx_employees_department_id (department_id),
                INDEX idx_employees_position_id (position_id),
                INDEX idx_employees_status (status),
                INDEX idx_employees_name (name),
                CONSTRAINT fk_employees_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE RESTRICT,
                CONSTRAINT fk_employees_department FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
                CONSTRAINT fk_employees_position FOREIGN KEY (position_id) REFERENCES positions(id) ON DELETE SET NULL,
                CONSTRAINT fk_employees_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS employees;');
    }
};
