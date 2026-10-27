<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Exceptions\ValidationException;
use App\Services\LabRequestService;
use App\Validators\LabRequestValidator;

class LabRequestController extends Controller
{
    public function __construct(private readonly LabRequestService $service = new LabRequestService())
    {
    }

    public function index(Request $request): never
    {
        $status = (string) $request->query('status', '');
        $statuses = $status !== '' ? [$status] : [];

        $this->view('lab-requests.index', [
            'title' => 'Laboratorium',
            'requests' => $this->service->queue($statuses),
            'filters' => ['status' => $status],
        ]);
    }

    public function store(Request $request): never
    {
        $data = $this->validate($request, LabRequestValidator::rules());
        $data['lab_test_ids'] = (array) $request->input('lab_test_ids', []);

        try {
            $labRequest = $this->service->create($data);
        } catch (ValidationException $e) {
            flash('error', current($e->errors())[0] ?? 'Permintaan lab tidak dapat dibuat.');
            $this->back($request, '/medical-records/' . $data['medical_record_id']);
        }

        flash('success', 'Permintaan pemeriksaan lab berhasil dibuat: ' . $labRequest['request_number']);
        $this->back($request, '/medical-records/' . $data['medical_record_id']);
    }

    public function show(Request $request, string $id): never
    {
        $labRequest = $this->service->find($id);

        $this->view('lab-requests.show', [
            'title' => $labRequest['request_number'],
            'labRequest' => $labRequest,
        ]);
    }

    public function collectSample(Request $request, string $id): never
    {
        $data = $this->validate($request, LabRequestValidator::sampleRules());

        try {
            $this->service->collectSample($id, $data);
        } catch (ValidationException $e) {
            Session::flash('_errors', $e->errors());
            $this->back($request, '/lab-requests/' . $id);
        }

        flash('success', 'Sampel berhasil dicatat.');
        $this->back($request, '/lab-requests/' . $id);
    }

    public function submitResult(Request $request, string $id, string $itemId): never
    {
        $values = (array) $request->input('values', []);

        try {
            $this->service->submitResult($id, $itemId, $values);
        } catch (ValidationException $e) {
            flash('error', current($e->errors())[0] ?? 'Hasil tidak dapat disimpan.');
            $this->back($request, '/lab-requests/' . $id);
        }

        flash('success', 'Hasil pemeriksaan berhasil disimpan.');
        $this->back($request, '/lab-requests/' . $id);
    }

    public function validateRequest(Request $request, string $id): never
    {
        try {
            $this->service->validateRequest($id);
        } catch (ValidationException $e) {
            flash('error', current($e->errors())[0] ?? 'Permintaan belum dapat divalidasi.');
            $this->back($request, '/lab-requests/' . $id);
        }

        flash('success', 'Hasil pemeriksaan berhasil divalidasi.');
        $this->back($request, '/lab-requests/' . $id);
    }

    public function cancel(Request $request, string $id): never
    {
        try {
            $this->service->cancel($id);
        } catch (ValidationException $e) {
            flash('error', current($e->errors())[0] ?? 'Permintaan tidak dapat dibatalkan.');
            $this->back($request, '/lab-requests/' . $id);
        }

        flash('success', 'Permintaan lab berhasil dibatalkan.');
        $this->back($request, '/lab-requests');
    }
}
