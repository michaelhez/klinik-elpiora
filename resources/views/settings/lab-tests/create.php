<?php
\App\Core\View::extend('layouts.app');
$errors = \App\Core\Session::getFlash('_errors') ?? [];
?>

<div class="breadcrumb-custom"><a href="/dashboard">Dashboard</a> / <a href="/settings/lab-tests">Jenis Pemeriksaan Lab</a> / Tambah</div>
<h1 class="page-title mb-4">Tambah Jenis Pemeriksaan</h1>

<form action="/settings/lab-tests" method="POST" data-guard class="row g-4">
    <?= csrf_field() ?>
    <div class="col-lg-8">
        <div class="surface-card p-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label form-label-required">Kode</label>
                    <input type="text" name="code" class="form-control<?= isset($errors['code']) ? ' is-invalid' : '' ?>" value="<?= e(old('code')) ?>" required>
                    <?php if (isset($errors['code'])): ?><div class="form-error"><?= e($errors['code'][0]) ?></div><?php endif; ?>
                </div>
                <div class="col-md-8">
                    <label class="form-label form-label-required">Nama Pemeriksaan</label>
                    <input type="text" name="name" class="form-control" value="<?= e(old('name')) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Kategori</label>
                    <input type="text" name="category" class="form-control" placeholder="Contoh: Hematologi" value="<?= e(old('category')) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Jenis Sampel</label>
                    <input type="text" name="sample_type" class="form-control" placeholder="Contoh: Darah, Urine" value="<?= e(old('sample_type')) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label form-label-required">Harga</label>
                    <input type="number" name="price" class="form-control" value="<?= e(old('price', '0')) ?>" min="0" step="1000" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-control" rows="2"><?= e(old('description')) ?></textarea>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary py-2" data-loading-text="Menyimpan...">Simpan</button>
            <a href="/settings/lab-tests" class="btn btn-outline-secondary">Batal</a>
        </div>
    </div>
</form>
