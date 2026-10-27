<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE patients (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                medical_record_number VARCHAR(30) NOT NULL,
                name VARCHAR(150) NOT NULL,
                nik VARCHAR(20) NULL,
                gender ENUM('MALE','FEMALE') NOT NULL,
                blood_type ENUM('A','B','AB','O','UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
                birth_place VARCHAR(100) NULL,
                birth_date DATE NULL,
                phone VARCHAR(30) NULL,
                email VARCHAR(150) NULL,
                address VARCHAR(255) NULL,
                city VARCHAR(100) NULL,
                photo VARCHAR(255) NULL,
                marital_status ENUM('SINGLE','MARRIED','DIVORCED','WIDOWED') NULL,
                occupation VARCHAR(100) NULL,
                status ENUM('ACTIVE','INACTIVE') NOT NULL DEFAULT 'ACTIVE',
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP NULL DEFAULT NULL,
                UNIQUE KEY uq_patients_mrn (medical_record_number),
                UNIQUE KEY uq_patients_nik (nik),
                INDEX idx_patients_name (name),
                INDEX idx_patients_phone (phone),
                INDEX idx_patients_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS patients;');
    }
};
