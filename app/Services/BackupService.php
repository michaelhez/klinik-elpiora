<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;

final class BackupService
{
    private string $backupDir;

    public function __construct(private readonly AuditService $auditService = new AuditService())
    {
        $this->backupDir = base_path('storage/backups');
    }

    public function list(): array
    {
        $files = glob($this->backupDir . '/*.sql') ?: [];
        rsort($files);

        return array_map(fn (string $path) => $this->describe(basename($path)), $files);
    }

    public function create(): array
    {
        $config = config('database');
        $filename = 'backup-' . date('Y-m-d_His') . '.sql';
        $path = $this->backupDir . '/' . $filename;

        $passwordArg = ($config['password'] ?? '') !== '' ? '-p' . escapeshellarg($config['password']) : '';

        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s %s --single-transaction %s > %s 2>&1',
            escapeshellarg($config['host']),
            escapeshellarg((string) $config['port']),
            escapeshellarg($config['username']),
            $passwordArg,
            escapeshellarg($config['database']),
            escapeshellarg($path)
        );

        exec($command, $output, $exitCode);

        if ($exitCode !== 0 || !file_exists($path) || filesize($path) === 0) {
            @unlink($path);
            throw new ValidationException(['backup' => ['Backup database gagal dibuat. Periksa konfigurasi database dan ketersediaan mysqldump.']]);
        }

        $this->auditService->log('CREATE', 'backup', 'system', null, null, ['filename' => $filename]);

        return $this->describe($filename);
    }

    public function path(string $filename): string
    {
        $safeName = basename($filename);
        $path = $this->backupDir . '/' . $safeName;

        if (!str_ends_with($safeName, '.sql') || !file_exists($path)) {
            throw new NotFoundException('File backup tidak ditemukan.');
        }

        return $path;
    }

    private function describe(string $filename): array
    {
        $path = $this->backupDir . '/' . $filename;

        return [
            'filename' => $filename,
            'size' => filesize($path),
            'size_human' => $this->humanSize((int) filesize($path)),
            'created_at' => date('Y-m-d H:i:s', filemtime($path)),
        ];
    }

    private function humanSize(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        if ($bytes < 1048576) {
            return round($bytes / 1024, 1) . ' KB';
        }

        return round($bytes / 1048576, 1) . ' MB';
    }
}
