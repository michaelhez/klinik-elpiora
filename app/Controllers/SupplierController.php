<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Exceptions\ValidationException;
use App\Services\SupplierService;
use App\Validators\SupplierValidator;

class SupplierController extends Controller
{
    public function __construct(private readonly SupplierService $service = new SupplierService())
    {
    }

    public function index(Request $request): never
    {
        $this->view('suppliers.index', [
            'title' => 'Pemasok',
            'suppliers' => $this->service->list((string) $request->query('search', '')),
            'filters' => ['search' => (string) $request->query('search', '')],
        ]);
    }

    public function store(Request $request): never
    {
        $data = $this->validate($request, SupplierValidator::rules());

        try {
            $supplier = $this->service->create($data);
        } catch (ValidationException $e) {
            $this->fail('Validasi gagal.', ['errors' => $e->errors()], 422);
        }

        $this->success('Pemasok berhasil ditambahkan.', $supplier, 201);
    }

    public function update(Request $request, string $id): never
    {
        $data = $this->validate($request, SupplierValidator::rules());

        try {
            $supplier = $this->service->update($id, $data);
        } catch (ValidationException $e) {
            $this->fail('Validasi gagal.', ['errors' => $e->errors()], 422);
        }

        $this->success('Pemasok berhasil diperbarui.', $supplier);
    }

    public function destroy(Request $request, string $id): never
    {
        try {
            $this->service->delete($id);
        } catch (ValidationException $e) {
            $this->fail($e->errors()['code'][0] ?? 'Tidak dapat menghapus data.', [], 422);
        }

        $this->success('Pemasok berhasil dihapus.');
    }
}
