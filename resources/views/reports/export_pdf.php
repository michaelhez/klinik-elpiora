<?php
/** @var string $title */
/** @var array $headers */
/** @var array $rows */
/** @var string $from */
/** @var string $to */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?= e($title) ?></title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1a1a1a; }
        h1 { font-size: 16px; margin: 0 0 4px 0; }
        p.period { margin: 0 0 16px 0; color: #444; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 5px 8px; text-align: left; }
        th { background: #f1f5f9; }
    </style>
</head>
<body>
    <h1><?= e($title) ?></h1>
    <p class="period">Periode: <?= format_date($from) ?> - <?= format_date($to) ?></p>
    <table>
        <thead>
            <tr><?php foreach ($headers as $header): ?><th><?= e($header) ?></th><?php endforeach; ?></tr>
        </thead>
        <tbody>
            <?php if ($rows === []): ?>
            <tr><td colspan="<?= count($headers) ?>">Tidak ada data pada periode ini.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $row): ?>
            <tr><?php foreach ($row as $cell): ?><td><?= e((string) $cell) ?></td><?php endforeach; ?></tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
