<?php \App\Core\View::extend('layouts.app'); ?>

<div class="breadcrumb-custom"><a href="/dashboard">Dashboard</a> / Janji Temu</div>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
    <div>
        <h1 class="page-title mb-1">Janji Temu</h1>
        <p class="text-muted-custom mb-0">Kelola jadwal kunjungan pasien yang telah dijadwalkan.</p>
    </div>
    <?php if (can('appointment.create')): ?>
    <a href="/appointments/create" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Buat Janji Temu</a>
    <?php endif; ?>
</div>

<div class="surface-card p-3 mb-3">
    <form method="GET" action="/appointments" class="row g-2 align-items-end">
        <div class="col-12 col-md-4">
            <label class="form-label">Cari</label>
            <input type="text" name="search" class="form-control" placeholder="Nama pasien atau nomor" value="<?= e($filters['search']) ?>">
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label">Tanggal</label>
            <input type="date" name="date" class="form-control" value="<?= e($filters['date']) ?>">
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">Semua</option>
                <?php foreach (['BOOKED','CONFIRMED','ARRIVED','IN_SERVICE','COMPLETED','CANCELLED','NO_SHOW'] as $s): ?>
                <option value="<?= $s ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="bi bi-funnel me-1"></i>Terapkan Filter</button>
            <a href="/appointments" class="btn btn-link btn-sm">Reset</a>
        </div>
    </form>
</div>

<div class="surface-card p-3">
    <?php if ($appointments === []): ?>
        <div class="empty-state">
            <i class="bi bi-calendar-check d-block"></i>
            <h6>Tidak ada janji temu</h6>
            <?php if (can('appointment.create')): ?><a href="/appointments/create" class="btn btn-primary btn-sm mt-2">Buat Janji Temu</a><?php endif; ?>
        </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>No. Janji Temu</th><th>Pasien</th><th>Dokter</th><th>Poliklinik</th><th>Tanggal & Jam</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($appointments as $apt): ?>
                <tr>
                    <td><?= e($apt['appointment_number']) ?></td>
                    <td><?= e($apt['patient_name']) ?><div class="text-caption"><?= e($apt['medical_record_number']) ?></div></td>
                    <td><?= e($apt['doctor_name']) ?></td>
                    <td><?= e($apt['polyclinic_name']) ?></td>
                    <td><?= format_date($apt['appointment_date']) ?> <?= substr($apt['appointment_time'], 0, 5) ?></td>
                    <td>
                        <?php
                        $badge = match ($apt['status']) {
                            'COMPLETED' => 'badge-success', 'CANCELLED', 'NO_SHOW' => 'badge-danger',
                            'ARRIVED', 'IN_SERVICE' => 'badge-warning', default => 'badge-info',
                        };
                        ?>
                        <span class="badge-status <?= $badge ?>"><?= e($apt['status']) ?></span>
                    </td>
                    <td class="text-end">
                        <?php if (can('appointment.delete') && !in_array($apt['status'], ['COMPLETED', 'CANCELLED', 'NO_SHOW'], true)): ?>
                        <form action="/appointments/<?= e($apt['id']) ?>/cancel" method="POST" class="d-inline" onsubmit="return confirm('Batalkan janji temu ini?');">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger">Batalkan</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php \App\Core\View::include('components.pagination', ['pagination' => $pagination, 'baseUrl' => '/appointments']); ?>
    <?php endif; ?>
</div>
