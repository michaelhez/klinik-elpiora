<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Core\Seeder;
use App\Models\Patient;

class PatientSeeder extends Seeder
{
    private const FIRST_NAMES_MALE = [
        'Ahmad', 'Budi', 'Cahyo', 'Dedi', 'Eko', 'Fajar', 'Gunawan', 'Hendra', 'Irfan', 'Joko',
        'Kurniawan', 'Lukman', 'Muhammad', 'Nugroho', 'Oscar', 'Putra', 'Rizki', 'Setiawan', 'Taufik', 'Umar',
    ];

    private const FIRST_NAMES_FEMALE = [
        'Ayu', 'Bunga', 'Citra', 'Dewi', 'Eka', 'Fitri', 'Gita', 'Hana', 'Indah', 'Julia',
        'Kartika', 'Lestari', 'Maya', 'Nadia', 'Oktavia', 'Putri', 'Rani', 'Sari', 'Tika', 'Utami',
    ];

    private const LAST_NAMES = [
        'Pratama', 'Wijaya', 'Kusuma', 'Santoso', 'Saputra', 'Nugraha', 'Setiawan', 'Hidayat', 'Permata', 'Utomo',
    ];

    private const CITIES = ['Jakarta', 'Bekasi', 'Depok', 'Tangerang', 'Bogor'];

    public function run(): void
    {
        $existingCount = (int) \App\Core\Database::connection()->query('SELECT COUNT(*) FROM patients')->fetchColumn();

        if ($existingCount >= 50) {
            $this->info('Patients already seeded (' . $existingCount . '), skipping.');
            return;
        }

        $target = 50;
        $created = 0;

        for ($i = 0; $i < $target; $i++) {
            $gender = $i % 2 === 0 ? 'MALE' : 'FEMALE';
            $firstName = $gender === 'MALE'
                ? self::FIRST_NAMES_MALE[$i % count(self::FIRST_NAMES_MALE)]
                : self::FIRST_NAMES_FEMALE[$i % count(self::FIRST_NAMES_FEMALE)];
            $lastName = self::LAST_NAMES[$i % count(self::LAST_NAMES)];
            $name = "{$firstName} {$lastName}";

            $birthYear = random_int(1955, 2018);
            $birthDate = sprintf('%04d-%02d-%02d', $birthYear, random_int(1, 12), random_int(1, 28));
            $nik = '32' . str_pad((string) (1000000000 + $i), 14, '0', STR_PAD_LEFT);

            Patient::create([
                'medical_record_number' => Patient::nextMedicalRecordNumber(),
                'name' => $name,
                'nik' => $nik,
                'gender' => $gender,
                'blood_type' => ['A', 'B', 'AB', 'O', 'UNKNOWN'][array_rand(['A', 'B', 'AB', 'O', 'UNKNOWN'])],
                'birth_place' => self::CITIES[array_rand(self::CITIES)],
                'birth_date' => $birthDate,
                'phone' => '08' . str_pad((string) (2000000000 + $i), 10, '0', STR_PAD_LEFT),
                'email' => strtolower(str_replace(' ', '.', $name)) . $i . '@example.com',
                'address' => 'Jl. Mawar No. ' . ($i + 1),
                'city' => self::CITIES[$i % count(self::CITIES)],
                'status' => 'ACTIVE',
            ]);

            $created++;
        }

        $this->info("Patients created: {$created}");
    }
}
