<?php

declare(strict_types=1);

namespace App\Controllers\Settings;

use App\Core\Controller;
use App\Core\Request;
use App\Exceptions\ValidationException;
use App\Services\RoomService;
use App\Validators\RoomValidator;

class RoomController extends Controller
{
    public function __construct(private readonly RoomService $service = new RoomService())
    {
    }

    public function index(Request $request): never
    {
        $this->success('OK', $this->service->list((string) $request->query('search', '')));
    }

    public function store(Request $request): never
    {
        $data = $this->validate($request, RoomValidator::rules());

        try {
            $room = $this->service->create($data);
        } catch (ValidationException $e) {
            $this->fail('Validasi gagal.', ['errors' => $e->errors()], 422);
        }

        $this->success('Ruangan berhasil ditambahkan.', $room, 201);
    }

    public function update(Request $request, string $id): never
    {
        $data = $this->validate($request, RoomValidator::rules());

        try {
            $room = $this->service->update($id, $data);
        } catch (ValidationException $e) {
            $this->fail('Validasi gagal.', ['errors' => $e->errors()], 422);
        }

        $this->success('Ruangan berhasil diperbarui.', $room);
    }

    public function destroy(Request $request, string $id): never
    {
        try {
            $this->service->delete($id);
        } catch (ValidationException $e) {
            $this->fail($e->errors()['code'][0] ?? 'Tidak dapat menghapus data.', [], 422);
        }

        $this->success('Ruangan berhasil dihapus.');
    }
}
