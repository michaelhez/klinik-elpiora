<?php \App\Core\View::extend('layouts.app'); ?>

<div class="breadcrumb-custom"><a href="/dashboard">Dashboard</a> / Obat</div>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
    <div>
        <h1 class="page-title mb-1">Obat</h1>
        <p class="text-muted-custom mb-0">Kelola data obat dan stok.</p>
    </div>
    <?php if (can('medicine.create')): ?>
    <a href="/medicines/create" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah Obat</a>
    <?php endif; ?>
</div>

<div class="surface-card p-3 mb-3">
    <form method="GET" action="/medicines" class="row g-2 align-items-end">
        <div class="col-12 col-md-6">
            <label class="form-label">Cari</label>
            <input type="text" name="search" class="form-control" placeholder="Nama, kode, atau nama generik" value="<?= e($filters['search']) ?>">
        </div>
        <div class="col-6 col-md-4">
            <label class="form-label">Kategori</label>
            <select name="category_id" class="form-select">
                <option value="">Semua Kategori</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= e($cat['id']) ?>" <?= (string) $filters['category_id'] === (string) $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="bi bi-funnel me-1"></i>Cari</button>
            <a href="/medicines" class="btn btn-link btn-sm">Reset</a>
        </div>
    </form>
</div>

<div class="surface-card p-3">
    <?php if ($medicines === []): ?>
        <div class="empty-state">
            <i class="bi bi-capsule d-block"></i>
            <h6>Belum ada data obat</h6>
            <?php if (can('medicine.create')): ?><a href="/medicines/create" class="btn btn-primary btn-sm mt-2">Tambah Obat</a><?php endif; ?>
        </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Kode</th><th>Nama</th><th>Kategori</th><th>Satuan</th><th>Harga Jual</th><th>Stok</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($medicines as $m): ?>
                <tr>
                    <td><?= e($m['code']) ?></td>
                    <td><?= e($m['name']) ?><?php if ($m['generic_name']): ?><div class="text-caption"><?= e($m['generic_name']) ?></div><?php endif; ?></td>
                    <td><?= e($m['category_name'] ?? '-') ?></td>
                    <td><?= e($m['unit_name'] ?? '-') ?></td>
                    <td><?= format_currency($m['selling_price']) ?></td>
                    <td>
                        <?php $lowStock = $m['current_stock'] <= $m['minimum_stock']; ?>
                        <span class="badge-status <?= $lowStock ? 'badge-danger' : 'badge-success' ?>"><?= (int) $m['current_stock'] ?></span>
                    </td>
                    <td><span class="badge-status <?= $m['status'] === 'ACTIVE' ? 'badge-success' : 'badge-muted' ?>"><?= $m['status'] === 'ACTIVE' ? 'Aktif' : 'Nonaktif' ?></span></td>
                    <td class="text-end">
                        <a href="/medicines/<?= e($m['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                        <?php if (can('medicine.update')): ?><a href="/medicines/<?= e($m['id']) ?>/edit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a><?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php \App\Core\View::include('components.pagination', ['pagination' => $pagination, 'baseUrl' => '/medicines']); ?>
    <?php endif; ?>
</div>
