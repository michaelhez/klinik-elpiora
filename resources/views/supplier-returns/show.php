<?php \App\Core\View::extend('layouts.app'); ?>

<div class="breadcrumb-custom"><a href="/dashboard">Dashboard</a> / <a href="/supplier-returns">Retur Pemasok</a> / <?= e($return['return_number']) ?></div>

<div class="surface-card p-4 mb-4">
    <h1 class="page-title mb-1"><?= e($return['return_number']) ?></h1>
    <div class="text-muted-custom">Pemasok: <?= e($return['supplier_name']) ?></div>
    <div class="text-caption mt-1">Alasan: <?= e($return['reason']) ?></div>
    <div class="text-caption">Tanggal: <?= format_date($return['created_at'], 'd M Y H:i') ?></div>
</div>

<div class="surface-card p-4">
    <h2 class="section-title mb-3">Item yang Diretur</h2>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Obat</th><th>No. Batch</th><th>Jumlah</th><th>Catatan</th></tr></thead>
            <tbody>
            <?php foreach ($return['items'] as $item): ?>
                <tr>
                    <td><?= e($item['medicine_name']) ?></td>
                    <td><?= e($item['batch_number']) ?></td>
                    <td><?= (int) $item['quantity'] ?></td>
                    <td><?= e($item['notes'] ?? '-') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
