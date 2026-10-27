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
use App\Models\Queue;
use App\Models\QueueSequence;
use App\Models\QueueStatusHistory;
use App\Models\Registration;
use App\Models\RegistrationStatusHistory;
use App\Models\Room;

final class RegistrationService
{
    public function __construct(private readonly AuditService $auditService = new AuditService())
    {
    }

    public function paginate(array $filters, int $page, int $perPage): array
    {
        [$where, $bindings] = $this->buildWhere($filters);

        $countStmt = Database::connection()->prepare("SELECT COUNT(*) FROM registrations r INNER JOIN patients p ON p.id = r.patient_id {$where}");
        $countStmt->execute($bindings);
        $total = (int) $countStmt->fetchColumn();

        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT r.*, p.name AS patient_name, p.medical_record_number,
                       e.name AS doctor_name, poly.name AS polyclinic_name, q.queue_number, q.status AS queue_status
                FROM registrations r
                INNER JOIN patients p ON p.id = r.patient_id
                LEFT JOIN doctors d ON d.id = r.doctor_id
                LEFT JOIN employees e ON e.id = d.employee_id
                INNER JOIN polyclinics poly ON poly.id = r.polyclinic_id
                LEFT JOIN queues q ON q.id = r.queue_id
                {$where}
                ORDER BY r.registered_at DESC
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
        $registration = Registration::findWithRelations($id);

        if ($registration === null) {
            throw new NotFoundException('Data registrasi tidak ditemukan.');
        }

        return $registration;
    }

    /**
     * Creates a registration and atomically generates its queue number.
     * RULE: registration & queue generation must run inside a single transaction.
     */
    public function create(array $data): array
    {
        if (Patient::find($data['patient_id']) === null) {
            throw new ValidationException(['patient_id' => ['Pasien tidak valid.']]);
        }

        $polyclinic = Polyclinic::find($data['polyclinic_id']);
        if ($polyclinic === null) {
            throw new ValidationException(['polyclinic_id' => ['Poliklinik tidak valid.']]);
        }

        if (!empty($data['doctor_id']) && Doctor::find($data['doctor_id']) === null) {
            throw new ValidationException(['doctor_id' => ['Dokter tidak valid.']]);
        }

        if (!empty($data['room_id']) && Room::find($data['room_id']) === null) {
            throw new ValidationException(['room_id' => ['Ruangan tidak valid.']]);
        }

        $appointment = null;
        if (!empty($data['appointment_id'])) {
            $appointment = Appointment::find($data['appointment_id']);
            if ($appointment === null) {
                throw new ValidationException(['appointment_id' => ['Janji temu tidak valid.']]);
            }
        }

        $userId = Session::get('auth_user')['id'] ?? null;
        $today = date('Y-m-d');

        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $registrationId = Registration::create([
                'registration_number' => Registration::nextRegistrationNumber(),
                'patient_id' => $data['patient_id'],
                'doctor_id' => empty($data['doctor_id']) ? null : $data['doctor_id'],
                'polyclinic_id' => $data['polyclinic_id'],
                'room_id' => empty($data['room_id']) ? null : $data['room_id'],
                'appointment_id' => empty($data['appointment_id']) ? null : $data['appointment_id'],
                'payment_type' => $data['payment_type'],
                'status' => 'REGISTERED',
                'notes' => $data['notes'] ?? null,
                'registered_by' => $userId,
                'registered_at' => date('Y-m-d H:i:s'),
            ]);

            RegistrationStatusHistory::create([
                'registration_id' => $registrationId,
                'status' => 'REGISTERED',
                'changed_by' => $userId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $sequenceNumber = QueueSequence::nextNumber($data['polyclinic_id'], $today);
            $queueNumber = $polyclinic['queue_prefix'] . '-' . str_pad((string) $sequenceNumber, 3, '0', STR_PAD_LEFT);

            $queueId = Queue::create([
                'registration_id' => $registrationId,
                'polyclinic_id' => $data['polyclinic_id'],
                'queue_number' => $queueNumber,
                'queue_date' => $today,
                'status' => 'WAITING',
            ]);

            QueueStatusHistory::create([
                'queue_id' => $queueId,
                'status' => 'WAITING',
                'changed_by' => $userId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            Registration::update($registrationId, ['queue_id' => $queueId]);

            if ($appointment !== null) {
                Appointment::update($appointment['id'], ['status' => 'ARRIVED']);
                AppointmentStatusHistory::create([
                    'appointment_id' => $appointment['id'],
                    'status' => 'ARRIVED',
                    'changed_by' => $userId,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        $this->auditService->log('CREATE', 'registration', 'registrations', $registrationId, null, $data);

        return $this->find($registrationId);
    }

    public function cancel(int|string $id): array
    {
        $existing = $this->find($id);

        if ($existing['status'] === 'CANCELLED') {
            throw new ValidationException(['status' => ['Registrasi ini sudah dibatalkan.']]);
        }

        if (in_array($existing['queue_status'], ['COMPLETED'], true)) {
            throw new ValidationException(['status' => ['Registrasi tidak dapat dibatalkan karena pasien sudah selesai dilayani.']]);
        }

        $userId = Session::get('auth_user')['id'] ?? null;
        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            Registration::update($id, ['status' => 'CANCELLED']);
            RegistrationStatusHistory::create([
                'registration_id' => $id,
                'status' => 'CANCELLED',
                'changed_by' => $userId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            if (!empty($existing['queue_id'])) {
                Queue::update($existing['queue_id'], ['status' => 'CANCELLED']);
                QueueStatusHistory::create([
                    'queue_id' => $existing['queue_id'],
                    'status' => 'CANCELLED',
                    'changed_by' => $userId,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        $this->auditService->log('CANCEL', 'registration', 'registrations', $id, $existing, ['status' => 'CANCELLED']);

        return $this->find($id);
    }

    /**
     * @return array{0: string, 1: array<string, mixed>}
     */
    private function buildWhere(array $filters): array
    {
        $conditions = ['1 = 1'];
        $bindings = [];

        if (!empty($filters['search'])) {
            $conditions[] = '(p.name LIKE :search1 OR r.registration_number LIKE :search2)';
            $needle = '%' . $filters['search'] . '%';
            $bindings['search1'] = $needle;
            $bindings['search2'] = $needle;
        }

        if (!empty($filters['date'])) {
            $conditions[] = 'DATE(r.registered_at) = :date';
            $bindings['date'] = $filters['date'];
        }

        if (!empty($filters['status'])) {
            $conditions[] = 'r.status = :status';
            $bindings['status'] = $filters['status'];
        }

        return ['WHERE ' . implode(' AND ', $conditions), $bindings];
    }
}
