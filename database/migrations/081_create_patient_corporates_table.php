<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE patient_corporates (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                patient_id BIGINT UNSIGNED NOT NULL,
                corporate_id BIGINT UNSIGNED NOT NULL,
                employee_number VARCHAR(50) NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_patient_corporates_patient_id (patient_id),
                INDEX idx_patient_corporates_corporate_id (corporate_id),
                CONSTRAINT fk_patient_corporates_patient FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
                CONSTRAINT fk_patient_corporates_corporate FOREIGN KEY (corporate_id) REFERENCES corporates(id) ON DELETE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS patient_corporates;');
    }
};
