<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE queues (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                registration_id BIGINT UNSIGNED NOT NULL,
                polyclinic_id BIGINT UNSIGNED NOT NULL,
                queue_number VARCHAR(10) NOT NULL,
                queue_date DATE NOT NULL,
                status ENUM('WAITING','CALLED','IN_SERVICE','COMPLETED','SKIPPED','CANCELLED') NOT NULL DEFAULT 'WAITING',
                called_at TIMESTAMP NULL DEFAULT NULL,
                started_at TIMESTAMP NULL DEFAULT NULL,
                completed_at TIMESTAMP NULL DEFAULT NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_queues_registration_id (registration_id),
                INDEX idx_queues_polyclinic_date (polyclinic_id, queue_date),
                INDEX idx_queues_status (status),
                CONSTRAINT fk_queues_registration FOREIGN KEY (registration_id) REFERENCES registrations(id) ON DELETE CASCADE,
                CONSTRAINT fk_queues_polyclinic FOREIGN KEY (polyclinic_id) REFERENCES polyclinics(id) ON DELETE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS queues;');
    }
};
