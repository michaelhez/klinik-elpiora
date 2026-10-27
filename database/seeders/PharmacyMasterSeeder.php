<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Core\Seeder;
use App\Models\Branch;
use App\Models\Medicine;
use App\Models\MedicineCategory;
use App\Models\MedicineUnit;
use App\Services\InventoryService;

class PharmacyMasterSeeder extends Seeder
{
    private const CATEGORIES = [
        ['code' => 'CAT-ANLG', 'name' => 'Analgesik'],
        ['code' => 'CAT-ANTB', 'name' => 'Antibiotik'],
        ['code' => 'CAT-ANTI', 'name' => 'Antasida & Pencernaan'],
        ['code' => 'CAT-VITA', 'name' => 'Vitamin & Suplemen'],
        ['code' => 'CAT-RESP', 'name' => 'Obat Saluran Pernapasan'],
        ['code' => 'CAT-ALKS', 'name' => 'Alat Kesehatan'],
    ];

    private const UNITS = [
        ['code' => 'UNT-TAB', 'name' => 'Tablet'],
        ['code' => 'UNT-KAP', 'name' => 'Kapsul'],
        ['code' => 'UNT-BOT', 'name' => 'Botol'],
        ['code' => 'UNT-STR', 'name' => 'Strip'],
        ['code' => 'UNT-AMP', 'name' => 'Ampul'],
        ['code' => 'UNT-TUB', 'name' => 'Tube'],
    ];

    private const MEDICINES = [
        ['code' => 'MED-0001', 'name' => 'Paracetamol 500mg', 'generic_name' => 'Paracetamol', 'category' => 'CAT-ANLG', 'unit' => 'UNT-TAB', 'purchase_price' => 150, 'selling_price' => 300, 'minimum_stock' => 200, 'initial_stock' => 1000],
        ['code' => 'MED-0002', 'name' => 'Amoxicillin 500mg', 'generic_name' => 'Amoxicillin', 'category' => 'CAT-ANTB', 'unit' => 'UNT-KAP', 'purchase_price' => 400, 'selling_price' => 750, 'minimum_stock' => 150, 'initial_stock' => 600],
        ['code' => 'MED-0003', 'name' => 'Antasida Doen', 'generic_name' => 'Antasida', 'category' => 'CAT-ANTI', 'unit' => 'UNT-TAB', 'purchase_price' => 100, 'selling_price' => 250, 'minimum_stock' => 100, 'initial_stock' => 400],
        ['code' => 'MED-0004', 'name' => 'Vitamin C 500mg', 'generic_name' => 'Ascorbic Acid', 'category' => 'CAT-VITA', 'unit' => 'UNT-TAB', 'purchase_price' => 200, 'selling_price' => 400, 'minimum_stock' => 200, 'initial_stock' => 800],
        ['code' => 'MED-0005', 'name' => 'CTM 4mg', 'generic_name' => 'Chlorpheniramine Maleate', 'category' => 'CAT-RESP', 'unit' => 'UNT-TAB', 'purchase_price' => 80, 'selling_price' => 200, 'minimum_stock' => 150, 'initial_stock' => 500],
        ['code' => 'MED-0006', 'name' => 'Sirup Obat Batuk Hitam', 'generic_name' => 'OBH', 'category' => 'CAT-RESP', 'unit' => 'UNT-BOT', 'purchase_price' => 8000, 'selling_price' => 15000, 'minimum_stock' => 30, 'initial_stock' => 100],
        ['code' => 'MED-0007', 'name' => 'Omeprazole 20mg', 'generic_name' => 'Omeprazole', 'category' => 'CAT-ANTI', 'unit' => 'UNT-KAP', 'purchase_price' => 500, 'selling_price' => 950, 'minimum_stock' => 100, 'initial_stock' => 300],
        ['code' => 'MED-0008', 'name' => 'Cetirizine 10mg', 'generic_name' => 'Cetirizine', 'category' => 'CAT-RESP', 'unit' => 'UNT-TAB', 'purchase_price' => 250, 'selling_price' => 500, 'minimum_stock' => 100, 'initial_stock' => 400],
        ['code' => 'MED-0009', 'name' => 'Ibuprofen 400mg', 'generic_name' => 'Ibuprofen', 'category' => 'CAT-ANLG', 'unit' => 'UNT-TAB', 'purchase_price' => 220, 'selling_price' => 450, 'minimum_stock' => 150, 'initial_stock' => 500],
        ['code' => 'MED-0010', 'name' => 'Vitamin B Complex', 'generic_name' => 'Vitamin B Complex', 'category' => 'CAT-VITA', 'unit' => 'UNT-TAB', 'purchase_price' => 180, 'selling_price' => 350, 'minimum_stock' => 100, 'initial_stock' => 400],
        ['code' => 'MED-0011', 'name' => 'Masker Medis (Box)', 'generic_name' => null, 'category' => 'CAT-ALKS', 'unit' => 'UNT-BOT', 'purchase_price' => 25000, 'selling_price' => 40000, 'minimum_stock' => 20, 'initial_stock' => 50],
        ['code' => 'MED-0012', 'name' => 'Injeksi Ceftriaxone 1g', 'generic_name' => 'Ceftriaxone', 'category' => 'CAT-ANTB', 'unit' => 'UNT-AMP', 'purchase_price' => 12000, 'selling_price' => 20000, 'minimum_stock' => 30, 'initial_stock' => 80],
    ];

    public function run(): void
    {
        $categoryIds = [];
        foreach (self::CATEGORIES as $category) {
            $categoryIds[$category['code']] = $this->firstOrCreate(MedicineCategory::class, 'code', $category['code'], [
                'name' => $category['name'],
                'code' => $category['code'],
                'is_active' => 1,
            ]);
        }
        $this->info('Medicine categories ready (' . count(self::CATEGORIES) . ')');

        $unitIds = [];
        foreach (self::UNITS as $unit) {
            $unitIds[$unit['code']] = $this->firstOrCreate(MedicineUnit::class, 'code', $unit['code'], [
                'name' => $unit['name'],
                'code' => $unit['code'],
                'is_active' => 1,
            ]);
        }
        $this->info('Medicine units ready (' . count(self::UNITS) . ')');

        $branch = Branch::findBy('code', 'BR-PST');
        if ($branch === null) {
            $this->info('Skipping medicines/batches: main branch (BR-PST) not found.');
            return;
        }

        $inventoryService = new InventoryService();
        $created = 0;

        foreach (self::MEDICINES as $medicine) {
            $existing = Medicine::findBy('code', $medicine['code']);

            if ($existing !== null) {
                continue;
            }

            $medicineId = Medicine::create([
                'category_id' => $categoryIds[$medicine['category']],
                'unit_id' => $unitIds[$medicine['unit']],
                'code' => $medicine['code'],
                'name' => $medicine['name'],
                'generic_name' => $medicine['generic_name'],
                'brand_name' => null,
                'purchase_price' => $medicine['purchase_price'],
                'selling_price' => $medicine['selling_price'],
                'minimum_stock' => $medicine['minimum_stock'],
                'status' => 'ACTIVE',
            ]);

            $inventoryService->receiveStock(
                $medicineId,
                $branch['id'],
                'INIT-' . $medicine['code'],
                date('Y-m-d', strtotime('+1 year')),
                $medicine['initial_stock'],
                $medicine['purchase_price'],
                'PURCHASE',
                'seeder',
                null,
                'Stok awal seeder',
            );

            $created++;
        }

        $this->info("Medicines ready ({$created} created, initial batches seeded at Cabang Pusat)");
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
