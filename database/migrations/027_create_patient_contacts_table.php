<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE patient_contacts (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                patient_id BIGINT UNSIGNED NOT NULL,
                type ENUM('PHONE','MOBILE','EMAIL','WHATSAPP','OTHER') NOT NULL,
                value VARCHAR(150) NOT NULL,
                label VARCHAR(50) NULL,
                is_primary TINYINT(1) NOT NULL DEFAULT 0,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_patient_contacts_patient_id (patient_id),
                CONSTRAINT fk_patient_contacts_patient FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS patient_contacts;');
    }
};
