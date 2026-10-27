<?php
\App\Core\View::extend('layouts.app');
$errors = \App\Core\Session::getFlash('_errors') ?? [];
?>

<div class="breadcrumb-custom"><a href="/dashboard">Dashboard</a> / <a href="/employees">Karyawan</a> / Tambah</div>
<h1 class="page-title mb-4">Tambah Karyawan</h1>

<form action="/employees" method="POST" enctype="multipart/form-data" data-guard class="row g-4">
    <?= csrf_field() ?>

    <div class="col-lg-8">
        <div class="surface-card p-4">
            <h2 class="section-title mb-3">Data Pribadi</h2>
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label form-label-required">Nama Lengkap</label>
                    <input type="text" name="name" class="form-control<?= isset($errors['name']) ? ' is-invalid' : '' ?>" value="<?= e(old('name')) ?>" required>
                    <?php if (isset($errors['name'])): ?><div class="form-error"><?= e($errors['name'][0]) ?></div><?php endif; ?>
                </div>
                <div class="col-md-4">
                    <label class="form-label form-label-required">Jenis Kelamin</label>
                    <select name="gender" class="form-select<?= isset($errors['gender']) ? ' is-invalid' : '' ?>" required>
                        <option value="">Pilih</option>
                        <option value="MALE" <?= old('gender') === 'MALE' ? 'selected' : '' ?>>Laki-laki</option>
                        <option value="FEMALE" <?= old('gender') === 'FEMALE' ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">NIK</label>
                    <input type="text" name="nik" class="form-control<?= isset($errors['nik']) ? ' is-invalid' : '' ?>" value="<?= e(old('nik')) ?>" maxlength="20">
                    <?php if (isset($errors['nik'])): ?><div class="form-error"><?= e($errors['nik'][0]) ?></div><?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Telepon</label>
                    <input type="text" name="phone" class="form-control" value="<?= e(old('phone')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control<?= isset($errors['email']) ? ' is-invalid' : '' ?>" value="<?= e(old('email')) ?>">
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
                    <label class="form-label">Tanggal Bergabung</label>
                    <input type="date" name="join_date" class="form-control" value="<?= e(old('join_date')) ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Alamat</label>
                    <textarea name="address" class="form-control" rows="2"><?= e(old('address')) ?></textarea>
                </div>
            </div>
        </div>

        <div class="surface-card p-4 mt-3">
            <h2 class="section-title mb-3">Penempatan</h2>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label form-label-required">Cabang</label>
                    <select name="branch_id" class="form-select<?= isset($errors['branch_id']) ? ' is-invalid' : '' ?>" required>
                        <option value="">Pilih Cabang</option>
                        <?php foreach ($branches as $branch): ?>
                        <option value="<?= e($branch['id']) ?>" <?= (string) old('branch_id') === (string) $branch['id'] ? 'selected' : '' ?>><?= e($branch['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['branch_id'])): ?><div class="form-error"><?= e($errors['branch_id'][0]) ?></div><?php endif; ?>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Departemen</label>
                    <select name="department_id" class="form-select">
                        <option value="">Pilih Departemen</option>
                        <?php foreach ($departments as $department): ?>
                        <option value="<?= e($department['id']) ?>" <?= (string) old('department_id') === (string) $department['id'] ? 'selected' : '' ?>><?= e($department['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Jabatan</label>
                    <select name="position_id" class="form-select">
                        <option value="">Pilih Jabatan</option>
                        <?php foreach ($positions as $position): ?>
                        <option value="<?= e($position['id']) ?>" <?= (string) old('position_id') === (string) $position['id'] ? 'selected' : '' ?>><?= e($position['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
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
            <button type="submit" class="btn btn-primary py-2" data-loading-text="Menyimpan...">Simpan Karyawan</button>
            <a href="/employees" class="btn btn-outline-secondary">Batal</a>
        </div>
    </div>
</form>
