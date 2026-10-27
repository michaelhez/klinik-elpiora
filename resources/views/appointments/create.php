<?php
\App\Core\View::extend('layouts.app');
$errors = \App\Core\Session::getFlash('_errors') ?? [];
?>

<div class="breadcrumb-custom"><a href="/dashboard">Dashboard</a> / <a href="/appointments">Janji Temu</a> / Buat</div>
<h1 class="page-title mb-4">Buat Janji Temu</h1>

<form action="/appointments" method="POST" data-guard class="row g-4">
    <?= csrf_field() ?>

    <div class="col-lg-8">
        <div class="surface-card p-4">
            <h2 class="section-title mb-3">Detail Janji Temu</h2>
            <div class="row g-3">
                <div class="col-12 position-relative">
                    <label class="form-label form-label-required">Pasien</label>
                    <input type="text" id="patientSearch" class="form-control" placeholder="Ketik nama, No. RM, atau NIK pasien"
                           value="<?= $patient ? e($patient['name'] . ' (' . $patient['medical_record_number'] . ')') : '' ?>" autocomplete="off" required>
                    <input type="hidden" name="patient_id" id="patientId" value="<?= $patient ? e($patient['id']) : e(old('patient_id')) ?>">
                    <div id="patientResults" class="list-group position-absolute w-100 shadow-sm" style="z-index:1050;display:none;"></div>
                    <?php if (isset($errors['patient_id'])): ?><div class="form-error"><?= e($errors['patient_id'][0]) ?></div><?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label form-label-required">Poliklinik</label>
                    <select name="polyclinic_id" id="polyclinicSelect" class="form-select<?= isset($errors['polyclinic_id']) ? ' is-invalid' : '' ?>" required>
                        <option value="">Pilih Poliklinik</option>
                        <?php foreach ($polyclinics as $poly): ?>
                        <option value="<?= e($poly['id']) ?>" <?= (string) old('polyclinic_id') === (string) $poly['id'] ? 'selected' : '' ?>><?= e($poly['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label form-label-required">Dokter</label>
                    <select name="doctor_id" id="doctorSelect" class="form-select<?= isset($errors['doctor_id']) ? ' is-invalid' : '' ?>" required>
                        <option value="">Pilih Dokter</option>
                        <?php foreach ($doctors as $doc): ?>
                        <option value="<?= e($doc['id']) ?>" <?= (string) old('doctor_id') === (string) $doc['id'] ? 'selected' : '' ?>><?= e($doc['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label form-label-required">Tanggal</label>
                    <input type="date" name="appointment_date" class="form-control<?= isset($errors['appointment_date']) ? ' is-invalid' : '' ?>" value="<?= e(old('appointment_date', date('Y-m-d'))) ?>" min="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label form-label-required">Jam</label>
                    <input type="time" name="appointment_time" class="form-control" value="<?= e(old('appointment_time')) ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" class="form-control" rows="2"><?= e(old('notes')) ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary py-2" data-loading-text="Menyimpan...">Simpan Janji Temu</button>
            <a href="/appointments" class="btn btn-outline-secondary">Batal</a>
        </div>
    </div>
</form>

<script src="<?= asset('js/appointments.js') ?>"></script>
