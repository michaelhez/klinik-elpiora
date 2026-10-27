<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SystemSetting;

final class SystemSettingService
{
    private const EDITABLE_KEYS = [
        'clinic_name' => 'Nama Klinik',
        'clinic_address' => 'Alamat Klinik',
        'clinic_phone' => 'Telepon Klinik',
        'clinic_email' => 'Email Klinik',
        'timezone' => 'Zona Waktu',
        'currency' => 'Mata Uang',
        'date_format' => 'Format Tanggal',
    ];

    public function __construct(private readonly AuditService $auditService = new AuditService())
    {
    }

    public function list(): array
    {
        $settings = [];

        foreach (self::EDITABLE_KEYS as $key => $label) {
            $settings[] = [
                'key' => $key,
                'label' => $label,
                'value' => SystemSetting::get($key, ''),
            ];
        }

        return $settings;
    }

    public function update(array $data): void
    {
        $before = $this->list();

        foreach (self::EDITABLE_KEYS as $key => $label) {
            if (array_key_exists($key, $data)) {
                SystemSetting::set($key, (string) $data[$key]);
            }
        }

        $this->auditService->log('UPDATE', 'setting', 'system_settings', null, $before, $data);
    }
}
