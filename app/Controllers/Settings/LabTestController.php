<?php

declare(strict_types=1);

namespace App\Controllers\Settings;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Exceptions\ValidationException;
use App\Services\LabTestService;
use App\Validators\LabTestValidator;

class LabTestController extends Controller
{
    public function __construct(private readonly LabTestService $service = new LabTestService())
    {
    }

    public function index(Request $request): never
    {
        $this->view('settings.lab-tests.index', [
            'title' => 'Jenis Pemeriksaan Lab',
            'tests' => $this->service->list((string) $request->query('search', '')),
            'filters' => ['search' => (string) $request->query('search', '')],
        ]);
    }

    public function create(Request $request): never
    {
        $this->view('settings.lab-tests.create', ['title' => 'Tambah Jenis Pemeriksaan']);
    }

    public function store(Request $request): never
    {
        $data = $this->validate($request, LabTestValidator::rules());

        try {
            $test = $this->service->create($data);
        } catch (ValidationException $e) {
            Session::flash('_errors', $e->errors());
            Session::flash('_old_input', $request->all());
            $this->redirect('/settings/lab-tests/create');
        }

        flash('success', 'Jenis pemeriksaan berhasil ditambahkan.');
        $this->redirect('/settings/lab-tests/' . $test['id']);
    }

    public function show(Request $request, string $id): never
    {
        $test = $this->service->find($id);

        $this->view('settings.lab-tests.show', [
            'title' => $test['name'],
            'test' => $test,
            'parameters' => $this->service->parameters($id),
        ]);
    }

    public function edit(Request $request, string $id): never
    {
        $test = $this->service->find($id);
        $this->view('settings.lab-tests.edit', ['title' => 'Edit Jenis Pemeriksaan', 'test' => $test]);
    }

    public function update(Request $request, string $id): never
    {
        $data = $this->validate($request, LabTestValidator::rules());

        try {
            $this->service->update($id, $data);
        } catch (ValidationException $e) {
            Session::flash('_errors', $e->errors());
            Session::flash('_old_input', $request->all());
            $this->redirect("/settings/lab-tests/{$id}/edit");
        }

        flash('success', 'Jenis pemeriksaan berhasil diperbarui.');
        $this->redirect('/settings/lab-tests/' . $id);
    }

    public function destroy(Request $request, string $id): never
    {
        try {
            $this->service->delete($id);
        } catch (ValidationException $e) {
            flash('error', current($e->errors())[0] ?? 'Tidak dapat menghapus data.');
            $this->back($request, '/settings/lab-tests');
        }

        flash('success', 'Jenis pemeriksaan berhasil dihapus.');
        $this->redirect('/settings/lab-tests');
    }

    public function storeParameter(Request $request, string $id): never
    {
        $data = $this->validate($request, LabTestValidator::parameterRules());

        try {
            $parameters = $this->service->addParameter($id, $data);
        } catch (ValidationException $e) {
            $this->fail('Validasi gagal.', ['errors' => $e->errors()], 422);
        }

        $this->success('Parameter berhasil ditambahkan.', $parameters, 201);
    }

    public function updateParameter(Request $request, string $id, string $parameterId): never
    {
        $data = $this->validate($request, LabTestValidator::parameterRules());

        try {
            $parameters = $this->service->updateParameter($id, $parameterId, $data);
        } catch (ValidationException $e) {
            $this->fail('Validasi gagal.', ['errors' => $e->errors()], 422);
        }

        $this->success('Parameter berhasil diperbarui.', $parameters);
    }

    public function destroyParameter(Request $request, string $id, string $parameterId): never
    {
        try {
            $parameters = $this->service->deleteParameter($id, $parameterId);
        } catch (ValidationException $e) {
            $this->fail(current($e->errors())[0] ?? 'Tidak dapat menghapus parameter.', [], 422);
        }

        $this->success('Parameter berhasil dihapus.', $parameters);
    }
}
