<?php
\App\Core\View::extend('layouts.app');
$errors = \App\Core\Session::getFlash('_errors') ?? [];
?>

<div class="breadcrumb-custom"><a href="/dashboard">Dashboard</a> / <a href="/medicines">Obat</a> / Tambah</div>
<h1 class="page-title mb-4">Tambah Obat</h1>

<form action="/medicines" method="POST" data-guard class="row g-4">
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
                    <label class="form-label form-label-required">Nama Obat</label>
                    <input type="text" name="name" class="form-control" value="<?= e(old('name')) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nama Generik</label>
                    <input type="text" name="generic_name" class="form-control" value="<?= e(old('generic_name')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nama Merk</label>
                    <input type="text" name="brand_name" class="form-control" value="<?= e(old('brand_name')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Kategori</label>
                    <select name="category_id" class="form-select">
                        <option value="">Pilih Kategori</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= e($cat['id']) ?>" <?= (string) old('category_id') === (string) $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Satuan</label>
                    <select name="unit_id" class="form-select">
                        <option value="">Pilih Satuan</option>
                        <?php foreach ($units as $unit): ?>
                        <option value="<?= e($unit['id']) ?>" <?= (string) old('unit_id') === (string) $unit['id'] ? 'selected' : '' ?>><?= e($unit['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label form-label-required">Harga Beli</label>
                    <input type="number" name="purchase_price" class="form-control" value="<?= e(old('purchase_price', '0')) ?>" min="0" step="100" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label form-label-required">Harga Jual</label>
                    <input type="number" name="selling_price" class="form-control" value="<?= e(old('selling_price', '0')) ?>" min="0" step="100" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Stok Minimum</label>
                    <input type="number" name="minimum_stock" class="form-control" value="<?= e(old('minimum_stock', '10')) ?>" min="0">
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
            <button type="submit" class="btn btn-primary py-2" data-loading-text="Menyimpan...">Simpan Obat</button>
            <a href="/medicines" class="btn btn-outline-secondary">Batal</a>
        </div>
    </div>
</form>
