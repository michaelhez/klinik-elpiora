<?php \App\Core\View::extend('layouts.app'); ?>

<div class="breadcrumb-custom"><a href="/dashboard">Dashboard</a> / Tagihan</div>
<h1 class="page-title mb-1">Tagihan</h1>
<p class="text-muted-custom mb-4">Kelola tagihan pasien dan status pembayaran.</p>

<div class="surface-card p-3 mb-3">
    <form method="GET" action="/billing" class="row g-2 align-items-end">
        <div class="col-12 col-md-4">
            <label class="form-label">Cari</label>
            <input type="text" name="search" class="form-control" placeholder="No. tagihan atau nama pasien..." value="<?= e($filters['search']) ?>">
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">Semua Status</option>
                <?php foreach (['UNPAID' => 'Belum Dibayar', 'PARTIAL' => 'Dibayar Sebagian', 'PAID' => 'Lunas', 'CANCELLED' => 'Dibatalkan'] as $val => $label): ?>
                <option value="<?= $val ?>" <?= $filters['status'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
        </div>
    </form>
</div>

<div class="surface-card p-3">
    <?php if ($invoices === []): ?>
        <div class="empty-state"><i class="bi bi-receipt d-block"></i><h6>Belum ada tagihan</h6></div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>No. Tagihan</th><th>Pasien</th><th>Total</th><th>Dibayar</th><th>Status</th><th>Tanggal</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            <?php
            $labels = ['UNPAID' => 'Belum Dibayar', 'PARTIAL' => 'Dibayar Sebagian', 'PAID' => 'Lunas', 'CANCELLED' => 'Dibatalkan'];
            $badges = ['PAID' => 'badge-success', 'CANCELLED' => 'badge-danger', 'PARTIAL' => 'badge-warning', 'UNPAID' => 'badge-muted'];
            ?>
            <?php foreach ($invoices as $inv): ?>
                <tr>
                    <td><?= e($inv['invoice_number']) ?></td>
                    <td><?= e($inv['patient_name']) ?></td>
                    <td><?= format_currency($inv['total']) ?></td>
                    <td><?= format_currency($inv['paid_amount']) ?></td>
                    <td><span class="badge-status <?= $badges[$inv['status']] ?? 'badge-muted' ?>"><?= $labels[$inv['status']] ?? $inv['status'] ?></span></td>
                    <td><?= format_date($inv['created_at'], 'd M Y H:i') ?></td>
                    <td class="text-end"><a href="/billing/<?= e($inv['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
