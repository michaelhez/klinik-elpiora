<?php

declare(strict_types=1);

namespace App\Controllers\Settings;

use App\Core\Controller;
use App\Core\Request;
use App\Exceptions\ValidationException;
use App\Services\CorporateService;
use App\Validators\CorporateValidator;

class CorporateController extends Controller
{
    public function __construct(private readonly CorporateService $service = new CorporateService())
    {
    }

    public function index(Request $request): never
    {
        $this->success('OK', $this->service->list((string) $request->query('search', '')));
    }

    public function store(Request $request): never
    {
        $data = $this->validate($request, CorporateValidator::rules());

        try {
            $corporate = $this->service->create($data);
        } catch (ValidationException $e) {
            $this->fail('Validasi gagal.', ['errors' => $e->errors()], 422);
        }

        $this->success('Perusahaan rekanan berhasil ditambahkan.', $corporate, 201);
    }

    public function update(Request $request, string $id): never
    {
        $data = $this->validate($request, CorporateValidator::rules());

        try {
            $corporate = $this->service->update($id, $data);
        } catch (ValidationException $e) {
            $this->fail('Validasi gagal.', ['errors' => $e->errors()], 422);
        }

        $this->success('Perusahaan rekanan berhasil diperbarui.', $corporate);
    }
}
