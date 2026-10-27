<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE medical_record_actions (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                medical_record_id BIGINT UNSIGNED NOT NULL,
                medical_action_id BIGINT UNSIGNED NOT NULL,
                quantity INT UNSIGNED NOT NULL DEFAULT 1,
                price DECIMAL(12,2) NOT NULL DEFAULT 0,
                notes VARCHAR(255) NULL,
                created_by BIGINT UNSIGNED NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_medical_record_actions_record (medical_record_id),
                CONSTRAINT fk_medical_record_actions_record FOREIGN KEY (medical_record_id) REFERENCES medical_records(id) ON DELETE CASCADE,
                CONSTRAINT fk_medical_record_actions_action FOREIGN KEY (medical_action_id) REFERENCES medical_actions(id) ON DELETE RESTRICT,
                CONSTRAINT fk_medical_record_actions_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS medical_record_actions;');
    }
};
