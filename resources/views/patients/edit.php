<?php
\App\Core\View::extend('layouts.app');
$errors = \App\Core\Session::getFlash('_errors') ?? [];
$val = static fn (string $key, mixed $default = '') => old($key) !== '' ? old($key) : ($patient[$key] ?? $default);
?>

<div class="breadcrumb-custom"><a href="/dashboard">Dashboard</a> / <a href="/patients">Pasien</a> / Edit</div>
<h1 class="page-title mb-4">Edit Pasien</h1>

<form action="/patients/<?= e($patient['id']) ?>/update" method="POST" enctype="multipart/form-data" data-guard class="row g-4">
    <?= csrf_field() ?>

    <div class="col-lg-8">
        <div class="surface-card p-4">
            <h2 class="section-title mb-3">Data Pribadi</h2>
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label form-label-required">Nama Lengkap</label>
                    <input type="text" name="name" class="form-control" value="<?= e($val('name')) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label form-label-required">Jenis Kelamin</label>
                    <select name="gender" class="form-select" required>
                        <option value="MALE" <?= $val('gender') === 'MALE' ? 'selected' : '' ?>>Laki-laki</option>
                        <option value="FEMALE" <?= $val('gender') === 'FEMALE' ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">NIK</label>
                    <input type="text" name="nik" class="form-control<?= isset($errors['nik']) ? ' is-invalid' : '' ?>" value="<?= e($val('nik')) ?>" maxlength="20">
                    <?php if (isset($errors['nik'])): ?><div class="form-error"><?= e($errors['nik'][0]) ?></div><?php endif; ?>
                </div>
                <div class="col-md-4">
                    <label class="form-label form-label-required">Telepon</label>
                    <input type="text" name="phone" class="form-control" value="<?= e($val('phone')) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Golongan Darah</label>
                    <select name="blood_type" class="form-select">
                        <?php foreach (['UNKNOWN' => 'Tidak Diketahui', 'A' => 'A', 'B' => 'B', 'AB' => 'AB', 'O' => 'O'] as $val2 => $label): ?>
                        <option value="<?= $val2 ?>" <?= $val('blood_type', 'UNKNOWN') === $val2 ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= e($val('email')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tempat Lahir</label>
                    <input type="text" name="birth_place" class="form-control" value="<?= e($val('birth_place')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tanggal Lahir</label>
                    <input type="date" name="birth_date" class="form-control" value="<?= e($val('birth_date')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status Perkawinan</label>
                    <select name="marital_status" class="form-select">
                        <option value="">Pilih</option>
                        <option value="SINGLE" <?= $val('marital_status') === 'SINGLE' ? 'selected' : '' ?>>Belum Menikah</option>
                        <option value="MARRIED" <?= $val('marital_status') === 'MARRIED' ? 'selected' : '' ?>>Menikah</option>
                        <option value="DIVORCED" <?= $val('marital_status') === 'DIVORCED' ? 'selected' : '' ?>>Cerai</option>
                        <option value="WIDOWED" <?= $val('marital_status') === 'WIDOWED' ? 'selected' : '' ?>>Janda/Duda</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Pekerjaan</label>
                    <input type="text" name="occupation" class="form-control" value="<?= e($val('occupation')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Kota</label>
                    <input type="text" name="city" class="form-control" value="<?= e($val('city')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="ACTIVE" <?= $val('status') === 'ACTIVE' ? 'selected' : '' ?>>Aktif</option>
                        <option value="INACTIVE" <?= $val('status') === 'INACTIVE' ? 'selected' : '' ?>>Nonaktif</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Alamat</label>
                    <textarea name="address" class="form-control" rows="2"><?= e($val('address')) ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="surface-card p-4">
            <h2 class="section-title mb-3">Foto Profil</h2>
            <?php if (!empty($patient['photo'])): ?>
                <img src="<?= e(url('uploads/' . $patient['photo'])) ?>" class="rounded mb-2" style="width:100%;max-width:160px;">
            <?php endif; ?>
            <input type="file" name="photo" class="form-control" accept="image/jpeg,image/png,image/webp">
            <div class="form-helper">Kosongkan jika tidak ingin mengubah foto.</div>
        </div>
        <div class="d-grid gap-2 mt-3">
            <button type="submit" class="btn btn-primary py-2" data-loading-text="Menyimpan...">Simpan Perubahan</button>
            <a href="/patients/<?= e($patient['id']) ?>" class="btn btn-outline-secondary">Batal</a>
        </div>
    </div>
</form>
