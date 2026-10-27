<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE doctor_specializations (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                doctor_id BIGINT UNSIGNED NOT NULL,
                specialization_id BIGINT UNSIGNED NOT NULL,
                is_primary TINYINT(1) NOT NULL DEFAULT 0,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_doctor_specializations (doctor_id, specialization_id),
                CONSTRAINT fk_doctor_specializations_doctor FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
                CONSTRAINT fk_doctor_specializations_specialization FOREIGN KEY (specialization_id) REFERENCES specializations(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS doctor_specializations;');
    }
};
