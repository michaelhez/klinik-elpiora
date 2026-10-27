<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Session;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\Appointment;
use App\Models\AppointmentStatusHistory;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Polyclinic;

final class AppointmentService
{
    public function __construct(private readonly AuditService $auditService = new AuditService())
    {
    }

    public function paginate(array $filters, int $page, int $perPage): array
    {
        [$where, $bindings] = $this->buildWhere($filters);

        $countStmt = Database::connection()->prepare("SELECT COUNT(*) FROM appointments a INNER JOIN patients p ON p.id = a.patient_id {$where}");
        $countStmt->execute($bindings);
        $total = (int) $countStmt->fetchColumn();

        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT a.*, p.name AS patient_name, p.medical_record_number, e.name AS doctor_name, poly.name AS polyclinic_name
                FROM appointments a
                INNER JOIN patients p ON p.id = a.patient_id
                INNER JOIN doctors d ON d.id = a.doctor_id
                INNER JOIN employees e ON e.id = d.employee_id
                INNER JOIN polyclinics poly ON poly.id = a.polyclinic_id
                {$where}
                ORDER BY a.appointment_date DESC, a.appointment_time DESC
                LIMIT {$perPage} OFFSET {$offset}";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($bindings);

        return [
            'data' => $stmt->fetchAll(),
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int) max(1, ceil($total / $perPage)),
        ];
    }

    public function find(int|string $id): array
    {
        $sql = "SELECT a.*, p.name AS patient_name, p.medical_record_number, e.name AS doctor_name, poly.name AS polyclinic_name
                FROM appointments a
                INNER JOIN patients p ON p.id = a.patient_id
                INNER JOIN doctors d ON d.id = a.doctor_id
                INNER JOIN employees e ON e.id = d.employee_id
                INNER JOIN polyclinics poly ON poly.id = a.polyclinic_id
                WHERE a.id = :id";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $appointment = $stmt->fetch();

        if ($appointment === false) {
            throw new NotFoundException('Data janji temu tidak ditemukan.');
        }

        return $appointment;
    }

    public function create(array $data): array
    {
        if (Patient::find($data['patient_id']) === null) {
            throw new ValidationException(['patient_id' => ['Pasien tidak valid.']]);
        }

        if (Doctor::find($data['doctor_id']) === null) {
            throw new ValidationException(['doctor_id' => ['Dokter tidak valid.']]);
        }

        if (Polyclinic::find($data['polyclinic_id']) === null) {
            throw new ValidationException(['polyclinic_id' => ['Poliklinik tidak valid.']]);
        }

        $data['appointment_number'] = Appointment::nextAppointmentNumber();
        $data['status'] = 'BOOKED';
        $data['created_by'] = Session::get('auth_user')['id'] ?? null;

        $id = Appointment::create($data);

        AppointmentStatusHistory::create([
            'appointment_id' => $id,
            'status' => 'BOOKED',
            'changed_by' => $data['created_by'],
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->auditService->log('CREATE', 'appointment', 'appointments', $id, null, $data);

        return $this->find($id);
    }

    public function updateStatus(int|string $id, string $status, ?string $notes = null): array
    {
        $existing = $this->find($id);
        $userId = Session::get('auth_user')['id'] ?? null;

        Appointment::update($id, ['status' => $status]);

        AppointmentStatusHistory::create([
            'appointment_id' => $id,
            'status' => $status,
            'notes' => $notes,
            'changed_by' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->auditService->log('UPDATE', 'appointment', 'appointments', $id, $existing, ['status' => $status]);

        return $this->find($id);
    }

    public function cancel(int|string $id): array
    {
        return $this->updateStatus($id, 'CANCELLED', 'Dibatalkan oleh petugas.');
    }

    /**
     * @return array{0: string, 1: array<string, mixed>}
     */
    private function buildWhere(array $filters): array
    {
        $conditions = ['1 = 1'];
        $bindings = [];

        if (!empty($filters['search'])) {
            $conditions[] = '(p.name LIKE :search1 OR a.appointment_number LIKE :search2)';
            $needle = '%' . $filters['search'] . '%';
            $bindings['search1'] = $needle;
            $bindings['search2'] = $needle;
        }

        if (!empty($filters['date'])) {
            $conditions[] = 'a.appointment_date = :date';
            $bindings['date'] = $filters['date'];
        }

        if (!empty($filters['status'])) {
            $conditions[] = 'a.status = :status';
            $bindings['status'] = $filters['status'];
        }

        return ['WHERE ' . implode(' AND ', $conditions), $bindings];
    }
}
