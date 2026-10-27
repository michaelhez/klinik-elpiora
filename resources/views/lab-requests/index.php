<?php \App\Core\View::extend('layouts.app'); ?>

<div class="breadcrumb-custom"><a href="/dashboard">Dashboard</a> / Laboratorium</div>
<h1 class="page-title mb-1">Laboratorium</h1>
<p class="text-muted-custom mb-4">Antrian permintaan pemeriksaan laboratorium.</p>

<div class="surface-card p-3 mb-3">
    <form method="GET" action="/lab-requests" class="row g-2 align-items-end">
        <div class="col-6 col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">Semua</option>
                <?php foreach (['REQUESTED' => 'Diminta', 'SAMPLE_COLLECTED' => 'Sampel Diambil', 'PROCESSING' => 'Diproses', 'COMPLETED' => 'Selesai', 'VALIDATED' => 'Tervalidasi', 'CANCELLED' => 'Dibatalkan'] as $val => $label): ?>
                <option value="<?= $val ?>" <?= $filters['status'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>
</div>

<div class="surface-card p-3">
    <?php if ($requests === []): ?>
        <div class="empty-state">
            <i class="bi bi-flask d-block"></i>
            <h6>Tidak ada permintaan lab</h6>
        </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>No. Permintaan</th><th>Pasien</th><th>Dokter</th><th>Status</th><th>Tanggal</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($requests as $r): ?>
                <?php
                $labels = ['REQUESTED' => 'Diminta', 'SAMPLE_COLLECTED' => 'Sampel Diambil', 'PROCESSING' => 'Diproses', 'COMPLETED' => 'Selesai', 'VALIDATED' => 'Tervalidasi', 'CANCELLED' => 'Dibatalkan'];
                $badge = match ($r['status']) {
                    'VALIDATED' => 'badge-success', 'COMPLETED' => 'badge-info', 'CANCELLED' => 'badge-danger', default => 'badge-warning',
                };
                ?>
                <tr>
                    <td><?= e($r['request_number']) ?></td>
                    <td><?= e($r['patient_name']) ?><div class="text-caption"><?= e($r['medical_record_number']) ?></div></td>
                    <td><?= e($r['doctor_name']) ?></td>
                    <td><span class="badge-status <?= $badge ?>"><?= $labels[$r['status']] ?? $r['status'] ?></span></td>
                    <td><?= format_date($r['requested_at'], 'd M Y H:i') ?></td>
                    <td class="text-end"><a href="/lab-requests/<?= e($r['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
