<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE prescription_compounds (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                prescription_id BIGINT UNSIGNED NOT NULL,
                compound_name VARCHAR(150) NOT NULL,
                dosage_instructions VARCHAR(255) NULL,
                quantity INT UNSIGNED NOT NULL DEFAULT 1,
                price DECIMAL(12,2) NOT NULL DEFAULT 0,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_prescription_compounds_prescription_id (prescription_id),
                CONSTRAINT fk_prescription_compounds_prescription FOREIGN KEY (prescription_id) REFERENCES prescriptions(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS prescription_compounds;');
    }
};
