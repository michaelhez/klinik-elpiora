<?php \App\Core\View::extend('layouts.app'); ?>

<div class="breadcrumb-custom"><a href="/dashboard">Dashboard</a> / Pasien</div>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
    <div>
        <h1 class="page-title mb-1">Pasien</h1>
        <p class="text-muted-custom mb-0">Kelola data rekam medis pasien.</p>
    </div>
    <?php if (can('patient.create')): ?>
    <a href="/patients/create" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah Pasien</a>
    <?php endif; ?>
</div>

<div class="surface-card p-3 mb-3">
    <form method="GET" action="/patients" class="row g-2 align-items-end">
        <div class="col-12 col-md-5">
            <label class="form-label">Cari</label>
            <input type="text" name="search" class="form-control" placeholder="Nama, No. RM, NIK, atau telepon" value="<?= e($filters['search']) ?>">
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label">Jenis Kelamin</label>
            <select name="gender" class="form-select">
                <option value="">Semua</option>
                <option value="MALE" <?= $filters['gender'] === 'MALE' ? 'selected' : '' ?>>Laki-laki</option>
                <option value="FEMALE" <?= $filters['gender'] === 'FEMALE' ? 'selected' : '' ?>>Perempuan</option>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">Semua</option>
                <option value="ACTIVE" <?= $filters['status'] === 'ACTIVE' ? 'selected' : '' ?>>Aktif</option>
                <option value="INACTIVE" <?= $filters['status'] === 'INACTIVE' ? 'selected' : '' ?>>Nonaktif</option>
            </select>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="bi bi-funnel me-1"></i>Terapkan Filter</button>
            <a href="/patients" class="btn btn-link btn-sm">Reset</a>
        </div>
    </form>
</div>

<div class="surface-card p-3">
    <?php if ($patients === []): ?>
        <div class="empty-state">
            <i class="bi bi-people d-block"></i>
            <h6>Tidak ada data pasien</h6>
            <p class="mb-3 small">Tidak ada pasien yang cocok dengan pencarian Anda.</p>
            <?php if (can('patient.create')): ?><a href="/patients/create" class="btn btn-primary btn-sm">Tambah Pasien</a><?php endif; ?>
        </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Pasien</th><th>No. RM</th><th>Jenis Kelamin</th><th>Telepon</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($patients as $row): ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar-circle"><?= e(mb_strtoupper(mb_substr($row['name'], 0, 1))) ?></div>
                            <div class="card-title-sm"><?= e($row['name']) ?></div>
                        </div>
                    </td>
                    <td><?= e($row['medical_record_number']) ?></td>
                    <td><?= $row['gender'] === 'MALE' ? 'Laki-laki' : 'Perempuan' ?></td>
                    <td><?= e($row['phone'] ?? '-') ?></td>
                    <td><span class="badge-status <?= $row['status'] === 'ACTIVE' ? 'badge-success' : 'badge-muted' ?>"><?= $row['status'] === 'ACTIVE' ? 'Aktif' : 'Nonaktif' ?></span></td>
                    <td class="text-end">
                        <a href="/patients/<?= e($row['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                        <?php if (can('patient.update')): ?><a href="/patients/<?= e($row['id']) ?>/edit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a><?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php \App\Core\View::include('components.pagination', ['pagination' => $pagination, 'baseUrl' => '/patients']); ?>
    <?php endif; ?>
</div>
