<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Database\Seeders\DatabaseSeeder;

$dotenvPath = dirname(__DIR__);
if (file_exists($dotenvPath . '/.env')) {
    \Dotenv\Dotenv::createImmutable($dotenvPath)->load();
}

(new DatabaseSeeder())->run();

echo PHP_EOL . "Seeding completed." . PHP_EOL;
