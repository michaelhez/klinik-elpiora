<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Core\Seeder;
use App\Models\LabParameter;
use App\Models\LabTest;

class LabMasterSeeder extends Seeder
{
    private const TESTS = [
        [
            'code' => 'LAB-001',
            'name' => 'Darah Lengkap',
            'category' => 'Hematologi',
            'sample_type' => 'Darah Vena',
            'price' => 85000,
            'parameters' => [
                ['name' => 'Hemoglobin', 'unit' => 'g/dL', 'value_type' => 'NUMERIC', 'reference_min' => 12, 'reference_max' => 16],
                ['name' => 'Leukosit', 'unit' => 'ribu/uL', 'value_type' => 'NUMERIC', 'reference_min' => 4, 'reference_max' => 10],
                ['name' => 'Trombosit', 'unit' => 'ribu/uL', 'value_type' => 'NUMERIC', 'reference_min' => 150, 'reference_max' => 450],
                ['name' => 'Hematokrit', 'unit' => '%', 'value_type' => 'NUMERIC', 'reference_min' => 37, 'reference_max' => 47],
            ],
        ],
        [
            'code' => 'LAB-002',
            'name' => 'Gula Darah Puasa',
            'category' => 'Kimia Klinik',
            'sample_type' => 'Darah Vena',
            'price' => 40000,
            'parameters' => [
                ['name' => 'Glukosa Puasa', 'unit' => 'mg/dL', 'value_type' => 'NUMERIC', 'reference_min' => 70, 'reference_max' => 100],
            ],
        ],
        [
            'code' => 'LAB-003',
            'name' => 'Urine Lengkap',
            'category' => 'Urinalisis',
            'sample_type' => 'Urine',
            'price' => 50000,
            'parameters' => [
                ['name' => 'Warna', 'unit' => null, 'value_type' => 'TEXT', 'reference_text' => 'Kuning Jernih'],
                ['name' => 'Protein', 'unit' => null, 'value_type' => 'TEXT', 'reference_text' => 'Negatif'],
                ['name' => 'Glukosa', 'unit' => null, 'value_type' => 'TEXT', 'reference_text' => 'Negatif'],
            ],
        ],
        [
            'code' => 'LAB-004',
            'name' => 'Profil Lipid',
            'category' => 'Kimia Klinik',
            'sample_type' => 'Darah Vena',
            'price' => 120000,
            'parameters' => [
                ['name' => 'Kolesterol Total', 'unit' => 'mg/dL', 'value_type' => 'NUMERIC', 'reference_min' => 0, 'reference_max' => 200],
                ['name' => 'HDL', 'unit' => 'mg/dL', 'value_type' => 'NUMERIC', 'reference_min' => 40, 'reference_max' => 60],
                ['name' => 'LDL', 'unit' => 'mg/dL', 'value_type' => 'NUMERIC', 'reference_min' => 0, 'reference_max' => 130],
                ['name' => 'Trigliserida', 'unit' => 'mg/dL', 'value_type' => 'NUMERIC', 'reference_min' => 0, 'reference_max' => 150],
            ],
        ],
        [
            'code' => 'LAB-005',
            'name' => 'Fungsi Ginjal',
            'category' => 'Kimia Klinik',
            'sample_type' => 'Darah Vena',
            'price' => 95000,
            'parameters' => [
                ['name' => 'Ureum', 'unit' => 'mg/dL', 'value_type' => 'NUMERIC', 'reference_min' => 10, 'reference_max' => 50],
                ['name' => 'Kreatinin', 'unit' => 'mg/dL', 'value_type' => 'NUMERIC', 'reference_min' => 0.6, 'reference_max' => 1.3],
            ],
        ],
    ];

    public function run(): void
    {
        foreach (self::TESTS as $test) {
            $existing = LabTest::findBy('code', $test['code']);

            if ($existing !== null) {
                continue;
            }

            $testId = LabTest::create([
                'code' => $test['code'],
                'name' => $test['name'],
                'category' => $test['category'],
                'sample_type' => $test['sample_type'],
                'price' => $test['price'],
                'status' => 'ACTIVE',
            ]);

            foreach ($test['parameters'] as $order => $param) {
                LabParameter::create([
                    'lab_test_id' => $testId,
                    'name' => $param['name'],
                    'unit' => $param['unit'] ?? null,
                    'value_type' => $param['value_type'],
                    'reference_min' => $param['reference_min'] ?? null,
                    'reference_max' => $param['reference_max'] ?? null,
                    'reference_text' => $param['reference_text'] ?? null,
                    'sort_order' => $order,
                ]);
            }
        }

        $this->info('Lab tests ready (' . count(self::TESTS) . ')');
    }
}
