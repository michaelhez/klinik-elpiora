<?php \App\Core\View::extend('layouts.app'); ?>

<div class="breadcrumb-custom"><a href="/dashboard">Dashboard</a> / Backup Database</div>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
    <div>
        <h1 class="page-title mb-1">Backup Database</h1>
        <p class="text-muted-custom mb-0">Buat dan unduh cadangan database sistem.</p>
    </div>
    <?php if (can('backup.create')): ?>
    <form action="/settings/backup" method="POST" onsubmit="return confirm('Buat backup database sekarang? Proses ini mungkin memakan waktu beberapa saat.');">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-primary"><i class="bi bi-database-add me-1"></i>Buat Backup Baru</button>
    </form>
    <?php endif; ?>
</div>

<div class="alert alert-warning d-flex align-items-start gap-2 mb-4">
    <i class="bi bi-exclamation-triangle-fill mt-1"></i>
    <div>Fitur restore database tidak tersedia melalui antarmuka ini demi keamanan. Untuk memulihkan data dari backup, hubungi administrator sistem untuk melakukan restore secara manual melalui akses database langsung.</div>
</div>

<div class="surface-card p-3">
    <?php if ($backups === []): ?>
        <div class="empty-state"><i class="bi bi-database d-block"></i><h6>Belum ada backup</h6></div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Nama File</th><th>Ukuran</th><th>Dibuat</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($backups as $backup): ?>
                <tr>
                    <td><?= e($backup['filename']) ?></td>
                    <td><?= e($backup['size_human']) ?></td>
                    <td><?= format_date($backup['created_at'], 'd M Y H:i') ?></td>
                    <td class="text-end"><a href="/settings/backup/<?= e($backup['filename']) ?>/download" class="btn btn-sm btn-outline-secondary"><i class="bi bi-download"></i></a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
