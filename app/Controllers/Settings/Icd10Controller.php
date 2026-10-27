<?php

declare(strict_types=1);

namespace App\Controllers\Settings;

use App\Core\Controller;
use App\Core\Request;
use App\Exceptions\ValidationException;
use App\Services\Icd10Service;
use App\Validators\Icd10Validator;

class Icd10Controller extends Controller
{
    public function __construct(private readonly Icd10Service $service = new Icd10Service())
    {
    }

    public function index(Request $request): never
    {
        $this->success('OK', $this->service->list((string) $request->query('search', '')));
    }

    public function store(Request $request): never
    {
        $data = $this->validate($request, Icd10Validator::rules());

        try {
            $icd10 = $this->service->create($data);
        } catch (ValidationException $e) {
            $this->fail('Validasi gagal.', ['errors' => $e->errors()], 422);
        }

        $this->success('Kode ICD-10 berhasil ditambahkan.', $icd10, 201);
    }

    public function update(Request $request, string $id): never
    {
        $data = $this->validate($request, Icd10Validator::rules());

        try {
            $icd10 = $this->service->update($id, $data);
        } catch (ValidationException $e) {
            $this->fail('Validasi gagal.', ['errors' => $e->errors()], 422);
        }

        $this->success('Kode ICD-10 berhasil diperbarui.', $icd10);
    }

    public function destroy(Request $request, string $id): never
    {
        try {
            $this->service->delete($id);
        } catch (ValidationException $e) {
            $this->fail($e->errors()['code'][0] ?? 'Tidak dapat menghapus data.', [], 422);
        }

        $this->success('Kode ICD-10 berhasil dihapus.');
    }
}
