<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE prescriptions (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                prescription_number VARCHAR(30) NOT NULL,
                medical_record_id BIGINT UNSIGNED NOT NULL,
                patient_id BIGINT UNSIGNED NOT NULL,
                doctor_id BIGINT UNSIGNED NOT NULL,
                branch_id BIGINT UNSIGNED NOT NULL,
                status ENUM('DRAFT','WAITING_PHARMACY','PROCESSING','READY','DISPENSED','CANCELLED') NOT NULL DEFAULT 'WAITING_PHARMACY',
                notes VARCHAR(255) NULL,
                created_by BIGINT UNSIGNED NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_prescriptions_number (prescription_number),
                INDEX idx_prescriptions_medical_record_id (medical_record_id),
                INDEX idx_prescriptions_patient_id (patient_id),
                INDEX idx_prescriptions_status (status),
                CONSTRAINT fk_prescriptions_medical_record FOREIGN KEY (medical_record_id) REFERENCES medical_records(id) ON DELETE RESTRICT,
                CONSTRAINT fk_prescriptions_patient FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE RESTRICT,
                CONSTRAINT fk_prescriptions_doctor FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE RESTRICT,
                CONSTRAINT fk_prescriptions_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE RESTRICT,
                CONSTRAINT fk_prescriptions_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS prescriptions;');
    }
};
