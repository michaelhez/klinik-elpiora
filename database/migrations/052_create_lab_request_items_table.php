<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE lab_request_items (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                lab_request_id BIGINT UNSIGNED NOT NULL,
                lab_test_id BIGINT UNSIGNED NOT NULL,
                price DECIMAL(12,2) NOT NULL DEFAULT 0,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_lab_request_items_request_id (lab_request_id),
                CONSTRAINT fk_lab_request_items_request FOREIGN KEY (lab_request_id) REFERENCES lab_requests(id) ON DELETE CASCADE,
                CONSTRAINT fk_lab_request_items_test FOREIGN KEY (lab_test_id) REFERENCES lab_tests(id) ON DELETE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS lab_request_items;');
    }
};
