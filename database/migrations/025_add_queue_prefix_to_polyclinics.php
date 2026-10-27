<?php

declare(strict_types=1);

use App\Core\Migration;

return new class extends Migration {
    public function up(): void
    {
        $this->exec('ALTER TABLE polyclinics ADD COLUMN queue_prefix VARCHAR(5) NOT NULL DEFAULT \'A\' AFTER icon;');
    }

    public function down(): void
    {
        $this->exec('ALTER TABLE polyclinics DROP COLUMN queue_prefix;');
    }
};
