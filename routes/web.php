<?php

declare(strict_types=1);

use App\Controllers\AppointmentController;
use App\Controllers\AuditLogController;
use App\Controllers\AuthController;
use App\Controllers\BillingController;
use App\Controllers\DashboardController;
use App\Controllers\DoctorController;
use App\Controllers\EmployeeController;
use App\Controllers\InventoryController;
use App\Controllers\LabRequestController;
use App\Controllers\MedicalDocumentController;
use App\Controllers\MedicalRecordController;
use App\Controllers\MedicineController;
use App\Controllers\PatientController;
use App\Controllers\PaymentController;
use App\Controllers\PharmacyController;
use App\Controllers\PrescriptionController;
use App\Controllers\PurchaseOrderController;
use App\Controllers\QueueController;
use App\Controllers\RegistrationController;
use App\Controllers\ReportController;
use App\Controllers\SettingsController;
use App\Controllers\Settings\BackupController;
use App\Controllers\Settings\BranchController;
use App\Controllers\Settings\ClinicController;
use App\Controllers\Settings\CorporateController;
use App\Controllers\Settings\DepartmentController;
use App\Controllers\Settings\Icd10Controller;
use App\Controllers\Settings\InsuranceController;
use App\Controllers\Settings\LabTestController;
use App\Controllers\Settings\MedicalActionCategoryController;
use App\Controllers\Settings\MedicalActionController;
use App\Controllers\Settings\MedicineCategoryController;
use App\Controllers\Settings\MedicineUnitController;
use App\Controllers\Settings\PolyclinicController;
use App\Controllers\Settings\PositionController;
use App\Controllers\Settings\RoomController;
use App\Controllers\Settings\SpecializationController;
use App\Controllers\Settings\SystemController;
use App\Controllers\RoleController;
use App\Controllers\SupplierController;
use App\Controllers\SupplierReturnController;
use App\Controllers\UserController;
use App\Core\Router;

/** @var Router $router */

$router->get('/', function () {
    App\Core\Response::redirect('/dashboard');
});

$router->group(['CsrfMiddleware'], function (Router $router) {
    $router->group(['GuestMiddleware'], function (Router $router) {
        $router->get('/login', [AuthController::class, 'showLogin']);
        $router->post('/login', [AuthController::class, 'login']);
    });

    $router->group(['AuthMiddleware'], function (Router $router) {
        $router->post('/logout', [AuthController::class, 'logout']);

        $router->get('/dashboard', [DashboardController::class, 'index'], ['PermissionMiddleware:dashboard.view']);

        $router->get('/settings/organization', [SettingsController::class, 'organization']);

        $router->get('/settings/organization/clinics', [ClinicController::class, 'index'], ['PermissionMiddleware:clinic.view']);
        $router->post('/settings/organization/clinics', [ClinicController::class, 'store'], ['PermissionMiddleware:clinic.create']);
        $router->put('/settings/organization/clinics/{id}', [ClinicController::class, 'update'], ['PermissionMiddleware:clinic.update']);
        $router->delete('/settings/organization/clinics/{id}', [ClinicController::class, 'destroy'], ['PermissionMiddleware:clinic.delete']);

        $router->get('/settings/organization/branches', [BranchController::class, 'index'], ['PermissionMiddleware:branch.view']);
        $router->post('/settings/organization/branches', [BranchController::class, 'store'], ['PermissionMiddleware:branch.create']);
        $router->put('/settings/organization/branches/{id}', [BranchController::class, 'update'], ['PermissionMiddleware:branch.update']);
        $router->delete('/settings/organization/branches/{id}', [BranchController::class, 'destroy'], ['PermissionMiddleware:branch.delete']);

        $router->get('/settings/organization/departments', [DepartmentController::class, 'index'], ['PermissionMiddleware:department.view']);
        $router->post('/settings/organization/departments', [DepartmentController::class, 'store'], ['PermissionMiddleware:department.create']);
        $router->put('/settings/organization/departments/{id}', [DepartmentController::class, 'update'], ['PermissionMiddleware:department.update']);
        $router->delete('/settings/organization/departments/{id}', [DepartmentController::class, 'destroy'], ['PermissionMiddleware:department.delete']);

        $router->get('/settings/organization/polyclinics', [PolyclinicController::class, 'index'], ['PermissionMiddleware:polyclinic.view']);
        $router->post('/settings/organization/polyclinics', [PolyclinicController::class, 'store'], ['PermissionMiddleware:polyclinic.create']);
        $router->put('/settings/organization/polyclinics/{id}', [PolyclinicController::class, 'update'], ['PermissionMiddleware:polyclinic.update']);
        $router->delete('/settings/organization/polyclinics/{id}', [PolyclinicController::class, 'destroy'], ['PermissionMiddleware:polyclinic.delete']);

        $router->get('/settings/organization/rooms', [RoomController::class, 'index'], ['PermissionMiddleware:room.view']);
        $router->post('/settings/organization/rooms', [RoomController::class, 'store'], ['PermissionMiddleware:room.create']);
        $router->put('/settings/organization/rooms/{id}', [RoomController::class, 'update'], ['PermissionMiddleware:room.update']);
        $router->delete('/settings/organization/rooms/{id}', [RoomController::class, 'destroy'], ['PermissionMiddleware:room.delete']);

        $router->get('/settings/organization/positions', [PositionController::class, 'index'], ['PermissionMiddleware:position.view']);
        $router->post('/settings/organization/positions', [PositionController::class, 'store'], ['PermissionMiddleware:position.create']);
        $router->put('/settings/organization/positions/{id}', [PositionController::class, 'update'], ['PermissionMiddleware:position.update']);
        $router->delete('/settings/organization/positions/{id}', [PositionController::class, 'destroy'], ['PermissionMiddleware:position.delete']);

        $router->get('/settings/organization/specializations', [SpecializationController::class, 'index'], ['PermissionMiddleware:specialization.view']);
        $router->post('/settings/organization/specializations', [SpecializationController::class, 'store'], ['PermissionMiddleware:specialization.create']);
        $router->put('/settings/organization/specializations/{id}', [SpecializationController::class, 'update'], ['PermissionMiddleware:specialization.update']);
        $router->delete('/settings/organization/specializations/{id}', [SpecializationController::class, 'destroy'], ['PermissionMiddleware:specialization.delete']);

        $router->get('/settings/medical', [SettingsController::class, 'medical']);

        $router->get('/settings/medical/icd10', [Icd10Controller::class, 'index'], ['PermissionMiddleware:icd10.view']);
        $router->post('/settings/medical/icd10', [Icd10Controller::class, 'store'], ['PermissionMiddleware:icd10.create']);
        $router->put('/settings/medical/icd10/{id}', [Icd10Controller::class, 'update'], ['PermissionMiddleware:icd10.update']);
        $router->delete('/settings/medical/icd10/{id}', [Icd10Controller::class, 'destroy'], ['PermissionMiddleware:icd10.delete']);

        $router->get('/settings/medical/action-categories', [MedicalActionCategoryController::class, 'index'], ['PermissionMiddleware:medical_action_category.view']);
        $router->post('/settings/medical/action-categories', [MedicalActionCategoryController::class, 'store'], ['PermissionMiddleware:medical_action_category.create']);
        $router->put('/settings/medical/action-categories/{id}', [MedicalActionCategoryController::class, 'update'], ['PermissionMiddleware:medical_action_category.update']);
        $router->delete('/settings/medical/action-categories/{id}', [MedicalActionCategoryController::class, 'destroy'], ['PermissionMiddleware:medical_action_category.delete']);

        $router->get('/settings/medical/actions', [MedicalActionController::class, 'index'], ['PermissionMiddleware:medical_action.view']);
        $router->post('/settings/medical/actions', [MedicalActionController::class, 'store'], ['PermissionMiddleware:medical_action.create']);
        $router->put('/settings/medical/actions/{id}', [MedicalActionController::class, 'update'], ['PermissionMiddleware:medical_action.update']);
        $router->delete('/settings/medical/actions/{id}', [MedicalActionController::class, 'destroy'], ['PermissionMiddleware:medical_action.delete']);

        $router->get('/settings/lab-tests', [LabTestController::class, 'index'], ['PermissionMiddleware:lab_test.view']);
        $router->get('/settings/lab-tests/create', [LabTestController::class, 'create'], ['PermissionMiddleware:lab_test.create']);
        $router->post('/settings/lab-tests', [LabTestController::class, 'store'], ['PermissionMiddleware:lab_test.create']);
        $router->get('/settings/lab-tests/{id}', [LabTestController::class, 'show'], ['PermissionMiddleware:lab_test.view']);
        $router->get('/settings/lab-tests/{id}/edit', [LabTestController::class, 'edit'], ['PermissionMiddleware:lab_test.update']);
        $router->post('/settings/lab-tests/{id}/update', [LabTestController::class, 'update'], ['PermissionMiddleware:lab_test.update']);
        $router->post('/settings/lab-tests/{id}/delete', [LabTestController::class, 'destroy'], ['PermissionMiddleware:lab_test.delete']);
        $router->post('/settings/lab-tests/{id}/parameters', [LabTestController::class, 'storeParameter'], ['PermissionMiddleware:lab_test.update']);
        $router->put('/settings/lab-tests/{id}/parameters/{parameterId}', [LabTestController::class, 'updateParameter'], ['PermissionMiddleware:lab_test.update']);
        $router->delete('/settings/lab-tests/{id}/parameters/{parameterId}', [LabTestController::class, 'destroyParameter'], ['PermissionMiddleware:lab_test.delete']);

        $router->get('/settings/pharmacy', [SettingsController::class, 'pharmacy']);

        $router->get('/settings/pharmacy/categories', [MedicineCategoryController::class, 'index'], ['PermissionMiddleware:medicine_category.view']);
        $router->post('/settings/pharmacy/categories', [MedicineCategoryController::class, 'store'], ['PermissionMiddleware:medicine_category.create']);
        $router->put('/settings/pharmacy/categories/{id}', [MedicineCategoryController::class, 'update'], ['PermissionMiddleware:medicine_category.update']);
        $router->delete('/settings/pharmacy/categories/{id}', [MedicineCategoryController::class, 'destroy'], ['PermissionMiddleware:medicine_category.delete']);

        $router->get('/settings/pharmacy/units', [MedicineUnitController::class, 'index'], ['PermissionMiddleware:medicine_unit.view']);
        $router->post('/settings/pharmacy/units', [MedicineUnitController::class, 'store'], ['PermissionMiddleware:medicine_unit.create']);
        $router->put('/settings/pharmacy/units/{id}', [MedicineUnitController::class, 'update'], ['PermissionMiddleware:medicine_unit.update']);
        $router->delete('/settings/pharmacy/units/{id}', [MedicineUnitController::class, 'destroy'], ['PermissionMiddleware:medicine_unit.delete']);

        $router->get('/settings/finance', [SettingsController::class, 'finance']);

        $router->get('/settings/finance/insurances', [InsuranceController::class, 'index'], ['PermissionMiddleware:insurance.view']);
        $router->post('/settings/finance/insurances', [InsuranceController::class, 'store'], ['PermissionMiddleware:insurance.create']);
        $router->put('/settings/finance/insurances/{id}', [InsuranceController::class, 'update'], ['PermissionMiddleware:insurance.update']);

        $router->get('/settings/finance/corporates', [CorporateController::class, 'index'], ['PermissionMiddleware:corporate.view']);
        $router->post('/settings/finance/corporates', [CorporateController::class, 'store'], ['PermissionMiddleware:corporate.create']);
        $router->put('/settings/finance/corporates/{id}', [CorporateController::class, 'update'], ['PermissionMiddleware:corporate.update']);

        $router->get('/medicines', [MedicineController::class, 'index'], ['PermissionMiddleware:medicine.view']);
        $router->get('/medicines/create', [MedicineController::class, 'create'], ['PermissionMiddleware:medicine.create']);
        $router->post('/medicines', [MedicineController::class, 'store'], ['PermissionMiddleware:medicine.create']);
        $router->get('/medicines/{id}', [MedicineController::class, 'show'], ['PermissionMiddleware:medicine.view']);
        $router->get('/medicines/{id}/edit', [MedicineController::class, 'edit'], ['PermissionMiddleware:medicine.update']);
        $router->post('/medicines/{id}/update', [MedicineController::class, 'update'], ['PermissionMiddleware:medicine.update']);
        $router->post('/medicines/{id}/delete', [MedicineController::class, 'destroy'], ['PermissionMiddleware:medicine.delete']);
        $router->post('/medicines/{id}/batches', [MedicineController::class, 'storeBatch'], ['PermissionMiddleware:inventory.adjust']);
        $router->post('/medicines/{id}/batches/adjust', [InventoryController::class, 'adjust'], ['PermissionMiddleware:inventory.adjust']);
        $router->post('/medicines/{id}/batches/transfer', [InventoryController::class, 'transfer'], ['PermissionMiddleware:inventory.transfer']);

        $router->get('/inventory', [InventoryController::class, 'index'], ['PermissionMiddleware:inventory.view']);

        $router->get('/suppliers', [SupplierController::class, 'index'], ['PermissionMiddleware:supplier.view']);
        $router->post('/suppliers', [SupplierController::class, 'store'], ['PermissionMiddleware:supplier.create']);
        $router->put('/suppliers/{id}', [SupplierController::class, 'update'], ['PermissionMiddleware:supplier.update']);
        $router->delete('/suppliers/{id}', [SupplierController::class, 'destroy'], ['PermissionMiddleware:supplier.delete']);

        $router->get('/purchase-orders', [PurchaseOrderController::class, 'index'], ['PermissionMiddleware:purchase_order.view']);
        $router->get('/purchase-orders/create', [PurchaseOrderController::class, 'create'], ['PermissionMiddleware:purchase_order.create']);
        $router->post('/purchase-orders', [PurchaseOrderController::class, 'store'], ['PermissionMiddleware:purchase_order.create']);
        $router->get('/purchase-orders/{id}', [PurchaseOrderController::class, 'show'], ['PermissionMiddleware:purchase_order.view']);
        $router->post('/purchase-orders/{id}/submit', [PurchaseOrderController::class, 'submit'], ['PermissionMiddleware:purchase_order.create']);
        $router->post('/purchase-orders/{id}/approve', [PurchaseOrderController::class, 'approve'], ['PermissionMiddleware:purchase_order.approve']);
        $router->post('/purchase-orders/{id}/mark-ordered', [PurchaseOrderController::class, 'markOrdered'], ['PermissionMiddleware:purchase_order.receive']);
        $router->post('/purchase-orders/{id}/cancel', [PurchaseOrderController::class, 'cancel'], ['PermissionMiddleware:purchase_order.create']);
        $router->post('/purchase-orders/{id}/receive', [PurchaseOrderController::class, 'receive'], ['PermissionMiddleware:purchase_order.receive']);

        $router->get('/supplier-returns', [SupplierReturnController::class, 'index'], ['PermissionMiddleware:supplier_return.view']);
        $router->get('/supplier-returns/create', [SupplierReturnController::class, 'create'], ['PermissionMiddleware:supplier_return.create']);
        $router->post('/supplier-returns', [SupplierReturnController::class, 'store'], ['PermissionMiddleware:supplier_return.create']);
        $router->get('/supplier-returns/{id}', [SupplierReturnController::class, 'show'], ['PermissionMiddleware:supplier_return.view']);

        $router->get('/billing', [BillingController::class, 'index'], ['PermissionMiddleware:billing.view']);
        $router->post('/billing/generate', [BillingController::class, 'generate'], ['PermissionMiddleware:billing.create']);
        $router->get('/billing/{id}', [BillingController::class, 'show'], ['PermissionMiddleware:billing.view']);
        $router->post('/billing/{id}/cancel', [BillingController::class, 'cancel'], ['PermissionMiddleware:billing.update']);
        $router->post('/billing/{invoiceId}/pay', [PaymentController::class, 'pay'], ['PermissionMiddleware:payment.create']);
        $router->post('/billing/{invoiceId}/refund', [PaymentController::class, 'refund'], ['PermissionMiddleware:payment.refund']);

        $router->get('/reports', [ReportController::class, 'index'], ['PermissionMiddleware:report.view']);
        $router->get('/reports/export/{type}', [ReportController::class, 'export'], ['PermissionMiddleware:report.export']);

        $router->get('/users', [UserController::class, 'index'], ['PermissionMiddleware:user.view']);
        $router->get('/users/create', [UserController::class, 'create'], ['PermissionMiddleware:user.create']);
        $router->post('/users', [UserController::class, 'store'], ['PermissionMiddleware:user.create']);
        $router->get('/users/{id}/edit', [UserController::class, 'edit'], ['PermissionMiddleware:user.update']);
        $router->post('/users/{id}/update', [UserController::class, 'update'], ['PermissionMiddleware:user.update']);
        $router->post('/users/{id}/delete', [UserController::class, 'destroy'], ['PermissionMiddleware:user.delete']);
        $router->post('/users/{id}/reset-password', [UserController::class, 'resetPassword'], ['PermissionMiddleware:user.update']);

        $router->get('/roles', [RoleController::class, 'index'], ['PermissionMiddleware:role.view']);
        $router->get('/roles/create', [RoleController::class, 'create'], ['PermissionMiddleware:role.create']);
        $router->post('/roles', [RoleController::class, 'store'], ['PermissionMiddleware:role.create']);
        $router->get('/roles/{id}/edit', [RoleController::class, 'edit'], ['PermissionMiddleware:role.update']);
        $router->post('/roles/{id}/update', [RoleController::class, 'update'], ['PermissionMiddleware:role.update']);
        $router->post('/roles/{id}/delete', [RoleController::class, 'destroy'], ['PermissionMiddleware:role.delete']);

        $router->get('/audit-logs', [AuditLogController::class, 'index'], ['PermissionMiddleware:audit.view']);
        $router->get('/audit-logs/{id}', [AuditLogController::class, 'show'], ['PermissionMiddleware:audit.view']);

        $router->get('/settings/system', [SystemController::class, 'edit'], ['PermissionMiddleware:setting.view']);
        $router->post('/settings/system', [SystemController::class, 'update'], ['PermissionMiddleware:setting.update']);

        $router->get('/settings/backup', [BackupController::class, 'index'], ['PermissionMiddleware:backup.view']);
        $router->post('/settings/backup', [BackupController::class, 'store'], ['PermissionMiddleware:backup.create']);
        $router->get('/settings/backup/{filename}/download', [BackupController::class, 'download'], ['PermissionMiddleware:backup.view']);

        $router->get('/employees', [EmployeeController::class, 'index'], ['PermissionMiddleware:employee.view']);
        $router->get('/employees/create', [EmployeeController::class, 'create'], ['PermissionMiddleware:employee.create']);
        $router->post('/employees', [EmployeeController::class, 'store'], ['PermissionMiddleware:employee.create']);
        $router->get('/employees/{id}', [EmployeeController::class, 'show'], ['PermissionMiddleware:employee.view']);
        $router->get('/employees/{id}/edit', [EmployeeController::class, 'edit'], ['PermissionMiddleware:employee.update']);
        $router->post('/employees/{id}/update', [EmployeeController::class, 'update'], ['PermissionMiddleware:employee.update']);
        $router->post('/employees/{id}/delete', [EmployeeController::class, 'destroy'], ['PermissionMiddleware:employee.delete']);
        $router->post('/employees/{id}/documents', [EmployeeController::class, 'storeDocument'], ['PermissionMiddleware:employee.update']);
        $router->post('/employees/{id}/documents/{documentId}/delete', [EmployeeController::class, 'destroyDocument'], ['PermissionMiddleware:employee.update']);
        $router->get('/employees/{id}/documents/{documentId}/download', [EmployeeController::class, 'downloadDocument'], ['PermissionMiddleware:employee.view']);
        $router->post('/employees/{id}/emergency-contacts', [EmployeeController::class, 'storeEmergencyContact'], ['PermissionMiddleware:employee.update']);
        $router->post('/employees/{id}/emergency-contacts/{contactId}/delete', [EmployeeController::class, 'destroyEmergencyContact'], ['PermissionMiddleware:employee.update']);

        $router->get('/doctors', [DoctorController::class, 'index'], ['PermissionMiddleware:doctor.view']);
        $router->get('/doctors/create', [DoctorController::class, 'create'], ['PermissionMiddleware:doctor.create']);
        $router->post('/doctors', [DoctorController::class, 'store'], ['PermissionMiddleware:doctor.create']);
        $router->get('/doctors/{id}', [DoctorController::class, 'show'], ['PermissionMiddleware:doctor.view']);
        $router->get('/doctors/{id}/edit', [DoctorController::class, 'edit'], ['PermissionMiddleware:doctor.update']);
        $router->post('/doctors/{id}/update', [DoctorController::class, 'update'], ['PermissionMiddleware:doctor.update']);
        $router->post('/doctors/{id}/delete', [DoctorController::class, 'destroy'], ['PermissionMiddleware:doctor.delete']);
        $router->post('/doctors/{id}/schedules', [DoctorController::class, 'storeSchedule'], ['PermissionMiddleware:doctor.update']);
        $router->post('/doctors/{id}/schedules/{scheduleId}/delete', [DoctorController::class, 'destroySchedule'], ['PermissionMiddleware:doctor.update']);

        $router->get('/patients', [PatientController::class, 'index'], ['PermissionMiddleware:patient.view']);
        $router->get('/patients/create', [PatientController::class, 'create'], ['PermissionMiddleware:patient.create']);
        $router->post('/patients', [PatientController::class, 'store'], ['PermissionMiddleware:patient.create']);
        $router->get('/patients/{id}', [PatientController::class, 'show'], ['PermissionMiddleware:patient.view']);
        $router->get('/patients/{id}/edit', [PatientController::class, 'edit'], ['PermissionMiddleware:patient.update']);
        $router->post('/patients/{id}/update', [PatientController::class, 'update'], ['PermissionMiddleware:patient.update']);
        $router->post('/patients/{id}/delete', [PatientController::class, 'destroy'], ['PermissionMiddleware:patient.delete']);
        $router->post('/patients/{id}/allergies', [PatientController::class, 'storeAllergy'], ['PermissionMiddleware:patient.update']);
        $router->post('/patients/{id}/allergies/{allergyId}/delete', [PatientController::class, 'destroyAllergy'], ['PermissionMiddleware:patient.update']);
        $router->post('/patients/{id}/medical-histories', [PatientController::class, 'storeMedicalHistory'], ['PermissionMiddleware:patient.update']);
        $router->post('/patients/{id}/medical-histories/{historyId}/delete', [PatientController::class, 'destroyMedicalHistory'], ['PermissionMiddleware:patient.update']);
        $router->post('/patients/{id}/emergency-contacts', [PatientController::class, 'storeEmergencyContact'], ['PermissionMiddleware:patient.update']);
        $router->post('/patients/{id}/emergency-contacts/{contactId}/delete', [PatientController::class, 'destroyEmergencyContact'], ['PermissionMiddleware:patient.update']);

        $router->get('/appointments', [AppointmentController::class, 'index'], ['PermissionMiddleware:appointment.view']);
        $router->get('/appointments/create', [AppointmentController::class, 'create'], ['PermissionMiddleware:appointment.create']);
        $router->post('/appointments', [AppointmentController::class, 'store'], ['PermissionMiddleware:appointment.create']);
        $router->post('/appointments/{id}/cancel', [AppointmentController::class, 'cancel'], ['PermissionMiddleware:appointment.delete']);

        $router->get('/registrations', [RegistrationController::class, 'index'], ['PermissionMiddleware:registration.view']);
        $router->get('/registrations/create', [RegistrationController::class, 'create'], ['PermissionMiddleware:registration.create']);
        $router->post('/registrations', [RegistrationController::class, 'store'], ['PermissionMiddleware:registration.create']);
        $router->get('/registrations/{id}', [RegistrationController::class, 'show'], ['PermissionMiddleware:registration.view']);
        $router->post('/registrations/{id}/cancel', [RegistrationController::class, 'cancel'], ['PermissionMiddleware:registration.update']);

        $router->get('/queue', [QueueController::class, 'index'], ['PermissionMiddleware:queue.view']);
        $router->get('/queue/display', [QueueController::class, 'display'], ['PermissionMiddleware:queue.view']);

        $router->get('/medical-records', [MedicalRecordController::class, 'index'], ['PermissionMiddleware:medical_record.view']);
        $router->post('/medical-records', [MedicalRecordController::class, 'create'], ['PermissionMiddleware:medical_record.create']);
        $router->get('/medical-records/{id}', [MedicalRecordController::class, 'show'], ['PermissionMiddleware:medical_record.view']);
        $router->post('/medical-records/{id}/vitals', [MedicalRecordController::class, 'storeVitals'], ['PermissionMiddleware:medical_record.update']);
        $router->post('/medical-records/{id}/anamnesis', [MedicalRecordController::class, 'storeAnamnesis'], ['PermissionMiddleware:medical_record.update']);
        $router->post('/medical-records/{id}/physical-examination', [MedicalRecordController::class, 'storePhysicalExamination'], ['PermissionMiddleware:medical_record.update']);
        $router->post('/medical-records/{id}/diagnoses', [MedicalRecordController::class, 'storeDiagnosis'], ['PermissionMiddleware:diagnosis.create']);
        $router->post('/medical-records/{id}/diagnoses/{diagnosisId}/delete', [MedicalRecordController::class, 'destroyDiagnosis'], ['PermissionMiddleware:diagnosis.create']);
        $router->post('/medical-records/{id}/actions', [MedicalRecordController::class, 'storeAction'], ['PermissionMiddleware:medical_action.create']);
        $router->post('/medical-records/{id}/actions/{actionId}/delete', [MedicalRecordController::class, 'destroyAction'], ['PermissionMiddleware:medical_action.create']);
        $router->post('/medical-records/{id}/notes', [MedicalRecordController::class, 'storeNote'], ['PermissionMiddleware:medical_record.update']);
        $router->post('/medical-records/{id}/finalize', [MedicalRecordController::class, 'finalize'], ['PermissionMiddleware:medical_record.finalize']);
        $router->post('/medical-records/{id}/amend', [MedicalRecordController::class, 'amend'], ['PermissionMiddleware:medical_record.amend']);
        $router->post('/medical-records/{id}/documents', [MedicalDocumentController::class, 'store'], ['PermissionMiddleware:medical_document.create']);

        $router->get('/medical-documents/{id}/pdf', [MedicalDocumentController::class, 'pdf'], ['PermissionMiddleware:medical_document.view']);

        $router->get('/lab-requests', [LabRequestController::class, 'index'], ['PermissionMiddleware:laboratory.view']);
        $router->post('/lab-requests', [LabRequestController::class, 'store'], ['PermissionMiddleware:laboratory.request']);
        $router->get('/lab-requests/{id}', [LabRequestController::class, 'show'], ['PermissionMiddleware:laboratory.view']);
        $router->post('/lab-requests/{id}/collect-sample', [LabRequestController::class, 'collectSample'], ['PermissionMiddleware:laboratory.process']);
        $router->post('/lab-requests/{id}/items/{itemId}/result', [LabRequestController::class, 'submitResult'], ['PermissionMiddleware:laboratory.process']);
        $router->post('/lab-requests/{id}/validate', [LabRequestController::class, 'validateRequest'], ['PermissionMiddleware:laboratory.validate']);
        $router->post('/lab-requests/{id}/cancel', [LabRequestController::class, 'cancel'], ['PermissionMiddleware:laboratory.process']);

        $router->post('/prescriptions', [PrescriptionController::class, 'store'], ['PermissionMiddleware:prescription.create']);
        $router->get('/prescriptions/{id}', [PrescriptionController::class, 'show'], ['PermissionMiddleware:prescription.view']);
        $router->post('/prescriptions/{id}/cancel', [PrescriptionController::class, 'cancel'], ['PermissionMiddleware:prescription.update']);

        $router->get('/pharmacy', [PharmacyController::class, 'index'], ['PermissionMiddleware:prescription.view']);
        $router->post('/pharmacy/{id}/verify', [PharmacyController::class, 'verify'], ['PermissionMiddleware:prescription.dispense']);
        $router->post('/pharmacy/{id}/prepare', [PharmacyController::class, 'prepare'], ['PermissionMiddleware:prescription.dispense']);
        $router->post('/pharmacy/{id}/handover', [PharmacyController::class, 'handover'], ['PermissionMiddleware:prescription.dispense']);
    });
});
