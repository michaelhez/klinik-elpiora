<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class SystemSetting extends Model
{
    protected static string $table = 'system_settings';
    protected static array $fillable = ['setting_key', 'setting_value', 'type'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $stmt = Database::connection()->prepare('SELECT setting_value FROM system_settings WHERE setting_key = :key');
        $stmt->execute(['key' => $key]);
        $value = $stmt->fetchColumn();
        return $value === false ? $default : $value;
    }

    public static function set(string $key, mixed $value): void
    {
        $sql = 'INSERT INTO system_settings (setting_key, setting_value, created_at, updated_at)
                VALUES (:key, :value, NOW(), NOW())
                ON DUPLICATE KEY UPDATE setting_value = :value2, updated_at = NOW()';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['key' => $key, 'value' => $value, 'value2' => $value]);
    }
}
