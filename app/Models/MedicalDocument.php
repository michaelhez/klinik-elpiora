<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class MedicalDocument extends Model
{
    protected static string $table = 'medical_documents';
    protected static bool $timestamps = false;
    protected static array $fillable = ['document_number', 'medical_record_id', 'patient_id', 'type', 'title', 'content', 'issued_by', 'issued_at', 'created_at'];

    public static function nextDocumentNumber(): string
    {
        $year = date('Y');
        $prefix = "DOC-{$year}-";

        $sql = 'SELECT document_number FROM medical_documents WHERE document_number LIKE :prefix ORDER BY id DESC LIMIT 1';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['prefix' => $prefix . '%']);
        $last = $stmt->fetchColumn();

        $sequence = $last ? ((int) substr((string) $last, -6)) + 1 : 1;

        return $prefix . str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }

    public static function forMedicalRecord(int|string $medicalRecordId): array
    {
        $sql = 'SELECT * FROM medical_documents WHERE medical_record_id = :id ORDER BY id DESC';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['id' => $medicalRecordId]);
        return $stmt->fetchAll();
    }

    public static function findWithRelations(int|string $id): ?array
    {
        $sql = "SELECT doc.*, p.name AS patient_name, p.medical_record_number, p.nik, p.birth_date, p.gender, p.address,
                       mr.record_number, e.name AS doctor_name, u.name AS issued_by_name
                FROM medical_documents doc
                INNER JOIN patients p ON p.id = doc.patient_id
                INNER JOIN medical_records mr ON mr.id = doc.medical_record_id
                INNER JOIN doctors d ON d.id = mr.doctor_id
                INNER JOIN employees e ON e.id = d.employee_id
                LEFT JOIN users u ON u.id = doc.issued_by
                WHERE doc.id = :id";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();

        return $result ?: null;
    }
}
