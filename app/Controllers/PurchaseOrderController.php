<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Exceptions\ValidationException;
use App\Models\Branch;
use App\Models\Supplier;
use App\Services\PurchaseOrderService;
use App\Validators\PurchaseOrderValidator;

class PurchaseOrderController extends Controller
{
    public function __construct(private readonly PurchaseOrderService $service = new PurchaseOrderService())
    {
    }

    public function index(Request $request): never
    {
        $status = (string) $request->query('status', '');

        $this->view('purchase-orders.index', [
            'title' => 'Purchase Order',
            'orders' => $this->service->list(['status' => $status]),
            'filters' => ['status' => $status],
        ]);
    }

    public function create(Request $request): never
    {
        $this->view('purchase-orders.create', [
            'title' => 'Buat Purchase Order',
            'suppliers' => Supplier::query()->where('status', '=', 'ACTIVE')->orderBy('name', 'ASC')->get(),
            'branches' => Branch::query()->where('is_active', '=', 1)->orderBy('name', 'ASC')->get(),
        ]);
    }

    public function store(Request $request): never
    {
        $data = $this->validate($request, PurchaseOrderValidator::rules());
        $items = (array) $request->input('items', []);

        try {
            $order = $this->service->create($data, $items);
        } catch (ValidationException $e) {
            $this->fail(current($e->errors())[0] ?? 'Purchase order tidak dapat dibuat.', ['errors' => $e->errors()], 422);
        }

        $this->success('Purchase order berhasil dibuat.', $order, 201);
    }

    public function show(Request $request, string $id): never
    {
        $order = $this->service->find($id);

        $this->view('purchase-orders.show', [
            'title' => $order['po_number'],
            'order' => $order,
        ]);
    }

    public function submit(Request $request, string $id): never
    {
        try {
            $this->service->submit($id);
        } catch (ValidationException $e) {
            flash('error', current($e->errors())[0] ?? 'Purchase order tidak dapat diajukan.');
            $this->back($request, '/purchase-orders/' . $id);
        }

        flash('success', 'Purchase order berhasil diajukan untuk persetujuan.');
        $this->back($request, '/purchase-orders/' . $id);
    }

    public function approve(Request $request, string $id): never
    {
        try {
            $this->service->approve($id);
        } catch (ValidationException $e) {
            flash('error', current($e->errors())[0] ?? 'Purchase order tidak dapat disetujui.');
            $this->back($request, '/purchase-orders/' . $id);
        }

        flash('success', 'Purchase order berhasil disetujui.');
        $this->back($request, '/purchase-orders/' . $id);
    }

    public function markOrdered(Request $request, string $id): never
    {
        try {
            $this->service->markOrdered($id);
        } catch (ValidationException $e) {
            flash('error', current($e->errors())[0] ?? 'Purchase order tidak dapat ditandai dipesan.');
            $this->back($request, '/purchase-orders/' . $id);
        }

        flash('success', 'Purchase order berhasil ditandai sebagai dipesan ke pemasok.');
        $this->back($request, '/purchase-orders/' . $id);
    }

    public function cancel(Request $request, string $id): never
    {
        try {
            $this->service->cancel($id);
        } catch (ValidationException $e) {
            flash('error', current($e->errors())[0] ?? 'Purchase order tidak dapat dibatalkan.');
            $this->back($request, '/purchase-orders/' . $id);
        }

        flash('success', 'Purchase order berhasil dibatalkan.');
        $this->back($request, '/purchase-orders/' . $id);
    }

    public function receive(Request $request, string $id): never
    {
        $data = $this->validate($request, PurchaseOrderValidator::receiveRules());
        $items = (array) $request->input('items', []);

        try {
            $order = $this->service->receive($id, $items, $data['notes'] ?? null);
        } catch (ValidationException $e) {
            $this->fail(current($e->errors())[0] ?? 'Penerimaan barang gagal diproses.', ['errors' => $e->errors()], 422);
        }

        $this->success('Barang berhasil diterima dan stok telah diperbarui.', $order);
    }
}
