<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Core\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public const ROLES = [
        ['name' => 'Super Admin', 'slug' => 'super_admin', 'description' => 'Akses penuh ke seluruh sistem.', 'is_system' => 1],
        ['name' => 'Admin', 'slug' => 'admin', 'description' => 'Akses operasional klinik.', 'is_system' => 1],
        ['name' => 'Receptionist', 'slug' => 'receptionist', 'description' => 'Mengelola pendaftaran, antrian, dan data pasien.', 'is_system' => 0],
        ['name' => 'Doctor', 'slug' => 'doctor', 'description' => 'Melakukan pemeriksaan, diagnosis, dan tindakan medis.', 'is_system' => 0],
        ['name' => 'Nurse', 'slug' => 'nurse', 'description' => 'Mendukung pemeriksaan dan pencatatan keperawatan.', 'is_system' => 0],
        ['name' => 'Pharmacist', 'slug' => 'pharmacist', 'description' => 'Mengelola resep, obat, dan farmasi.', 'is_system' => 0],
        ['name' => 'Lab Officer', 'slug' => 'lab_officer', 'description' => 'Mengelola permintaan dan hasil laboratorium.', 'is_system' => 0],
        ['name' => 'Cashier', 'slug' => 'cashier', 'description' => 'Mengelola invoice dan pembayaran.', 'is_system' => 0],
        ['name' => 'Finance', 'slug' => 'finance', 'description' => 'Mengelola laporan keuangan.', 'is_system' => 0],
        ['name' => 'HR', 'slug' => 'hr', 'description' => 'Mengelola data karyawan dan kepegawaian.', 'is_system' => 0],
        ['name' => 'Manager', 'slug' => 'manager', 'description' => 'Melihat laporan dan analitik manajemen.', 'is_system' => 0],
        ['name' => 'Director', 'slug' => 'director', 'description' => 'Melihat laporan dan analitik tingkat direksi.', 'is_system' => 0],
    ];

    public function run(): void
    {
        foreach (self::ROLES as $role) {
            $existing = Role::findBySlug($role['slug']);

            if ($existing === null) {
                Role::create($role);
                $this->info("Role created: {$role['name']}");
            }
        }
    }
}
