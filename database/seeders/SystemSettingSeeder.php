<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Core\Seeder;
use App\Models\SystemSetting;

class SystemSettingSeeder extends Seeder
{
    private const DEFAULTS = [
        'clinic_name' => 'Klinik Sehat Sentosa',
        'clinic_address' => 'Jl. Kesehatan No. 10, Jakarta',
        'clinic_phone' => '(021) 555-0100',
        'clinic_email' => 'info@kliniksehatsentosa.local',
        'timezone' => 'Asia/Jakarta',
        'currency' => 'IDR',
        'date_format' => 'd M Y',
        'invoice_prefix' => 'INV',
        'medical_record_prefix' => 'RM',
        'prescription_prefix' => 'RX',
        'queue_prefix' => 'A',
    ];

    public function run(): void
    {
        foreach (self::DEFAULTS as $key => $value) {
            if (SystemSetting::get($key) === null) {
                SystemSetting::set($key, $value);
                $this->info("Setting seeded: {$key} = {$value}");
            }
        }
    }
}
