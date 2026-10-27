<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Exceptions\ValidationException;
use App\Models\Supplier;
use App\Services\SupplierReturnService;
use App\Validators\SupplierReturnValidator;

class SupplierReturnController extends Controller
{
    public function __construct(private readonly SupplierReturnService $service = new SupplierReturnService())
    {
    }

    public function index(Request $request): never
    {
        $this->view('supplier-returns.index', [
            'title' => 'Retur Pemasok',
            'returns' => $this->service->list(),
        ]);
    }

    public function create(Request $request): never
    {
        $this->view('supplier-returns.create', [
            'title' => 'Buat Retur Pemasok',
            'suppliers' => Supplier::query()->where('status', '=', 'ACTIVE')->orderBy('name', 'ASC')->get(),
        ]);
    }

    public function store(Request $request): never
    {
        $data = $this->validate($request, SupplierReturnValidator::rules());
        $items = (array) $request->input('items', []);

        try {
            $return = $this->service->create($data, $items);
        } catch (ValidationException $e) {
            $this->fail(current($e->errors())[0] ?? 'Retur tidak dapat diproses.', ['errors' => $e->errors()], 422);
        }

        $this->success('Retur pemasok berhasil diproses.', $return, 201);
    }

    public function show(Request $request, string $id): never
    {
        $return = $this->service->find($id);

        $this->view('supplier-returns.show', [
            'title' => $return['return_number'],
            'return' => $return,
        ]);
    }
}
