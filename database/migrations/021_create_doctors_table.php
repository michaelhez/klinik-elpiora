<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE doctors (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                employee_id BIGINT UNSIGNED NOT NULL,
                doctor_number VARCHAR(30) NOT NULL,
                str_number VARCHAR(50) NULL,
                sip_number VARCHAR(50) NULL,
                consultation_fee DECIMAL(12,2) NOT NULL DEFAULT 0,
                bio TEXT NULL,
                status ENUM('ACTIVE','INACTIVE') NOT NULL DEFAULT 'ACTIVE',
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP NULL DEFAULT NULL,
                UNIQUE KEY uq_doctors_number (doctor_number),
                UNIQUE KEY uq_doctors_employee_id (employee_id),
                INDEX idx_doctors_status (status),
                CONSTRAINT fk_doctors_employee FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS doctors;');
    }
};
