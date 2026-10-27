<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Exceptions\ValidationException;
use App\Models\MedicineBatch;
use App\Services\InventoryService;
use App\Validators\MedicineValidator;

class InventoryController extends Controller
{
    public function __construct(private readonly InventoryService $service = new InventoryService())
    {
    }

    public function index(Request $request): never
    {
        $this->view('inventory.index', [
            'title' => 'Inventori',
            'lowStock' => $this->service->lowStock(),
            'expiringSoon' => $this->service->expiringSoon(90),
        ]);
    }

    public function adjust(Request $request, string $id): never
    {
        $data = $this->validate($request, MedicineValidator::adjustRules());

        $batch = MedicineBatch::find($data['medicine_batch_id']);
        if ($batch === null || (string) $batch['medicine_id'] !== (string) $id) {
            $this->fail('Batch tidak ditemukan untuk obat ini.', [], 422);
        }

        try {
            $this->service->adjust($id, $data['medicine_batch_id'], $batch['branch_id'], $data['adjustment_type'], (int) $data['quantity'], $data['reason']);
        } catch (ValidationException $e) {
            $this->fail('Validasi gagal.', ['errors' => $e->errors()], 422);
        }

        $this->success('Stok berhasil disesuaikan.', [], 200);
    }

    public function transfer(Request $request, string $id): never
    {
        $data = $this->validate($request, MedicineValidator::transferRules());

        $batch = MedicineBatch::find($data['medicine_batch_id']);
        if ($batch === null || (string) $batch['medicine_id'] !== (string) $id) {
            $this->fail('Batch tidak ditemukan untuk obat ini.', [], 422);
        }

        try {
            $this->service->transfer($id, $data['medicine_batch_id'], $batch['branch_id'], $data['to_branch_id'], (int) $data['quantity'], $data['notes'] ?? null);
        } catch (ValidationException $e) {
            $this->fail('Validasi gagal.', ['errors' => $e->errors()], 422);
        }

        $this->success('Stok berhasil ditransfer.', [], 200);
    }
}
