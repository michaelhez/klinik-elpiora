<?php
\App\Core\View::extend('layouts.app');
$errors = \App\Core\Session::getFlash('_errors') ?? [];
?>

<div class="breadcrumb-custom"><a href="/dashboard">Dashboard</a> / <a href="/roles">Peran & Akses</a> / Tambah</div>
<h1 class="page-title mb-4">Tambah Peran</h1>

<form action="/roles" method="POST">
    <?= csrf_field() ?>
    <div class="surface-card p-4 mb-3">
        <h2 class="section-title mb-3">Informasi Peran</h2>
        <div class="row g-2">
            <div class="col-md-6 mb-3">
                <label class="form-label form-label-required">Nama Peran</label>
                <input type="text" name="name" class="form-control<?= isset($errors['name']) ? ' is-invalid' : '' ?>" value="<?= e(old('name')) ?>" required>
                <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= e($errors['name'][0]) ?></div><?php endif; ?>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Deskripsi</label>
                <input type="text" name="description" class="form-control" value="<?= e(old('description')) ?>">
            </div>
        </div>
    </div>

    <div class="surface-card p-4 mb-3">
        <h2 class="section-title mb-3">Izin Akses</h2>
        <?= \App\Core\View::include('roles._permission_matrix', ['catalogue' => $catalogue, 'assignedIds' => []]) ?>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">Simpan Peran</button>
        <a href="/roles" class="btn btn-outline-secondary">Batal</a>
    </div>
</form>
