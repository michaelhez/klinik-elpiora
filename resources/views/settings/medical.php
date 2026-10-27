<?php \App\Core\View::extend('layouts.app'); ?>

<div class="breadcrumb-custom">
    <a href="/dashboard">Dashboard</a> / <a href="/settings/organization">Pengaturan</a> / Data Medis
</div>
<h1 class="page-title">Data Medis</h1>
<p class="text-muted-custom mb-4">Kelola kode diagnosis ICD-10, kategori tindakan, dan tindakan medis.</p>

<div class="surface-card p-3 p-md-4">
    <ul class="nav nav-pills flex-wrap gap-1 mb-4" id="medTabs" role="tablist">
        <?php if (can('icd10.view')): ?>
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-icd10" type="button">ICD-10</button></li>
        <?php endif; ?>
        <?php if (can('medical_action_category.view')): ?>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-category" type="button">Kategori Tindakan</button></li>
        <?php endif; ?>
        <?php if (can('medical_action.view')): ?>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-action" type="button">Tindakan Medis</button></li>
        <?php endif; ?>
        <?php if (can('lab_test.view')): ?>
        <li class="nav-item"><a href="/settings/lab-tests" class="nav-link">Jenis Pemeriksaan Lab <i class="bi bi-box-arrow-up-right ms-1" style="font-size:.7rem;"></i></a></li>
        <?php endif; ?>
    </ul>

    <div class="tab-content">
        <?php if (can('icd10.view')): ?>
        <div class="tab-pane fade show active" id="tab-icd10">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="section-title mb-0">Kode ICD-10</h2>
                <?php if (can('icd10.create')): ?>
                <button type="button" class="btn btn-primary btn-sm" onclick="Med.openModal('icd10')"><i class="bi bi-plus-lg me-1"></i>Tambah Kode</button>
                <?php endif; ?>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Kode</th><th>Deskripsi</th><th>Kategori</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody id="tbody-icd10">
                    <?php foreach ($icd10Codes as $row): ?>
                        <tr data-id="<?= e($row['id']) ?>">
                            <td class="fw-semibold"><?= e($row['code']) ?></td>
                            <td><?= e($row['description']) ?></td>
                            <td><?= e($row['category'] ?? '-') ?></td>
                            <td><span class="badge-status <?= $row['status'] === 'ACTIVE' ? 'badge-success' : 'badge-muted' ?>"><?= $row['status'] === 'ACTIVE' ? 'Aktif' : 'Nonaktif' ?></span></td>
                            <td class="text-end">
                                <?php if (can('icd10.update')): ?><button class="btn btn-sm btn-outline-secondary" onclick='Med.openModal("icd10", <?= json_encode($row) ?>)'><i class="bi bi-pencil"></i></button><?php endif; ?>
                                <?php if (can('icd10.delete')): ?><button class="btn btn-sm btn-outline-danger" onclick="Med.remove('icd10', <?= e($row['id']) ?>)"><i class="bi bi-trash"></i></button><?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if ($icd10Codes === []): ?>
                <div class="empty-state"><i class="bi bi-clipboard2-pulse d-block"></i><h6>Belum ada kode ICD-10</h6></div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (can('medical_action_category.view')): ?>
        <div class="tab-pane fade" id="tab-category">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="section-title mb-0">Kategori Tindakan</h2>
                <?php if (can('medical_action_category.create')): ?>
                <button type="button" class="btn btn-primary btn-sm" onclick="Med.openModal('category')"><i class="bi bi-plus-lg me-1"></i>Tambah Kategori</button>
                <?php endif; ?>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Nama</th><th>Kode</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody id="tbody-category">
                    <?php foreach ($actionCategories as $row): ?>
                        <tr data-id="<?= e($row['id']) ?>">
                            <td><?= e($row['name']) ?></td>
                            <td><?= e($row['code']) ?></td>
                            <td><span class="badge-status <?= $row['is_active'] ? 'badge-success' : 'badge-muted' ?>"><?= $row['is_active'] ? 'Aktif' : 'Nonaktif' ?></span></td>
                            <td class="text-end">
                                <?php if (can('medical_action_category.update')): ?><button class="btn btn-sm btn-outline-secondary" onclick='Med.openModal("category", <?= json_encode($row) ?>)'><i class="bi bi-pencil"></i></button><?php endif; ?>
                                <?php if (can('medical_action_category.delete')): ?><button class="btn btn-sm btn-outline-danger" onclick="Med.remove('category', <?= e($row['id']) ?>)"><i class="bi bi-trash"></i></button><?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if ($actionCategories === []): ?>
                <div class="empty-state"><i class="bi bi-tags d-block"></i><h6>Belum ada kategori tindakan</h6></div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (can('medical_action.view')): ?>
        <div class="tab-pane fade" id="tab-action">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="section-title mb-0">Tindakan Medis</h2>
                <?php if (can('medical_action.create')): ?>
                <button type="button" class="btn btn-primary btn-sm" onclick="Med.openModal('action')"><i class="bi bi-plus-lg me-1"></i>Tambah Tindakan</button>
                <?php endif; ?>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Kode</th><th>Nama</th><th>Kategori</th><th>Harga</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody id="tbody-action">
                    <?php foreach ($medicalActions as $row): ?>
                        <tr data-id="<?= e($row['id']) ?>">
                            <td><?= e($row['code']) ?></td>
                            <td><?= e($row['name']) ?></td>
                            <td><?= e($row['category_name'] ?? '-') ?></td>
                            <td><?= format_currency($row['price']) ?></td>
                            <td><span class="badge-status <?= $row['status'] === 'ACTIVE' ? 'badge-success' : 'badge-muted' ?>"><?= $row['status'] === 'ACTIVE' ? 'Aktif' : 'Nonaktif' ?></span></td>
                            <td class="text-end">
                                <?php if (can('medical_action.update')): ?><button class="btn btn-sm btn-outline-secondary" onclick='Med.openModal("action", <?= json_encode($row) ?>)'><i class="bi bi-pencil"></i></button><?php endif; ?>
                                <?php if (can('medical_action.delete')): ?><button class="btn btn-sm btn-outline-danger" onclick="Med.remove('action', <?= e($row['id']) ?>)"><i class="bi bi-trash"></i></button><?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if ($medicalActions === []): ?>
                <div class="empty-state"><i class="bi bi-clipboard2-pulse d-block"></i><h6>Belum ada tindakan medis</h6></div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="medModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="medForm" data-guard>
                <div class="modal-header">
                    <h5 class="modal-title" id="medModalTitle">Tambah Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="medModalBody"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" data-loading-text="Menyimpan...">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
window.MED_REFS = {
    categories: <?= json_encode($actionCategories) ?>,
};
</script>
<script src="<?= asset('js/medical-settings.js') ?>"></script>
