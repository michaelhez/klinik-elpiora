<?php \App\Core\View::extend('layouts.app'); ?>

<div class="breadcrumb-custom"><a href="/dashboard">Dashboard</a> / Purchase Order</div>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
    <div>
        <h1 class="page-title mb-1">Purchase Order</h1>
        <p class="text-muted-custom mb-0">Kelola pemesanan obat ke pemasok.</p>
    </div>
    <?php if (can('purchase_order.create')): ?>
    <a href="/purchase-orders/create" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Buat Purchase Order</a>
    <?php endif; ?>
</div>

<div class="surface-card p-3 mb-3">
    <form method="GET" action="/purchase-orders" class="row g-2 align-items-end">
        <div class="col-6 col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <?php foreach (['DRAFT' => 'Draf', 'SUBMITTED' => 'Diajukan', 'APPROVED' => 'Disetujui', 'ORDERED' => 'Dipesan', 'RECEIVED' => 'Diterima Sebagian', 'COMPLETED' => 'Selesai', 'CANCELLED' => 'Dibatalkan'] as $val => $label): ?>
                <option value="<?= $val ?>" <?= $filters['status'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>
</div>

<div class="surface-card p-3">
    <?php if ($orders === []): ?>
        <div class="empty-state"><i class="bi bi-cart d-block"></i><h6>Belum ada purchase order</h6></div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>No. PO</th><th>Pemasok</th><th>Status</th><th>Tanggal Pesan</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            <?php
            $labels = ['DRAFT' => 'Draf', 'SUBMITTED' => 'Diajukan', 'APPROVED' => 'Disetujui', 'ORDERED' => 'Dipesan', 'RECEIVED' => 'Diterima Sebagian', 'COMPLETED' => 'Selesai', 'CANCELLED' => 'Dibatalkan'];
            $badges = ['COMPLETED' => 'badge-success', 'CANCELLED' => 'badge-danger', 'RECEIVED' => 'badge-info', 'ORDERED' => 'badge-info', 'APPROVED' => 'badge-warning', 'SUBMITTED' => 'badge-warning', 'DRAFT' => 'badge-muted'];
            ?>
            <?php foreach ($orders as $po): ?>
                <tr>
                    <td><?= e($po['po_number']) ?></td>
                    <td><?= e($po['supplier_name']) ?></td>
                    <td><span class="badge-status <?= $badges[$po['status']] ?? 'badge-muted' ?>"><?= $labels[$po['status']] ?? $po['status'] ?></span></td>
                    <td><?= $po['order_date'] ? format_date($po['order_date']) : '-' ?></td>
                    <td class="text-end"><a href="/purchase-orders/<?= e($po['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
