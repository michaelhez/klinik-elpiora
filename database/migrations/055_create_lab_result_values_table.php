<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec(<<<SQL
            CREATE TABLE lab_result_values (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                lab_result_id BIGINT UNSIGNED NOT NULL,
                lab_parameter_id BIGINT UNSIGNED NOT NULL,
                value VARCHAR(100) NOT NULL,
                flag ENUM('LOW','NORMAL','HIGH','CRITICAL') NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_lab_result_values (lab_result_id, lab_parameter_id),
                CONSTRAINT fk_lab_result_values_result FOREIGN KEY (lab_result_id) REFERENCES lab_results(id) ON DELETE CASCADE,
                CONSTRAINT fk_lab_result_values_parameter FOREIGN KEY (lab_parameter_id) REFERENCES lab_parameters(id) ON DELETE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);
    }

    public function down(): void
    {
        $this->exec('DROP TABLE IF EXISTS lab_result_values;');
    }
};
