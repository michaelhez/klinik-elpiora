<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE payments (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                payment_number VARCHAR(30) NOT NULL,
                invoice_id BIGINT UNSIGNED NOT NULL,
                type ENUM('PAYMENT','REFUND') NOT NULL DEFAULT 'PAYMENT',
                method ENUM('CASH','TRANSFER','DEBIT','CREDIT','INSURANCE','CORPORATE') NOT NULL,
                amount DECIMAL(14,2) NOT NULL,
                reference_number VARCHAR(100) NULL,
                notes VARCHAR(255) NULL,
                paid_by BIGINT UNSIGNED NULL,
                paid_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_payments_number (payment_number),
                INDEX idx_payments_invoice_id (invoice_id),
                CONSTRAINT fk_payments_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE RESTRICT,
                CONSTRAINT fk_payments_user FOREIGN KEY (paid_by) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS payments;');
    }
};
