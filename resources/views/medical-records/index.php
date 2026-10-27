<?php \App\Core\View::extend('layouts.app'); ?>

<div class="breadcrumb-custom"><a href="/dashboard">Dashboard</a> / Rekam Medis</div>
<h1 class="page-title mb-1">Rekam Medis</h1>
<p class="text-muted-custom mb-4">Daftar rekam medis pemeriksaan pasien.</p>

<div class="surface-card p-3 mb-3">
    <form method="GET" action="/medical-records" class="row g-2 align-items-end">
        <div class="col-12 col-md-6">
            <label class="form-label">Cari</label>
            <input type="text" name="search" class="form-control" placeholder="Nama pasien atau nomor rekam medis" value="<?= e($filters['search']) ?>">
        </div>
        <div class="col-6 col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">Semua</option>
                <?php foreach (['DRAFT' => 'Draf', 'IN_PROGRESS' => 'Berlangsung', 'FINALIZED' => 'Selesai', 'AMENDED' => 'Diamendemen'] as $val => $label): ?>
                <option value="<?= $val ?>" <?= $filters['status'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="bi bi-funnel me-1"></i>Terapkan Filter</button>
            <a href="/medical-records" class="btn btn-link btn-sm">Reset</a>
        </div>
    </form>
</div>

<div class="surface-card p-3">
    <?php if ($records === []): ?>
        <div class="empty-state">
            <i class="bi bi-file-medical d-block"></i>
            <h6>Belum ada rekam medis</h6>
            <p class="small mb-0">Rekam medis dibuat dari halaman Pendaftaran saat pasien mulai diperiksa.</p>
        </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>No. Rekam Medis</th><th>Pasien</th><th>Dokter</th><th>Status</th><th>Tanggal</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($records as $r): ?>
                <tr>
                    <td><?= e($r['record_number']) ?></td>
                    <td><?= e($r['patient_name']) ?><div class="text-caption"><?= e($r['medical_record_number']) ?></div></td>
                    <td><?= e($r['doctor_name']) ?></td>
                    <td>
                        <?php
                        $badge = match ($r['status']) {
                            'FINALIZED' => 'badge-success', 'AMENDED' => 'badge-warning',
                            'IN_PROGRESS' => 'badge-info', default => 'badge-muted',
                        };
                        $labels = ['DRAFT' => 'Draf', 'IN_PROGRESS' => 'Berlangsung', 'FINALIZED' => 'Selesai', 'AMENDED' => 'Diamendemen'];
                        ?>
                        <span class="badge-status <?= $badge ?>"><?= $labels[$r['status']] ?? $r['status'] ?></span>
                    </td>
                    <td><?= format_date($r['created_at'], 'd M Y H:i') ?></td>
                    <td class="text-end">
                        <a href="/medical-records/<?= e($r['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
