<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Core\Seeder;
use App\Models\Icd10Code;
use App\Models\MedicalAction;
use App\Models\MedicalActionCategory;

class MedicalMasterSeeder extends Seeder
{
    private const ICD10_CODES = [
        ['code' => 'J06.9', 'description' => 'Infeksi saluran pernapasan atas akut, tidak spesifik', 'category' => 'Pernapasan'],
        ['code' => 'A09', 'description' => 'Diare dan gastroenteritis diduga berasal infeksi', 'category' => 'Pencernaan'],
        ['code' => 'R50.9', 'description' => 'Demam, tidak spesifik', 'category' => 'Umum'],
        ['code' => 'K30', 'description' => 'Dispepsia fungsional', 'category' => 'Pencernaan'],
        ['code' => 'M79.1', 'description' => 'Mialgia', 'category' => 'Muskuloskeletal'],
        ['code' => 'I10', 'description' => 'Hipertensi esensial (primer)', 'category' => 'Kardiovaskular'],
        ['code' => 'E11.9', 'description' => 'Diabetes melitus tipe 2 tanpa komplikasi', 'category' => 'Endokrin'],
        ['code' => 'J45.9', 'description' => 'Asma, tidak spesifik', 'category' => 'Pernapasan'],
        ['code' => 'L23.9', 'description' => 'Dermatitis kontak alergi, penyebab tidak spesifik', 'category' => 'Kulit'],
        ['code' => 'H10.9', 'description' => 'Konjungtivitis, tidak spesifik', 'category' => 'Mata'],
        ['code' => 'K02.9', 'description' => 'Karies gigi, tidak spesifik', 'category' => 'Gigi'],
        ['code' => 'N39.0', 'description' => 'Infeksi saluran kemih, lokasi tidak spesifik', 'category' => 'Genitourinari'],
        ['code' => 'G43.9', 'description' => 'Migrain, tidak spesifik', 'category' => 'Saraf'],
        ['code' => 'R51', 'description' => 'Sakit kepala', 'category' => 'Umum'],
        ['code' => 'J02.9', 'description' => 'Faringitis akut, tidak spesifik', 'category' => 'Pernapasan'],
    ];

    private const CATEGORIES = [
        ['code' => 'CAT-KONS', 'name' => 'Konsultasi'],
        ['code' => 'CAT-TIND', 'name' => 'Tindakan Minor'],
        ['code' => 'CAT-LAB', 'name' => 'Prosedur Laboratorium'],
        ['code' => 'CAT-GIGI', 'name' => 'Tindakan Gigi'],
    ];

    private const ACTIONS = [
        ['code' => 'ACT-001', 'name' => 'Konsultasi Dokter Umum', 'category' => 'CAT-KONS', 'price' => 100000, 'duration' => 15],
        ['code' => 'ACT-002', 'name' => 'Konsultasi Dokter Spesialis', 'category' => 'CAT-KONS', 'price' => 250000, 'duration' => 20],
        ['code' => 'ACT-003', 'name' => 'Ganti Perban', 'category' => 'CAT-TIND', 'price' => 50000, 'duration' => 10],
        ['code' => 'ACT-004', 'name' => 'Jahit Luka Ringan', 'category' => 'CAT-TIND', 'price' => 150000, 'duration' => 30],
        ['code' => 'ACT-005', 'name' => 'Nebulizer', 'category' => 'CAT-TIND', 'price' => 75000, 'duration' => 15],
        ['code' => 'ACT-006', 'name' => 'Suntik Vitamin', 'category' => 'CAT-TIND', 'price' => 40000, 'duration' => 5],
        ['code' => 'ACT-007', 'name' => 'Pembersihan Karang Gigi', 'category' => 'CAT-GIGI', 'price' => 200000, 'duration' => 30],
        ['code' => 'ACT-008', 'name' => 'Cabut Gigi', 'category' => 'CAT-GIGI', 'price' => 175000, 'duration' => 20],
    ];

    public function run(): void
    {
        foreach (self::ICD10_CODES as $icd) {
            if (Icd10Code::findBy('code', $icd['code']) === null) {
                Icd10Code::create([
                    'code' => $icd['code'],
                    'description' => $icd['description'],
                    'category' => $icd['category'],
                    'status' => 'ACTIVE',
                ]);
            }
        }
        $this->info('ICD-10 codes ready (' . count(self::ICD10_CODES) . ')');

        $categoryIds = [];
        foreach (self::CATEGORIES as $cat) {
            $existing = MedicalActionCategory::findBy('code', $cat['code']);
            $categoryIds[$cat['code']] = $existing['id'] ?? MedicalActionCategory::create([
                'name' => $cat['name'],
                'code' => $cat['code'],
                'is_active' => 1,
            ]);
        }
        $this->info('Medical action categories ready (' . count(self::CATEGORIES) . ')');

        foreach (self::ACTIONS as $action) {
            if (MedicalAction::findBy('code', $action['code']) === null) {
                MedicalAction::create([
                    'category_id' => $categoryIds[$action['category']],
                    'code' => $action['code'],
                    'name' => $action['name'],
                    'price' => $action['price'],
                    'duration' => $action['duration'],
                    'status' => 'ACTIVE',
                ]);
            }
        }
        $this->info('Medical actions ready (' . count(self::ACTIONS) . ')');
    }
}
