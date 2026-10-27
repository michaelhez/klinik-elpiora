<?php
\App\Core\View::extend('layouts.app');
$errors = \App\Core\Session::getFlash('_errors') ?? [];
?>

<div class="breadcrumb-custom"><a href="/dashboard">Dashboard</a> / <a href="/doctors">Dokter</a> / Tambah</div>
<h1 class="page-title mb-4">Tambah Dokter</h1>

<?php if ($employees === []): ?>
<div class="surface-card p-4">
    <div class="empty-state">
        <i class="bi bi-person-plus d-block"></i>
        <h6>Tidak ada karyawan yang tersedia</h6>
        <p class="small mb-3">Semua karyawan aktif sudah terdaftar sebagai dokter, atau belum ada data karyawan.</p>
        <a href="/employees/create" class="btn btn-primary btn-sm">Tambah Karyawan Baru</a>
    </div>
</div>
<?php else: ?>
<form action="/doctors" method="POST" data-guard class="row g-4">
    <?= csrf_field() ?>

    <div class="col-lg-8">
        <div class="surface-card p-4">
            <h2 class="section-title mb-3">Informasi Dokter</h2>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label form-label-required">Karyawan</label>
                    <select name="employee_id" class="form-select<?= isset($errors['employee_id']) ? ' is-invalid' : '' ?>" required>
                        <option value="">Pilih Karyawan</option>
                        <?php foreach ($employees as $emp): ?>
                        <option value="<?= e($emp['id']) ?>" <?= (string) old('employee_id') === (string) $emp['id'] ? 'selected' : '' ?>><?= e($emp['name']) ?> (<?= e($emp['employee_number']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['employee_id'])): ?><div class="form-error"><?= e($errors['employee_id'][0]) ?></div><?php endif; ?>
                    <div class="form-helper">Belum menemukan karyawan? <a href="/employees/create">Tambah karyawan baru</a>.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nomor STR</label>
                    <input type="text" name="str_number" class="form-control" value="<?= e(old('str_number')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nomor SIP</label>
                    <input type="text" name="sip_number" class="form-control" value="<?= e(old('sip_number')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label form-label-required">Tarif Konsultasi</label>
                    <input type="number" name="consultation_fee" class="form-control<?= isset($errors['consultation_fee']) ? ' is-invalid' : '' ?>" value="<?= e(old('consultation_fee', '0')) ?>" min="0" step="1000" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Bio</label>
                    <textarea name="bio" class="form-control" rows="3"><?= e(old('bio')) ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="surface-card p-4">
            <h2 class="section-title mb-3">Spesialisasi</h2>
            <?php if (isset($errors['specialization_ids'])): ?><div class="form-error mb-2"><?= e($errors['specialization_ids'][0]) ?></div><?php endif; ?>
            <?php foreach ($specializations as $i => $spec): ?>
            <div class="form-check mb-2">
                <input type="checkbox" class="form-check-input" name="specialization_ids[]" value="<?= e($spec['id']) ?>" id="spec_<?= e($spec['id']) ?>">
                <label class="form-check-label" for="spec_<?= e($spec['id']) ?>"><?= e($spec['name']) ?></label>
                <div class="form-check form-check-inline ms-3">
                    <input type="radio" class="form-check-input" name="primary_specialization_id" value="<?= e($spec['id']) ?>" <?= $i === 0 ? 'checked' : '' ?>>
                    <label class="form-check-label text-caption">Utama</label>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="d-grid gap-2 mt-3">
            <button type="submit" class="btn btn-primary py-2" data-loading-text="Menyimpan...">Simpan Dokter</button>
            <a href="/doctors" class="btn btn-outline-secondary">Batal</a>
        </div>
    </div>
</form>
<?php endif; ?>
