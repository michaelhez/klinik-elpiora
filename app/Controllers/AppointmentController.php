<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Exceptions\ValidationException;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Polyclinic;
use App\Services\AppointmentService;
use App\Validators\AppointmentValidator;

class AppointmentController extends Controller
{
    public function __construct(private readonly AppointmentService $service = new AppointmentService())
    {
    }

    public function index(Request $request): never
    {
        $filters = [
            'search' => (string) $request->query('search', ''),
            'date' => (string) $request->query('date', ''),
            'status' => (string) $request->query('status', ''),
        ];

        $page = max(1, (int) $request->query('page', 1));
        $result = $this->service->paginate($filters, $page, 20);

        $this->view('appointments.index', [
            'title' => 'Janji Temu',
            'appointments' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
        ]);
    }

    public function create(Request $request): never
    {
        $patient = null;
        if ($request->query('patient_id')) {
            $patient = Patient::find((string) $request->query('patient_id'));
        }

        $this->view('appointments.create', [
            'title' => 'Buat Janji Temu',
            'patient' => $patient,
            'doctors' => Doctor::activeWithNames(),
            'polyclinics' => Polyclinic::query()->where('is_active', '=', 1)->orderBy('name', 'ASC')->get(),
        ]);
    }

    public function store(Request $request): never
    {
        $data = $this->validate($request, AppointmentValidator::rules());

        try {
            $appointment = $this->service->create($data);
        } catch (ValidationException $e) {
            Session::flash('_errors', $e->errors());
            Session::flash('_old_input', $request->all());
            $this->redirect('/appointments/create');
        }

        flash('success', 'Janji temu berhasil dibuat.');
        $this->redirect('/appointments');
    }

    public function cancel(Request $request, string $id): never
    {
        $this->service->cancel($id);
        flash('success', 'Janji temu berhasil dibatalkan.');
        $this->back($request, '/appointments');
    }
}
