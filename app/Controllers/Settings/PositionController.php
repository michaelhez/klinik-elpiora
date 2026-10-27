<?php

declare(strict_types=1);

namespace App\Controllers\Settings;

use App\Core\Controller;
use App\Core\Request;
use App\Exceptions\ValidationException;
use App\Services\PositionService;
use App\Validators\PositionValidator;

class PositionController extends Controller
{
    public function __construct(private readonly PositionService $service = new PositionService())
    {
    }

    public function index(Request $request): never
    {
        $this->success('OK', $this->service->list((string) $request->query('search', '')));
    }

    public function store(Request $request): never
    {
        $data = $this->validate($request, PositionValidator::rules());

        try {
            $position = $this->service->create($data);
        } catch (ValidationException $e) {
            $this->fail('Validasi gagal.', ['errors' => $e->errors()], 422);
        }

        $this->success('Jabatan berhasil ditambahkan.', $position, 201);
    }

    public function update(Request $request, string $id): never
    {
        $data = $this->validate($request, PositionValidator::rules());

        try {
            $position = $this->service->update($id, $data);
        } catch (ValidationException $e) {
            $this->fail('Validasi gagal.', ['errors' => $e->errors()], 422);
        }

        $this->success('Jabatan berhasil diperbarui.', $position);
    }

    public function destroy(Request $request, string $id): never
    {
        try {
            $this->service->delete($id);
        } catch (ValidationException $e) {
            $this->fail($e->errors()['code'][0] ?? 'Tidak dapat menghapus data.', [], 422);
        }

        $this->success('Jabatan berhasil dihapus.');
    }
}
