<?php
\App\Core\View::extend('layouts.app');
$errors = \App\Core\Session::getFlash('_errors') ?? [];
?>

<div class="breadcrumb-custom"><a href="/dashboard">Dashboard</a> / <a href="/users">Pengguna</a> / Edit</div>
<h1 class="page-title mb-4">Edit Pengguna</h1>

<form action="/users/<?= e($user['id']) ?>/update" method="POST">
    <?= csrf_field() ?>
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="surface-card p-4">
                <h2 class="section-title mb-3">Informasi Akun</h2>
                <div class="mb-3">
                    <label class="form-label form-label-required">Nama Lengkap</label>
                    <input type="text" name="name" class="form-control<?= isset($errors['name']) ? ' is-invalid' : '' ?>" value="<?= e(old('name', $user['name'])) ?>" required>
                </div>
                <div class="row g-2">
                    <div class="col-6 mb-3">
                        <label class="form-label form-label-required">Email</label>
                        <input type="email" name="email" class="form-control<?= isset($errors['email']) ? ' is-invalid' : '' ?>" value="<?= e(old('email', $user['email'])) ?>" required>
                        <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?= e($errors['email'][0]) ?></div><?php endif; ?>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control<?= isset($errors['username']) ? ' is-invalid' : '' ?>" value="<?= e(old('username', $user['username'] ?? '')) ?>">
                        <?php if (isset($errors['username'])): ?><div class="invalid-feedback"><?= e($errors['username'][0]) ?></div><?php endif; ?>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Telepon</label>
                    <input type="text" name="phone" class="form-control" value="<?= e(old('phone', $user['phone'] ?? '')) ?>">
                </div>
                <div class="form-check form-switch mb-3">
                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" <?= $user['is_active'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="is_active">Akun Aktif</label>
                </div>

                <div class="border-top pt-3">
                    <button type="button" class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#resetPasswordModal"><i class="bi bi-key me-1"></i>Reset Kata Sandi</button>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="surface-card p-4">
                <h2 class="section-title mb-3">Peran</h2>
                <?php foreach ($roles as $role): ?>
                <div class="form-check mb-2">
                    <input type="checkbox" class="form-check-input" id="role_<?= e($role['id']) ?>" name="role_ids[]" value="<?= e($role['id']) ?>" <?= in_array($role['id'], $assignedRoleIds, false) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="role_<?= e($role['id']) ?>"><?= e($role['name']) ?></label>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        <a href="/users" class="btn btn-outline-secondary">Batal</a>
    </div>
</form>

<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form id="resetPasswordForm" data-guard>
            <div class="modal-header"><h5 class="modal-title">Reset Kata Sandi</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-0">
                    <label class="form-label form-label-required">Kata Sandi Baru</label>
                    <input type="password" name="password" class="form-control" minlength="8" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-warning" data-loading-text="Menyimpan...">Reset</button>
            </div>
        </form>
    </div></div>
</div>

<script>window.USER_ID = <?= (int) $user['id'] ?>;</script>
<script src="<?= asset('js/users.js') ?>"></script>
