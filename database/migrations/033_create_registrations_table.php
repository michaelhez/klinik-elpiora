<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE registrations (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                registration_number VARCHAR(30) NOT NULL,
                patient_id BIGINT UNSIGNED NOT NULL,
                doctor_id BIGINT UNSIGNED NULL,
                polyclinic_id BIGINT UNSIGNED NOT NULL,
                room_id BIGINT UNSIGNED NULL,
                appointment_id BIGINT UNSIGNED NULL,
                queue_id BIGINT UNSIGNED NULL,
                payment_type ENUM('SELF_PAY','BPJS','INSURANCE','CORPORATE') NOT NULL DEFAULT 'SELF_PAY',
                status ENUM('REGISTERED','CANCELLED') NOT NULL DEFAULT 'REGISTERED',
                notes VARCHAR(255) NULL,
                registered_by BIGINT UNSIGNED NULL,
                registered_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_registrations_number (registration_number),
                INDEX idx_registrations_patient_id (patient_id),
                INDEX idx_registrations_doctor_id (doctor_id),
                INDEX idx_registrations_status (status),
                INDEX idx_registrations_registered_at (registered_at),
                CONSTRAINT fk_registrations_patient FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE RESTRICT,
                CONSTRAINT fk_registrations_doctor FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE SET NULL,
                CONSTRAINT fk_registrations_polyclinic FOREIGN KEY (polyclinic_id) REFERENCES polyclinics(id) ON DELETE RESTRICT,
                CONSTRAINT fk_registrations_room FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE SET NULL,
                CONSTRAINT fk_registrations_appointment FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE SET NULL,
                CONSTRAINT fk_registrations_user FOREIGN KEY (registered_by) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS registrations;');
    }
};
