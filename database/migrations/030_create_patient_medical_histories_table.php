<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE patient_medical_histories (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                patient_id BIGINT UNSIGNED NOT NULL,
                condition_name VARCHAR(150) NOT NULL,
                diagnosed_date DATE NULL,
                status ENUM('ONGOING','RESOLVED') NOT NULL DEFAULT 'ONGOING',
                notes VARCHAR(255) NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_patient_medical_histories_patient_id (patient_id),
                CONSTRAINT fk_patient_medical_histories_patient FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS patient_medical_histories;');
    }
};
