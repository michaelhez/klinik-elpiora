<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Core\Seeder;
use App\Models\Branch;
use App\Models\Clinic;
use App\Models\Department;
use App\Models\Polyclinic;
use App\Models\Position;
use App\Models\Room;
use App\Models\Specialization;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $clinicId = $this->firstOrCreate(Clinic::class, 'code', 'KSS', [
            'name' => 'Klinik Sehat Sentosa',
            'code' => 'KSS',
            'address' => 'Jl. Kesehatan No. 10, Jakarta Selatan',
            'phone' => '(021) 555-0100',
            'email' => 'info@kliniksehatsentosa.local',
            'is_active' => 1,
        ]);
        $this->info('Clinic ready: Klinik Sehat Sentosa');

        $branchPusatId = $this->firstOrCreate(Branch::class, 'code', 'BR-PST', [
            'clinic_id' => $clinicId,
            'name' => 'Cabang Pusat',
            'code' => 'BR-PST',
            'address' => 'Jl. Kesehatan No. 10, Jakarta Selatan',
            'phone' => '(021) 555-0100',
            'email' => 'pusat@kliniksehatsentosa.local',
            'is_main' => 1,
            'is_active' => 1,
        ]);

        $branchTimurId = $this->firstOrCreate(Branch::class, 'code', 'BR-TMR', [
            'clinic_id' => $clinicId,
            'name' => 'Cabang Jakarta Timur',
            'code' => 'BR-TMR',
            'address' => 'Jl. Dokter Sehat No. 5, Jakarta Timur',
            'phone' => '(021) 555-0200',
            'email' => 'timur@kliniksehatsentosa.local',
            'is_main' => 0,
            'is_active' => 1,
        ]);
        $this->info('Branches ready: Cabang Pusat, Cabang Jakarta Timur');

        $departments = [
            ['code' => 'DEP-MED', 'name' => 'Pelayanan Medis'],
            ['code' => 'DEP-NUR', 'name' => 'Keperawatan'],
            ['code' => 'DEP-PHR', 'name' => 'Farmasi'],
            ['code' => 'DEP-ADM', 'name' => 'Administrasi'],
            ['code' => 'DEP-FIN', 'name' => 'Keuangan'],
        ];
        foreach ($departments as $dept) {
            $this->firstOrCreate(Department::class, 'code', $dept['code'], [
                'branch_id' => $branchPusatId,
                'name' => $dept['name'],
                'code' => $dept['code'],
                'is_active' => 1,
            ]);
        }
        $this->info('Departments ready (' . count($departments) . ')');

        $polyclinics = [
            ['code' => 'POLI-UM', 'name' => 'Poli Umum', 'icon' => 'bi-clipboard2-pulse', 'prefix' => 'A', 'fee' => 50000],
            ['code' => 'POLI-GG', 'name' => 'Poli Gigi', 'icon' => 'bi-emoji-smile', 'prefix' => 'B', 'fee' => 75000],
            ['code' => 'POLI-PD', 'name' => 'Poli Penyakit Dalam', 'icon' => 'bi-heart-pulse', 'prefix' => 'C', 'fee' => 100000],
            ['code' => 'POLI-AN', 'name' => 'Poli Anak', 'icon' => 'bi-emoji-laughing', 'prefix' => 'D', 'fee' => 60000],
            ['code' => 'POLI-LAB', 'name' => 'Laboratorium', 'icon' => 'bi-flask', 'prefix' => 'L', 'fee' => 0],
        ];
        $polyclinicIds = [];
        foreach ($polyclinics as $poly) {
            $polyclinicIds[$poly['code']] = $this->firstOrCreate(Polyclinic::class, 'code', $poly['code'], [
                'branch_id' => $branchPusatId,
                'name' => $poly['name'],
                'code' => $poly['code'],
                'icon' => $poly['icon'],
                'queue_prefix' => $poly['prefix'],
                'consultation_fee' => $poly['fee'],
                'is_active' => 1,
            ]);

            Polyclinic::update($polyclinicIds[$poly['code']], ['consultation_fee' => $poly['fee']]);
        }
        $this->info('Polyclinics ready (' . count($polyclinics) . ')');

        $rooms = [
            ['code' => 'R-101', 'name' => 'Ruang Periksa 1', 'poly' => 'POLI-UM'],
            ['code' => 'R-102', 'name' => 'Ruang Periksa 2', 'poly' => 'POLI-UM'],
            ['code' => 'R-201', 'name' => 'Ruang Gigi 1', 'poly' => 'POLI-GG'],
            ['code' => 'R-301', 'name' => 'Ruang Penyakit Dalam', 'poly' => 'POLI-PD'],
            ['code' => 'R-401', 'name' => 'Ruang Anak', 'poly' => 'POLI-AN'],
            ['code' => 'R-501', 'name' => 'Ruang Laboratorium', 'poly' => 'POLI-LAB'],
        ];
        foreach ($rooms as $room) {
            $this->firstOrCreate(Room::class, 'code', $room['code'], [
                'branch_id' => $branchPusatId,
                'polyclinic_id' => $polyclinicIds[$room['poly']],
                'name' => $room['name'],
                'code' => $room['code'],
                'capacity' => 1,
                'is_active' => 1,
            ]);
        }
        $this->info('Rooms ready (' . count($rooms) . ')');

        $positions = [
            ['code' => 'POS-DOK', 'name' => 'Dokter', 'level' => 'Staff'],
            ['code' => 'POS-PRW', 'name' => 'Perawat', 'level' => 'Staff'],
            ['code' => 'POS-APT', 'name' => 'Apoteker', 'level' => 'Staff'],
            ['code' => 'POS-ADM', 'name' => 'Staff Administrasi', 'level' => 'Staff'],
            ['code' => 'POS-KAS', 'name' => 'Kasir', 'level' => 'Staff'],
            ['code' => 'POS-MGR', 'name' => 'Manager Klinik', 'level' => 'Manager'],
        ];
        foreach ($positions as $pos) {
            $this->firstOrCreate(Position::class, 'code', $pos['code'], [
                'name' => $pos['name'],
                'code' => $pos['code'],
                'level' => $pos['level'],
                'is_active' => 1,
            ]);
        }
        $this->info('Positions ready (' . count($positions) . ')');

        $specializations = [
            ['code' => 'SPC-UM', 'name' => 'Dokter Umum'],
            ['code' => 'SPC-GG', 'name' => 'Dokter Gigi'],
            ['code' => 'SPC-PD', 'name' => 'Spesialis Penyakit Dalam'],
            ['code' => 'SPC-AN', 'name' => 'Spesialis Anak'],
            ['code' => 'SPC-KLM', 'name' => 'Spesialis Kulit dan Kelamin'],
            ['code' => 'SPC-MAT', 'name' => 'Spesialis Mata'],
            ['code' => 'SPC-THT', 'name' => 'Spesialis THT'],
            ['code' => 'SPC-OBG', 'name' => 'Spesialis Kandungan'],
        ];
        foreach ($specializations as $spec) {
            $this->firstOrCreate(Specialization::class, 'code', $spec['code'], [
                'name' => $spec['name'],
                'code' => $spec['code'],
                'is_active' => 1,
            ]);
        }
        $this->info('Specializations ready (' . count($specializations) . ')');
    }

    private function firstOrCreate(string $modelClass, string $column, string $value, array $data): int|string
    {
        $existing = $modelClass::findBy($column, $value);

        if ($existing !== null) {
            return $existing['id'];
        }

        return $modelClass::create($data);
    }
}
