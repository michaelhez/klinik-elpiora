<?php
\App\Core\View::extend('layouts.app');
$errors = \App\Core\Session::getFlash('_errors') ?? [];
?>

<div class="breadcrumb-custom"><a href="/dashboard">Dashboard</a> / <a href="/registrations">Pendaftaran</a> / Baru</div>
<h1 class="page-title mb-1">Pendaftaran Kunjungan</h1>
<p class="text-muted-custom mb-4">Mendaftarkan pasien akan otomatis membuatkan nomor antrian.</p>

<form action="/registrations" method="POST" data-guard class="row g-4">
    <?= csrf_field() ?>

    <div class="col-lg-8">
        <div class="surface-card p-4">
            <h2 class="section-title mb-3">Detail Pendaftaran</h2>
            <div class="row g-3">
                <div class="col-12 position-relative">
                    <label class="form-label form-label-required">Pasien</label>
                    <input type="text" id="patientSearch" class="form-control" placeholder="Ketik nama, No. RM, atau NIK pasien"
                           value="<?= $patient ? e($patient['name'] . ' (' . $patient['medical_record_number'] . ')') : '' ?>" autocomplete="off" required>
                    <input type="hidden" name="patient_id" id="patientId" value="<?= $patient ? e($patient['id']) : e(old('patient_id')) ?>">
                    <div id="patientResults" class="list-group position-absolute w-100 shadow-sm" style="z-index:1050;display:none;"></div>
                    <?php if (isset($errors['patient_id'])): ?><div class="form-error"><?= e($errors['patient_id'][0]) ?></div><?php endif; ?>
                    <div class="form-helper">Belum terdaftar? <a href="/patients/create" target="_blank">Tambah pasien baru</a>.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label form-label-required">Poliklinik</label>
                    <select name="polyclinic_id" id="polyclinicSelect" class="form-select<?= isset($errors['polyclinic_id']) ? ' is-invalid' : '' ?>" required>
                        <option value="">Pilih Poliklinik</option>
                        <?php foreach ($polyclinics as $poly): ?>
                        <option value="<?= e($poly['id']) ?>"><?= e($poly['name']) ?> (<?= e($poly['queue_prefix']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Dokter</label>
                    <select name="doctor_id" id="doctorSelect" class="form-select">
                        <option value="">Pilih Poliklinik terlebih dahulu</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Ruangan</label>
                    <select name="room_id" class="form-select">
                        <option value="">-- Tidak ditentukan --</option>
                        <?php foreach ($rooms as $room): ?>
                        <option value="<?= e($room['id']) ?>"><?= e($room['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label form-label-required">Jenis Pembayaran</label>
                    <select name="payment_type" class="form-select" required>
                        <option value="SELF_PAY">Umum / Bayar Sendiri</option>
                        <option value="BPJS">BPJS</option>
                        <option value="INSURANCE">Asuransi</option>
                        <option value="CORPORATE">Perusahaan</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" class="form-control" rows="2"><?= e(old('notes')) ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="surface-card p-4">
            <div class="empty-state py-3">
                <i class="bi bi-ticket-perforated d-block"></i>
                <h6>Nomor antrian dibuat otomatis</h6>
                <p class="small mb-0">Setelah pendaftaran disimpan, nomor antrian akan langsung tersedia untuk poliklinik yang dipilih.</p>
            </div>
        </div>
        <div class="d-grid gap-2 mt-3">
            <button type="submit" class="btn btn-primary py-2" data-loading-text="Mendaftarkan...">Daftarkan Pasien</button>
            <a href="/registrations" class="btn btn-outline-secondary">Batal</a>
        </div>
    </div>
</form>

<script src="<?= asset('js/appointments.js') ?>"></script>
