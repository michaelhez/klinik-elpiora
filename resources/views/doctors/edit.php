<?php
\App\Core\View::extend('layouts.app');
$errors = \App\Core\Session::getFlash('_errors') ?? [];
$val = static fn (string $key, mixed $default = '') => old($key) !== '' ? old($key) : ($doctor[$key] ?? $default);
$currentSpecIds = array_column($doctor['specializations'], 'id');
$currentPrimary = null;
foreach ($doctor['specializations'] as $s) {
    if ($s['is_primary']) { $currentPrimary = $s['id']; }
}
?>

<div class="breadcrumb-custom"><a href="/dashboard">Dashboard</a> / <a href="/doctors">Dokter</a> / Edit</div>
<h1 class="page-title mb-1">Edit Dokter</h1>
<p class="text-muted-custom mb-4"><?= e($doctor['name']) ?> &bull; <?= e($doctor['doctor_number']) ?></p>

<form action="/doctors/<?= e($doctor['id']) ?>/update" method="POST" data-guard class="row g-4">
    <?= csrf_field() ?>

    <div class="col-lg-8">
        <div class="surface-card p-4">
            <h2 class="section-title mb-3">Informasi Dokter</h2>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nomor STR</label>
                    <input type="text" name="str_number" class="form-control" value="<?= e($val('str_number')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nomor SIP</label>
                    <input type="text" name="sip_number" class="form-control" value="<?= e($val('sip_number')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label form-label-required">Tarif Konsultasi</label>
                    <input type="number" name="consultation_fee" class="form-control<?= isset($errors['consultation_fee']) ? ' is-invalid' : '' ?>" value="<?= e($val('consultation_fee')) ?>" min="0" step="1000" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="ACTIVE" <?= $val('status') === 'ACTIVE' ? 'selected' : '' ?>>Aktif</option>
                        <option value="INACTIVE" <?= $val('status') === 'INACTIVE' ? 'selected' : '' ?>>Nonaktif</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Bio</label>
                    <textarea name="bio" class="form-control" rows="3"><?= e($val('bio')) ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="surface-card p-4">
            <h2 class="section-title mb-3">Spesialisasi</h2>
            <?php if (isset($errors['specialization_ids'])): ?><div class="form-error mb-2"><?= e($errors['specialization_ids'][0]) ?></div><?php endif; ?>
            <?php foreach ($specializations as $spec): ?>
            <div class="form-check mb-2">
                <input type="checkbox" class="form-check-input" name="specialization_ids[]" value="<?= e($spec['id']) ?>" id="spec_<?= e($spec['id']) ?>" <?= in_array($spec['id'], $currentSpecIds) ? 'checked' : '' ?>>
                <label class="form-check-label" for="spec_<?= e($spec['id']) ?>"><?= e($spec['name']) ?></label>
                <div class="form-check form-check-inline ms-3">
                    <input type="radio" class="form-check-input" name="primary_specialization_id" value="<?= e($spec['id']) ?>" <?= $spec['id'] === $currentPrimary ? 'checked' : '' ?>>
                    <label class="form-check-label text-caption">Utama</label>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="d-grid gap-2 mt-3">
            <button type="submit" class="btn btn-primary py-2" data-loading-text="Menyimpan...">Simpan Perubahan</button>
            <a href="/doctors/<?= e($doctor['id']) ?>" class="btn btn-outline-secondary">Batal</a>
        </div>
    </div>
</form>
