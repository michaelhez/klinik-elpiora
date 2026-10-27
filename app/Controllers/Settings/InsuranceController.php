<?php

declare(strict_types=1);

namespace App\Controllers\Settings;

use App\Core\Controller;
use App\Core\Request;
use App\Exceptions\ValidationException;
use App\Services\InsuranceService;
use App\Validators\InsuranceValidator;

class InsuranceController extends Controller
{
    public function __construct(private readonly InsuranceService $service = new InsuranceService())
    {
    }

    public function index(Request $request): never
    {
        $this->success('OK', $this->service->list((string) $request->query('search', '')));
    }

    public function store(Request $request): never
    {
        $data = $this->validate($request, InsuranceValidator::rules());

        try {
            $insurance = $this->service->create($data);
        } catch (ValidationException $e) {
            $this->fail('Validasi gagal.', ['errors' => $e->errors()], 422);
        }

        $this->success('Asuransi berhasil ditambahkan.', $insurance, 201);
    }

    public function update(Request $request, string $id): never
    {
        $data = $this->validate($request, InsuranceValidator::rules());

        try {
            $insurance = $this->service->update($id, $data);
        } catch (ValidationException $e) {
            $this->fail('Validasi gagal.', ['errors' => $e->errors()], 422);
        }

        $this->success('Asuransi berhasil diperbarui.', $insurance);
    }
}
