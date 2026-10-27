<?php
\App\Core\View::extend('layouts.app');
$days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', "Jum'at", 'Sabtu'];
?>

<div class="breadcrumb-custom"><a href="/dashboard">Dashboard</a> / <a href="/doctors">Dokter</a> / <?= e($doctor['name']) ?></div>

<div class="surface-card p-4 mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <?php if (!empty($doctor['photo'])): ?>
                <img src="<?= e(url('uploads/' . $doctor['photo'])) ?>" class="rounded-circle" style="width:64px;height:64px;object-fit:cover;">
            <?php else: ?>
                <div class="avatar-circle" style="width:64px;height:64px;font-size:1.5rem;"><?= e(mb_strtoupper(mb_substr($doctor['name'], 0, 1))) ?></div>
            <?php endif; ?>
            <div>
                <h1 class="page-title mb-1"><?= e($doctor['name']) ?></h1>
                <div class="text-muted-custom mb-1"><?= e($doctor['doctor_number']) ?> &bull; <?= e($doctor['branch_name']) ?></div>
                <div class="d-flex flex-wrap gap-1">
                    <?php foreach ($doctor['specializations'] as $spec): ?>
                        <span class="badge-status badge-info"><?= e($spec['name']) ?><?= $spec['is_primary'] ? ' (Utama)' : '' ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="d-flex align-items-start gap-2">
            <span class="badge-status <?= $doctor['status'] === 'ACTIVE' ? 'badge-success' : 'badge-muted' ?> align-self-center"><?= $doctor['status'] === 'ACTIVE' ? 'Aktif' : 'Nonaktif' ?></span>
            <?php if (can('doctor.update')): ?><a href="/doctors/<?= e($doctor['id']) ?>/edit" class="btn btn-outline-secondary btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a><?php endif; ?>
            <?php if (can('doctor.delete')): ?>
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="Doctors.remove(<?= e($doctor['id']) ?>)"><i class="bi bi-trash me-1"></i>Hapus</button>
            <?php endif; ?>
        </div>
    </div>
</div>

<ul class="nav nav-pills gap-1 mb-3">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-overview">Overview</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-schedule">Jadwal Praktik</button></li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade show active" id="tab-overview">
        <div class="surface-card p-4">
            <div class="row g-3">
                <div class="col-md-4"><div class="text-caption">Nomor STR</div><div class="card-title-sm"><?= e($doctor['str_number'] ?? '-') ?></div></div>
                <div class="col-md-4"><div class="text-caption">Nomor SIP</div><div class="card-title-sm"><?= e($doctor['sip_number'] ?? '-') ?></div></div>
                <div class="col-md-4"><div class="text-caption">Tarif Konsultasi</div><div class="card-title-sm"><?= format_currency($doctor['consultation_fee']) ?></div></div>
                <div class="col-md-4"><div class="text-caption">Telepon</div><div class="card-title-sm"><?= e($doctor['phone'] ?? '-') ?></div></div>
                <div class="col-md-4"><div class="text-caption">Email</div><div class="card-title-sm"><?= e($doctor['email'] ?? '-') ?></div></div>
                <div class="col-12"><div class="text-caption">Bio</div><div class="card-title-sm"><?= e($doctor['bio'] ?? '-') ?></div></div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="tab-schedule">
        <div class="surface-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="section-title mb-0">Jadwal Praktik</h2>
                <?php if (can('doctor.update')): ?>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#scheduleModal"><i class="bi bi-plus-lg me-1"></i>Tambah Jadwal</button>
                <?php endif; ?>
            </div>
            <?php if ($schedules === []): ?>
                <div class="empty-state"><i class="bi bi-calendar-week d-block"></i><h6>Belum ada jadwal praktik</h6></div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Hari</th><th>Jam</th><th>Poliklinik</th><th>Ruangan</th><th>Kuota</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($schedules as $schedule): ?>
                        <tr>
                            <td><?= e($days[(int) $schedule['day_of_week']]) ?></td>
                            <td><?= substr($schedule['start_time'], 0, 5) ?> - <?= substr($schedule['end_time'], 0, 5) ?></td>
                            <td><?= e($schedule['polyclinic_name']) ?></td>
                            <td><?= e($schedule['room_name'] ?? '-') ?></td>
                            <td><?= e($schedule['quota'] ?? 'Tanpa batas') ?></td>
                            <td class="text-end">
                                <?php if (can('doctor.update')): ?>
                                <button class="btn btn-sm btn-outline-danger" onclick="Doctors.removeSchedule(<?= e($schedule['id']) ?>)"><i class="bi bi-trash"></i></button>
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

<div class="modal fade" id="scheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="scheduleForm" data-guard>
                <div class="modal-header"><h5 class="modal-title">Tambah Jadwal Praktik</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label form-label-required">Hari</label>
                        <select name="day_of_week" class="form-select" required>
                            <?php foreach ($days as $i => $day): ?>
                            <option value="<?= $i ?>"><?= e($day) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-2">
                        <div class="col-6 mb-3">
                            <label class="form-label form-label-required">Jam Mulai</label>
                            <input type="time" name="start_time" class="form-control" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label form-label-required">Jam Selesai</label>
                            <input type="time" name="end_time" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label form-label-required">Poliklinik</label>
                        <select name="polyclinic_id" class="form-select" required>
                            <option value="">Pilih Poliklinik</option>
                            <?php foreach ($polyclinics as $poly): ?>
                            <option value="<?= e($poly['id']) ?>"><?= e($poly['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ruangan</label>
                        <select name="room_id" class="form-select">
                            <option value="">-- Tidak ditentukan --</option>
                            <?php foreach ($rooms as $room): ?>
                            <option value="<?= e($room['id']) ?>"><?= e($room['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kuota Pasien</label>
                        <input type="number" name="quota" class="form-control" min="1" placeholder="Kosongkan jika tanpa batas">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" data-loading-text="Menyimpan...">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>window.DOCTOR_ID = <?= (int) $doctor['id'] ?>;</script>
<script src="<?= asset('js/doctors.js') ?>"></script>
