<?php \App\Core\View::extend('layouts.app'); ?>

<div class="breadcrumb-custom"><a href="/dashboard">Dashboard</a> / Karyawan</div>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
    <div>
        <h1 class="page-title mb-1">Karyawan</h1>
        <p class="text-muted-custom mb-0">Kelola data karyawan klinik.</p>
    </div>
    <?php if (can('employee.create')): ?>
    <a href="/employees/create" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah Karyawan</a>
    <?php endif; ?>
</div>

<div class="surface-card p-3 mb-3">
    <form method="GET" action="/employees" class="row g-2 align-items-end">
        <div class="col-12 col-md-4">
            <label class="form-label">Cari</label>
            <input type="text" name="search" class="form-control" placeholder="Nama, NIP, atau NIK" value="<?= e($filters['search']) ?>">
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label">Cabang</label>
            <select name="branch_id" class="form-select">
                <option value="">Semua Cabang</option>
                <?php foreach ($branches as $branch): ?>
                <option value="<?= e($branch['id']) ?>" <?= (string) $filters['branch_id'] === (string) $branch['id'] ? 'selected' : '' ?>><?= e($branch['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label">Departemen</label>
            <select name="department_id" class="form-select">
                <option value="">Semua Departemen</option>
                <?php foreach ($departments as $department): ?>
                <option value="<?= e($department['id']) ?>" <?= (string) $filters['department_id'] === (string) $department['id'] ? 'selected' : '' ?>><?= e($department['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">Semua</option>
                <option value="ACTIVE" <?= $filters['status'] === 'ACTIVE' ? 'selected' : '' ?>>Aktif</option>
                <option value="INACTIVE" <?= $filters['status'] === 'INACTIVE' ? 'selected' : '' ?>>Nonaktif</option>
                <option value="RESIGNED" <?= $filters['status'] === 'RESIGNED' ? 'selected' : '' ?>>Resign</option>
            </select>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="bi bi-funnel me-1"></i>Terapkan Filter</button>
            <a href="/employees" class="btn btn-link btn-sm">Reset</a>
        </div>
    </form>
</div>

<div class="surface-card p-3">
    <?php if ($employees === []): ?>
        <div class="empty-state">
            <i class="bi bi-people d-block"></i>
            <h6>Tidak ada data karyawan</h6>
            <p class="mb-3 small">Tidak ada karyawan yang cocok dengan pencarian Anda.</p>
            <?php if (can('employee.create')): ?><a href="/employees/create" class="btn btn-primary btn-sm">Tambah Karyawan</a><?php endif; ?>
        </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Karyawan</th><th>Cabang</th><th>Departemen</th><th>Jabatan</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($employees as $row): ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar-circle"><?= e(mb_strtoupper(mb_substr($row['name'], 0, 1))) ?></div>
                            <div>
                                <div class="card-title-sm"><?= e($row['name']) ?></div>
                                <div class="text-caption"><?= e($row['employee_number']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td><?= e($row['branch_name']) ?></td>
                    <td><?= e($row['department_name'] ?? '-') ?></td>
                    <td><?= e($row['position_name'] ?? '-') ?></td>
                    <td>
                        <?php
                        $statusClass = match ($row['status']) {
                            'ACTIVE' => 'badge-success',
                            'INACTIVE' => 'badge-warning',
                            default => 'badge-muted',
                        };
                        $statusLabel = match ($row['status']) {
                            'ACTIVE' => 'Aktif', 'INACTIVE' => 'Nonaktif', default => 'Resign',
                        };
                        ?>
                        <span class="badge-status <?= $statusClass ?>"><?= $statusLabel ?></span>
                    </td>
                    <td class="text-end">
                        <a href="/employees/<?= e($row['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                        <?php if (can('employee.update')): ?><a href="/employees/<?= e($row['id']) ?>/edit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a><?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php \App\Core\View::include('components.pagination', ['pagination' => $pagination, 'baseUrl' => '/employees']); ?>
    <?php endif; ?>
</div>
