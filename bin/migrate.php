<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Core\Migrator;

$dotenvPath = dirname(__DIR__);
if (file_exists($dotenvPath . '/.env')) {
    \Dotenv\Dotenv::createImmutable($dotenvPath)->load();
}

$command = $argv[1] ?? 'run';

$migrator = new Migrator();

if ($command === 'rollback') {
    $reverted = $migrator->rollback();
    if ($reverted === []) {
        echo "Nothing to rollback.\n";
    } else {
        echo "Rolled back:\n";
        foreach ($reverted as $file) {
            echo "  - {$file}\n";
        }
    }
    exit(0);
}

$applied = $migrator->run();

if ($applied === []) {
    echo "Nothing to migrate. Database is up to date.\n";
} else {
    echo "Migrated:\n";
    foreach ($applied as $file) {
        echo "  - {$file}\n";
    }
}
