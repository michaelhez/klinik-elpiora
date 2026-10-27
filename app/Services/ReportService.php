<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

final class ReportService
{
    public function __construct(private readonly InventoryService $inventoryService = new InventoryService())
    {
    }

    /**
     * Revenue collected (payments minus refunds) within a date range, broken down by day,
     * payment method, and payment type.
     */
    public function revenueReport(string $from, string $to): array
    {
        $pdo = Database::connection();
        $bindings = ['from' => $from, 'to' => $to . ' 23:59:59'];

        $dailyStmt = $pdo->prepare(
            "SELECT DATE(pay.paid_at) AS date,
                    SUM(CASE WHEN pay.type = 'PAYMENT' THEN pay.amount ELSE -pay.amount END) AS total
             FROM payments pay
             WHERE pay.paid_at BETWEEN :from AND :to
             GROUP BY DATE(pay.paid_at)
             ORDER BY date ASC"
        );
        $dailyStmt->execute($bindings);
        $daily = $dailyStmt->fetchAll();

        $methodStmt = $pdo->prepare(
            "SELECT pay.method,
                    SUM(CASE WHEN pay.type = 'PAYMENT' THEN pay.amount ELSE -pay.amount END) AS total
             FROM payments pay
             WHERE pay.paid_at BETWEEN :from AND :to
             GROUP BY pay.method
             ORDER BY total DESC"
        );
        $methodStmt->execute($bindings);
        $byMethod = $methodStmt->fetchAll();

        $paymentTypeStmt = $pdo->prepare(
            "SELECT inv.payment_type,
                    SUM(CASE WHEN pay.type = 'PAYMENT' THEN pay.amount ELSE -pay.amount END) AS total
             FROM payments pay
             INNER JOIN invoices inv ON inv.id = pay.invoice_id
             WHERE pay.paid_at BETWEEN :from AND :to
             GROUP BY inv.payment_type
             ORDER BY total DESC"
        );
        $paymentTypeStmt->execute($bindings);
        $byPaymentType = $paymentTypeStmt->fetchAll();

        $grandTotal = array_reduce($daily, fn (float $carry, array $row) => $carry + (float) $row['total'], 0.0);

        $invoiceCountStmt = $pdo->prepare(
            "SELECT COUNT(*) FROM invoices WHERE created_at BETWEEN :from AND :to AND status != 'CANCELLED'"
        );
        $invoiceCountStmt->execute($bindings);

        return [
            'daily' => $daily,
            'by_method' => $byMethod,
            'by_payment_type' => $byPaymentType,
            'grand_total' => $grandTotal,
            'invoice_count' => (int) $invoiceCountStmt->fetchColumn(),
        ];
    }

    /**
     * Patient visit counts within a date range, broken down by day and by polyclinic.
     */
    public function patientVisitReport(string $from, string $to): array
    {
        $pdo = Database::connection();
        $bindings = ['from' => $from, 'to' => $to . ' 23:59:59'];

        $dailyStmt = $pdo->prepare(
            "SELECT DATE(r.created_at) AS date, COUNT(*) AS total
             FROM registrations r
             WHERE r.created_at BETWEEN :from AND :to AND r.status != 'CANCELLED'
             GROUP BY DATE(r.created_at)
             ORDER BY date ASC"
        );
        $dailyStmt->execute($bindings);

        $polyStmt = $pdo->prepare(
            "SELECT poly.name AS polyclinic_name, COUNT(*) AS total
             FROM registrations r
             INNER JOIN polyclinics poly ON poly.id = r.polyclinic_id
             WHERE r.created_at BETWEEN :from AND :to AND r.status != 'CANCELLED'
             GROUP BY poly.id, poly.name
             ORDER BY total DESC"
        );
        $polyStmt->execute($bindings);

        $totalStmt = $pdo->prepare(
            "SELECT COUNT(*) FROM registrations WHERE created_at BETWEEN :from AND :to AND status != 'CANCELLED'"
        );
        $totalStmt->execute($bindings);

        return [
            'daily' => $dailyStmt->fetchAll(),
            'by_polyclinic' => $polyStmt->fetchAll(),
            'total' => (int) $totalStmt->fetchColumn(),
        ];
    }

    /**
     * Top dispensed medicines by quantity within a date range.
     */
    public function medicineDispensingReport(string $from, string $to, int $limit = 15): array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            "SELECT m.name AS medicine_name, m.code AS medicine_code,
                    SUM(di.quantity_taken) AS total_quantity,
                    COUNT(DISTINCT d.id) AS dispensing_count
             FROM dispensing_items di
             INNER JOIN dispensings d ON d.id = di.dispensing_id
             INNER JOIN medicine_batches b ON b.id = di.medicine_batch_id
             INNER JOIN medicines m ON m.id = b.medicine_id
             WHERE d.dispensed_at BETWEEN :from AND :to
             GROUP BY m.id, m.name, m.code
             ORDER BY total_quantity DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':from', $from);
        $stmt->bindValue(':to', $to . ' 23:59:59');
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Finalized medical record counts per doctor within a date range.
     */
    public function doctorPerformanceReport(string $from, string $to): array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            "SELECT e.name AS doctor_name, poly.name AS polyclinic_name, COUNT(*) AS total_patients
             FROM medical_records mr
             INNER JOIN doctors doc ON doc.id = mr.doctor_id
             INNER JOIN employees e ON e.id = doc.employee_id
             INNER JOIN registrations r ON r.id = mr.registration_id
             INNER JOIN polyclinics poly ON poly.id = r.polyclinic_id
             WHERE mr.status IN ('FINALIZED', 'AMENDED') AND mr.finalized_at BETWEEN :from AND :to
             GROUP BY doc.id, e.name, poly.name
             ORDER BY total_patients DESC"
        );
        $stmt->execute(['from' => $from, 'to' => $to . ' 23:59:59']);

        return $stmt->fetchAll();
    }

    public function inventoryReport(): array
    {
        return [
            'low_stock' => $this->inventoryService->lowStock(),
            'expiring_soon' => $this->inventoryService->expiringSoon(90),
        ];
    }
}
