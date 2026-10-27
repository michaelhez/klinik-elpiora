<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class Patient extends Model
{
    protected static string $table = 'patients';
    protected static bool $softDeletes = true;
    protected static array $fillable = [
        'medical_record_number', 'name', 'nik', 'gender', 'blood_type', 'birth_place',
        'birth_date', 'phone', 'email', 'address', 'city', 'photo', 'marital_status',
        'occupation', 'status',
    ];

    public static function nextMedicalRecordNumber(): string
    {
        $year = date('Y');
        $prefix = "RM-{$year}-";

        $sql = 'SELECT medical_record_number FROM patients WHERE medical_record_number LIKE :prefix ORDER BY id DESC LIMIT 1';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['prefix' => $prefix . '%']);
        $last = $stmt->fetchColumn();

        $sequence = $last ? ((int) substr((string) $last, -6)) + 1 : 1;

        return $prefix . str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }

    public static function findByNik(string $nik): ?array
    {
        return static::findBy('nik', $nik);
    }

    public static function search(string $term, int $limit = 10): array
    {
        $sql = 'SELECT id, medical_record_number, name, nik, phone, gender, birth_date FROM patients
                WHERE deleted_at IS NULL AND (name LIKE :term1 OR medical_record_number LIKE :term2 OR nik LIKE :term3 OR phone LIKE :term4)
                ORDER BY name ASC LIMIT :limit';

        $stmt = Database::connection()->prepare($sql);
        $needle = "%{$term}%";
        $stmt->bindValue(':term1', $needle);
        $stmt->bindValue(':term2', $needle);
        $stmt->bindValue(':term3', $needle);
        $stmt->bindValue(':term4', $needle);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
