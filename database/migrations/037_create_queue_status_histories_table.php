<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE queue_status_histories (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                queue_id BIGINT UNSIGNED NOT NULL,
                status VARCHAR(20) NOT NULL,
                notes VARCHAR(255) NULL,
                changed_by BIGINT UNSIGNED NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_queue_status_histories_queue_id (queue_id),
                CONSTRAINT fk_queue_status_histories_queue FOREIGN KEY (queue_id) REFERENCES queues(id) ON DELETE CASCADE,
                CONSTRAINT fk_queue_status_histories_user FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS queue_status_histories;');
    }
};
