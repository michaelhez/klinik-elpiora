<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Core\Seeder;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Employee;
use App\Models\Polyclinic;
use App\Models\Position;
use App\Models\Specialization;

class DoctorSeeder extends Seeder
{
    private const DOCTORS = [
        [
            'name' => 'dr. Andi Wijaya',
            'gender' => 'MALE',
            'specialization' => 'SPC-UM',
            'polyclinic' => 'POLI-UM',
            'fee' => 100000,
            'schedule' => [1, 3, 5],
        ],
        [
            'name' => 'drg. Siti Nurhaliza',
            'gender' => 'FEMALE',
            'specialization' => 'SPC-GG',
            'polyclinic' => 'POLI-GG',
            'fee' => 150000,
            'schedule' => [2, 4],
        ],
        [
            'name' => 'dr. Budi Santoso, Sp.PD',
            'gender' => 'MALE',
            'specialization' => 'SPC-PD',
            'polyclinic' => 'POLI-PD',
            'fee' => 250000,
            'schedule' => [1, 2, 3],
        ],
        [
            'name' => 'dr. Maya Kusuma, Sp.A',
            'gender' => 'FEMALE',
            'specialization' => 'SPC-AN',
            'polyclinic' => 'POLI-AN',
            'fee' => 220000,
            'schedule' => [2, 4, 6],
        ],
        [
            'name' => 'dr. Rudi Hartono',
            'gender' => 'MALE',
            'specialization' => 'SPC-UM',
            'polyclinic' => 'POLI-UM',
            'fee' => 100000,
            'schedule' => [6],
        ],
    ];

    public function run(): void
    {
        $branch = Branch::findBy('code', 'BR-PST');
        $department = Department::findBy('code', 'DEP-MED');
        $position = Position::findBy('code', 'POS-DOK');

        if ($branch === null || $position === null) {
            $this->info('Skipped: master data (branch/position) not found. Run MasterDataSeeder first.');
            return;
        }

        foreach (self::DOCTORS as $index => $doc) {
            $existingEmployee = Employee::query()->where('name', '=', $doc['name'])->first();

            if ($existingEmployee !== null && Doctor::findBy('employee_id', $existingEmployee['id']) !== null) {
                $this->info("Doctor already exists: {$doc['name']}");
                continue;
            }

            $employeeId = $existingEmployee['id'] ?? Employee::create([
                'branch_id' => $branch['id'],
                'department_id' => $department['id'] ?? null,
                'position_id' => $position['id'],
                'employee_number' => Employee::nextEmployeeNumber(),
                'name' => $doc['name'],
                'gender' => $doc['gender'],
                'phone' => '08' . str_pad((string) (1000000000 + $index), 10, '0', STR_PAD_LEFT),
                'email' => 'dokter' . ($index + 1) . '@kliniksehatsentosa.local',
                'join_date' => date('Y-m-d', strtotime('-' . (365 * (2 + $index)) . ' days')),
                'status' => 'ACTIVE',
            ]);

            $doctorId = Doctor::create([
                'employee_id' => $employeeId,
                'doctor_number' => Doctor::nextDoctorNumber(),
                'str_number' => 'STR-' . str_pad((string) (1000 + $index), 6, '0', STR_PAD_LEFT),
                'sip_number' => 'SIP-' . str_pad((string) (2000 + $index), 6, '0', STR_PAD_LEFT),
                'consultation_fee' => $doc['fee'],
                'bio' => 'Melayani konsultasi dan pemeriksaan di ' . $doc['name'] . '.',
                'status' => 'ACTIVE',
            ]);

            $specialization = Specialization::findBy('code', $doc['specialization']);
            if ($specialization !== null) {
                Doctor::syncSpecializations($doctorId, [$specialization['id']], $specialization['id']);
            }

            $polyclinic = Polyclinic::findBy('code', $doc['polyclinic']);
            if ($polyclinic !== null) {
                foreach ($doc['schedule'] as $day) {
                    DoctorSchedule::create([
                        'doctor_id' => $doctorId,
                        'polyclinic_id' => $polyclinic['id'],
                        'room_id' => null,
                        'day_of_week' => $day,
                        'start_time' => '08:00:00',
                        'end_time' => '14:00:00',
                        'quota' => 20,
                        'is_active' => 1,
                    ]);
                }
            }

            $this->info("Doctor created: {$doc['name']}");
        }
    }
}
