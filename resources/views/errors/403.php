<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Akses Ditolak</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body>
    <div class="error-wrapper">
        <div>
            <i class="bi bi-shield-lock display-1" style="color:var(--color-danger);"></i>
            <div class="error-code mt-3">403</div>
            <h1 class="section-title mt-2">Akses Ditolak</h1>
            <p class="text-muted-custom">
                <?= e($message !== '' ? $message : 'Anda tidak memiliki izin untuk mengakses halaman ini.') ?>
            </p>
            <a href="/dashboard" class="btn btn-primary mt-2">Kembali ke Dashboard</a>
        </div>
    </div>
</body>
</html>
