<?php \App\Core\View::extend('layouts.app'); ?>

<div class="breadcrumb-custom"><a href="/dashboard">Dashboard</a> / <a href="/employees">Karyawan</a> / <?= e($employee['name']) ?></div>

<div class="surface-card p-4 mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <?php if (!empty($employee['photo'])): ?>
                <img src="<?= e(url('uploads/' . $employee['photo'])) ?>" class="rounded-circle" style="width:64px;height:64px;object-fit:cover;">
            <?php else: ?>
                <div class="avatar-circle" style="width:64px;height:64px;font-size:1.5rem;"><?= e(mb_strtoupper(mb_substr($employee['name'], 0, 1))) ?></div>
            <?php endif; ?>
            <div>
                <h1 class="page-title mb-1"><?= e($employee['name']) ?></h1>
                <div class="text-muted-custom"><?= e($employee['employee_number']) ?> &bull; <?= e($employee['branch_name']) ?><?= $employee['position_name'] ? ' &bull; ' . e($employee['position_name']) : '' ?></div>
            </div>
        </div>
        <div class="d-flex align-items-start gap-2">
            <?php
            $statusClass = match ($employee['status']) { 'ACTIVE' => 'badge-success', 'INACTIVE' => 'badge-warning', default => 'badge-muted' };
            $statusLabel = match ($employee['status']) { 'ACTIVE' => 'Aktif', 'INACTIVE' => 'Nonaktif', default => 'Resign' };
            ?>
            <span class="badge-status <?= $statusClass ?> align-self-center"><?= $statusLabel ?></span>
            <?php if (can('employee.update')): ?><a href="/employees/<?= e($employee['id']) ?>/edit" class="btn btn-outline-secondary btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a><?php endif; ?>
            <?php if (can('employee.delete')): ?>
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="Employees.remove(<?= e($employee['id']) ?>)"><i class="bi bi-trash me-1"></i>Hapus</button>
            <?php endif; ?>
        </div>
    </div>
</div>

<ul class="nav nav-pills gap-1 mb-3">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-overview">Overview</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-documents">Dokumen</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-emergency">Kontak Darurat</button></li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade show active" id="tab-overview">
        <div class="surface-card p-4">
            <div class="row g-3">
                <div class="col-md-4"><div class="text-caption">Jenis Kelamin</div><div class="card-title-sm"><?= $employee['gender'] === 'MALE' ? 'Laki-laki' : 'Perempuan' ?></div></div>
                <div class="col-md-4"><div class="text-caption">NIK</div><div class="card-title-sm"><?= e($employee['nik'] ?? '-') ?></div></div>
                <div class="col-md-4"><div class="text-caption">Telepon</div><div class="card-title-sm"><?= e($employee['phone'] ?? '-') ?></div></div>
                <div class="col-md-4"><div class="text-caption">Email</div><div class="card-title-sm"><?= e($employee['email'] ?? '-') ?></div></div>
                <div class="col-md-4"><div class="text-caption">Tempat, Tanggal Lahir</div><div class="card-title-sm"><?= e($employee['birth_place'] ?? '-') ?>, <?= format_date($employee['birth_date']) ?></div></div>
                <div class="col-md-4"><div class="text-caption">Tanggal Bergabung</div><div class="card-title-sm"><?= format_date($employee['join_date']) ?></div></div>
                <div class="col-md-4"><div class="text-caption">Departemen</div><div class="card-title-sm"><?= e($employee['department_name'] ?? '-') ?></div></div>
                <div class="col-md-8"><div class="text-caption">Alamat</div><div class="card-title-sm"><?= e($employee['address'] ?? '-') ?></div></div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="tab-documents">
        <div class="surface-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="section-title mb-0">Dokumen Karyawan</h2>
                <?php if (can('employee.update')): ?>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#docModal"><i class="bi bi-upload me-1"></i>Unggah Dokumen</button>
                <?php endif; ?>
            </div>
            <?php if ($documents === []): ?>
                <div class="empty-state"><i class="bi bi-file-earmark d-block"></i><h6>Belum ada dokumen</h6></div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Jenis Dokumen</th><th>Nama Berkas</th><th>Tanggal Unggah</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($documents as $doc): ?>
                        <tr>
                            <td><?= e($doc['document_type']) ?></td>
                            <td><?= e($doc['file_name']) ?></td>
                            <td><?= format_date($doc['uploaded_at'], 'd M Y H:i') ?></td>
                            <td class="text-end">
                                <a href="/employees/<?= e($employee['id']) ?>/documents/<?= e($doc['id']) ?>/download" class="btn btn-sm btn-outline-secondary"><i class="bi bi-download"></i></a>
                                <?php if (can('employee.update')): ?>
                                <button class="btn btn-sm btn-outline-danger" onclick="Employees.removeDocument(<?= e($doc['id']) ?>)"><i class="bi bi-trash"></i></button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="tab-pane fade" id="tab-emergency">
        <div class="surface-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="section-title mb-0">Kontak Darurat</h2>
                <?php if (can('employee.update')): ?>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#contactModal"><i class="bi bi-plus-lg me-1"></i>Tambah Kontak</button>
                <?php endif; ?>
            </div>
            <?php if ($emergencyContacts === []): ?>
                <div class="empty-state"><i class="bi bi-telephone d-block"></i><h6>Belum ada kontak darurat</h6></div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Nama</th><th>Hubungan</th><th>Telepon</th><th>Alamat</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($emergencyContacts as $contact): ?>
                        <tr>
                            <td><?= e($contact['name']) ?></td>
                            <td><?= e($contact['relationship']) ?></td>
                            <td><?= e($contact['phone']) ?></td>
                            <td><?= e($contact['address'] ?? '-') ?></td>
                            <td class="text-end">
                                <?php if (can('employee.update')): ?>
                                <button class="btn btn-sm btn-outline-danger" onclick="Employees.removeContact(<?= e($contact['id']) ?>)"><i class="bi bi-trash"></i></button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="docModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="docForm" data-guard>
                <div class="modal-header"><h5 class="modal-title">Unggah Dokumen</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label form-label-required">Jenis Dokumen</label>
                        <input type="text" name="document_type" class="form-control" placeholder="Contoh: KTP, Ijazah, Kontrak Kerja" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label form-label-required">Berkas</label>
                        <input type="file" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                        <div class="form-helper">PDF, JPG, atau PNG. Maksimal 5MB.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" data-loading-text="Mengunggah...">Unggah</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="contactModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="contactForm" data-guard>
                <div class="modal-header"><h5 class="modal-title">Tambah Kontak Darurat</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label form-label-required">Nama</label><input type="text" name="name" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label form-label-required">Hubungan</label><input type="text" name="relationship" class="form-control" placeholder="Contoh: Suami, Istri, Orang Tua" required></div>
                    <div class="mb-3"><label class="form-label form-label-required">Telepon</label><input type="text" name="phone" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Alamat</label><textarea name="address" class="form-control" rows="2"></textarea></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" data-loading-text="Menyimpan...">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>window.EMPLOYEE_ID = <?= (int) $employee['id'] ?>;</script>
<script src="<?= asset('js/employees.js') ?>"></script>
