<?php \App\Core\View::extend('layouts.app'); ?>

<div class="breadcrumb-custom"><a href="/dashboard">Dashboard</a> / Pemasok</div>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
    <div>
        <h1 class="page-title mb-1">Pemasok</h1>
        <p class="text-muted-custom mb-0">Kelola data pemasok obat dan alat kesehatan.</p>
    </div>
    <?php if (can('supplier.create')): ?>
    <button type="button" class="btn btn-primary btn-sm" onclick="Sup.openModal()"><i class="bi bi-plus-lg me-1"></i>Tambah Pemasok</button>
    <?php endif; ?>
</div>

<div class="surface-card p-3 mb-3">
    <form method="GET" action="/suppliers" class="row g-2">
        <div class="col-12 col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Cari nama pemasok..." value="<?= e($filters['search']) ?>">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
        </div>
    </form>
</div>

<div class="surface-card p-3">
    <?php if ($suppliers === []): ?>
        <div class="empty-state"><i class="bi bi-truck d-block"></i><h6>Belum ada data pemasok</h6></div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Kode</th><th>Nama</th><th>Kontak</th><th>Telepon</th><th>Email</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($suppliers as $row): ?>
                <tr data-id="<?= e($row['id']) ?>">
                    <td><?= e($row['code']) ?></td>
                    <td><?= e($row['name']) ?></td>
                    <td><?= e($row['contact_person'] ?? '-') ?></td>
                    <td><?= e($row['phone'] ?? '-') ?></td>
                    <td><?= e($row['email'] ?? '-') ?></td>
                    <td><span class="badge-status <?= $row['status'] === 'ACTIVE' ? 'badge-success' : 'badge-muted' ?>"><?= $row['status'] === 'ACTIVE' ? 'Aktif' : 'Nonaktif' ?></span></td>
                    <td class="text-end">
                        <?php if (can('supplier.update')): ?><button class="btn btn-sm btn-outline-secondary" onclick='Sup.openModal(<?= json_encode($row) ?>)'><i class="bi bi-pencil"></i></button><?php endif; ?>
                        <?php if (can('supplier.delete')): ?><button class="btn btn-sm btn-outline-danger" onclick="Sup.remove(<?= e($row['id']) ?>)"><i class="bi bi-trash"></i></button><?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="supplierModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form id="supplierForm" data-guard>
            <div class="modal-header"><h5 class="modal-title" id="supplierModalTitle">Tambah Pemasok</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-2">
                    <div class="col-6 mb-3">
                        <label class="form-label form-label-required">Kode</label>
                        <input type="text" name="code" id="s_code" class="form-control" required>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" id="s_status" class="form-select">
                            <option value="ACTIVE">Aktif</option>
                            <option value="INACTIVE">Nonaktif</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label form-label-required">Nama Pemasok</label>
                    <input type="text" name="name" id="s_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Kontak Person</label>
                    <input type="text" name="contact_person" id="s_contact_person" class="form-control">
                </div>
                <div class="row g-2">
                    <div class="col-6 mb-3">
                        <label class="form-label">Telepon</label>
                        <input type="text" name="phone" id="s_phone" class="form-control">
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="s_email" class="form-control">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Alamat</label>
                    <textarea name="address" id="s_address" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" data-loading-text="Menyimpan...">Simpan</button>
            </div>
        </form>
    </div></div>
</div>

<script src="<?= asset('js/suppliers.js') ?>"></script>
