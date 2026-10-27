<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Exceptions\ValidationException;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Polyclinic;
use App\Models\Room;
use App\Services\RegistrationService;
use App\Validators\RegistrationValidator;

class RegistrationController extends Controller
{
    public function __construct(private readonly RegistrationService $service = new RegistrationService())
    {
    }

    public function index(Request $request): never
    {
        $filters = [
            'search' => (string) $request->query('search', ''),
            'date' => (string) $request->query('date', date('Y-m-d')),
            'status' => (string) $request->query('status', ''),
        ];

        $page = max(1, (int) $request->query('page', 1));
        $result = $this->service->paginate($filters, $page, 20);

        $this->view('registration.index', [
            'title' => 'Pendaftaran',
            'registrations' => $result['data'],
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

        $this->view('registration.create', [
            'title' => 'Pendaftaran Kunjungan',
            'patient' => $patient,
            'polyclinics' => Polyclinic::query()->where('is_active', '=', 1)->orderBy('name', 'ASC')->get(),
            'rooms' => Room::query()->where('is_active', '=', 1)->orderBy('name', 'ASC')->get(),
        ]);
    }

    public function store(Request $request): never
    {
        $data = $this->validate($request, RegistrationValidator::rules());

        try {
            $registration = $this->service->create($data);
        } catch (ValidationException $e) {
            Session::flash('_errors', $e->errors());
            Session::flash('_old_input', $request->all());
            $this->redirect('/registrations/create');
        }

        flash('success', 'Pendaftaran berhasil. Nomor antrian: ' . $registration['queue_number']);
        $this->redirect('/registrations/' . $registration['id']);
    }

    public function show(Request $request, string $id): never
    {
        $registration = $this->service->find($id);

        $this->view('registration.show', [
            'title' => 'Detail Pendaftaran',
            'registration' => $registration,
            'medicalRecord' => MedicalRecord::findByRegistration($id),
        ]);
    }

    public function cancel(Request $request, string $id): never
    {
        try {
            $this->service->cancel($id);
        } catch (ValidationException $e) {
            flash('error', current($e->errors())[0] ?? 'Registrasi tidak dapat dibatalkan.');
            $this->back($request, '/registrations');
        }

        flash('success', 'Registrasi berhasil dibatalkan.');
        $this->back($request, '/registrations');
    }
}
