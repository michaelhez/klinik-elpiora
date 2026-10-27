<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Exceptions\ValidationException;
use App\Models\Employee;
use App\Models\Polyclinic;
use App\Models\Room;
use App\Models\Specialization;
use App\Services\DoctorService;
use App\Validators\DoctorValidator;

class DoctorController extends Controller
{
    public function __construct(private readonly DoctorService $service = new DoctorService())
    {
    }

    public function index(Request $request): never
    {
        $filters = [
            'search' => (string) $request->query('search', ''),
            'specialization_id' => (string) $request->query('specialization_id', ''),
            'status' => (string) $request->query('status', ''),
        ];

        $page = max(1, (int) $request->query('page', 1));
        $perPage = (int) $request->query('per_page', 20);
        $perPage = in_array($perPage, [10, 20, 50, 100], true) ? $perPage : 20;

        $result = $this->service->paginate($filters, $page, $perPage);

        $this->view('doctors.index', [
            'title' => 'Dokter',
            'doctors' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'specializations' => Specialization::query()->where('is_active', '=', 1)->orderBy('name', 'ASC')->get(),
        ]);
    }

    public function create(Request $request): never
    {
        $this->view('doctors.create', [
            'title' => 'Tambah Dokter',
            'employees' => Employee::withoutDoctorRecord(),
            'specializations' => Specialization::query()->where('is_active', '=', 1)->orderBy('name', 'ASC')->get(),
        ]);
    }

    public function store(Request $request): never
    {
        $data = $this->validate($request, DoctorValidator::rules());
        $data['specialization_ids'] = (array) $request->input('specialization_ids', []);
        $data['primary_specialization_id'] = $request->input('primary_specialization_id');

        try {
            $doctor = $this->service->create($data);
        } catch (ValidationException $e) {
            Session::flash('_errors', $e->errors());
            Session::flash('_old_input', $request->all());
            $this->redirect('/doctors/create');
        }

        flash('success', 'Dokter berhasil ditambahkan.');
        $this->redirect('/doctors/' . $doctor['id']);
    }

    public function show(Request $request, string $id): never
    {
        $doctor = $this->service->find($id);

        $this->view('doctors.show', [
            'title' => $doctor['name'],
            'doctor' => $doctor,
            'schedules' => $this->service->schedules($id),
            'polyclinics' => Polyclinic::query()->where('is_active', '=', 1)->orderBy('name', 'ASC')->get(),
            'rooms' => Room::query()->where('is_active', '=', 1)->orderBy('name', 'ASC')->get(),
        ]);
    }

    public function edit(Request $request, string $id): never
    {
        $doctor = $this->service->find($id);

        $this->view('doctors.edit', [
            'title' => 'Edit Dokter',
            'doctor' => $doctor,
            'specializations' => Specialization::query()->where('is_active', '=', 1)->orderBy('name', 'ASC')->get(),
        ]);
    }

    public function update(Request $request, string $id): never
    {
        $data = $this->validate($request, [
            'str_number' => 'max:50',
            'sip_number' => 'max:50',
            'consultation_fee' => 'required|numeric',
            'bio' => 'max:1000',
            'status' => 'in:ACTIVE,INACTIVE',
        ]);
        $data['specialization_ids'] = (array) $request->input('specialization_ids', []);
        $data['primary_specialization_id'] = $request->input('primary_specialization_id');

        try {
            $this->service->update($id, $data);
        } catch (ValidationException $e) {
            Session::flash('_errors', $e->errors());
            Session::flash('_old_input', $request->all());
            $this->redirect("/doctors/{$id}/edit");
        }

        flash('success', 'Data dokter berhasil diperbarui.');
        $this->redirect('/doctors/' . $id);
    }

    public function destroy(Request $request, string $id): never
    {
        try {
            $this->service->delete($id);
        } catch (ValidationException $e) {
            flash('error', $e->errors()['name'][0] ?? 'Dokter tidak dapat dihapus.');
            $this->redirect('/doctors/' . $id);
        }

        flash('success', 'Dokter berhasil dihapus.');
        $this->redirect('/doctors');
    }

    public function storeSchedule(Request $request, string $id): never
    {
        $data = $this->validate($request, DoctorValidator::scheduleRules());

        try {
            $schedule = $this->service->addSchedule($id, $data);
        } catch (ValidationException $e) {
            $this->fail(current($e->errors())[0] ?? 'Jadwal tidak valid.', ['errors' => $e->errors()], 422);
        }

        $this->success('Jadwal praktik berhasil ditambahkan.', $schedule, 201);
    }

    public function destroySchedule(Request $request, string $id, string $scheduleId): never
    {
        $this->service->deleteSchedule($id, $scheduleId);
        $this->success('Jadwal praktik berhasil dihapus.');
    }
}
