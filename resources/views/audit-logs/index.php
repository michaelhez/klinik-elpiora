<?php \App\Core\View::extend('layouts.app'); ?>

<div class="breadcrumb-custom"><a href="/dashboard">Dashboard</a> / Audit Log</div>
<h1 class="page-title mb-1">Audit Log</h1>
<p class="text-muted-custom mb-4">Riwayat aktivitas pengguna dan perubahan data pada sistem.</p>

<div class="surface-card p-3 mb-3">
    <form method="GET" action="/audit-logs" class="row g-2 align-items-end">
        <div class="col-6 col-md-2">
            <label class="form-label">Modul</label>
            <select name="module" class="form-select">
                <option value="">Semua</option>
                <?php foreach ($modules as $module): ?>
                <option value="<?= e($module) ?>" <?= $filters['module'] === $module ? 'selected' : '' ?>><?= e($module) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label">Aksi</label>
            <select name="action" class="form-select">
                <option value="">Semua</option>
                <?php foreach ($actions as $action): ?>
                <option value="<?= e($action) ?>" <?= $filters['action'] === $action ? 'selected' : '' ?>><?= e($action) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label">Dari Tanggal</label>
            <input type="date" name="date_from" class="form-control" value="<?= e($filters['date_from']) ?>">
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label">Sampai Tanggal</label>
            <input type="date" name="date_to" class="form-control" value="<?= e($filters['date_to']) ?>">
        </div>
        <div class="col-8 col-md-3">
            <label class="form-label">Cari</label>
            <input type="text" name="search" class="form-control" placeholder="Nama tabel atau ID..." value="<?= e($filters['search']) ?>">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-funnel"></i></button>
        </div>
    </form>
</div>

<div class="surface-card p-3">
    <?php if ($logs === []): ?>
        <div class="empty-state"><i class="bi bi-journal-text d-block"></i><h6>Tidak ada log audit</h6></div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Waktu</th><th>Pengguna</th><th>Aksi</th><th>Modul</th><th>Tabel</th><th>Record</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= format_date($log['created_at'], 'd M Y H:i') ?></td>
                    <td><?= e($log['user_name'] ?? 'Sistem') ?></td>
                    <td><span class="badge-status badge-info"><?= e($log['action']) ?></span></td>
                    <td><?= e($log['module']) ?></td>
                    <td><?= e($log['table_name'] ?? '-') ?></td>
                    <td><?= e($log['record_id'] ?? '-') ?></td>
                    <td class="text-end"><button type="button" class="btn btn-sm btn-outline-secondary" onclick="AuditLog.showDetail(<?= e($log['id']) ?>)"><i class="bi bi-eye"></i></button></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php \App\Core\View::include('components.pagination', ['pagination' => $pagination, 'baseUrl' => '/audit-logs']); ?>
    <?php endif; ?>
</div>

<div class="modal fade" id="auditDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Detail Log Audit</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body" id="auditDetailBody">Memuat...</div>
    </div></div>
</div>

<script src="<?= asset('js/audit-logs.js') ?>"></script>
