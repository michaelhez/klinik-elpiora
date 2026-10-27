<?php \App\Core\View::extend('layouts.app'); ?>

<div class="breadcrumb-custom"><a href="/dashboard">Dashboard</a> / Inventori</div>
<h1 class="page-title mb-1">Inventori</h1>
<p class="text-muted-custom mb-4">Ringkasan stok menipis dan obat yang akan kadaluwarsa di seluruh cabang.</p>

<ul class="nav nav-pills gap-1 mb-3">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-low-stock">Stok Menipis <span class="badge-status badge-muted ms-1"><?= count($lowStock) ?></span></button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-expiring">Segera Kadaluwarsa <span class="badge-status badge-muted ms-1"><?= count($expiringSoon) ?></span></button></li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade show active" id="tab-low-stock">
        <div class="surface-card p-4">
            <h2 class="section-title mb-3">Stok Menipis (Di Bawah Stok Minimum)</h2>
            <?php if ($lowStock === []): ?>
                <div class="empty-state"><i class="bi bi-check-circle d-block"></i><h6>Semua stok obat aman</h6></div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Kode</th><th>Obat</th><th>Stok Saat Ini</th><th>Stok Minimum</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($lowStock as $row): ?>
                        <tr>
                            <td><?= e($row['code']) ?></td>
                            <td><?= e($row['name']) ?></td>
                            <td class="fw-semibold text-danger"><?= (int) $row['current_stock'] ?></td>
                            <td><?= (int) $row['minimum_stock'] ?></td>
                            <td class="text-end"><a href="/medicines/<?= e($row['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="tab-pane fade" id="tab-expiring">
        <div class="surface-card p-4">
            <h2 class="section-title mb-3">Batch Segera Kadaluwarsa (90 Hari)</h2>
            <?php if ($expiringSoon === []): ?>
                <div class="empty-state"><i class="bi bi-check-circle d-block"></i><h6>Tidak ada batch yang segera kadaluwarsa</h6></div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Obat</th><th>No. Batch</th><th>Cabang</th><th>Kadaluwarsa</th><th>Jumlah</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($expiringSoon as $row): ?>
                        <?php $isExpired = strtotime($row['expired_date']) < strtotime('today'); ?>
                        <tr>
                            <td><?= e($row['medicine_name']) ?> <span class="text-caption">(<?= e($row['medicine_code']) ?>)</span></td>
                            <td><?= e($row['batch_number']) ?></td>
                            <td><?= e($row['branch_name']) ?></td>
                            <td><?= format_date($row['expired_date']) ?> <?php if ($isExpired): ?><span class="badge-status badge-danger ms-1">Kadaluwarsa</span><?php else: ?><span class="badge-status badge-warning ms-1">Segera</span><?php endif; ?></td>
                            <td><?= (int) $row['quantity'] ?></td>
                            <td class="text-end"><a href="/medicines/<?= e($row['medicine_id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
