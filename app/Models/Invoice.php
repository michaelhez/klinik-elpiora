<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class Invoice extends Model
{
    protected static string $table = 'invoices';
    protected static array $fillable = [
        'invoice_number', 'registration_id', 'medical_record_id', 'patient_id', 'payment_type',
        'insurance_id', 'corporate_id', 'subtotal', 'discount', 'total', 'paid_amount', 'status',
        'notes', 'created_by',
    ];

    public static function nextInvoiceNumber(): string
    {
        $year = date('Y');
        $prefix = "INV-{$year}-";

        $sql = 'SELECT invoice_number FROM invoices WHERE invoice_number LIKE :prefix ORDER BY id DESC LIMIT 1';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['prefix' => $prefix . '%']);
        $last = $stmt->fetchColumn();

        $sequence = $last ? ((int) substr((string) $last, -6)) + 1 : 1;

        return $prefix . str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }

    public static function findWithRelations(int|string $id): ?array
    {
        $sql = "SELECT inv.*, p.name AS patient_name, p.medical_record_number,
                       r.registration_number, i.name AS insurance_name, c.name AS corporate_name
                FROM invoices inv
                INNER JOIN patients p ON p.id = inv.patient_id
                INNER JOIN registrations r ON r.id = inv.registration_id
                LEFT JOIN insurances i ON i.id = inv.insurance_id
                LEFT JOIN corporates c ON c.id = inv.corporate_id
                WHERE inv.id = :id";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    public static function forRegistration(int|string $registrationId): ?array
    {
        return static::query()->where('registration_id', '=', $registrationId)->first();
    }

    public static function list(array $filters = []): array
    {
        $conditions = ['1 = 1'];
        $bindings = [];

        if (!empty($filters['status'])) {
            $conditions[] = 'inv.status = :status';
            $bindings['status'] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $conditions[] = '(inv.invoice_number LIKE :search1 OR p.name LIKE :search2)';
            $bindings['search1'] = '%' . $filters['search'] . '%';
            $bindings['search2'] = '%' . $filters['search'] . '%';
        }

        $sql = 'SELECT inv.*, p.name AS patient_name FROM invoices inv
                INNER JOIN patients p ON p.id = inv.patient_id
                WHERE ' . implode(' AND ', $conditions) . '
                ORDER BY inv.created_at DESC LIMIT 100';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll();
    }
}
