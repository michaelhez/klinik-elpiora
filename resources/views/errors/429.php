<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>429 - Terlalu Banyak Percobaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body>
    <div class="error-wrapper">
        <div>
            <i class="bi bi-hourglass-split display-1" style="color:var(--color-warning);"></i>
            <div class="error-code mt-3">429</div>
            <h1 class="section-title mt-2">Terlalu Banyak Percobaan</h1>
            <p class="text-muted-custom">
                <?= e($message !== '' ? $message : 'Anda telah mencoba terlalu banyak. Silakan coba lagi beberapa saat lagi.') ?>
            </p>
            <a href="/login" class="btn btn-primary mt-2">Kembali ke Login</a>
        </div>
    </div>
</body>
</html>
