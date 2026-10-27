<?php \App\Core\View::extend('layouts.app'); ?>

<div class="breadcrumb-custom"><a href="/dashboard">Dashboard</a> / Pengaturan Sistem</div>
<h1 class="page-title mb-4">Pengaturan Sistem</h1>

<form action="/settings/system" method="POST">
    <?= csrf_field() ?>
    <div class="surface-card p-4" style="max-width:640px;">
        <?php foreach ($settings as $setting): ?>
        <div class="mb-3">
            <label class="form-label" for="s_<?= e($setting['key']) ?>"><?= e($setting['label']) ?></label>
            <input type="text" class="form-control" id="s_<?= e($setting['key']) ?>" name="<?= e($setting['key']) ?>" value="<?= e($setting['value']) ?>">
        </div>
        <?php endforeach; ?>
        <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
    </div>
</form>
