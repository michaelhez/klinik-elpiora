<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE medical_documents (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                document_number VARCHAR(30) NOT NULL,
                medical_record_id BIGINT UNSIGNED NOT NULL,
                patient_id BIGINT UNSIGNED NOT NULL,
                type ENUM('SICK_NOTE','REFERRAL_LETTER','MEDICAL_CERTIFICATE') NOT NULL,
                title VARCHAR(150) NOT NULL,
                content JSON NOT NULL,
                issued_by BIGINT UNSIGNED NULL,
                issued_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_medical_documents_number (document_number),
                INDEX idx_medical_documents_medical_record_id (medical_record_id),
                INDEX idx_medical_documents_patient_id (patient_id),
                CONSTRAINT fk_medical_documents_record FOREIGN KEY (medical_record_id) REFERENCES medical_records(id) ON DELETE RESTRICT,
                CONSTRAINT fk_medical_documents_patient FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE RESTRICT,
                CONSTRAINT fk_medical_documents_user FOREIGN KEY (issued_by) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS medical_documents;');
    }
};
