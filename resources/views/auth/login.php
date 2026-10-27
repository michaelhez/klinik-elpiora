<?php
\App\Core\View::extend('layouts.auth');
$errors = \App\Core\Session::getFlash('_errors') ?? [];
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="text-center mb-4">
            <div class="auth-brand-icon"><i class="bi bi-heart-pulse-fill"></i></div>
            <h1 class="page-title mb-1"><?= e(config('app.clinic.name', config('app.name'))) ?></h1>
            <p class="text-muted-custom mb-0 small">Masuk untuk mengelola operasional klinik</p>
        </div>

        <?php if (isset($errors['email'])): ?>
            <div class="alert alert-danger py-2 small mb-3" role="alert">
                <?= e($errors['email'][0]) ?>
            </div>
        <?php endif; ?>

        <form action="/login" method="POST" data-guard novalidate>
            <?= csrf_field() ?>

            <div class="mb-3">
                <label for="email" class="form-label form-label-required">Email</label>
                <input type="email" class="form-control<?= isset($errors['email']) ? ' is-invalid' : '' ?>"
                       id="email" name="email" value="<?= e(old('email')) ?>"
                       autocomplete="username" required autofocus>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label form-label-required">Kata Sandi</label>
                <div class="input-group">
                    <input type="password" class="form-control<?= isset($errors['password']) ? ' is-invalid' : '' ?>"
                           id="password" name="password" autocomplete="current-password" required>
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword" aria-label="Tampilkan kata sandi">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                <?php if (isset($errors['password'])): ?>
                    <div class="form-error"><?= e($errors['password'][0]) ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2" data-loading-text="Memproses...">
                Masuk
            </button>
        </form>

        <p class="text-center text-caption mt-4 mb-0">
            &copy; <?= date('Y') ?> <?= e(config('app.clinic.name', config('app.name'))) ?>. All rights reserved.
        </p>
    </div>
</div>

<script>
document.getElementById('togglePassword')?.addEventListener('click', function () {
    const input = document.getElementById('password');
    const icon = this.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
});
</script>
