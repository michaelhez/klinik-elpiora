<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Exceptions\ValidationException;
use App\Models\Invoice;
use App\Models\MedicalActionCategory;
use App\Services\BillingService;
use App\Services\LabRequestService;
use App\Services\MedicalDocumentService;
use App\Services\MedicalRecordService;
use App\Services\PrescriptionService;
use App\Validators\MedicalRecordValidator;

class MedicalRecordController extends Controller
{
    public function __construct(
        private readonly MedicalRecordService $service = new MedicalRecordService(),
        private readonly LabRequestService $labRequestService = new LabRequestService(),
        private readonly PrescriptionService $prescriptionService = new PrescriptionService(),
        private readonly MedicalDocumentService $documentService = new MedicalDocumentService(),
    ) {
    }

    public function index(Request $request): never
    {
        $search = (string) $request->query('search', '');
        $status = (string) $request->query('status', '');

        $conditions = ['1 = 1'];
        $bindings = [];

        if ($search !== '') {
            $conditions[] = '(p.name LIKE :search1 OR mr.record_number LIKE :search2)';
            $bindings['search1'] = "%{$search}%";
            $bindings['search2'] = "%{$search}%";
        }

        if ($status !== '') {
            $conditions[] = 'mr.status = :status';
            $bindings['status'] = $status;
        }

        $sql = 'SELECT mr.*, p.name AS patient_name, p.medical_record_number, e.name AS doctor_name
                FROM medical_records mr
                INNER JOIN patients p ON p.id = mr.patient_id
                INNER JOIN doctors d ON d.id = mr.doctor_id
                INNER JOIN employees e ON e.id = d.employee_id
                WHERE ' . implode(' AND ', $conditions) . '
                ORDER BY mr.created_at DESC LIMIT 100';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($bindings);

        $this->view('medical-records.index', [
            'title' => 'Rekam Medis',
            'records' => $stmt->fetchAll(),
            'filters' => ['search' => $search, 'status' => $status],
        ]);
    }

    public function create(Request $request): never
    {
        $registrationId = $request->input('registration_id');

        try {
            $record = $this->service->createFromRegistration($registrationId);
        } catch (ValidationException $e) {
            flash('error', current($e->errors())[0] ?? 'Rekam medis tidak dapat dibuat.');
            $this->back($request, '/registrations');
        }

        $this->redirect('/medical-records/' . $record['id']);
    }

    public function show(Request $request, string $id): never
    {
        $record = $this->service->find($id);

        $this->view('medical-records.show', [
            'title' => $record['record_number'],
            'record' => $record,
            'vitals' => $this->service->vitals($id),
            'anamnesis' => $this->service->anamnesis($id),
            'physicalExam' => $this->service->physicalExamination($id),
            'diagnoses' => $this->service->diagnoses($id),
            'actions' => $this->service->actions($id),
            'notes' => $this->service->notes($id),
            'histories' => $this->service->histories($id),
            'actionCategories' => MedicalActionCategory::query()->where('is_active', '=', 1)->get(),
            'labRequests' => $this->labRequestService->forMedicalRecord($id),
            'prescriptions' => $this->prescriptionService->forMedicalRecord($id),
            'invoice' => Invoice::forRegistration($record['registration_id']),
            'documents' => $this->documentService->forMedicalRecord($id),
        ]);
    }

    public function storeVitals(Request $request, string $id): never
    {
        $data = $this->validate($request, MedicalRecordValidator::vitalsRules());

        try {
            $vitals = $this->service->upsertVitals($id, $data);
        } catch (ValidationException $e) {
            $this->fail(current($e->errors())[0] ?? 'Data tidak valid.', ['errors' => $e->errors()], 422);
        }

        $this->success('Tanda vital berhasil disimpan.', $vitals);
    }

    public function storeAnamnesis(Request $request, string $id): never
    {
        $data = $this->validate($request, MedicalRecordValidator::anamnesisRules());

        try {
            $anamnesis = $this->service->upsertAnamnesis($id, $data);
        } catch (ValidationException $e) {
            $this->fail(current($e->errors())[0] ?? 'Data tidak valid.', ['errors' => $e->errors()], 422);
        }

        $this->success('Anamnesis berhasil disimpan.', $anamnesis);
    }

    public function storePhysicalExamination(Request $request, string $id): never
    {
        $data = $this->validate($request, MedicalRecordValidator::physicalExaminationRules());

        try {
            $exam = $this->service->upsertPhysicalExamination($id, $data);
        } catch (ValidationException $e) {
            $this->fail(current($e->errors())[0] ?? 'Data tidak valid.', ['errors' => $e->errors()], 422);
        }

        $this->success('Pemeriksaan fisik berhasil disimpan.', $exam);
    }

    public function storeDiagnosis(Request $request, string $id): never
    {
        $data = $this->validate($request, MedicalRecordValidator::diagnosisRules());

        try {
            $diagnoses = $this->service->addDiagnosis($id, $data);
        } catch (ValidationException $e) {
            $this->fail(current($e->errors())[0] ?? 'Data tidak valid.', ['errors' => $e->errors()], 422);
        }

        $this->success('Diagnosis berhasil ditambahkan.', $diagnoses, 201);
    }

    public function destroyDiagnosis(Request $request, string $id, string $diagnosisId): never
    {
        try {
            $diagnoses = $this->service->removeDiagnosis($id, $diagnosisId);
        } catch (ValidationException $e) {
            $this->fail(current($e->errors())[0] ?? 'Tidak dapat menghapus diagnosis.', [], 422);
        }

        $this->success('Diagnosis berhasil dihapus.', $diagnoses);
    }

    public function storeAction(Request $request, string $id): never
    {
        $data = $this->validate($request, MedicalRecordValidator::actionRules());

        try {
            $actions = $this->service->addAction($id, $data);
        } catch (ValidationException $e) {
            $this->fail(current($e->errors())[0] ?? 'Data tidak valid.', ['errors' => $e->errors()], 422);
        }

        $this->success('Tindakan berhasil ditambahkan.', $actions, 201);
    }

    public function destroyAction(Request $request, string $id, string $actionId): never
    {
        try {
            $actions = $this->service->removeAction($id, $actionId);
        } catch (ValidationException $e) {
            $this->fail(current($e->errors())[0] ?? 'Tidak dapat menghapus tindakan.', [], 422);
        }

        $this->success('Tindakan berhasil dihapus.', $actions);
    }

    public function storeNote(Request $request, string $id): never
    {
        $data = $this->validate($request, MedicalRecordValidator::noteRules());

        try {
            $notes = $this->service->addNote($id, $data['note']);
        } catch (ValidationException $e) {
            $this->fail(current($e->errors())[0] ?? 'Data tidak valid.', [], 422);
        }

        $this->success('Catatan berhasil ditambahkan.', $notes, 201);
    }

    public function finalize(Request $request, string $id): never
    {
        try {
            $this->service->finalize($id);
        } catch (ValidationException $e) {
            $this->fail(current($e->errors())[0] ?? 'Rekam medis belum dapat difinalisasi.', ['errors' => $e->errors()], 422);
        }

        $this->success('Rekam medis berhasil difinalisasi.');
    }

    public function amend(Request $request, string $id): never
    {
        $meta = $this->validate($request, MedicalRecordValidator::amendRules());
        $section = $meta['section'];

        $rules = $section === 'anamnesis'
            ? MedicalRecordValidator::anamnesisRules()
            : MedicalRecordValidator::physicalExaminationRules();

        $newData = [];
        foreach (array_keys($rules) as $field) {
            $newData[$field] = $request->input($field, '');
        }

        try {
            $this->service->amend($id, $section, $newData, $meta['reason']);
        } catch (ValidationException $e) {
            $this->fail(current($e->errors())[0] ?? 'Amendemen gagal.', ['errors' => $e->errors()], 422);
        }

        $this->success('Amendemen berhasil disimpan.');
    }
}
