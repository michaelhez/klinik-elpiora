<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Core\Seeder;

class DatabaseSeeder extends Seeder
{
    /** @var array<int, class-string<Seeder>> */
    private const SEEDERS = [
        RoleSeeder::class,
        PermissionSeeder::class,
        UserSeeder::class,
        SystemSettingSeeder::class,
        MasterDataSeeder::class,
        DoctorSeeder::class,
        PatientSeeder::class,
        MedicalMasterSeeder::class,
        LabMasterSeeder::class,
        PharmacyMasterSeeder::class,
        SupplierSeeder::class,
        TransactionSeeder::class,
    ];

    public function run(): void
    {
        foreach (self::SEEDERS as $seederClass) {
            echo "Seeding: {$seederClass}" . PHP_EOL;
            (new $seederClass())->run();
        }
    }
}
