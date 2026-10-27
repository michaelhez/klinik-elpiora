<?php
\App\Core\View::extend('layouts.app');
$errors = \App\Core\Session::getFlash('_errors') ?? [];
?>

<div class="breadcrumb-custom"><a href="/dashboard">Dashboard</a> / <a href="/patients">Pasien</a> / Tambah</div>
<h1 class="page-title mb-4">Tambah Pasien</h1>

<form action="/patients" method="POST" enctype="multipart/form-data" data-guard class="row g-4">
    <?= csrf_field() ?>

    <div class="col-lg-8">
        <div class="surface-card p-4">
            <h2 class="section-title mb-3">Data Pribadi</h2>
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label form-label-required">Nama Lengkap</label>
                    <input type="text" name="name" class="form-control<?= isset($errors['name']) ? ' is-invalid' : '' ?>" value="<?= e(old('name')) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label form-label-required">Jenis Kelamin</label>
                    <select name="gender" class="form-select" required>
                        <option value="">Pilih</option>
                        <option value="MALE" <?= old('gender') === 'MALE' ? 'selected' : '' ?>>Laki-laki</option>
                        <option value="FEMALE" <?= old('gender') === 'FEMALE' ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">NIK</label>
                    <input type="text" name="nik" class="form-control<?= isset($errors['nik']) ? ' is-invalid' : '' ?>" value="<?= e(old('nik')) ?>" maxlength="20">
                    <?php if (isset($errors['nik'])): ?><div class="form-error"><?= e($errors['nik'][0]) ?></div><?php endif; ?>
                </div>
                <div class="col-md-4">
                    <label class="form-label form-label-required">Telepon</label>
                    <input type="text" name="phone" class="form-control<?= isset($errors['phone']) ? ' is-invalid' : '' ?>" value="<?= e(old('phone')) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Golongan Darah</label>
                    <select name="blood_type" class="form-select">
                        <?php foreach (['UNKNOWN' => 'Tidak Diketahui', 'A' => 'A', 'B' => 'B', 'AB' => 'AB', 'O' => 'O'] as $val => $label): ?>
                        <option value="<?= $val ?>" <?= old('blood_type', 'UNKNOWN') === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= e(old('email')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tempat Lahir</label>
                    <input type="text" name="birth_place" class="form-control" value="<?= e(old('birth_place')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tanggal Lahir</label>
                    <input type="date" name="birth_date" class="form-control" value="<?= e(old('birth_date')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status Perkawinan</label>
                    <select name="marital_status" class="form-select">
                        <option value="">Pilih</option>
                        <option value="SINGLE" <?= old('marital_status') === 'SINGLE' ? 'selected' : '' ?>>Belum Menikah</option>
                        <option value="MARRIED" <?= old('marital_status') === 'MARRIED' ? 'selected' : '' ?>>Menikah</option>
                        <option value="DIVORCED" <?= old('marital_status') === 'DIVORCED' ? 'selected' : '' ?>>Cerai</option>
                        <option value="WIDOWED" <?= old('marital_status') === 'WIDOWED' ? 'selected' : '' ?>>Janda/Duda</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Pekerjaan</label>
                    <input type="text" name="occupation" class="form-control" value="<?= e(old('occupation')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Kota</label>
                    <input type="text" name="city" class="form-control" value="<?= e(old('city')) ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Alamat</label>
                    <textarea name="address" class="form-control" rows="2"><?= e(old('address')) ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="surface-card p-4">
            <h2 class="section-title mb-3">Foto Profil</h2>
            <input type="file" name="photo" class="form-control" accept="image/jpeg,image/png,image/webp">
            <div class="form-helper">JPG, PNG, atau WEBP. Maksimal 2MB.</div>
        </div>
        <div class="d-grid gap-2 mt-3">
            <button type="submit" class="btn btn-primary py-2" data-loading-text="Menyimpan...">Simpan Pasien</button>
            <a href="/patients" class="btn btn-outline-secondary">Batal</a>
        </div>
    </div>
</form>
