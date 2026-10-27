<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE lab_requests (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                request_number VARCHAR(30) NOT NULL,
                medical_record_id BIGINT UNSIGNED NOT NULL,
                patient_id BIGINT UNSIGNED NOT NULL,
                doctor_id BIGINT UNSIGNED NOT NULL,
                status ENUM('REQUESTED','SAMPLE_COLLECTED','PROCESSING','COMPLETED','VALIDATED','CANCELLED') NOT NULL DEFAULT 'REQUESTED',
                notes VARCHAR(255) NULL,
                requested_by BIGINT UNSIGNED NULL,
                requested_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_lab_requests_number (request_number),
                INDEX idx_lab_requests_medical_record_id (medical_record_id),
                INDEX idx_lab_requests_patient_id (patient_id),
                INDEX idx_lab_requests_status (status),
                CONSTRAINT fk_lab_requests_medical_record FOREIGN KEY (medical_record_id) REFERENCES medical_records(id) ON DELETE RESTRICT,
                CONSTRAINT fk_lab_requests_patient FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE RESTRICT,
                CONSTRAINT fk_lab_requests_doctor FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE RESTRICT,
                CONSTRAINT fk_lab_requests_user FOREIGN KEY (requested_by) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS lab_requests;');
    }
};
