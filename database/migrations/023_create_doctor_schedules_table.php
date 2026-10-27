<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE doctor_schedules (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                doctor_id BIGINT UNSIGNED NOT NULL,
                polyclinic_id BIGINT UNSIGNED NOT NULL,
                room_id BIGINT UNSIGNED NULL,
                day_of_week TINYINT UNSIGNED NOT NULL COMMENT '0=Minggu .. 6=Sabtu',
                start_time TIME NOT NULL,
                end_time TIME NOT NULL,
                quota INT UNSIGNED NULL,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_doctor_schedules_doctor_id (doctor_id),
                INDEX idx_doctor_schedules_polyclinic_id (polyclinic_id),
                INDEX idx_doctor_schedules_day (day_of_week),
                CONSTRAINT fk_doctor_schedules_doctor FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
                CONSTRAINT fk_doctor_schedules_polyclinic FOREIGN KEY (polyclinic_id) REFERENCES polyclinics(id) ON DELETE CASCADE,
                CONSTRAINT fk_doctor_schedules_room FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS doctor_schedules;');
    }
};
