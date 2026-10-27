<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE lab_samples (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                lab_request_id BIGINT UNSIGNED NOT NULL,
                sample_number VARCHAR(30) NOT NULL,
                sample_type VARCHAR(50) NULL,
                collected_at TIMESTAMP NULL DEFAULT NULL,
                collected_by BIGINT UNSIGNED NULL,
                notes VARCHAR(255) NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_lab_samples_number (sample_number),
                INDEX idx_lab_samples_request_id (lab_request_id),
                CONSTRAINT fk_lab_samples_request FOREIGN KEY (lab_request_id) REFERENCES lab_requests(id) ON DELETE CASCADE,
                CONSTRAINT fk_lab_samples_user FOREIGN KEY (collected_by) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS lab_samples;');
    }
};
