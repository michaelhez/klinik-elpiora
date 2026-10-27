<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE medical_record_histories (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                medical_record_id BIGINT UNSIGNED NOT NULL,
                action_type ENUM('FINALIZE','AMEND') NOT NULL,
                section VARCHAR(50) NULL,
                old_data JSON NULL,
                new_data JSON NULL,
                reason VARCHAR(255) NULL,
                changed_by BIGINT UNSIGNED NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_medical_record_histories_record (medical_record_id),
                CONSTRAINT fk_medical_record_histories_record FOREIGN KEY (medical_record_id) REFERENCES medical_records(id) ON DELETE CASCADE,
                CONSTRAINT fk_medical_record_histories_user FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS medical_record_histories;');
    }
};
