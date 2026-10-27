<?php \App\Core\View::extend('layouts.app'); ?>

<div class="breadcrumb-custom"><a href="/dashboard">Dashboard</a> / Pendaftaran</div>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
    <div>
        <h1 class="page-title mb-1">Pendaftaran</h1>
        <p class="text-muted-custom mb-0">Daftar kunjungan pasien dan status antrian.</p>
    </div>
    <?php if (can('registration.create')): ?>
    <a href="/registrations/create" class="btn btn-primary"><i class="bi bi-clipboard-plus me-1"></i>Pendaftaran Baru</a>
    <?php endif; ?>
</div>

<div class="surface-card p-3 mb-3">
    <form method="GET" action="/registrations" class="row g-2 align-items-end">
        <div class="col-12 col-md-4">
            <label class="form-label">Cari</label>
            <input type="text" name="search" class="form-control" placeholder="Nama pasien atau nomor registrasi" value="<?= e($filters['search']) ?>">
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label">Tanggal</label>
            <input type="date" name="date" class="form-control" value="<?= e($filters['date']) ?>">
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">Semua</option>
                <option value="REGISTERED" <?= $filters['status'] === 'REGISTERED' ? 'selected' : '' ?>>Terdaftar</option>
                <option value="CANCELLED" <?= $filters['status'] === 'CANCELLED' ? 'selected' : '' ?>>Dibatalkan</option>
            </select>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="bi bi-funnel me-1"></i>Terapkan Filter</button>
            <a href="/registrations" class="btn btn-link btn-sm">Reset</a>
        </div>
    </form>
</div>

<div class="surface-card p-3">
    <?php if ($registrations === []): ?>
        <div class="empty-state">
            <i class="bi bi-clipboard2-pulse d-block"></i>
            <h6>Tidak ada data pendaftaran</h6>
            <?php if (can('registration.create')): ?><a href="/registrations/create" class="btn btn-primary btn-sm mt-2">Pendaftaran Baru</a><?php endif; ?>
        </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>No. Registrasi</th><th>Pasien</th><th>Poliklinik</th><th>Dokter</th><th>No. Antrian</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($registrations as $reg): ?>
                <tr>
                    <td><?= e($reg['registration_number']) ?></td>
                    <td><?= e($reg['patient_name']) ?><div class="text-caption"><?= e($reg['medical_record_number']) ?></div></td>
                    <td><?= e($reg['polyclinic_name']) ?></td>
                    <td><?= e($reg['doctor_name'] ?? '-') ?></td>
                    <td><span class="fw-semibold"><?= e($reg['queue_number'] ?? '-') ?></span></td>
                    <td>
                        <?php
                        $label = $reg['status'] === 'CANCELLED' ? 'Dibatalkan' : ($reg['queue_status'] ?? 'Terdaftar');
                        $badge = $reg['status'] === 'CANCELLED' ? 'badge-danger' : ($reg['queue_status'] === 'COMPLETED' ? 'badge-success' : 'badge-info');
                        ?>
                        <span class="badge-status <?= $badge ?>"><?= e($label) ?></span>
                    </td>
                    <td class="text-end">
                        <a href="/registrations/<?= e($reg['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php \App\Core\View::include('components.pagination', ['pagination' => $pagination, 'baseUrl' => '/registrations']); ?>
    <?php endif; ?>
</div>
