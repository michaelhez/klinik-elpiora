<?php

declare(strict_types=1);

namespace App\Controllers\Settings;

use App\Core\Controller;
use App\Core\Request;
use App\Exceptions\ValidationException;
use App\Services\BackupService;

class BackupController extends Controller
{
    public function __construct(private readonly BackupService $service = new BackupService())
    {
    }

    public function index(Request $request): never
    {
        $this->view('settings.backup', [
            'title' => 'Backup Database',
            'backups' => $this->service->list(),
        ]);
    }

    public function store(Request $request): never
    {
        try {
            $this->service->create();
        } catch (ValidationException $e) {
            flash('error', current($e->errors())[0] ?? 'Backup gagal dibuat.');
            $this->redirect('/settings/backup');
        }

        flash('success', 'Backup database berhasil dibuat.');
        $this->redirect('/settings/backup');
    }

    public function download(Request $request, string $filename): never
    {
        $path = $this->service->path($filename);

        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . basename($path) . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }
}
