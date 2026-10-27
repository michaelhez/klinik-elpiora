<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Exceptions\ValidationException;
use App\Services\BillingService;

class BillingController extends Controller
{
    public function __construct(private readonly BillingService $service = new BillingService())
    {
    }

    public function index(Request $request): never
    {
        $status = (string) $request->query('status', '');
        $search = (string) $request->query('search', '');

        $this->view('billing.index', [
            'title' => 'Tagihan',
            'invoices' => $this->service->list(['status' => $status, 'search' => $search]),
            'filters' => ['status' => $status, 'search' => $search],
        ]);
    }

    public function show(Request $request, string $id): never
    {
        $invoice = $this->service->find($id);

        $this->view('billing.show', [
            'title' => $invoice['invoice_number'],
            'invoice' => $invoice,
        ]);
    }

    public function generate(Request $request): never
    {
        $medicalRecordId = $request->input('medical_record_id');

        try {
            $invoice = $this->service->generateFromMedicalRecord($medicalRecordId);
        } catch (ValidationException $e) {
            flash('error', current($e->errors())[0] ?? 'Tagihan tidak dapat dibuat.');
            $this->back($request, '/medical-records/' . $medicalRecordId);
        }

        flash('success', 'Tagihan berhasil dibuat: ' . $invoice['invoice_number']);
        $this->redirect('/billing/' . $invoice['id']);
    }

    public function cancel(Request $request, string $id): never
    {
        try {
            $this->service->cancel($id);
        } catch (ValidationException $e) {
            flash('error', current($e->errors())[0] ?? 'Tagihan tidak dapat dibatalkan.');
            $this->back($request, '/billing/' . $id);
        }

        flash('success', 'Tagihan berhasil dibatalkan.');
        $this->back($request, '/billing/' . $id);
    }
}
