<?php \App\Core\View::extend('layouts.app'); ?>

<div class="breadcrumb-custom">
    <a href="/dashboard">Dashboard</a> / <a href="/settings/organization">Pengaturan</a> / Data Keuangan
</div>
<h1 class="page-title">Data Keuangan</h1>
<p class="text-muted-custom mb-4">Kelola data asuransi dan perusahaan rekanan untuk penagihan.</p>

<div class="surface-card p-3 p-md-4">
    <ul class="nav nav-pills flex-wrap gap-1 mb-4">
        <?php if (can('insurance.view')): ?>
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-insurance" type="button">Asuransi</button></li>
        <?php endif; ?>
        <?php if (can('corporate.view')): ?>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-corporate" type="button">Perusahaan Rekanan</button></li>
        <?php endif; ?>
    </ul>

    <div class="tab-content">
        <?php if (can('insurance.view')): ?>
        <div class="tab-pane fade show active" id="tab-insurance">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="section-title mb-0">Asuransi</h2>
                <?php if (can('insurance.create')): ?>
                <button type="button" class="btn btn-primary btn-sm" onclick="Fin.openModal('insurance')"><i class="bi bi-plus-lg me-1"></i>Tambah Asuransi</button>
                <?php endif; ?>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Nama</th><th>Kode</th><th>Tipe</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($insurances as $row): ?>
                        <tr data-id="<?= e($row['id']) ?>">
                            <td><?= e($row['name']) ?></td>
                            <td><?= e($row['code']) ?></td>
                            <td><?= e($row['type']) ?></td>
                            <td><span class="badge-status <?= $row['status'] === 'ACTIVE' ? 'badge-success' : 'badge-muted' ?>"><?= $row['status'] === 'ACTIVE' ? 'Aktif' : 'Nonaktif' ?></span></td>
                            <td class="text-end">
                                <?php if (can('insurance.update')): ?><button class="btn btn-sm btn-outline-secondary" onclick='Fin.openModal("insurance", <?= json_encode($row) ?>)'><i class="bi bi-pencil"></i></button><?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if ($insurances === []): ?><div class="empty-state"><i class="bi bi-shield-check d-block"></i><h6>Belum ada data asuransi</h6></div><?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (can('corporate.view')): ?>
        <div class="tab-pane fade" id="tab-corporate">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="section-title mb-0">Perusahaan Rekanan</h2>
                <?php if (can('corporate.create')): ?>
                <button type="button" class="btn btn-primary btn-sm" onclick="Fin.openModal('corporate')"><i class="bi bi-plus-lg me-1"></i>Tambah Perusahaan</button>
                <?php endif; ?>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Nama</th><th>Kode</th><th>PIC</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($corporates as $row): ?>
                        <tr data-id="<?= e($row['id']) ?>">
                            <td><?= e($row['name']) ?></td>
                            <td><?= e($row['code']) ?></td>
                            <td><?= e($row['pic_name'] ?? '-') ?></td>
                            <td><span class="badge-status <?= $row['status'] === 'ACTIVE' ? 'badge-success' : 'badge-muted' ?>"><?= $row['status'] === 'ACTIVE' ? 'Aktif' : 'Nonaktif' ?></span></td>
                            <td class="text-end">
                                <?php if (can('corporate.update')): ?><button class="btn btn-sm btn-outline-secondary" onclick='Fin.openModal("corporate", <?= json_encode($row) ?>)'><i class="bi bi-pencil"></i></button><?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if ($corporates === []): ?><div class="empty-state"><i class="bi bi-building d-block"></i><h6>Belum ada perusahaan rekanan</h6></div><?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="finModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form id="finForm" data-guard>
            <div class="modal-header"><h5 class="modal-title" id="finModalTitle">Tambah Data</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="finModalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" data-loading-text="Menyimpan...">Simpan</button>
            </div>
        </form>
    </div></div>
</div>

<script src="<?= asset('js/finance-settings.js') ?>"></script>
