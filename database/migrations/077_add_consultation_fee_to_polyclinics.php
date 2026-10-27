<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec('ALTER TABLE polyclinics ADD COLUMN consultation_fee DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER queue_prefix;');
    }

    public function down(): void
    {
        $this->exec('ALTER TABLE polyclinics DROP COLUMN consultation_fee;');
    }
};
