<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Exceptions\ValidationException;
use App\Services\PrescriptionService;
use App\Validators\PrescriptionValidator;

class PrescriptionController extends Controller
{
    public function __construct(private readonly PrescriptionService $service = new PrescriptionService())
    {
    }

    public function store(Request $request): never
    {
        $data = $this->validate($request, PrescriptionValidator::rules());
        $items = (array) $request->input('items', []);
        $compounds = (array) $request->input('compounds', []);

        try {
            $prescription = $this->service->create($data, $items, $compounds);
        } catch (ValidationException $e) {
            flash('error', current($e->errors())[0] ?? 'Resep tidak dapat dibuat.');
            $this->back($request, '/medical-records/' . $data['medical_record_id']);
        }

        flash('success', 'Resep berhasil dibuat: ' . $prescription['prescription_number']);
        $this->back($request, '/medical-records/' . $data['medical_record_id']);
    }

    public function show(Request $request, string $id): never
    {
        $prescription = $this->service->find($id);

        $this->view('prescriptions.show', [
            'title' => $prescription['prescription_number'],
            'prescription' => $prescription,
        ]);
    }

    public function cancel(Request $request, string $id): never
    {
        try {
            $this->service->cancel($id);
        } catch (ValidationException $e) {
            flash('error', current($e->errors())[0] ?? 'Resep tidak dapat dibatalkan.');
            $this->back($request, '/prescriptions/' . $id);
        }

        flash('success', 'Resep berhasil dibatalkan.');
        $this->back($request, '/prescriptions/' . $id);
    }
}
