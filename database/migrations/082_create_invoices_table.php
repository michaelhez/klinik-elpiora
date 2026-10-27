<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE invoices (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                invoice_number VARCHAR(30) NOT NULL,
                registration_id BIGINT UNSIGNED NOT NULL,
                medical_record_id BIGINT UNSIGNED NULL,
                patient_id BIGINT UNSIGNED NOT NULL,
                payment_type ENUM('SELF_PAY','BPJS','INSURANCE','CORPORATE') NOT NULL DEFAULT 'SELF_PAY',
                insurance_id BIGINT UNSIGNED NULL,
                corporate_id BIGINT UNSIGNED NULL,
                subtotal DECIMAL(14,2) NOT NULL DEFAULT 0,
                discount DECIMAL(14,2) NOT NULL DEFAULT 0,
                total DECIMAL(14,2) NOT NULL DEFAULT 0,
                paid_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
                status ENUM('UNPAID','PARTIAL','PAID','CANCELLED') NOT NULL DEFAULT 'UNPAID',
                notes VARCHAR(255) NULL,
                created_by BIGINT UNSIGNED NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_invoices_number (invoice_number),
                UNIQUE KEY uq_invoices_registration_id (registration_id),
                INDEX idx_invoices_patient_id (patient_id),
                INDEX idx_invoices_status (status),
                CONSTRAINT fk_invoices_registration FOREIGN KEY (registration_id) REFERENCES registrations(id) ON DELETE RESTRICT,
                CONSTRAINT fk_invoices_medical_record FOREIGN KEY (medical_record_id) REFERENCES medical_records(id) ON DELETE SET NULL,
                CONSTRAINT fk_invoices_patient FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE RESTRICT,
                CONSTRAINT fk_invoices_insurance FOREIGN KEY (insurance_id) REFERENCES insurances(id) ON DELETE SET NULL,
                CONSTRAINT fk_invoices_corporate FOREIGN KEY (corporate_id) REFERENCES corporates(id) ON DELETE SET NULL,
                CONSTRAINT fk_invoices_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS invoices;');
    }
};
