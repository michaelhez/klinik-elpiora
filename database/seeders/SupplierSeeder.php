<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Core\Seeder;
use App\Models\Supplier;

class SupplierSeeder extends Seeder
{
    private const SUPPLIERS = [
        ['code' => 'SUP-001', 'name' => 'PT Kimia Farma Trading & Distribution', 'contact_person' => 'Budi Santoso', 'phone' => '(021) 555-1001', 'email' => 'sales@kimiafarma-td.local', 'address' => 'Jl. Veteran No. 9, Jakarta Pusat'],
        ['code' => 'SUP-002', 'name' => 'PT Enseval Putera Megatrading', 'contact_person' => 'Sari Wijaya', 'phone' => '(021) 555-1002', 'email' => 'order@enseval.local', 'address' => 'Jl. Pulo Lentut No. 10, Jakarta Timur'],
        ['code' => 'SUP-003', 'name' => 'PT Anugrah Pharmindo Lestari', 'contact_person' => 'Andi Prasetyo', 'phone' => '(021) 555-1003', 'email' => 'cs@apl-pharma.local', 'address' => 'Jl. Raya Bekasi Km 22, Jakarta Timur'],
        ['code' => 'SUP-004', 'name' => 'PT Merapi Utama Pharma', 'contact_person' => 'Dewi Lestari', 'phone' => '(021) 555-1004', 'email' => 'info@merapipharma.local', 'address' => 'Jl. Ahmad Yani No. 45, Jakarta Selatan'],
    ];

    public function run(): void
    {
        $created = 0;

        foreach (self::SUPPLIERS as $supplier) {
            $existing = Supplier::findBy('code', $supplier['code']);

            if ($existing !== null) {
                continue;
            }

            Supplier::create([
                'code' => $supplier['code'],
                'name' => $supplier['name'],
                'contact_person' => $supplier['contact_person'],
                'phone' => $supplier['phone'],
                'email' => $supplier['email'],
                'address' => $supplier['address'],
                'status' => 'ACTIVE',
            ]);

            $created++;
        }

        $this->info("Suppliers ready ({$created} created)");
    }
}
