<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\EmployeeEmergencyContact;
use App\Models\Position;
use App\Repositories\EmployeeRepository;

final class EmployeeService
{
    public function __construct(
        private readonly EmployeeRepository $repository = new EmployeeRepository(),
        private readonly AuditService $auditService = new AuditService(),
        private readonly FileUploadService $fileUploadService = new FileUploadService(),
    ) {
    }

    public function paginate(array $filters, int $page, int $perPage): array
    {
        return $this->repository->paginate($filters, $page, $perPage);
    }

    public function find(int|string $id): array
    {
        $employee = $this->repository->findWithRelations($id);

        if ($employee === null) {
            throw new NotFoundException('Data karyawan tidak ditemukan.');
        }

        return $employee;
    }

    public function documents(int|string $employeeId): array
    {
        return EmployeeDocument::forEmployee($employeeId);
    }

    public function emergencyContacts(int|string $employeeId): array
    {
        return EmployeeEmergencyContact::forEmployee($employeeId);
    }

    public function create(array $data, ?array $photoFile): array
    {
        $this->validateRelations($data);

        if (!empty($data['nik']) && Employee::findByNik($data['nik']) !== null) {
            throw new ValidationException(['nik' => ['NIK sudah terdaftar untuk karyawan lain.']]);
        }

        $data['employee_number'] = Employee::nextEmployeeNumber();
        $data['status'] = $data['status'] ?? 'ACTIVE';
        $data = $this->cleanRelationFields($data);

        if ($photoFile !== null && $photoFile['error'] !== UPLOAD_ERR_NO_FILE) {
            $data['photo'] = $this->fileUploadService->storePhoto($photoFile, 'employees');
        }

        $id = Employee::create($data);
        $this->auditService->log('CREATE', 'employee', 'employees', $id, null, $data);

        return $this->find($id);
    }

    public function update(int|string $id, array $data, ?array $photoFile): array
    {
        $existing = $this->find($id);
        $this->validateRelations($data);

        if (!empty($data['nik'])) {
            $duplicate = Employee::findByNik($data['nik']);
            if ($duplicate !== null && (string) $duplicate['id'] !== (string) $id) {
                throw new ValidationException(['nik' => ['NIK sudah terdaftar untuk karyawan lain.']]);
            }
        }

        $data = $this->cleanRelationFields($data);

        if ($photoFile !== null && $photoFile['error'] !== UPLOAD_ERR_NO_FILE) {
            $data['photo'] = $this->fileUploadService->storePhoto($photoFile, 'employees');
            if (!empty($existing['photo'])) {
                $this->fileUploadService->deletePhoto($existing['photo']);
            }
        }

        Employee::update($id, $data);
        $this->auditService->log('UPDATE', 'employee', 'employees', $id, $existing, $data);

        return $this->find($id);
    }

    public function delete(int|string $id): void
    {
        $existing = $this->find($id);

        $doctorExists = (int) Database::connection()
            ->query('SELECT COUNT(*) FROM doctors WHERE employee_id = ' . (int) $id . ' AND deleted_at IS NULL')
            ->fetchColumn();

        if ($doctorExists > 0) {
            throw new ValidationException(['name' => ['Karyawan tidak dapat dihapus karena terdaftar sebagai dokter aktif.']]);
        }

        Employee::delete($id);
        $this->auditService->log('DELETE', 'employee', 'employees', $id, $existing, null);
    }

    public function addDocument(int|string $employeeId, string $documentType, array $file): array
    {
        $this->find($employeeId);

        $path = $this->fileUploadService->storeDocument($file, 'employees/' . $employeeId);

        $docId = EmployeeDocument::create([
            'employee_id' => $employeeId,
            'document_type' => $documentType,
            'file_name' => $file['name'],
            'file_path' => $path,
            'uploaded_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->auditService->log('CREATE', 'employee', 'employee_documents', $docId, null, ['document_type' => $documentType]);

        return EmployeeDocument::findOrFail($docId);
    }

    public function deleteDocument(int|string $employeeId, int|string $documentId): void
    {
        $document = EmployeeDocument::find($documentId);

        if ($document === null || (string) $document['employee_id'] !== (string) $employeeId) {
            throw new NotFoundException('Dokumen tidak ditemukan.');
        }

        $this->fileUploadService->deleteDocument($document['file_path']);
        EmployeeDocument::delete($documentId);
        $this->auditService->log('DELETE', 'employee', 'employee_documents', $documentId, $document, null);
    }

    public function documentPath(int|string $employeeId, int|string $documentId): string
    {
        $document = EmployeeDocument::find($documentId);

        if ($document === null || (string) $document['employee_id'] !== (string) $employeeId) {
            throw new NotFoundException('Dokumen tidak ditemukan.');
        }

        return $this->fileUploadService->documentAbsolutePath($document['file_path']);
    }

    public function addEmergencyContact(int|string $employeeId, array $data): array
    {
        $this->find($employeeId);
        $data['employee_id'] = $employeeId;

        $id = EmployeeEmergencyContact::create($data);
        $this->auditService->log('CREATE', 'employee', 'employee_emergency_contacts', $id, null, $data);

        return EmployeeEmergencyContact::findOrFail($id);
    }

    public function deleteEmergencyContact(int|string $employeeId, int|string $contactId): void
    {
        $contact = EmployeeEmergencyContact::find($contactId);

        if ($contact === null || (string) $contact['employee_id'] !== (string) $employeeId) {
            throw new NotFoundException('Kontak darurat tidak ditemukan.');
        }

        EmployeeEmergencyContact::delete($contactId);
        $this->auditService->log('DELETE', 'employee', 'employee_emergency_contacts', $contactId, $contact, null);
    }

    private function validateRelations(array $data): void
    {
        if (Branch::find($data['branch_id']) === null) {
            throw new ValidationException(['branch_id' => ['Cabang tidak valid.']]);
        }

        if (!empty($data['department_id']) && Department::find($data['department_id']) === null) {
            throw new ValidationException(['department_id' => ['Departemen tidak valid.']]);
        }

        if (!empty($data['position_id']) && Position::find($data['position_id']) === null) {
            throw new ValidationException(['position_id' => ['Jabatan tidak valid.']]);
        }
    }

    private function cleanRelationFields(array $data): array
    {
        $data['department_id'] = $data['department_id'] ?: null;
        $data['position_id'] = $data['position_id'] ?: null;
        $data['nik'] = $data['nik'] ?: null;
        $data['birth_date'] = $data['birth_date'] ?: null;
        $data['join_date'] = $data['join_date'] ?: null;

        return $data;
    }
}
