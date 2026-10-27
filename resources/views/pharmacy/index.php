<?php \App\Core\View::extend('layouts.app'); ?>

<div class="breadcrumb-custom"><a href="/dashboard">Dashboard</a> / Farmasi</div>
<h1 class="page-title mb-1">Farmasi</h1>
<p class="text-muted-custom mb-4">Antrian resep yang perlu diverifikasi, disiapkan, atau diserahkan.</p>

<div class="surface-card p-3 mb-3">
    <form method="GET" action="/pharmacy" class="row g-2 align-items-end">
        <div class="col-6 col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">Aktif (Menunggu, Diproses, Siap)</option>
                <?php foreach (['WAITING_PHARMACY' => 'Menunggu Farmasi', 'PROCESSING' => 'Diproses', 'READY' => 'Siap', 'DISPENSED' => 'Sudah Diserahkan', 'CANCELLED' => 'Dibatalkan'] as $val => $label): ?>
                <option value="<?= $val ?>" <?= $filters['status'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>
</div>

<div class="surface-card p-3">
    <?php if ($prescriptions === []): ?>
        <div class="empty-state">
            <i class="bi bi-capsule d-block"></i>
            <h6>Tidak ada resep dalam antrian</h6>
        </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>No. Resep</th><th>Pasien</th><th>Dokter</th><th>Status</th><th>Tanggal</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            <?php
            $labels = ['DRAFT' => 'Draf', 'WAITING_PHARMACY' => 'Menunggu Farmasi', 'PROCESSING' => 'Diproses', 'READY' => 'Siap', 'DISPENSED' => 'Diserahkan', 'CANCELLED' => 'Dibatalkan'];
            $badges = ['DISPENSED' => 'badge-success', 'READY' => 'badge-info', 'CANCELLED' => 'badge-danger', 'PROCESSING' => 'badge-warning', 'WAITING_PHARMACY' => 'badge-muted'];
            ?>
            <?php foreach ($prescriptions as $rx): ?>
                <tr>
                    <td><?= e($rx['prescription_number']) ?></td>
                    <td><?= e($rx['patient_name']) ?><div class="text-caption"><?= e($rx['medical_record_number']) ?></div></td>
                    <td><?= e($rx['doctor_name']) ?></td>
                    <td><span class="badge-status <?= $badges[$rx['status']] ?? 'badge-muted' ?>"><?= $labels[$rx['status']] ?? $rx['status'] ?></span></td>
                    <td><?= format_date($rx['created_at'], 'd M Y H:i') ?></td>
                    <td class="text-end"><a href="/prescriptions/<?= e($rx['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
