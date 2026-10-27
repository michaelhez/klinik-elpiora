<?php \App\Core\View::extend('layouts.app'); ?>

<div class="breadcrumb-custom">
    <a href="/dashboard">Dashboard</a> / <a href="/settings/organization">Pengaturan</a> / Data Organisasi
</div>
<h1 class="page-title">Data Organisasi</h1>
<p class="text-muted-custom mb-4">Kelola struktur klinik, cabang, departemen, poliklinik, ruangan, jabatan, dan spesialisasi.</p>

<div class="surface-card p-3 p-md-4">
    <ul class="nav nav-pills flex-wrap gap-1 mb-4" id="orgTabs" role="tablist">
        <?php if (can('clinic.view')): ?>
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-clinic" type="button">Klinik</button></li>
        <?php endif; ?>
        <?php if (can('branch.view')): ?>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-branch" type="button">Cabang</button></li>
        <?php endif; ?>
        <?php if (can('department.view')): ?>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-department" type="button">Departemen</button></li>
        <?php endif; ?>
        <?php if (can('polyclinic.view')): ?>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-polyclinic" type="button">Poliklinik</button></li>
        <?php endif; ?>
        <?php if (can('room.view')): ?>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-room" type="button">Ruangan</button></li>
        <?php endif; ?>
        <?php if (can('position.view')): ?>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-position" type="button">Jabatan</button></li>
        <?php endif; ?>
        <?php if (can('specialization.view')): ?>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-specialization" type="button">Spesialisasi</button></li>
        <?php endif; ?>
    </ul>

    <div class="tab-content">
        <?php if (can('clinic.view')): ?>
        <div class="tab-pane fade show active" id="tab-clinic">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="section-title mb-0">Klinik</h2>
                <?php if (can('clinic.create')): ?>
                <button type="button" class="btn btn-primary btn-sm" onclick="Org.openModal('clinic')"><i class="bi bi-plus-lg me-1"></i>Tambah Klinik</button>
                <?php endif; ?>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Nama</th><th>Kode</th><th>Telepon</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody id="tbody-clinic">
                    <?php foreach ($clinics as $row): ?>
                        <tr data-id="<?= e($row['id']) ?>">
                            <td><?= e($row['name']) ?></td>
                            <td><?= e($row['code']) ?></td>
                            <td><?= e($row['phone'] ?? '-') ?></td>
                            <td><span class="badge-status <?= $row['is_active'] ? 'badge-success' : 'badge-muted' ?>"><?= $row['is_active'] ? 'Aktif' : 'Nonaktif' ?></span></td>
                            <td class="text-end">
                                <?php if (can('clinic.update')): ?><button class="btn btn-sm btn-outline-secondary" onclick='Org.openModal("clinic", <?= json_encode($row) ?>)'><i class="bi bi-pencil"></i></button><?php endif; ?>
                                <?php if (can('clinic.delete')): ?><button class="btn btn-sm btn-outline-danger" onclick="Org.remove('clinic', <?= e($row['id']) ?>)"><i class="bi bi-trash"></i></button><?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if ($clinics === []): ?>
                <div class="empty-state"><i class="bi bi-hospital d-block"></i><h6>Belum ada data klinik</h6><p class="mb-0 small">Tambahkan klinik pertama Anda.</p></div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (can('branch.view')): ?>
        <div class="tab-pane fade" id="tab-branch">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="section-title mb-0">Cabang</h2>
                <?php if (can('branch.create')): ?>
                <button type="button" class="btn btn-primary btn-sm" onclick="Org.openModal('branch')"><i class="bi bi-plus-lg me-1"></i>Tambah Cabang</button>
                <?php endif; ?>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Nama</th><th>Kode</th><th>Klinik</th><th>Utama</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody id="tbody-branch">
                    <?php foreach ($branches as $row): ?>
                        <tr data-id="<?= e($row['id']) ?>">
                            <td><?= e($row['name']) ?></td>
                            <td><?= e($row['code']) ?></td>
                            <td><?= e($row['clinic_name']) ?></td>
                            <td><?= $row['is_main'] ? '<i class="bi bi-check-circle-fill text-success"></i>' : '-' ?></td>
                            <td><span class="badge-status <?= $row['is_active'] ? 'badge-success' : 'badge-muted' ?>"><?= $row['is_active'] ? 'Aktif' : 'Nonaktif' ?></span></td>
                            <td class="text-end">
                                <?php if (can('branch.update')): ?><button class="btn btn-sm btn-outline-secondary" onclick='Org.openModal("branch", <?= json_encode($row) ?>)'><i class="bi bi-pencil"></i></button><?php endif; ?>
                                <?php if (can('branch.delete')): ?><button class="btn btn-sm btn-outline-danger" onclick="Org.remove('branch', <?= e($row['id']) ?>)"><i class="bi bi-trash"></i></button><?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if ($branches === []): ?>
                <div class="empty-state"><i class="bi bi-building d-block"></i><h6>Belum ada data cabang</h6></div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (can('department.view')): ?>
        <div class="tab-pane fade" id="tab-department">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="section-title mb-0">Departemen</h2>
                <?php if (can('department.create')): ?>
                <button type="button" class="btn btn-primary btn-sm" onclick="Org.openModal('department')"><i class="bi bi-plus-lg me-1"></i>Tambah Departemen</button>
                <?php endif; ?>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Nama</th><th>Kode</th><th>Cabang</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody id="tbody-department">
                    <?php foreach ($departments as $row): ?>
                        <tr data-id="<?= e($row['id']) ?>">
                            <td><?= e($row['name']) ?></td>
                            <td><?= e($row['code']) ?></td>
                            <td><?= e($row['branch_name']) ?></td>
                            <td><span class="badge-status <?= $row['is_active'] ? 'badge-success' : 'badge-muted' ?>"><?= $row['is_active'] ? 'Aktif' : 'Nonaktif' ?></span></td>
                            <td class="text-end">
                                <?php if (can('department.update')): ?><button class="btn btn-sm btn-outline-secondary" onclick='Org.openModal("department", <?= json_encode($row) ?>)'><i class="bi bi-pencil"></i></button><?php endif; ?>
                                <?php if (can('department.delete')): ?><button class="btn btn-sm btn-outline-danger" onclick="Org.remove('department', <?= e($row['id']) ?>)"><i class="bi bi-trash"></i></button><?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if ($departments === []): ?>
                <div class="empty-state"><i class="bi bi-diagram-3 d-block"></i><h6>Belum ada data departemen</h6></div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (can('polyclinic.view')): ?>
        <div class="tab-pane fade" id="tab-polyclinic">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="section-title mb-0">Poliklinik</h2>
                <?php if (can('polyclinic.create')): ?>
                <button type="button" class="btn btn-primary btn-sm" onclick="Org.openModal('polyclinic')"><i class="bi bi-plus-lg me-1"></i>Tambah Poliklinik</button>
                <?php endif; ?>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Nama</th><th>Kode</th><th>Cabang</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody id="tbody-polyclinic">
                    <?php foreach ($polyclinics as $row): ?>
                        <tr data-id="<?= e($row['id']) ?>">
                            <td><?= e($row['name']) ?></td>
                            <td><?= e($row['code']) ?></td>
                            <td><?= e($row['branch_name']) ?></td>
                            <td><span class="badge-status <?= $row['is_active'] ? 'badge-success' : 'badge-muted' ?>"><?= $row['is_active'] ? 'Aktif' : 'Nonaktif' ?></span></td>
                            <td class="text-end">
                                <?php if (can('polyclinic.update')): ?><button class="btn btn-sm btn-outline-secondary" onclick='Org.openModal("polyclinic", <?= json_encode($row) ?>)'><i class="bi bi-pencil"></i></button><?php endif; ?>
                                <?php if (can('polyclinic.delete')): ?><button class="btn btn-sm btn-outline-danger" onclick="Org.remove('polyclinic', <?= e($row['id']) ?>)"><i class="bi bi-trash"></i></button><?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if ($polyclinics === []): ?>
                <div class="empty-state"><i class="bi bi-clipboard2-pulse d-block"></i><h6>Belum ada data poliklinik</h6></div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (can('room.view')): ?>
        <div class="tab-pane fade" id="tab-room">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="section-title mb-0">Ruangan</h2>
                <?php if (can('room.create')): ?>
                <button type="button" class="btn btn-primary btn-sm" onclick="Org.openModal('room')"><i class="bi bi-plus-lg me-1"></i>Tambah Ruangan</button>
                <?php endif; ?>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Nama</th><th>Kode</th><th>Cabang</th><th>Poliklinik</th><th>Kapasitas</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody id="tbody-room">
                    <?php foreach ($rooms as $row): ?>
                        <tr data-id="<?= e($row['id']) ?>">
                            <td><?= e($row['name']) ?></td>
                            <td><?= e($row['code']) ?></td>
                            <td><?= e($row['branch_name']) ?></td>
                            <td><?= e($row['polyclinic_name'] ?? '-') ?></td>
                            <td><?= e($row['capacity']) ?></td>
                            <td><span class="badge-status <?= $row['is_active'] ? 'badge-success' : 'badge-muted' ?>"><?= $row['is_active'] ? 'Aktif' : 'Nonaktif' ?></span></td>
                            <td class="text-end">
                                <?php if (can('room.update')): ?><button class="btn btn-sm btn-outline-secondary" onclick='Org.openModal("room", <?= json_encode($row) ?>)'><i class="bi bi-pencil"></i></button><?php endif; ?>
                                <?php if (can('room.delete')): ?><button class="btn btn-sm btn-outline-danger" onclick="Org.remove('room', <?= e($row['id']) ?>)"><i class="bi bi-trash"></i></button><?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if ($rooms === []): ?>
                <div class="empty-state"><i class="bi bi-door-open d-block"></i><h6>Belum ada data ruangan</h6></div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (can('position.view')): ?>
        <div class="tab-pane fade" id="tab-position">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="section-title mb-0">Jabatan</h2>
                <?php if (can('position.create')): ?>
                <button type="button" class="btn btn-primary btn-sm" onclick="Org.openModal('position')"><i class="bi bi-plus-lg me-1"></i>Tambah Jabatan</button>
                <?php endif; ?>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Nama</th><th>Kode</th><th>Departemen</th><th>Level</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody id="tbody-position">
                    <?php foreach ($positions as $row): ?>
                        <tr data-id="<?= e($row['id']) ?>">
                            <td><?= e($row['name']) ?></td>
                            <td><?= e($row['code']) ?></td>
                            <td><?= e($row['department_name'] ?? '-') ?></td>
                            <td><?= e($row['level'] ?? '-') ?></td>
                            <td><span class="badge-status <?= $row['is_active'] ? 'badge-success' : 'badge-muted' ?>"><?= $row['is_active'] ? 'Aktif' : 'Nonaktif' ?></span></td>
                            <td class="text-end">
                                <?php if (can('position.update')): ?><button class="btn btn-sm btn-outline-secondary" onclick='Org.openModal("position", <?= json_encode($row) ?>)'><i class="bi bi-pencil"></i></button><?php endif; ?>
                                <?php if (can('position.delete')): ?><button class="btn btn-sm btn-outline-danger" onclick="Org.remove('position', <?= e($row['id']) ?>)"><i class="bi bi-trash"></i></button><?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if ($positions === []): ?>
                <div class="empty-state"><i class="bi bi-person-badge d-block"></i><h6>Belum ada data jabatan</h6></div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (can('specialization.view')): ?>
        <div class="tab-pane fade" id="tab-specialization">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="section-title mb-0">Spesialisasi</h2>
                <?php if (can('specialization.create')): ?>
                <button type="button" class="btn btn-primary btn-sm" onclick="Org.openModal('specialization')"><i class="bi bi-plus-lg me-1"></i>Tambah Spesialisasi</button>
                <?php endif; ?>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Nama</th><th>Kode</th><th>Deskripsi</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody id="tbody-specialization">
                    <?php foreach ($specializations as $row): ?>
                        <tr data-id="<?= e($row['id']) ?>">
                            <td><?= e($row['name']) ?></td>
                            <td><?= e($row['code']) ?></td>
                            <td><?= e($row['description'] ?? '-') ?></td>
                            <td><span class="badge-status <?= $row['is_active'] ? 'badge-success' : 'badge-muted' ?>"><?= $row['is_active'] ? 'Aktif' : 'Nonaktif' ?></span></td>
                            <td class="text-end">
                                <?php if (can('specialization.update')): ?><button class="btn btn-sm btn-outline-secondary" onclick='Org.openModal("specialization", <?= json_encode($row) ?>)'><i class="bi bi-pencil"></i></button><?php endif; ?>
                                <?php if (can('specialization.delete')): ?><button class="btn btn-sm btn-outline-danger" onclick="Org.remove('specialization', <?= e($row['id']) ?>)"><i class="bi bi-trash"></i></button><?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if ($specializations === []): ?>
                <div class="empty-state"><i class="bi bi-clipboard2-heart d-block"></i><h6>Belum ada data spesialisasi</h6></div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Shared Modal -->
<div class="modal fade" id="orgModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="orgForm" data-guard>
                <div class="modal-header">
                    <h5 class="modal-title" id="orgModalTitle">Tambah Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="orgModalBody"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" data-loading-text="Menyimpan...">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
window.ORG_REFS = {
    clinics: <?= json_encode($clinics) ?>,
    branches: <?= json_encode($branches) ?>,
    departments: <?= json_encode($departments) ?>,
    polyclinics: <?= json_encode($polyclinics) ?>,
};
</script>
<script src="<?= asset('js/organization.js') ?>"></script>
