<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Notification extends Model
{
    protected static string $table = 'notifications';
    protected static array $fillable = ['user_id', 'type', 'title', 'message', 'data', 'is_read', 'read_at'];

    public static function unreadForUser(int|string $userId, int $limit = 10): array
    {
        return static::query()
            ->where('user_id', '=', $userId)
            ->where('is_read', '=', 0)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get();
    }

    public static function countUnread(int|string $userId): int
    {
        return static::query()
            ->where('user_id', '=', $userId)
            ->where('is_read', '=', 0)
            ->count();
    }
}
