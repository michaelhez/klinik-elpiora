<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Exceptions\ValidationException;
use App\Services\PaymentService;
use App\Validators\PaymentValidator;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $service = new PaymentService())
    {
    }

    public function pay(Request $request, string $invoiceId): never
    {
        $data = $this->validate($request, PaymentValidator::payRules());

        try {
            $invoice = $this->service->pay($invoiceId, $data['method'], (float) $data['amount'], $data['reference_number'] ?? null, $data['notes'] ?? null);
        } catch (ValidationException $e) {
            $this->fail(current($e->errors())[0] ?? 'Pembayaran tidak dapat diproses.', ['errors' => $e->errors()], 422);
        }

        $this->success('Pembayaran berhasil dicatat.', $invoice);
    }

    public function refund(Request $request, string $invoiceId): never
    {
        $data = $this->validate($request, PaymentValidator::refundRules());

        try {
            $invoice = $this->service->refund($invoiceId, (float) $data['amount'], $data['notes']);
        } catch (ValidationException $e) {
            $this->fail(current($e->errors())[0] ?? 'Refund tidak dapat diproses.', ['errors' => $e->errors()], 422);
        }

        $this->success('Refund berhasil dicatat.', $invoice);
    }
}
