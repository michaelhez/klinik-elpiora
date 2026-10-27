<?php

declare(strict_types=1);

namespace App\Controllers\Settings;

use App\Core\Controller;
use App\Core\Request;
use App\Exceptions\ValidationException;
use App\Services\ClinicService;
use App\Validators\ClinicValidator;

class ClinicController extends Controller
{
    public function __construct(private readonly ClinicService $service = new ClinicService())
    {
    }

    public function index(Request $request): never
    {
        $this->success('OK', $this->service->list((string) $request->query('search', '')));
    }

    public function store(Request $request): never
    {
        $data = $this->validate($request, ClinicValidator::rules());

        try {
            $clinic = $this->service->create($data);
        } catch (ValidationException $e) {
            $this->fail('Validasi gagal.', ['errors' => $e->errors()], 422);
        }

        $this->success('Klinik berhasil ditambahkan.', $clinic, 201);
    }

    public function update(Request $request, string $id): never
    {
        $data = $this->validate($request, ClinicValidator::rules());

        try {
            $clinic = $this->service->update($id, $data);
        } catch (ValidationException $e) {
            $this->fail('Validasi gagal.', ['errors' => $e->errors()], 422);
        }

        $this->success('Klinik berhasil diperbarui.', $clinic);
    }

    public function destroy(Request $request, string $id): never
    {
        try {
            $this->service->delete($id);
        } catch (ValidationException $e) {
            $this->fail($e->errors()['code'][0] ?? 'Tidak dapat menghapus data.', [], 422);
        }

        $this->success('Klinik berhasil dihapus.');
    }
}
