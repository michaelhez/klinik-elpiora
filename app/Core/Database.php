<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection === null) {
            $config = config('database');

            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset']
            );

            try {
                self::$connection = new PDO($dsn, $config['username'], $config['password'], $config['options']);
            } catch (PDOException $e) {
                Logger::error('Database connection failed', ['message' => $e->getMessage()]);
                throw new \App\Exceptions\DatabaseException('Unable to connect to the database.');
            }
        }

        return self::$connection;
    }

    public static function beginTransaction(): bool
    {
        return self::connection()->beginTransaction();
    }

    public static function commit(): bool
    {
        return self::connection()->commit();
    }

    public static function rollBack(): bool
    {
        return self::connection()->inTransaction() ? self::connection()->rollBack() : false;
    }

    public static function inTransaction(): bool
    {
        return self::connection()->inTransaction();
    }
}
