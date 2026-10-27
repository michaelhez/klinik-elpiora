<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

final class Migrator
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::connection();
        $this->ensureMigrationsTable();
    }

    private function ensureMigrationsTable(): void
    {
        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS migrations (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                batch INT UNSIGNED NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function run(): array
    {
        $files = $this->pendingMigrations();
        $applied = [];

        if ($files === []) {
            return $applied;
        }

        $batch = $this->nextBatch();

        foreach ($files as $file) {
            $migration = require $file;
            $migration->up();

            $stmt = $this->pdo->prepare('INSERT INTO migrations (migration, batch) VALUES (:migration, :batch)');
            $stmt->execute(['migration' => basename($file), 'batch' => $batch]);

            $applied[] = basename($file);
        }

        return $applied;
    }

    public function rollback(): array
    {
        $lastBatch = (int) $this->pdo->query('SELECT MAX(batch) FROM migrations')->fetchColumn();

        if ($lastBatch === 0) {
            return [];
        }

        $stmt = $this->pdo->prepare('SELECT migration FROM migrations WHERE batch = :batch ORDER BY id DESC');
        $stmt->execute(['batch' => $lastBatch]);
        $migrations = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $reverted = [];
        $dir = base_path('database/migrations');

        foreach ($migrations as $migrationFile) {
            $path = $dir . '/' . $migrationFile;
            if (file_exists($path)) {
                $migration = require $path;
                $migration->down();
            }

            $del = $this->pdo->prepare('DELETE FROM migrations WHERE migration = :migration');
            $del->execute(['migration' => $migrationFile]);
            $reverted[] = $migrationFile;
        }

        return $reverted;
    }

    private function nextBatch(): int
    {
        $result = $this->pdo->query('SELECT MAX(batch) FROM migrations')->fetchColumn();
        return ((int) $result) + 1;
    }

    private function pendingMigrations(): array
    {
        $dir = base_path('database/migrations');
        $files = glob($dir . '/*.php') ?: [];
        sort($files);

        $applied = $this->pdo->query('SELECT migration FROM migrations')->fetchAll(PDO::FETCH_COLUMN);

        return array_values(array_filter($files, static fn ($file) => !in_array(basename($file), $applied, true)));
    }
}
