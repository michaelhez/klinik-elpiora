<?php

declare(strict_types=1);

namespace App\Core;

abstract class Migration
{
    protected function pdo(): \PDO
    {
        return Database::connection();
    }

    protected function exec(string $sql): void
    {
        $this->pdo()->exec($sql);
    }

    abstract public function up(): void;

    abstract public function down(): void;
}
