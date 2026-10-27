<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE lab_results (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                lab_request_item_id BIGINT UNSIGNED NOT NULL,
                performed_by BIGINT UNSIGNED NULL,
                performed_at TIMESTAMP NULL DEFAULT NULL,
                validated_by BIGINT UNSIGNED NULL,
                validated_at TIMESTAMP NULL DEFAULT NULL,
                notes VARCHAR(255) NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_lab_results_item (lab_request_item_id),
                CONSTRAINT fk_lab_results_item FOREIGN KEY (lab_request_item_id) REFERENCES lab_request_items(id) ON DELETE CASCADE,
                CONSTRAINT fk_lab_results_performed_by FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE SET NULL,
                CONSTRAINT fk_lab_results_validated_by FOREIGN KEY (validated_by) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS lab_results;');
    }
};
