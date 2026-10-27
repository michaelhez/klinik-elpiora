<?php \App\Core\View::extend('layouts.app'); ?>

<div class="breadcrumb-custom">
    <a href="/dashboard">Dashboard</a> / <a href="/settings/organization">Pengaturan</a> / Data Farmasi
</div>
<h1 class="page-title">Data Farmasi</h1>
<p class="text-muted-custom mb-4">Kelola kategori dan satuan obat.</p>

<div class="surface-card p-3 p-md-4">
    <ul class="nav nav-pills flex-wrap gap-1 mb-4">
        <?php if (can('medicine_category.view')): ?>
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-category" type="button">Kategori Obat</button></li>
        <?php endif; ?>
        <?php if (can('medicine_unit.view')): ?>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-unit" type="button">Satuan</button></li>
        <?php endif; ?>
        <?php if (can('medicine.view')): ?>
        <li class="nav-item"><a href="/medicines" class="nav-link">Daftar Obat <i class="bi bi-box-arrow-up-right ms-1" style="font-size:.7rem;"></i></a></li>
        <?php endif; ?>
    </ul>

    <div class="tab-content">
        <?php if (can('medicine_category.view')): ?>
        <div class="tab-pane fade show active" id="tab-category">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="section-title mb-0">Kategori Obat</h2>
                <?php if (can('medicine_category.create')): ?>
                <button type="button" class="btn btn-primary btn-sm" onclick="Pharm.openModal('category')"><i class="bi bi-plus-lg me-1"></i>Tambah Kategori</button>
                <?php endif; ?>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Nama</th><th>Kode</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($categories as $row): ?>
                        <tr data-id="<?= e($row['id']) ?>">
                            <td><?= e($row['name']) ?></td>
                            <td><?= e($row['code']) ?></td>
                            <td><span class="badge-status <?= $row['is_active'] ? 'badge-success' : 'badge-muted' ?>"><?= $row['is_active'] ? 'Aktif' : 'Nonaktif' ?></span></td>
                            <td class="text-end">
                                <?php if (can('medicine_category.update')): ?><button class="btn btn-sm btn-outline-secondary" onclick='Pharm.openModal("category", <?= json_encode($row) ?>)'><i class="bi bi-pencil"></i></button><?php endif; ?>
                                <?php if (can('medicine_category.delete')): ?><button class="btn btn-sm btn-outline-danger" onclick="Pharm.remove('category', <?= e($row['id']) ?>)"><i class="bi bi-trash"></i></button><?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if ($categories === []): ?><div class="empty-state"><i class="bi bi-capsule d-block"></i><h6>Belum ada kategori</h6></div><?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (can('medicine_unit.view')): ?>
        <div class="tab-pane fade" id="tab-unit">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="section-title mb-0">Satuan</h2>
                <?php if (can('medicine_unit.create')): ?>
                <button type="button" class="btn btn-primary btn-sm" onclick="Pharm.openModal('unit')"><i class="bi bi-plus-lg me-1"></i>Tambah Satuan</button>
                <?php endif; ?>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Nama</th><th>Kode</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($units as $row): ?>
                        <tr data-id="<?= e($row['id']) ?>">
                            <td><?= e($row['name']) ?></td>
                            <td><?= e($row['code']) ?></td>
                            <td><span class="badge-status <?= $row['is_active'] ? 'badge-success' : 'badge-muted' ?>"><?= $row['is_active'] ? 'Aktif' : 'Nonaktif' ?></span></td>
                            <td class="text-end">
                                <?php if (can('medicine_unit.update')): ?><button class="btn btn-sm btn-outline-secondary" onclick='Pharm.openModal("unit", <?= json_encode($row) ?>)'><i class="bi bi-pencil"></i></button><?php endif; ?>
                                <?php if (can('medicine_unit.delete')): ?><button class="btn btn-sm btn-outline-danger" onclick="Pharm.remove('unit', <?= e($row['id']) ?>)"><i class="bi bi-trash"></i></button><?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if ($units === []): ?><div class="empty-state"><i class="bi bi-rulers d-block"></i><h6>Belum ada satuan</h6></div><?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="pharmModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form id="pharmForm" data-guard>
            <div class="modal-header"><h5 class="modal-title" id="pharmModalTitle">Tambah Data</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="pharmModalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" data-loading-text="Menyimpan...">Simpan</button>
            </div>
        </form>
    </div></div>
</div>

<script src="<?= asset('js/pharmacy-settings.js') ?>"></script>
