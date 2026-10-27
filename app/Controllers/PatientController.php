<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Exceptions\ValidationException;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Registration;
use App\Services\PatientService;
use App\Validators\PatientValidator;

class PatientController extends Controller
{
    public function __construct(private readonly PatientService $service = new PatientService())
    {
    }

    public function index(Request $request): never
    {
        $filters = [
            'search' => (string) $request->query('search', ''),
            'gender' => (string) $request->query('gender', ''),
            'status' => (string) $request->query('status', ''),
        ];

        $page = max(1, (int) $request->query('page', 1));
        $perPage = (int) $request->query('per_page', 20);
        $perPage = in_array($perPage, [10, 20, 50, 100], true) ? $perPage : 20;

        $result = $this->service->paginate($filters, $page, $perPage);

        $this->view('patients.index', [
            'title' => 'Pasien',
            'patients' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
        ]);
    }

    public function create(Request $request): never
    {
        $this->view('patients.create', ['title' => 'Tambah Pasien']);
    }

    public function store(Request $request): never
    {
        $data = $this->validate($request, PatientValidator::rules());

        try {
            $patient = $this->service->create($data, $request->file('photo'));
        } catch (ValidationException $e) {
            Session::flash('_errors', $e->errors());
            Session::flash('_old_input', $request->all());
            $this->redirect('/patients/create');
        }

        flash('success', 'Pasien berhasil ditambahkan.');
        $this->redirect('/patients/' . $patient['id']);
    }

    public function show(Request $request, string $id): never
    {
        $patient = $this->service->find($id);

        $this->view('patients.show', [
            'title' => $patient['name'],
            'patient' => $patient,
            'contacts' => $this->service->contacts($id),
            'emergencyContacts' => $this->service->emergencyContacts($id),
            'allergies' => $this->service->allergies($id),
            'medicalHistories' => $this->service->medicalHistories($id),
            'appointments' => Appointment::forPatient($id),
            'registrations' => Registration::forPatient($id),
            'medicalRecords' => MedicalRecord::forPatient($id),
        ]);
    }

    public function edit(Request $request, string $id): never
    {
        $patient = $this->service->find($id);

        $this->view('patients.edit', [
            'title' => 'Edit Pasien',
            'patient' => $patient,
        ]);
    }

    public function update(Request $request, string $id): never
    {
        $data = $this->validate($request, PatientValidator::rules());

        try {
            $this->service->update($id, $data, $request->file('photo'));
        } catch (ValidationException $e) {
            Session::flash('_errors', $e->errors());
            Session::flash('_old_input', $request->all());
            $this->redirect("/patients/{$id}/edit");
        }

        flash('success', 'Data pasien berhasil diperbarui.');
        $this->redirect('/patients/' . $id);
    }

    public function destroy(Request $request, string $id): never
    {
        $this->service->delete($id);
        flash('success', 'Pasien berhasil dihapus.');
        $this->redirect('/patients');
    }

    public function storeAllergy(Request $request, string $id): never
    {
        $data = $this->validate($request, PatientValidator::allergyRules());
        $allergy = $this->service->addAllergy($id, $data);
        $this->success('Alergi berhasil ditambahkan.', $allergy, 201);
    }

    public function destroyAllergy(Request $request, string $id, string $allergyId): never
    {
        $this->service->deleteAllergy($id, $allergyId);
        $this->success('Alergi berhasil dihapus.');
    }

    public function storeMedicalHistory(Request $request, string $id): never
    {
        $data = $this->validate($request, PatientValidator::medicalHistoryRules());
        $history = $this->service->addMedicalHistory($id, $data);
        $this->success('Riwayat medis berhasil ditambahkan.', $history, 201);
    }

    public function destroyMedicalHistory(Request $request, string $id, string $historyId): never
    {
        $this->service->deleteMedicalHistory($id, $historyId);
        $this->success('Riwayat medis berhasil dihapus.');
    }

    public function storeEmergencyContact(Request $request, string $id): never
    {
        $data = $this->validate($request, PatientValidator::emergencyContactRules());
        $contact = $this->service->addEmergencyContact($id, $data);
        $this->success('Kontak darurat berhasil ditambahkan.', $contact, 201);
    }

    public function destroyEmergencyContact(Request $request, string $id, string $contactId): never
    {
        $this->service->deleteEmergencyContact($id, $contactId);
        $this->success('Kontak darurat berhasil dihapus.');
    }
}
