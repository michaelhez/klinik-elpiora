<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE medical_records (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                record_number VARCHAR(30) NOT NULL,
                registration_id BIGINT UNSIGNED NOT NULL,
                patient_id BIGINT UNSIGNED NOT NULL,
                doctor_id BIGINT UNSIGNED NOT NULL,
                status ENUM('DRAFT','IN_PROGRESS','FINALIZED','AMENDED') NOT NULL DEFAULT 'DRAFT',
                chief_complaint VARCHAR(255) NULL,
                finalized_at TIMESTAMP NULL DEFAULT NULL,
                finalized_by BIGINT UNSIGNED NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_medical_records_number (record_number),
                UNIQUE KEY uq_medical_records_registration (registration_id),
                INDEX idx_medical_records_patient_id (patient_id),
                INDEX idx_medical_records_doctor_id (doctor_id),
                INDEX idx_medical_records_status (status),
                CONSTRAINT fk_medical_records_registration FOREIGN KEY (registration_id) REFERENCES registrations(id) ON DELETE RESTRICT,
                CONSTRAINT fk_medical_records_patient FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE RESTRICT,
                CONSTRAINT fk_medical_records_doctor FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE RESTRICT,
                CONSTRAINT fk_medical_records_finalized_by FOREIGN KEY (finalized_by) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS medical_records;');
    }
};
