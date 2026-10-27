<?php
\App\Core\View::extend('layouts.app');
$errors = \App\Core\Session::getFlash('_errors') ?? [];
$val = static fn (string $key, mixed $default = '') => old($key) !== '' ? old($key) : ($employee[$key] ?? $default);
?>

<div class="breadcrumb-custom"><a href="/dashboard">Dashboard</a> / <a href="/employees">Karyawan</a> / Edit</div>
<h1 class="page-title mb-4">Edit Karyawan</h1>

<form action="/employees/<?= e($employee['id']) ?>/update" method="POST" enctype="multipart/form-data" data-guard class="row g-4">
    <?= csrf_field() ?>

    <div class="col-lg-8">
        <div class="surface-card p-4">
            <h2 class="section-title mb-3">Data Pribadi</h2>
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label form-label-required">Nama Lengkap</label>
                    <input type="text" name="name" class="form-control<?= isset($errors['name']) ? ' is-invalid' : '' ?>" value="<?= e($val('name')) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label form-label-required">Jenis Kelamin</label>
                    <select name="gender" class="form-select" required>
                        <option value="MALE" <?= $val('gender') === 'MALE' ? 'selected' : '' ?>>Laki-laki</option>
                        <option value="FEMALE" <?= $val('gender') === 'FEMALE' ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">NIK</label>
                    <input type="text" name="nik" class="form-control<?= isset($errors['nik']) ? ' is-invalid' : '' ?>" value="<?= e($val('nik')) ?>" maxlength="20">
                    <?php if (isset($errors['nik'])): ?><div class="form-error"><?= e($errors['nik'][0]) ?></div><?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Telepon</label>
                    <input type="text" name="phone" class="form-control" value="<?= e($val('phone')) ?>">
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
                    <label class="form-label">Tanggal Bergabung</label>
                    <input type="date" name="join_date" class="form-control" value="<?= e($val('join_date')) ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Alamat</label>
                    <textarea name="address" class="form-control" rows="2"><?= e($val('address')) ?></textarea>
                </div>
            </div>
        </div>

        <div class="surface-card p-4 mt-3">
            <h2 class="section-title mb-3">Penempatan</h2>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label form-label-required">Cabang</label>
                    <select name="branch_id" class="form-select" required>
                        <?php foreach ($branches as $branch): ?>
                        <option value="<?= e($branch['id']) ?>" <?= (string) $val('branch_id') === (string) $branch['id'] ? 'selected' : '' ?>><?= e($branch['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Departemen</label>
                    <select name="department_id" class="form-select">
                        <option value="">Pilih Departemen</option>
                        <?php foreach ($departments as $department): ?>
                        <option value="<?= e($department['id']) ?>" <?= (string) $val('department_id') === (string) $department['id'] ? 'selected' : '' ?>><?= e($department['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Jabatan</label>
                    <select name="position_id" class="form-select">
                        <option value="">Pilih Jabatan</option>
                        <?php foreach ($positions as $position): ?>
                        <option value="<?= e($position['id']) ?>" <?= (string) $val('position_id') === (string) $position['id'] ? 'selected' : '' ?>><?= e($position['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="ACTIVE" <?= $val('status') === 'ACTIVE' ? 'selected' : '' ?>>Aktif</option>
                        <option value="INACTIVE" <?= $val('status') === 'INACTIVE' ? 'selected' : '' ?>>Nonaktif</option>
                        <option value="RESIGNED" <?= $val('status') === 'RESIGNED' ? 'selected' : '' ?>>Resign</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="surface-card p-4">
            <h2 class="section-title mb-3">Foto Profil</h2>
            <?php if (!empty($employee['photo'])): ?>
                <img src="<?= e(url('uploads/' . $employee['photo'])) ?>" class="rounded mb-2" style="width:100%;max-width:160px;">
            <?php endif; ?>
            <input type="file" name="photo" class="form-control" accept="image/jpeg,image/png,image/webp">
            <div class="form-helper">Kosongkan jika tidak ingin mengubah foto.</div>
        </div>
        <div class="d-grid gap-2 mt-3">
            <button type="submit" class="btn btn-primary py-2" data-loading-text="Menyimpan...">Simpan Perubahan</button>
            <a href="/employees/<?= e($employee['id']) ?>" class="btn btn-outline-secondary">Batal</a>
        </div>
    </div>
</form>
