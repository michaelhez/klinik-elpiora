<?php

declare(strict_types=1);

namespace App\Controllers\Settings;

use App\Core\Controller;
use App\Core\Request;
use App\Services\SystemSettingService;

class SystemController extends Controller
{
    public function __construct(private readonly SystemSettingService $service = new SystemSettingService())
    {
    }

    public function edit(Request $request): never
    {
        $this->view('settings.system', [
            'title' => 'Pengaturan Sistem',
            'settings' => $this->service->list(),
        ]);
    }

    public function update(Request $request): never
    {
        $this->service->update($request->all());

        flash('success', 'Pengaturan sistem berhasil diperbarui.');
        $this->redirect('/settings/system');
    }
}
