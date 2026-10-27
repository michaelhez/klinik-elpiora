<?php \App\Core\View::extend('layouts.app'); ?>

<div class="breadcrumb-custom"><a href="/dashboard">Dashboard</a> / Retur Pemasok</div>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
    <div>
        <h1 class="page-title mb-1">Retur Pemasok</h1>
        <p class="text-muted-custom mb-0">Riwayat pengembalian obat ke pemasok (rusak, kadaluwarsa, atau kesalahan kirim).</p>
    </div>
    <?php if (can('supplier_return.create')): ?>
    <a href="/supplier-returns/create" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Buat Retur</a>
    <?php endif; ?>
</div>

<div class="surface-card p-3">
    <?php if ($returns === []): ?>
        <div class="empty-state"><i class="bi bi-arrow-counterclockwise d-block"></i><h6>Belum ada retur pemasok</h6></div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>No. Retur</th><th>Pemasok</th><th>Alasan</th><th>Tanggal</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($returns as $sr): ?>
                <tr>
                    <td><?= e($sr['return_number']) ?></td>
                    <td><?= e($sr['supplier_name']) ?></td>
                    <td><?= e($sr['reason']) ?></td>
                    <td><?= format_date($sr['created_at'], 'd M Y H:i') ?></td>
                    <td class="text-end"><a href="/supplier-returns/<?= e($sr['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
