<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Exceptions\ValidationException;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Position;
use App\Services\EmployeeService;
use App\Validators\EmployeeValidator;

class EmployeeController extends Controller
{
    public function __construct(private readonly EmployeeService $service = new EmployeeService())
    {
    }

    public function index(Request $request): never
    {
        $filters = [
            'search' => (string) $request->query('search', ''),
            'branch_id' => (string) $request->query('branch_id', ''),
            'department_id' => (string) $request->query('department_id', ''),
            'status' => (string) $request->query('status', ''),
        ];

        $page = max(1, (int) $request->query('page', 1));
        $perPage = (int) $request->query('per_page', 20);
        $perPage = in_array($perPage, [10, 20, 50, 100], true) ? $perPage : 20;

        $result = $this->service->paginate($filters, $page, $perPage);

        $this->view('employees.index', [
            'title' => 'Karyawan',
            'employees' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'branches' => Branch::query()->where('is_active', '=', 1)->orderBy('name', 'ASC')->get(),
            'departments' => Department::query()->where('is_active', '=', 1)->orderBy('name', 'ASC')->get(),
        ]);
    }

    public function create(Request $request): never
    {
        $this->view('employees.create', [
            'title' => 'Tambah Karyawan',
            'branches' => Branch::query()->where('is_active', '=', 1)->orderBy('name', 'ASC')->get(),
            'departments' => Department::query()->where('is_active', '=', 1)->orderBy('name', 'ASC')->get(),
            'positions' => Position::query()->where('is_active', '=', 1)->orderBy('name', 'ASC')->get(),
        ]);
    }

    public function store(Request $request): never
    {
        $data = $this->validate($request, EmployeeValidator::rules());

        try {
            $employee = $this->service->create($data, $request->file('photo'));
        } catch (ValidationException $e) {
            Session::flash('_errors', $e->errors());
            Session::flash('_old_input', $request->all());
            $this->redirect('/employees/create');
        }

        flash('success', 'Karyawan berhasil ditambahkan.');
        $this->redirect('/employees/' . $employee['id']);
    }

    public function show(Request $request, string $id): never
    {
        $employee = $this->service->find($id);

        $this->view('employees.show', [
            'title' => $employee['name'],
            'employee' => $employee,
            'documents' => $this->service->documents($id),
            'emergencyContacts' => $this->service->emergencyContacts($id),
        ]);
    }

    public function edit(Request $request, string $id): never
    {
        $employee = $this->service->find($id);

        $this->view('employees.edit', [
            'title' => 'Edit Karyawan',
            'employee' => $employee,
            'branches' => Branch::query()->where('is_active', '=', 1)->orderBy('name', 'ASC')->get(),
            'departments' => Department::query()->where('is_active', '=', 1)->orderBy('name', 'ASC')->get(),
            'positions' => Position::query()->where('is_active', '=', 1)->orderBy('name', 'ASC')->get(),
        ]);
    }

    public function update(Request $request, string $id): never
    {
        $data = $this->validate($request, EmployeeValidator::rules());

        try {
            $this->service->update($id, $data, $request->file('photo'));
        } catch (ValidationException $e) {
            Session::flash('_errors', $e->errors());
            Session::flash('_old_input', $request->all());
            $this->redirect("/employees/{$id}/edit");
        }

        flash('success', 'Data karyawan berhasil diperbarui.');
        $this->redirect('/employees/' . $id);
    }

    public function destroy(Request $request, string $id): never
    {
        try {
            $this->service->delete($id);
        } catch (ValidationException $e) {
            flash('error', $e->errors()['name'][0] ?? 'Karyawan tidak dapat dihapus.');
            $this->redirect('/employees/' . $id);
        }

        flash('success', 'Karyawan berhasil dihapus.');
        $this->redirect('/employees');
    }

    public function storeDocument(Request $request, string $id): never
    {
        $documentType = (string) $request->input('document_type', 'Lainnya');
        $file = $request->file('file');

        if ($file === null) {
            $this->fail('Berkas wajib diunggah.', [], 422);
        }

        try {
            $document = $this->service->addDocument($id, $documentType, $file);
        } catch (ValidationException $e) {
            $this->fail($e->errors()['file'][0] ?? 'Berkas tidak valid.', ['errors' => $e->errors()], 422);
        }

        $this->success('Dokumen berhasil diunggah.', $document, 201);
    }

    public function destroyDocument(Request $request, string $id, string $documentId): never
    {
        $this->service->deleteDocument($id, $documentId);
        $this->success('Dokumen berhasil dihapus.');
    }

    public function downloadDocument(Request $request, string $id, string $documentId): never
    {
        $path = $this->service->documentPath($id, $documentId);

        if (!is_file($path)) {
            Response::abort(404, 'Berkas tidak ditemukan.');
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($path) . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    public function storeEmergencyContact(Request $request, string $id): never
    {
        $data = $this->validate($request, EmployeeValidator::emergencyContactRules());
        $contact = $this->service->addEmergencyContact($id, $data);
        $this->success('Kontak darurat berhasil ditambahkan.', $contact, 201);
    }

    public function destroyEmergencyContact(Request $request, string $id, string $contactId): never
    {
        $this->service->deleteEmergencyContact($id, $contactId);
        $this->success('Kontak darurat berhasil dihapus.');
    }
}
