<?php \App\Core\View::extend('layouts.app'); ?>

<div class="breadcrumb-custom"><a href="/dashboard">Dashboard</a> / Jenis Pemeriksaan Lab</div>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
    <div>
        <h1 class="page-title mb-1">Jenis Pemeriksaan Lab</h1>
        <p class="text-muted-custom mb-0">Kelola jenis pemeriksaan laboratorium beserta parameter dan nilai rujukan.</p>
    </div>
    <?php if (can('lab_test.create')): ?>
    <a href="/settings/lab-tests/create" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah Pemeriksaan</a>
    <?php endif; ?>
</div>

<div class="surface-card p-3 mb-3">
    <form method="GET" action="/settings/lab-tests" class="row g-2 align-items-end">
        <div class="col-12 col-md-6">
            <label class="form-label">Cari</label>
            <input type="text" name="search" class="form-control" placeholder="Nama atau kode pemeriksaan" value="<?= e($filters['search']) ?>">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="bi bi-funnel me-1"></i>Cari</button>
            <a href="/settings/lab-tests" class="btn btn-link btn-sm">Reset</a>
        </div>
    </form>
</div>

<div class="surface-card p-3">
    <?php if ($tests === []): ?>
        <div class="empty-state">
            <i class="bi bi-clipboard2-pulse d-block"></i>
            <h6>Belum ada jenis pemeriksaan</h6>
            <?php if (can('lab_test.create')): ?><a href="/settings/lab-tests/create" class="btn btn-primary btn-sm mt-2">Tambah Pemeriksaan</a><?php endif; ?>
        </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Kode</th><th>Nama</th><th>Kategori</th><th>Sampel</th><th>Harga</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($tests as $t): ?>
                <tr>
                    <td><?= e($t['code']) ?></td>
                    <td><?= e($t['name']) ?></td>
                    <td><?= e($t['category'] ?? '-') ?></td>
                    <td><?= e($t['sample_type'] ?? '-') ?></td>
                    <td><?= format_currency($t['price']) ?></td>
                    <td><span class="badge-status <?= $t['status'] === 'ACTIVE' ? 'badge-success' : 'badge-muted' ?>"><?= $t['status'] === 'ACTIVE' ? 'Aktif' : 'Nonaktif' ?></span></td>
                    <td class="text-end">
                        <a href="/settings/lab-tests/<?= e($t['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-list-check"></i></a>
                        <?php if (can('lab_test.update')): ?><a href="/settings/lab-tests/<?= e($t['id']) ?>/edit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a><?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
