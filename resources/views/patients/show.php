<?php
\App\Core\View::extend('layouts.app');
$age = $patient['birth_date'] ? (new DateTime($patient['birth_date']))->diff(new DateTime())->y : null;
$bloodType = $patient['blood_type'] !== 'UNKNOWN' ? $patient['blood_type'] : '-';
$allergyNames = array_column($allergies, 'allergen');
?>

<div class="breadcrumb-custom"><a href="/dashboard">Dashboard</a> / <a href="/patients">Pasien</a> / <?= e($patient['name']) ?></div>

<div class="surface-card p-4 mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <?php if (!empty($patient['photo'])): ?>
                <img src="<?= e(url('uploads/' . $patient['photo'])) ?>" class="rounded-circle" style="width:64px;height:64px;object-fit:cover;">
            <?php else: ?>
                <div class="avatar-circle" style="width:64px;height:64px;font-size:1.5rem;"><?= e(mb_strtoupper(mb_substr($patient['name'], 0, 1))) ?></div>
            <?php endif; ?>
            <div>
                <h1 class="page-title mb-1"><?= e($patient['name']) ?></h1>
                <div class="text-muted-custom mb-1">
                    <?= e($patient['medical_record_number']) ?> &bull;
                    <?= $patient['gender'] === 'MALE' ? 'Laki-laki' : 'Perempuan' ?><?= $age !== null ? " &bull; {$age} Tahun" : '' ?> &bull;
                    Gol. Darah <?= e($bloodType) ?>
                </div>
                <?php if ($allergyNames !== []): ?>
                <div class="badge-status badge-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i>Alergi: <?= e(implode(', ', $allergyNames)) ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="d-flex align-items-start gap-2">
            <span class="badge-status <?= $patient['status'] === 'ACTIVE' ? 'badge-success' : 'badge-muted' ?> align-self-center"><?= $patient['status'] === 'ACTIVE' ? 'Aktif' : 'Nonaktif' ?></span>
            <?php if (can('registration.create')): ?><a href="/registrations/create?patient_id=<?= e($patient['id']) ?>" class="btn btn-primary btn-sm"><i class="bi bi-clipboard-plus me-1"></i>Daftarkan Kunjungan</a><?php endif; ?>
            <?php if (can('patient.update')): ?><a href="/patients/<?= e($patient['id']) ?>/edit" class="btn btn-outline-secondary btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a><?php endif; ?>
        </div>
    </div>
</div>

<ul class="nav nav-pills gap-1 mb-3 flex-nowrap overflow-auto">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-overview">Overview</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-visits">Kunjungan</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-medical">Riwayat Medis</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-contacts">Kontak</button></li>
    <?php if (can('medical_record.view')): ?>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-records">Rekam Medis</button></li>
    <?php endif; ?>
    <li class="nav-item"><span class="nav-link disabled" style="opacity:.45;">Billing <span class="badge badge-status badge-muted ms-1" style="font-size:.625rem;">Segera</span></span></li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade show active" id="tab-overview">
        <div class="surface-card p-4">
            <div class="row g-3">
                <div class="col-md-4"><div class="text-caption">NIK</div><div class="card-title-sm"><?= e($patient['nik'] ?? '-') ?></div></div>
                <div class="col-md-4"><div class="text-caption">Telepon</div><div class="card-title-sm"><?= e($patient['phone'] ?? '-') ?></div></div>
                <div class="col-md-4"><div class="text-caption">Email</div><div class="card-title-sm"><?= e($patient['email'] ?? '-') ?></div></div>
                <div class="col-md-4"><div class="text-caption">Tempat, Tanggal Lahir</div><div class="card-title-sm"><?= e($patient['birth_place'] ?? '-') ?>, <?= format_date($patient['birth_date']) ?></div></div>
                <div class="col-md-4"><div class="text-caption">Status Perkawinan</div><div class="card-title-sm"><?= e($patient['marital_status'] ?? '-') ?></div></div>
                <div class="col-md-4"><div class="text-caption">Pekerjaan</div><div class="card-title-sm"><?= e($patient['occupation'] ?? '-') ?></div></div>
                <div class="col-12"><div class="text-caption">Alamat</div><div class="card-title-sm"><?= e($patient['address'] ?? '-') ?>, <?= e($patient['city'] ?? '') ?></div></div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="tab-visits">
        <div class="surface-card p-4">
            <h2 class="section-title mb-3">Riwayat Registrasi</h2>
            <?php if ($registrations === []): ?>
                <div class="empty-state"><i class="bi bi-clipboard2-pulse d-block"></i><h6>Belum ada kunjungan</h6></div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>No. Registrasi</th><th>Tanggal</th><th>Dokter</th><th>Poliklinik</th><th>No. Antrian</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($registrations as $reg): ?>
                        <tr>
                            <td><?= e($reg['registration_number']) ?></td>
                            <td><?= format_date($reg['registered_at'], 'd M Y H:i') ?></td>
                            <td><?= e($reg['doctor_name'] ?? '-') ?></td>
                            <td><?= e($reg['polyclinic_name']) ?></td>
                            <td><?= e($reg['queue_number'] ?? '-') ?></td>
                            <td><span class="badge-status <?= $reg['status'] === 'CANCELLED' ? 'badge-danger' : 'badge-info' ?>"><?= e($reg['queue_status'] ?? $reg['status']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <h2 class="section-title mb-3 mt-4">Janji Temu</h2>
            <?php if ($appointments === []): ?>
                <div class="empty-state"><i class="bi bi-calendar-check d-block"></i><h6>Belum ada janji temu</h6></div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>No. Janji Temu</th><th>Tanggal</th><th>Dokter</th><th>Poliklinik</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($appointments as $apt): ?>
                        <tr>
                            <td><?= e($apt['appointment_number']) ?></td>
                            <td><?= format_date($apt['appointment_date']) ?> <?= substr($apt['appointment_time'], 0, 5) ?></td>
                            <td><?= e($apt['doctor_name']) ?></td>
                            <td><?= e($apt['polyclinic_name']) ?></td>
                            <td><span class="badge-status badge-info"><?= e($apt['status']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="tab-pane fade" id="tab-medical">
        <div class="surface-card p-4 mb-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="section-title mb-0">Alergi</h2>
                <?php if (can('patient.update')): ?><button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#allergyModal"><i class="bi bi-plus-lg me-1"></i>Tambah Alergi</button><?php endif; ?>
            </div>
            <?php if ($allergies === []): ?>
                <div class="empty-state py-3"><i class="bi bi-shield-check d-block"></i><h6>Tidak ada alergi tercatat</h6></div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Alergen</th><th>Tingkat Keparahan</th><th>Reaksi</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($allergies as $a): ?>
                        <tr>
                            <td><?= e($a['allergen']) ?></td>
                            <td><span class="badge-status <?= match($a['severity']) { 'SEVERE' => 'badge-danger', 'MODERATE' => 'badge-warning', default => 'badge-muted' } ?>"><?= e($a['severity']) ?></span></td>
                            <td><?= e($a['reaction'] ?? '-') ?></td>
                            <td class="text-end"><?php if (can('patient.update')): ?><button class="btn btn-sm btn-outline-danger" onclick="Patients.removeAllergy(<?= e($a['id']) ?>)"><i class="bi bi-trash"></i></button><?php endif; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <div class="surface-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="section-title mb-0">Riwayat Penyakit</h2>
                <?php if (can('patient.update')): ?><button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#historyModal"><i class="bi bi-plus-lg me-1"></i>Tambah Riwayat</button><?php endif; ?>
            </div>
            <?php if ($medicalHistories === []): ?>
                <div class="empty-state py-3"><i class="bi bi-journal-medical d-block"></i><h6>Tidak ada riwayat penyakit</h6></div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Kondisi</th><th>Tanggal Diagnosis</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($medicalHistories as $h): ?>
                        <tr>
                            <td><?= e($h['condition_name']) ?></td>
                            <td><?= format_date($h['diagnosed_date']) ?></td>
                            <td><span class="badge-status <?= $h['status'] === 'ONGOING' ? 'badge-warning' : 'badge-success' ?>"><?= $h['status'] === 'ONGOING' ? 'Berlangsung' : 'Sembuh' ?></span></td>
                            <td class="text-end"><?php if (can('patient.update')): ?><button class="btn btn-sm btn-outline-danger" onclick="Patients.removeHistory(<?= e($h['id']) ?>)"><i class="bi bi-trash"></i></button><?php endif; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="tab-pane fade" id="tab-contacts">
        <div class="surface-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="section-title mb-0">Kontak Darurat</h2>
                <?php if (can('patient.update')): ?><button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#contactModal"><i class="bi bi-plus-lg me-1"></i>Tambah Kontak</button><?php endif; ?>
            </div>
            <?php if ($emergencyContacts === []): ?>
                <div class="empty-state py-3"><i class="bi bi-telephone d-block"></i><h6>Belum ada kontak darurat</h6></div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Nama</th><th>Hubungan</th><th>Telepon</th><th>Alamat</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($emergencyContacts as $c): ?>
                        <tr>
                            <td><?= e($c['name']) ?></td>
                            <td><?= e($c['relationship']) ?></td>
                            <td><?= e($c['phone']) ?></td>
                            <td><?= e($c['address'] ?? '-') ?></td>
                            <td class="text-end"><?php if (can('patient.update')): ?><button class="btn btn-sm btn-outline-danger" onclick="Patients.removeContact(<?= e($c['id']) ?>)"><i class="bi bi-trash"></i></button><?php endif; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (can('medical_record.view')): ?>
    <div class="tab-pane fade" id="tab-records">
        <div class="surface-card p-4">
            <h2 class="section-title mb-3">Rekam Medis</h2>
            <?php if ($medicalRecords === []): ?>
                <div class="empty-state"><i class="bi bi-file-medical d-block"></i><h6>Belum ada rekam medis</h6></div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>No. Rekam Medis</th><th>Dokter</th><th>Status</th><th>Tanggal</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($medicalRecords as $mr): ?>
                        <?php
                        $mrBadge = match ($mr['status']) {
                            'FINALIZED' => 'badge-success', 'AMENDED' => 'badge-warning', 'IN_PROGRESS' => 'badge-info', default => 'badge-muted',
                        };
                        $mrLabels = ['DRAFT' => 'Draf', 'IN_PROGRESS' => 'Berlangsung', 'FINALIZED' => 'Selesai', 'AMENDED' => 'Diamendemen'];
                        ?>
                        <tr>
                            <td><?= e($mr['record_number']) ?></td>
                            <td><?= e($mr['doctor_name']) ?></td>
                            <td><span class="badge-status <?= $mrBadge ?>"><?= $mrLabels[$mr['status']] ?? $mr['status'] ?></span></td>
                            <td><?= format_date($mr['created_at'], 'd M Y H:i') ?></td>
                            <td class="text-end"><a href="/medical-records/<?= e($mr['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="allergyModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form id="allergyForm" data-guard>
            <div class="modal-header"><h5 class="modal-title">Tambah Alergi</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label form-label-required">Alergen</label><input type="text" name="allergen" class="form-control" placeholder="Contoh: Penisilin, Kacang" required></div>
                <div class="mb-3"><label class="form-label form-label-required">Tingkat Keparahan</label>
                    <select name="severity" class="form-select" required>
                        <option value="MILD">Ringan</option><option value="MODERATE">Sedang</option><option value="SEVERE">Berat</option>
                    </select>
                </div>
                <div class="mb-3"><label class="form-label">Reaksi</label><input type="text" name="reaction" class="form-control"></div>
                <div class="mb-3"><label class="form-label">Catatan</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary" data-loading-text="Menyimpan...">Simpan</button></div>
        </form>
    </div></div>
</div>

<div class="modal fade" id="historyModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form id="historyForm" data-guard>
            <div class="modal-header"><h5 class="modal-title">Tambah Riwayat Penyakit</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label form-label-required">Kondisi/Penyakit</label><input type="text" name="condition_name" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Tanggal Diagnosis</label><input type="date" name="diagnosed_date" class="form-control"></div>
                <div class="mb-3"><label class="form-label">Status</label>
                    <select name="status" class="form-select"><option value="ONGOING">Berlangsung</option><option value="RESOLVED">Sembuh</option></select>
                </div>
                <div class="mb-3"><label class="form-label">Catatan</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary" data-loading-text="Menyimpan...">Simpan</button></div>
        </form>
    </div></div>
</div>

<div class="modal fade" id="contactModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form id="contactForm" data-guard>
            <div class="modal-header"><h5 class="modal-title">Tambah Kontak Darurat</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label form-label-required">Nama</label><input type="text" name="name" class="form-control" required></div>
                <div class="mb-3"><label class="form-label form-label-required">Hubungan</label><input type="text" name="relationship" class="form-control" required></div>
                <div class="mb-3"><label class="form-label form-label-required">Telepon</label><input type="text" name="phone" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Alamat</label><textarea name="address" class="form-control" rows="2"></textarea></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary" data-loading-text="Menyimpan...">Simpan</button></div>
        </form>
    </div></div>
</div>

<script>window.PATIENT_ID = <?= (int) $patient['id'] ?>;</script>
<script src="<?= asset('js/patients.js') ?>"></script>
