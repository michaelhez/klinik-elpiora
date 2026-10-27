<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\BranchService;
use App\Services\ClinicService;
use App\Services\CorporateService;
use App\Services\DepartmentService;
use App\Services\Icd10Service;
use App\Services\InsuranceService;
use App\Services\MedicalActionCategoryService;
use App\Services\MedicalActionService;
use App\Services\MedicineCategoryService;
use App\Services\MedicineUnitService;
use App\Services\PolyclinicService;
use App\Services\PositionService;
use App\Services\RoomService;
use App\Services\SpecializationService;

class SettingsController extends Controller
{
    public function organization(Request $request): never
    {
        $canViewAny = $this->can('clinic.view') || $this->can('branch.view') || $this->can('department.view')
            || $this->can('polyclinic.view') || $this->can('room.view') || $this->can('position.view')
            || $this->can('specialization.view');

        $this->authorize($canViewAny, 'Anda tidak memiliki akses ke Data Organisasi.');

        $this->view('settings.organization', [
            'title' => 'Data Organisasi',
            'clinics' => $this->can('clinic.view') ? (new ClinicService())->list() : [],
            'branches' => $this->can('branch.view') ? (new BranchService())->list() : [],
            'departments' => $this->can('department.view') ? (new DepartmentService())->list() : [],
            'polyclinics' => $this->can('polyclinic.view') ? (new PolyclinicService())->list() : [],
            'rooms' => $this->can('room.view') ? (new RoomService())->list() : [],
            'positions' => $this->can('position.view') ? (new PositionService())->list() : [],
            'specializations' => $this->can('specialization.view') ? (new SpecializationService())->list() : [],
        ]);
    }

    public function medical(Request $request): never
    {
        $canViewAny = $this->can('icd10.view') || $this->can('medical_action_category.view') || $this->can('medical_action.view');

        $this->authorize($canViewAny, 'Anda tidak memiliki akses ke Data Medis.');

        $this->view('settings.medical', [
            'title' => 'Data Medis',
            'icd10Codes' => $this->can('icd10.view') ? (new Icd10Service())->list() : [],
            'actionCategories' => $this->can('medical_action_category.view') ? (new MedicalActionCategoryService())->list() : [],
            'medicalActions' => $this->can('medical_action.view') ? (new MedicalActionService())->list() : [],
        ]);
    }

    public function pharmacy(Request $request): never
    {
        $canViewAny = $this->can('medicine_category.view') || $this->can('medicine_unit.view');

        $this->authorize($canViewAny, 'Anda tidak memiliki akses ke Data Farmasi.');

        $this->view('settings.pharmacy', [
            'title' => 'Data Farmasi',
            'categories' => $this->can('medicine_category.view') ? (new MedicineCategoryService())->list() : [],
            'units' => $this->can('medicine_unit.view') ? (new MedicineUnitService())->list() : [],
        ]);
    }

    public function finance(Request $request): never
    {
        $canViewAny = $this->can('insurance.view') || $this->can('corporate.view');

        $this->authorize($canViewAny, 'Anda tidak memiliki akses ke Data Keuangan.');

        $this->view('settings.finance', [
            'title' => 'Data Keuangan',
            'insurances' => $this->can('insurance.view') ? (new InsuranceService())->list() : [],
            'corporates' => $this->can('corporate.view') ? (new CorporateService())->list() : [],
        ]);
    }
}
