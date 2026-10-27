<?php \App\Core\View::extend('layouts.app'); ?>

<div class="breadcrumb-custom no-print"><a href="/dashboard">Dashboard</a> / <a href="/registrations">Pendaftaran</a> / <?= e($registration['registration_number']) ?></div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="surface-card p-4 text-center">
            <div class="text-caption mb-1">Nomor Antrian</div>
            <div class="display-4 fw-bold text-primary mb-2"><?= e($registration['queue_number'] ?? '-') ?></div>
            <span class="badge-status badge-info mb-3"><?= e($registration['polyclinic_name']) ?></span>
            <div class="text-caption">No. Registrasi: <?= e($registration['registration_number']) ?></div>
            <button type="button" class="btn btn-outline-secondary btn-sm mt-3 no-print" onclick="window.print()"><i class="bi bi-printer me-1"></i>Cetak Tiket</button>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="surface-card p-4">
            <h2 class="section-title mb-3">Detail Pendaftaran</h2>
            <div class="row g-3">
                <div class="col-md-6"><div class="text-caption">Pasien</div><div class="card-title-sm"><?= e($registration['patient_name']) ?></div></div>
                <div class="col-md-6"><div class="text-caption">No. RM</div><div class="card-title-sm"><?= e($registration['medical_record_number']) ?></div></div>
                <div class="col-md-6"><div class="text-caption">Dokter</div><div class="card-title-sm"><?= e($registration['doctor_name'] ?? '-') ?></div></div>
                <div class="col-md-6"><div class="text-caption">Ruangan</div><div class="card-title-sm"><?= e($registration['room_name'] ?? '-') ?></div></div>
                <div class="col-md-6"><div class="text-caption">Jenis Pembayaran</div><div class="card-title-sm"><?= e($registration['payment_type']) ?></div></div>
                <div class="col-md-6"><div class="text-caption">Waktu Daftar</div><div class="card-title-sm"><?= format_date($registration['registered_at'], 'd M Y H:i') ?></div></div>
                <div class="col-md-6"><div class="text-caption">Status Antrian</div><div><span class="badge-status badge-info"><?= e($registration['queue_status'] ?? '-') ?></span></div></div>
                <div class="col-md-6"><div class="text-caption">Status Registrasi</div><div><span class="badge-status <?= $registration['status'] === 'CANCELLED' ? 'badge-danger' : 'badge-success' ?>"><?= e($registration['status']) ?></span></div></div>
            </div>

            <div class="d-flex flex-wrap gap-2 mt-4 no-print">
                <?php if ($registration['status'] !== 'CANCELLED' && can('medical_record.create')): ?>
                    <?php if ($medicalRecord !== null): ?>
                    <a href="/medical-records/<?= e($medicalRecord['id']) ?>" class="btn btn-primary btn-sm"><i class="bi bi-file-medical me-1"></i>Lanjutkan Pemeriksaan</a>
                    <?php else: ?>
                    <form action="/medical-records" method="POST">
                        <?= csrf_field() ?>
                        <input type="hidden" name="registration_id" value="<?= e($registration['id']) ?>">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-file-medical me-1"></i>Mulai Pemeriksaan</button>
                    </form>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if ($registration['status'] !== 'CANCELLED' && can('registration.update')): ?>
                <form action="/registrations/<?= e($registration['id']) ?>/cancel" method="POST" onsubmit="return confirm('Batalkan registrasi ini? Nomor antrian juga akan dibatalkan.');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-danger btn-sm">Batalkan Registrasi</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
