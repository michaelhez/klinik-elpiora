<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Session;
use App\Exceptions\ValidationException;
use App\Models\Invoice;
use App\Models\Payment;

final class PaymentService
{
    public function __construct(
        private readonly BillingService $billingService = new BillingService(),
        private readonly AuditService $auditService = new AuditService(),
    ) {
    }

    public function pay(int|string $invoiceId, string $method, float $amount, ?string $referenceNumber = null, ?string $notes = null): array
    {
        $invoice = $this->billingService->find($invoiceId);

        if ($invoice['status'] === 'CANCELLED') {
            throw new ValidationException(['status' => ['Tagihan ini telah dibatalkan.']]);
        }

        if ($invoice['status'] === 'PAID') {
            throw new ValidationException(['status' => ['Tagihan ini sudah lunas.']]);
        }

        if ($amount <= 0) {
            throw new ValidationException(['amount' => ['Jumlah pembayaran harus lebih dari 0.']]);
        }

        $remaining = (float) $invoice['total'] - (float) $invoice['paid_amount'];

        if ($amount > $remaining + 0.01) {
            throw new ValidationException(['amount' => ["Jumlah pembayaran melebihi sisa tagihan (Rp " . number_format($remaining, 0, ',', '.') . ').']]);
        }

        $userId = Session::get('auth_user')['id'] ?? null;
        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $paymentId = Payment::create([
                'payment_number' => Payment::nextPaymentNumber(),
                'invoice_id' => $invoiceId,
                'type' => 'PAYMENT',
                'method' => $method,
                'amount' => $amount,
                'reference_number' => $referenceNumber,
                'notes' => $notes,
                'paid_by' => $userId,
                'paid_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $newPaid = (float) $invoice['paid_amount'] + $amount;
            $newStatus = $newPaid >= (float) $invoice['total'] ? 'PAID' : 'PARTIAL';

            Invoice::update($invoiceId, ['paid_amount' => $newPaid, 'status' => $newStatus]);

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        $this->auditService->log('PAYMENT', 'payment', 'payments', $paymentId, null, ['invoice_id' => $invoiceId, 'method' => $method, 'amount' => $amount]);

        return $this->billingService->find($invoiceId);
    }

    public function refund(int|string $invoiceId, float $amount, string $notes): array
    {
        $invoice = $this->billingService->find($invoiceId);

        if ($amount <= 0) {
            throw new ValidationException(['amount' => ['Jumlah refund harus lebih dari 0.']]);
        }

        if ($amount > (float) $invoice['paid_amount'] + 0.01) {
            throw new ValidationException(['amount' => ['Jumlah refund melebihi jumlah yang sudah dibayarkan.']]);
        }

        $userId = Session::get('auth_user')['id'] ?? null;
        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $paymentId = Payment::create([
                'payment_number' => Payment::nextPaymentNumber(),
                'invoice_id' => $invoiceId,
                'type' => 'REFUND',
                'method' => 'CASH',
                'amount' => $amount,
                'notes' => $notes,
                'paid_by' => $userId,
                'paid_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $newPaid = (float) $invoice['paid_amount'] - $amount;
            $newStatus = $newPaid <= 0 ? 'UNPAID' : ($newPaid >= (float) $invoice['total'] ? 'PAID' : 'PARTIAL');

            Invoice::update($invoiceId, ['paid_amount' => max(0, $newPaid), 'status' => $newStatus]);

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        $this->auditService->log('REFUND', 'payment', 'payments', $paymentId, null, ['invoice_id' => $invoiceId, 'amount' => $amount, 'notes' => $notes]);

        return $this->billingService->find($invoiceId);
    }
}
