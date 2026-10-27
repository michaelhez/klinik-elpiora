<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class QueueSequence extends Model
{
    protected static string $table = 'queue_sequences';
    protected static bool $timestamps = false;
    protected static array $fillable = ['polyclinic_id', 'queue_date', 'last_number'];

    /**
     * Atomically increments and returns the next queue sequence number for a polyclinic/date.
     */
    public static function nextNumber(int|string $polyclinicId, string $date): int
    {
        $pdo = Database::connection();

        $insert = $pdo->prepare(
            'INSERT INTO queue_sequences (polyclinic_id, queue_date, last_number) VALUES (:polyclinic_id, :queue_date, 1)
             ON DUPLICATE KEY UPDATE last_number = last_number + 1'
        );
        $insert->execute(['polyclinic_id' => $polyclinicId, 'queue_date' => $date]);

        $select = $pdo->prepare('SELECT last_number FROM queue_sequences WHERE polyclinic_id = :polyclinic_id AND queue_date = :queue_date');
        $select->execute(['polyclinic_id' => $polyclinicId, 'queue_date' => $date]);

        return (int) $select->fetchColumn();
    }
}
