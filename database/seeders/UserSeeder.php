<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Core\Seeder;
use App\Models\Role;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $existing = User::findByEmail('admin@clinic.local');

        if ($existing === null) {
            $userId = User::create([
                'name' => 'Super Administrator',
                'email' => 'admin@clinic.local',
                'username' => 'superadmin',
                'password' => password_hash('Admin@123456', PASSWORD_BCRYPT),
                'is_active' => 1,
            ]);

            $role = Role::findBySlug('super_admin');
            if ($role !== null) {
                User::assignRole($userId, $role['id']);
            }

            $this->info('Default Super Admin created: admin@clinic.local / Admin@123456');
            $this->info('WARNING: This credential is for DEVELOPMENT ONLY. Change it before production use.');
        } else {
            $this->info('Super Admin already exists, skipping.');
        }
    }
}
