<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE dispensings (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                prescription_id BIGINT UNSIGNED NOT NULL,
                dispensed_by BIGINT UNSIGNED NULL,
                dispensed_at TIMESTAMP NULL DEFAULT NULL,
                notes VARCHAR(255) NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_dispensings_prescription (prescription_id),
                CONSTRAINT fk_dispensings_prescription FOREIGN KEY (prescription_id) REFERENCES prescriptions(id) ON DELETE CASCADE,
                CONSTRAINT fk_dispensings_user FOREIGN KEY (dispensed_by) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS dispensings;');
    }
};
