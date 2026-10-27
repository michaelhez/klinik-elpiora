<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE queue_sequences (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                polyclinic_id BIGINT UNSIGNED NOT NULL,
                queue_date DATE NOT NULL,
                last_number INT UNSIGNED NOT NULL DEFAULT 0,
                UNIQUE KEY uq_queue_sequences (polyclinic_id, queue_date),
                CONSTRAINT fk_queue_sequences_polyclinic FOREIGN KEY (polyclinic_id) REFERENCES polyclinics(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS queue_sequences;');
    }
};
