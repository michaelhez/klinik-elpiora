<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Exceptions\ValidationException;
use App\Services\PrescriptionService;
use App\Services\PharmacyService;

class PharmacyController extends Controller
{
    public function __construct(
        private readonly PharmacyService $service = new PharmacyService(),
        private readonly PrescriptionService $prescriptionService = new PrescriptionService(),
    ) {
    }

    public function index(Request $request): never
    {
        $status = (string) $request->query('status', '');
        $statuses = $status !== '' ? [$status] : ['WAITING_PHARMACY', 'PROCESSING', 'READY'];

        $this->view('pharmacy.index', [
            'title' => 'Farmasi',
            'prescriptions' => $this->prescriptionService->queue($statuses),
            'filters' => ['status' => $status],
        ]);
    }

    public function verify(Request $request, string $id): never
    {
        try {
            $this->service->verify($id);
        } catch (ValidationException $e) {
            flash('error', current($e->errors())[0] ?? 'Resep tidak dapat diverifikasi.');
            $this->back($request, '/prescriptions/' . $id);
        }

        flash('success', 'Resep berhasil diverifikasi, siap diproses.');
        $this->back($request, '/prescriptions/' . $id);
    }

    public function prepare(Request $request, string $id): never
    {
        try {
            $this->service->prepare($id);
        } catch (ValidationException $e) {
            flash('error', current($e->errors())[0] ?? 'Resep tidak dapat disiapkan.');
            $this->back($request, '/prescriptions/' . $id);
        }

        flash('success', 'Obat berhasil disiapkan dan stok telah dipotong (FEFO).');
        $this->back($request, '/prescriptions/' . $id);
    }

    public function handover(Request $request, string $id): never
    {
        try {
            $this->service->handover($id);
        } catch (ValidationException $e) {
            flash('error', current($e->errors())[0] ?? 'Resep belum dapat diserahkan.');
            $this->back($request, '/prescriptions/' . $id);
        }

        flash('success', 'Resep berhasil diserahkan ke pasien.');
        $this->back($request, '/prescriptions/' . $id);
    }
}
