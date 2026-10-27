<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Exceptions\ValidationException;
use App\Services\UserService;
use App\Validators\UserValidator;

class UserController extends Controller
{
    public function __construct(private readonly UserService $service = new UserService())
    {
    }

    public function index(Request $request): never
    {
        $search = (string) $request->query('search', '');

        $this->view('users.index', [
            'title' => 'Pengguna',
            'users' => $this->service->list($search),
            'filters' => ['search' => $search],
        ]);
    }

    public function create(Request $request): never
    {
        $this->view('users.create', [
            'title' => 'Tambah Pengguna',
            'roles' => $this->service->roleOptions(),
        ]);
    }

    public function store(Request $request): never
    {
        $data = $this->validate($request, UserValidator::createRules());
        $roleIds = (array) $request->input('role_ids', []);

        try {
            $user = $this->service->create($data, $roleIds);
        } catch (ValidationException $e) {
            Session::flash('_errors', $e->errors());
            Session::flash('_old_input', $request->all());
            $this->redirect('/users/create');
        }

        flash('success', 'Pengguna berhasil ditambahkan.');
        $this->redirect('/users/' . $user['id'] . '/edit');
    }

    public function edit(Request $request, string $id): never
    {
        $user = $this->service->find($id);

        $this->view('users.edit', [
            'title' => 'Edit Pengguna',
            'user' => $user,
            'roles' => $this->service->roleOptions(),
            'assignedRoleIds' => array_column($user['roles'], 'id'),
        ]);
    }

    public function update(Request $request, string $id): never
    {
        $data = $this->validate($request, UserValidator::updateRules());
        $roleIds = (array) $request->input('role_ids', []);

        try {
            $this->service->update($id, $data, $roleIds);
        } catch (ValidationException $e) {
            Session::flash('_errors', $e->errors());
            Session::flash('_old_input', $request->all());
            $this->redirect("/users/{$id}/edit");
        }

        flash('success', 'Pengguna berhasil diperbarui.');
        $this->redirect('/users/' . $id . '/edit');
    }

    public function destroy(Request $request, string $id): never
    {
        try {
            $this->service->delete($id);
        } catch (ValidationException $e) {
            flash('error', current($e->errors())[0] ?? 'Pengguna tidak dapat dihapus.');
            $this->back($request, '/users');
        }

        flash('success', 'Pengguna berhasil dihapus.');
        $this->redirect('/users');
    }

    public function resetPassword(Request $request, string $id): never
    {
        $data = $this->validate($request, UserValidator::resetPasswordRules());

        $this->service->resetPassword($id, $data['password']);

        $this->success('Kata sandi berhasil direset.');
    }
}
