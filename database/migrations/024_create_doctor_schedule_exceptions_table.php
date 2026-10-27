<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE doctor_schedule_exceptions (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                doctor_id BIGINT UNSIGNED NOT NULL,
                exception_date DATE NOT NULL,
                type ENUM('UNAVAILABLE','EXTRA') NOT NULL DEFAULT 'UNAVAILABLE',
                reason VARCHAR(255) NULL,
                start_time TIME NULL,
                end_time TIME NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_doctor_schedule_exceptions_doctor_id (doctor_id),
                INDEX idx_doctor_schedule_exceptions_date (exception_date),
                CONSTRAINT fk_doctor_schedule_exceptions_doctor FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS doctor_schedule_exceptions;');
    }
};
