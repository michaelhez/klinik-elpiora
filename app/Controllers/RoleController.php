<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Exceptions\ValidationException;
use App\Services\RoleService;
use App\Validators\RoleValidator;

class RoleController extends Controller
{
    public function __construct(private readonly RoleService $service = new RoleService())
    {
    }

    public function index(Request $request): never
    {
        $this->view('roles.index', [
            'title' => 'Peran & Akses',
            'roles' => $this->service->list(),
        ]);
    }

    public function create(Request $request): never
    {
        $this->view('roles.create', [
            'title' => 'Tambah Peran',
            'catalogue' => $this->service->permissionCatalogue(),
        ]);
    }

    public function store(Request $request): never
    {
        $data = $this->validate($request, RoleValidator::rules());
        $permissionIds = (array) $request->input('permission_ids', []);

        try {
            $role = $this->service->create($data, $permissionIds);
        } catch (ValidationException $e) {
            Session::flash('_errors', $e->errors());
            Session::flash('_old_input', $request->all());
            $this->redirect('/roles/create');
        }

        flash('success', 'Peran berhasil ditambahkan.');
        $this->redirect('/roles/' . $role['id'] . '/edit');
    }

    public function edit(Request $request, string $id): never
    {
        $role = $this->service->find($id);

        $this->view('roles.edit', [
            'title' => 'Edit Peran',
            'role' => $role,
            'catalogue' => $this->service->permissionCatalogue(),
        ]);
    }

    public function update(Request $request, string $id): never
    {
        $permissionIds = (array) $request->input('permission_ids', []);
        $role = $this->service->find($id);

        try {
            if ((bool) $role['is_system']) {
                $this->service->updatePermissionsOnly($id, $permissionIds);
            } else {
                $data = $this->validate($request, RoleValidator::rules());
                $this->service->update($id, $data, $permissionIds);
            }
        } catch (ValidationException $e) {
            Session::flash('_errors', $e->errors());
            Session::flash('_old_input', $request->all());
            $this->redirect("/roles/{$id}/edit");
        }

        flash('success', 'Peran berhasil diperbarui.');
        $this->redirect('/roles/' . $id . '/edit');
    }

    public function destroy(Request $request, string $id): never
    {
        try {
            $this->service->delete($id);
        } catch (ValidationException $e) {
            flash('error', current($e->errors())[0] ?? 'Peran tidak dapat dihapus.');
            $this->back($request, '/roles');
        }

        flash('success', 'Peran berhasil dihapus.');
        $this->redirect('/roles');
    }
}
