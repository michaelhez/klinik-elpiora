<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE medical_record_diagnoses (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                medical_record_id BIGINT UNSIGNED NOT NULL,
                icd10_code_id BIGINT UNSIGNED NOT NULL,
                type ENUM('PRIMARY','SECONDARY') NOT NULL DEFAULT 'PRIMARY',
                notes VARCHAR(255) NULL,
                created_by BIGINT UNSIGNED NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_medical_record_diagnoses_record (medical_record_id),
                CONSTRAINT fk_medical_record_diagnoses_record FOREIGN KEY (medical_record_id) REFERENCES medical_records(id) ON DELETE CASCADE,
                CONSTRAINT fk_medical_record_diagnoses_icd10 FOREIGN KEY (icd10_code_id) REFERENCES icd10_codes(id) ON DELETE RESTRICT,
                CONSTRAINT fk_medical_record_diagnoses_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS medical_record_diagnoses;');
    }
};
