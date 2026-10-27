<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Doctor;
use App\Models\Icd10Code;
use App\Models\LabTest;
use App\Models\MedicalAction;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Patient;

class SearchController extends Controller
{
    public function patients(Request $request): never
    {
        $term = trim((string) $request->query('q', ''));

        if (mb_strlen($term) < 2) {
            $this->success('OK', []);
        }

        $this->success('OK', Patient::search($term));
    }

    public function doctors(Request $request): never
    {
        $polyclinicId = $request->query('polyclinic_id');

        $this->success('OK', Doctor::activeWithNames($polyclinicId ? (int) $polyclinicId : null));
    }

    public function icd10(Request $request): never
    {
        $term = trim((string) $request->query('q', ''));

        if (mb_strlen($term) < 2) {
            $this->success('OK', []);
        }

        $this->success('OK', Icd10Code::search($term));
    }

    public function medicalActions(Request $request): never
    {
        $term = trim((string) $request->query('q', ''));

        if (mb_strlen($term) < 1) {
            $this->success('OK', []);
        }

        $this->success('OK', MedicalAction::search($term));
    }

    public function labTests(Request $request): never
    {
        $term = trim((string) $request->query('q', ''));

        if (mb_strlen($term) < 1) {
            $this->success('OK', []);
        }

        $this->success('OK', LabTest::search($term));
    }

    public function medicines(Request $request): never
    {
        $term = trim((string) $request->query('q', ''));

        if (mb_strlen($term) < 1) {
            $this->success('OK', []);
        }

        $this->success('OK', Medicine::search($term));
    }

    public function medicineBatches(Request $request, string $id): never
    {
        $batches = array_values(array_filter(
            MedicineBatch::forMedicine($id),
            fn (array $batch) => (int) $batch['quantity'] > 0,
        ));

        $this->success('OK', $batches);
    }
}
