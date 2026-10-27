<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Exceptions\ValidationException;
use App\Models\Branch;
use App\Models\MedicineCategory;
use App\Models\MedicineUnit;
use App\Services\InventoryService;
use App\Services\MedicineService;
use App\Validators\MedicineValidator;

class MedicineController extends Controller
{
    public function __construct(
        private readonly MedicineService $service = new MedicineService(),
        private readonly InventoryService $inventoryService = new InventoryService(),
    ) {
    }

    public function index(Request $request): never
    {
        $filters = [
            'search' => (string) $request->query('search', ''),
            'category_id' => (string) $request->query('category_id', ''),
        ];

        $page = max(1, (int) $request->query('page', 1));
        $result = $this->service->paginate($filters, $page, 20);

        $this->view('medicines.index', [
            'title' => 'Obat',
            'medicines' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'categories' => MedicineCategory::query()->where('is_active', '=', 1)->orderBy('name', 'ASC')->get(),
        ]);
    }

    public function create(Request $request): never
    {
        $this->view('medicines.create', [
            'title' => 'Tambah Obat',
            'categories' => MedicineCategory::query()->where('is_active', '=', 1)->orderBy('name', 'ASC')->get(),
            'units' => MedicineUnit::query()->where('is_active', '=', 1)->orderBy('name', 'ASC')->get(),
        ]);
    }

    public function store(Request $request): never
    {
        $data = $this->validate($request, MedicineValidator::rules());

        try {
            $medicine = $this->service->create($data);
        } catch (ValidationException $e) {
            Session::flash('_errors', $e->errors());
            Session::flash('_old_input', $request->all());
            $this->redirect('/medicines/create');
        }

        flash('success', 'Obat berhasil ditambahkan.');
        $this->redirect('/medicines/' . $medicine['id']);
    }

    public function show(Request $request, string $id): never
    {
        $medicine = $this->service->find($id);

        $this->view('medicines.show', [
            'title' => $medicine['name'],
            'medicine' => $medicine,
            'batches' => $this->inventoryService->batches($id),
            'movements' => $this->inventoryService->movements($id),
            'branches' => Branch::query()->where('is_active', '=', 1)->orderBy('name', 'ASC')->get(),
        ]);
    }

    public function edit(Request $request, string $id): never
    {
        $medicine = $this->service->find($id);

        $this->view('medicines.edit', [
            'title' => 'Edit Obat',
            'medicine' => $medicine,
            'categories' => MedicineCategory::query()->where('is_active', '=', 1)->orderBy('name', 'ASC')->get(),
            'units' => MedicineUnit::query()->where('is_active', '=', 1)->orderBy('name', 'ASC')->get(),
        ]);
    }

    public function update(Request $request, string $id): never
    {
        $data = $this->validate($request, MedicineValidator::rules());

        try {
            $this->service->update($id, $data);
        } catch (ValidationException $e) {
            Session::flash('_errors', $e->errors());
            Session::flash('_old_input', $request->all());
            $this->redirect("/medicines/{$id}/edit");
        }

        flash('success', 'Data obat berhasil diperbarui.');
        $this->redirect('/medicines/' . $id);
    }

    public function destroy(Request $request, string $id): never
    {
        try {
            $this->service->delete($id);
        } catch (ValidationException $e) {
            flash('error', current($e->errors())[0] ?? 'Obat tidak dapat dihapus.');
            $this->back($request, '/medicines/' . $id);
        }

        flash('success', 'Obat berhasil dihapus.');
        $this->redirect('/medicines');
    }

    public function storeBatch(Request $request, string $id): never
    {
        $data = $this->validate($request, MedicineValidator::batchRules());

        try {
            $this->service->addBatch($id, $data);
        } catch (ValidationException $e) {
            $this->fail('Validasi gagal.', ['errors' => $e->errors()], 422);
        }

        $this->success('Batch stok berhasil ditambahkan.', $this->inventoryService->batches($id), 201);
    }
}
