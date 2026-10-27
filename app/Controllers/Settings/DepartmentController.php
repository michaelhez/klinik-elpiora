<?php

declare(strict_types=1);

namespace App\Controllers\Settings;

use App\Core\Controller;
use App\Core\Request;
use App\Exceptions\ValidationException;
use App\Services\DepartmentService;
use App\Validators\DepartmentValidator;

class DepartmentController extends Controller
{
    public function __construct(private readonly DepartmentService $service = new DepartmentService())
    {
    }

    public function index(Request $request): never
    {
        $this->success('OK', $this->service->list((string) $request->query('search', '')));
    }

    public function store(Request $request): never
    {
        $data = $this->validate($request, DepartmentValidator::rules());

        try {
            $department = $this->service->create($data);
        } catch (ValidationException $e) {
            $this->fail('Validasi gagal.', ['errors' => $e->errors()], 422);
        }

        $this->success('Departemen berhasil ditambahkan.', $department, 201);
    }

    public function update(Request $request, string $id): never
    {
        $data = $this->validate($request, DepartmentValidator::rules());

        try {
            $department = $this->service->update($id, $data);
        } catch (ValidationException $e) {
            $this->fail('Validasi gagal.', ['errors' => $e->errors()], 422);
        }

        $this->success('Departemen berhasil diperbarui.', $department);
    }

    public function destroy(Request $request, string $id): never
    {
        try {
            $this->service->delete($id);
        } catch (ValidationException $e) {
            $this->fail($e->errors()['code'][0] ?? 'Tidak dapat menghapus data.', [], 422);
        }

        $this->success('Departemen berhasil dihapus.');
    }
}
