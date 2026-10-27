<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class LoginAttempt extends Model
{
    protected static string $table = 'login_attempts';
    protected static bool $timestamps = false;
    protected static array $fillable = ['email', 'ip_address', 'is_successful', 'created_at'];

    public static function recentFailedCount(string $email, string $ip, int $minutes): int
    {
        $sql = 'SELECT COUNT(*) FROM login_attempts
                WHERE email = :email AND ip_address = :ip AND is_successful = 0
                AND created_at >= (NOW() - INTERVAL :minutes MINUTE)';

        $stmt = Database::connection()->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':ip', $ip);
        $stmt->bindValue(':minutes', $minutes, \PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public static function record(string $email, string $ip, bool $successful): void
    {
        static::create([
            'email' => $email,
            'ip_address' => $ip,
            'is_successful' => $successful,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
