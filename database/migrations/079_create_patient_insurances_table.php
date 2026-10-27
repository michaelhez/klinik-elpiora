<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE patient_insurances (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                patient_id BIGINT UNSIGNED NOT NULL,
                insurance_id BIGINT UNSIGNED NOT NULL,
                member_number VARCHAR(50) NOT NULL,
                valid_until DATE NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_patient_insurances_patient_id (patient_id),
                INDEX idx_patient_insurances_insurance_id (insurance_id),
                CONSTRAINT fk_patient_insurances_patient FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
                CONSTRAINT fk_patient_insurances_insurance FOREIGN KEY (insurance_id) REFERENCES insurances(id) ON DELETE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS patient_insurances;');
    }
};
