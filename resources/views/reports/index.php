<?php \App\Core\View::extend('layouts.app'); ?>

<div class="breadcrumb-custom"><a href="/dashboard">Dashboard</a> / Laporan</div>
<h1 class="page-title mb-1">Laporan</h1>
<p class="text-muted-custom mb-4">Analisis pendapatan, kunjungan, obat, kinerja dokter, dan stok.</p>

<div class="surface-card p-3 mb-3">
    <form method="GET" action="/reports" class="row g-2 align-items-end">
        <div class="col-6 col-md-3">
            <label class="form-label">Dari Tanggal</label>
            <input type="date" name="from" class="form-control" value="<?= e($from) ?>">
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label">Sampai Tanggal</label>
            <input type="date" name="to" class="form-control" value="<?= e($to) ?>">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Terapkan</button>
        </div>
    </form>
</div>

<ul class="nav nav-pills flex-wrap gap-1 mb-3">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-revenue">Pendapatan</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-visits">Kunjungan Pasien</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-dispensing">Obat Terlaris</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-doctors">Kinerja Dokter</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-inventory">Stok</button></li>
</ul>

<div class="tab-content">
    <!-- Revenue -->
    <div class="tab-pane fade show active" id="tab-revenue">
        <div class="surface-card p-4 mb-3">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h2 class="section-title mb-0">Pendapatan (<?= format_date($from) ?> - <?= format_date($to) ?>)</h2>
                    <div class="text-caption">Total: <strong><?= format_currency($revenue['grand_total']) ?></strong> dari <?= $revenue['invoice_count'] ?> tagihan</div>
                </div>
                <?php if (can('report.export')): ?>
                <div class="d-flex gap-2">
                    <a href="/reports/export/revenue?format=pdf&from=<?= e($from) ?>&to=<?= e($to) ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-file-pdf me-1"></i>PDF</a>
                    <a href="/reports/export/revenue?format=xlsx&from=<?= e($from) ?>&to=<?= e($to) ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-file-excel me-1"></i>Excel</a>
                </div>
                <?php endif; ?>
            </div>
            <canvas id="revenueChart" height="90"></canvas>
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="surface-card p-4">
                    <h3 class="card-title-sm mb-3">Berdasarkan Metode Pembayaran</h3>
                    <table class="table table-sm mb-0">
                        <?php foreach ($revenue['by_method'] as $row): ?>
                        <tr><td><?= e($row['method']) ?></td><td class="text-end"><?= format_currency($row['total']) ?></td></tr>
                        <?php endforeach; ?>
                        <?php if ($revenue['by_method'] === []): ?><tr><td class="text-muted-custom small">Tidak ada data.</td></tr><?php endif; ?>
                    </table>
                </div>
            </div>
            <div class="col-md-6">
                <div class="surface-card p-4">
                    <h3 class="card-title-sm mb-3">Berdasarkan Cara Bayar</h3>
                    <table class="table table-sm mb-0">
                        <?php $ptLabels = ['SELF_PAY' => 'Bayar Sendiri', 'BPJS' => 'BPJS', 'INSURANCE' => 'Asuransi', 'CORPORATE' => 'Perusahaan Rekanan']; ?>
                        <?php foreach ($revenue['by_payment_type'] as $row): ?>
                        <tr><td><?= $ptLabels[$row['payment_type']] ?? $row['payment_type'] ?></td><td class="text-end"><?= format_currency($row['total']) ?></td></tr>
                        <?php endforeach; ?>
                        <?php if ($revenue['by_payment_type'] === []): ?><tr><td class="text-muted-custom small">Tidak ada data.</td></tr><?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Visits -->
    <div class="tab-pane fade" id="tab-visits">
        <div class="surface-card p-4 mb-3">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h2 class="section-title mb-0">Kunjungan Pasien</h2>
                    <div class="text-caption">Total: <strong><?= $visits['total'] ?></strong> kunjungan</div>
                </div>
                <?php if (can('report.export')): ?>
                <div class="d-flex gap-2">
                    <a href="/reports/export/visits?format=pdf&from=<?= e($from) ?>&to=<?= e($to) ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-file-pdf me-1"></i>PDF</a>
                    <a href="/reports/export/visits?format=xlsx&from=<?= e($from) ?>&to=<?= e($to) ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-file-excel me-1"></i>Excel</a>
                </div>
                <?php endif; ?>
            </div>
            <canvas id="visitsChart" height="90"></canvas>
        </div>
        <div class="surface-card p-4">
            <h3 class="card-title-sm mb-3">Berdasarkan Poliklinik</h3>
            <table class="table table-sm mb-0">
                <thead><tr><th>Poliklinik</th><th class="text-end">Jumlah</th></tr></thead>
                <tbody>
                <?php foreach ($visits['by_polyclinic'] as $row): ?>
                <tr><td><?= e($row['polyclinic_name']) ?></td><td class="text-end"><?= (int) $row['total'] ?></td></tr>
                <?php endforeach; ?>
                <?php if ($visits['by_polyclinic'] === []): ?><tr><td colspan="2" class="text-muted-custom small">Tidak ada data.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Dispensing -->
    <div class="tab-pane fade" id="tab-dispensing">
        <div class="surface-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h2 class="section-title mb-0">Obat Terlaris</h2>
                <?php if (can('report.export')): ?>
                <div class="d-flex gap-2">
                    <a href="/reports/export/dispensing?format=pdf&from=<?= e($from) ?>&to=<?= e($to) ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-file-pdf me-1"></i>PDF</a>
                    <a href="/reports/export/dispensing?format=xlsx&from=<?= e($from) ?>&to=<?= e($to) ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-file-excel me-1"></i>Excel</a>
                </div>
                <?php endif; ?>
            </div>
            <?php if ($dispensing === []): ?>
                <div class="empty-state"><i class="bi bi-capsule d-block"></i><h6>Belum ada data dispensing pada periode ini</h6></div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Kode</th><th>Nama Obat</th><th class="text-end">Jumlah Terjual</th><th class="text-end">Jumlah Resep</th></tr></thead>
                    <tbody>
                    <?php foreach ($dispensing as $row): ?>
                    <tr>
                        <td><?= e($row['medicine_code']) ?></td>
                        <td><?= e($row['medicine_name']) ?></td>
                        <td class="text-end"><?= (int) $row['total_quantity'] ?></td>
                        <td class="text-end"><?= (int) $row['dispensing_count'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Doctors -->
    <div class="tab-pane fade" id="tab-doctors">
        <div class="surface-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h2 class="section-title mb-0">Kinerja Dokter</h2>
                <?php if (can('report.export')): ?>
                <div class="d-flex gap-2">
                    <a href="/reports/export/doctors?format=pdf&from=<?= e($from) ?>&to=<?= e($to) ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-file-pdf me-1"></i>PDF</a>
                    <a href="/reports/export/doctors?format=xlsx&from=<?= e($from) ?>&to=<?= e($to) ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-file-excel me-1"></i>Excel</a>
                </div>
                <?php endif; ?>
            </div>
            <?php if ($doctors === []): ?>
                <div class="empty-state"><i class="bi bi-heart-pulse d-block"></i><h6>Belum ada rekam medis difinalisasi pada periode ini</h6></div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Dokter</th><th>Poliklinik</th><th class="text-end">Jumlah Pasien</th></tr></thead>
                    <tbody>
                    <?php foreach ($doctors as $row): ?>
                    <tr>
                        <td><?= e($row['doctor_name']) ?></td>
                        <td><?= e($row['polyclinic_name']) ?></td>
                        <td class="text-end"><?= (int) $row['total_patients'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Inventory -->
    <div class="tab-pane fade" id="tab-inventory">
        <div class="surface-card p-4 mb-3">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h2 class="section-title mb-0">Stok Menipis</h2>
                <?php if (can('report.export')): ?>
                <div class="d-flex gap-2">
                    <a href="/reports/export/inventory?format=pdf&from=<?= e($from) ?>&to=<?= e($to) ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-file-pdf me-1"></i>PDF</a>
                    <a href="/reports/export/inventory?format=xlsx&from=<?= e($from) ?>&to=<?= e($to) ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-file-excel me-1"></i>Excel</a>
                </div>
                <?php endif; ?>
            </div>
            <?php if ($inventory['low_stock'] === []): ?>
                <div class="empty-state"><i class="bi bi-check-circle d-block"></i><h6>Semua stok obat aman</h6></div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Kode</th><th>Nama Obat</th><th class="text-end">Stok Saat Ini</th><th class="text-end">Stok Minimum</th></tr></thead>
                    <tbody>
                    <?php foreach ($inventory['low_stock'] as $row): ?>
                    <tr>
                        <td><?= e($row['code']) ?></td>
                        <td><?= e($row['name']) ?></td>
                        <td class="text-end text-danger"><?= (int) $row['current_stock'] ?></td>
                        <td class="text-end"><?= (int) $row['minimum_stock'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <div class="surface-card p-4">
            <h2 class="section-title mb-3">Segera Kadaluwarsa (90 Hari)</h2>
            <?php if ($inventory['expiring_soon'] === []): ?>
                <div class="empty-state"><i class="bi bi-check-circle d-block"></i><h6>Tidak ada batch yang segera kadaluwarsa</h6></div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Obat</th><th>No. Batch</th><th>Cabang</th><th>Kadaluwarsa</th><th class="text-end">Jumlah</th></tr></thead>
                    <tbody>
                    <?php foreach ($inventory['expiring_soon'] as $row): ?>
                    <tr>
                        <td><?= e($row['medicine_name']) ?></td>
                        <td><?= e($row['batch_number']) ?></td>
                        <td><?= e($row['branch_name']) ?></td>
                        <td><?= format_date($row['expired_date']) ?></td>
                        <td class="text-end"><?= (int) $row['quantity'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
window.REPORT_DATA = {
    revenue: <?= json_encode($revenue['daily']) ?>,
    visits: <?= json_encode($visits['daily']) ?>,
};
</script>
<script src="<?= asset('js/reports.js') ?>"></script>
