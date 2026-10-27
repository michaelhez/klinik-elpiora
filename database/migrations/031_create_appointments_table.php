<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE appointments (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                appointment_number VARCHAR(30) NOT NULL,
                patient_id BIGINT UNSIGNED NOT NULL,
                doctor_id BIGINT UNSIGNED NOT NULL,
                polyclinic_id BIGINT UNSIGNED NOT NULL,
                appointment_date DATE NOT NULL,
                appointment_time TIME NOT NULL,
                status ENUM('BOOKED','CONFIRMED','ARRIVED','IN_SERVICE','COMPLETED','CANCELLED','NO_SHOW') NOT NULL DEFAULT 'BOOKED',
                notes VARCHAR(255) NULL,
                created_by BIGINT UNSIGNED NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_appointments_number (appointment_number),
                INDEX idx_appointments_patient_id (patient_id),
                INDEX idx_appointments_doctor_id (doctor_id),
                INDEX idx_appointments_date (appointment_date),
                INDEX idx_appointments_status (status),
                CONSTRAINT fk_appointments_patient FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE RESTRICT,
                CONSTRAINT fk_appointments_doctor FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE RESTRICT,
                CONSTRAINT fk_appointments_polyclinic FOREIGN KEY (polyclinic_id) REFERENCES polyclinics(id) ON DELETE RESTRICT,
                CONSTRAINT fk_appointments_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS appointments;');
    }
};
