<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\DoctorScheduleException;
use App\Models\Employee;
use App\Models\Polyclinic;
use App\Models\Room;
use App\Repositories\DoctorRepository;

final class DoctorService
{
    public function __construct(
        private readonly DoctorRepository $repository = new DoctorRepository(),
        private readonly AuditService $auditService = new AuditService(),
    ) {
    }

    public function paginate(array $filters, int $page, int $perPage): array
    {
        return $this->repository->paginate($filters, $page, $perPage);
    }

    public function find(int|string $id): array
    {
        $doctor = $this->repository->findWithRelations($id);

        if ($doctor === null) {
            throw new NotFoundException('Data dokter tidak ditemukan.');
        }

        $doctor['specializations'] = Doctor::specializations($id);

        return $doctor;
    }

    public function schedules(int|string $doctorId): array
    {
        return DoctorSchedule::forDoctor($doctorId);
    }

    public function create(array $data): array
    {
        if (Employee::find($data['employee_id']) === null) {
            throw new ValidationException(['employee_id' => ['Karyawan tidak valid.']]);
        }

        $existingDoctor = Doctor::findBy('employee_id', $data['employee_id']);
        if ($existingDoctor !== null) {
            throw new ValidationException(['employee_id' => ['Karyawan ini sudah terdaftar sebagai dokter.']]);
        }

        $specializationIds = (array) ($data['specialization_ids'] ?? []);
        if ($specializationIds === []) {
            throw new ValidationException(['specialization_ids' => ['Pilih minimal satu spesialisasi.']]);
        }

        $data['doctor_number'] = Doctor::nextDoctorNumber();
        $data['status'] = $data['status'] ?? 'ACTIVE';
        $insertData = $this->onlyDoctorFields($data);

        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $id = Doctor::create($insertData);
            $primaryId = $data['primary_specialization_id'] ?? $specializationIds[0];
            Doctor::syncSpecializations($id, $specializationIds, $primaryId);
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        $this->auditService->log('CREATE', 'doctor', 'doctors', $id, null, $insertData);

        return $this->find($id);
    }

    public function update(int|string $id, array $data): array
    {
        $existing = $this->find($id);

        $specializationIds = (array) ($data['specialization_ids'] ?? []);
        if ($specializationIds === []) {
            throw new ValidationException(['specialization_ids' => ['Pilih minimal satu spesialisasi.']]);
        }

        $updateData = $this->onlyDoctorFields($data);

        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            Doctor::update($id, $updateData);
            $primaryId = $data['primary_specialization_id'] ?? $specializationIds[0];
            Doctor::syncSpecializations($id, $specializationIds, $primaryId);
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        $this->auditService->log('UPDATE', 'doctor', 'doctors', $id, $existing, $updateData);

        return $this->find($id);
    }

    public function delete(int|string $id): void
    {
        $existing = $this->find($id);

        $scheduleCount = (int) Database::connection()
            ->query('SELECT COUNT(*) FROM doctor_schedules WHERE doctor_id = ' . (int) $id . ' AND is_active = 1')
            ->fetchColumn();

        if ($scheduleCount > 0) {
            throw new ValidationException(['name' => ['Dokter tidak dapat dihapus karena masih memiliki jadwal praktik aktif.']]);
        }

        Doctor::delete($id);
        $this->auditService->log('DELETE', 'doctor', 'doctors', $id, $existing, null);
    }

    public function addSchedule(int|string $doctorId, array $data): array
    {
        $this->find($doctorId);

        if (Polyclinic::find($data['polyclinic_id']) === null) {
            throw new ValidationException(['polyclinic_id' => ['Poliklinik tidak valid.']]);
        }

        if (!empty($data['room_id']) && Room::find($data['room_id']) === null) {
            throw new ValidationException(['room_id' => ['Ruangan tidak valid.']]);
        }

        if ($data['start_time'] >= $data['end_time']) {
            throw new ValidationException(['end_time' => ['Jam selesai harus setelah jam mulai.']]);
        }

        $data['doctor_id'] = $doctorId;
        $data['room_id'] = $data['room_id'] ?: null;
        $data['quota'] = $data['quota'] ?: null;
        $data['is_active'] = 1;

        $id = DoctorSchedule::create($data);
        $this->auditService->log('CREATE', 'doctor', 'doctor_schedules', $id, null, $data);

        return DoctorSchedule::findOrFail($id);
    }

    public function deleteSchedule(int|string $doctorId, int|string $scheduleId): void
    {
        $schedule = DoctorSchedule::find($scheduleId);

        if ($schedule === null || (string) $schedule['doctor_id'] !== (string) $doctorId) {
            throw new NotFoundException('Jadwal tidak ditemukan.');
        }

        DoctorSchedule::delete($scheduleId);
        $this->auditService->log('DELETE', 'doctor', 'doctor_schedules', $scheduleId, $schedule, null);
    }

    private function onlyDoctorFields(array $data): array
    {
        return array_intersect_key($data, array_flip([
            'employee_id', 'doctor_number', 'str_number', 'sip_number',
            'consultation_fee', 'bio', 'status',
        ]));
    }
}
