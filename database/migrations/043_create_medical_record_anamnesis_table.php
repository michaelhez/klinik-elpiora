<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE medical_record_anamnesis (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                medical_record_id BIGINT UNSIGNED NOT NULL,
                chief_complaint VARCHAR(255) NULL,
                present_illness_history TEXT NULL,
                past_medical_history TEXT NULL,
                notes TEXT NULL,
                created_by BIGINT UNSIGNED NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_medical_record_anamnesis_record (medical_record_id),
                CONSTRAINT fk_medical_record_anamnesis_record FOREIGN KEY (medical_record_id) REFERENCES medical_records(id) ON DELETE CASCADE,
                CONSTRAINT fk_medical_record_anamnesis_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS medical_record_anamnesis;');
    }
};
