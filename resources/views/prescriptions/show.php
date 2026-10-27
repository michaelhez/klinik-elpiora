<?php
\App\Core\View::extend('layouts.app');
$labels = ['DRAFT' => 'Draf', 'WAITING_PHARMACY' => 'Menunggu Farmasi', 'PROCESSING' => 'Diproses', 'READY' => 'Siap', 'DISPENSED' => 'Diserahkan', 'CANCELLED' => 'Dibatalkan'];
$badge = match ($prescription['status']) {
    'DISPENSED' => 'badge-success', 'READY' => 'badge-info', 'CANCELLED' => 'badge-danger', 'PROCESSING' => 'badge-warning', default => 'badge-muted',
};
?>

<div class="breadcrumb-custom"><a href="/dashboard">Dashboard</a> / <a href="/pharmacy">Farmasi</a> / <?= e($prescription['prescription_number']) ?></div>

<div class="surface-card p-4 mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
        <div>
            <h1 class="page-title mb-1"><?= e($prescription['patient_name']) ?></h1>
            <div class="text-muted-custom"><?= e($prescription['medical_record_number']) ?> &bull; Dokter: <?= e($prescription['doctor_name']) ?> &bull; <?= e($prescription['branch_name']) ?></div>
            <div class="text-caption mt-1">No. Resep: <?= e($prescription['prescription_number']) ?> &bull; Rekam Medis: <a href="/medical-records/<?= e($prescription['medical_record_id']) ?>"><?= e($prescription['record_number']) ?></a></div>
        </div>
        <div class="text-md-end">
            <span class="badge-status <?= $badge ?> mb-2 d-inline-block"><?= $labels[$prescription['status']] ?? $prescription['status'] ?></span>
            <div class="d-flex gap-2 justify-content-md-end flex-wrap">
                <?php if ($prescription['status'] === 'WAITING_PHARMACY' && can('prescription.dispense')): ?>
                <form action="/pharmacy/<?= e($prescription['id']) ?>/verify" method="POST">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check2 me-1"></i>Verifikasi</button>
                </form>
                <?php endif; ?>
                <?php if ($prescription['status'] === 'PROCESSING' && can('prescription.dispense')): ?>
                <form action="/pharmacy/<?= e($prescription['id']) ?>/prepare" method="POST" onsubmit="return confirm('Siapkan obat dan potong stok (FEFO)?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-box-seam me-1"></i>Siapkan Obat</button>
                </form>
                <?php endif; ?>
                <?php if ($prescription['status'] === 'READY' && can('prescription.dispense')): ?>
                <form action="/pharmacy/<?= e($prescription['id']) ?>/handover" method="POST" onsubmit="return confirm('Serahkan resep ini ke pasien?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-hand-thumbs-up me-1"></i>Serahkan ke Pasien</button>
                </form>
                <?php endif; ?>
                <?php if (!in_array($prescription['status'], ['DISPENSED', 'CANCELLED'], true) && can('prescription.update')): ?>
                <form action="/prescriptions/<?= e($prescription['id']) ?>/cancel" method="POST" onsubmit="return confirm('Batalkan resep ini?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-danger btn-sm">Batalkan</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="surface-card p-4 mb-4">
    <h2 class="section-title mb-3">Obat (Non-Racikan)</h2>
    <?php if ($prescription['items'] === []): ?>
        <p class="text-muted-custom small mb-0">Tidak ada obat non-racikan pada resep ini.</p>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Obat</th><th>Dosis</th><th>Jumlah</th><th>Aturan Pakai</th><th>Harga</th></tr></thead>
            <tbody>
            <?php foreach ($prescription['items'] as $item): ?>
                <tr>
                    <td><?= e($item['medicine_name']) ?> <span class="text-caption">(<?= e($item['medicine_code']) ?>)</span></td>
                    <td><?= e($item['dosage'] ?? '-') ?></td>
                    <td><?= (int) $item['quantity'] ?></td>
                    <td><?= e($item['instructions'] ?? '-') ?></td>
                    <td><?= format_currency($item['price'] * $item['quantity']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php if ($prescription['compounds'] !== []): ?>
<div class="surface-card p-4 mb-4">
    <h2 class="section-title mb-3">Obat Racikan</h2>
    <?php foreach ($prescription['compounds'] as $compound): ?>
    <div class="border rounded-3 p-3 mb-3">
        <div class="d-flex justify-content-between">
            <div class="card-title-sm"><?= e($compound['compound_name']) ?> &times; <?= (int) $compound['quantity'] ?> bungkus</div>
            <div class="text-caption"><?= format_currency($compound['price']) ?></div>
        </div>
        <div class="text-caption mb-2"><?= e($compound['dosage_instructions'] ?? '-') ?></div>
        <table class="table table-sm mb-0">
            <thead><tr><th>Komposisi</th><th>Jumlah/Bungkus</th></tr></thead>
            <tbody>
            <?php foreach ($compound['items'] as $ci): ?>
                <tr><td><?= e($ci['medicine_name']) ?></td><td><?= e($ci['quantity_per_dose']) ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if ($prescription['dispensing'] !== null && $prescription['dispensing_items'] !== []): ?>
<div class="surface-card p-4">
    <h2 class="section-title mb-3">Detail Pengambilan Stok (FEFO)</h2>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Batch</th><th>Kadaluwarsa</th><th>Jumlah Diambil</th></tr></thead>
            <tbody>
            <?php foreach ($prescription['dispensing_items'] as $di): ?>
                <tr>
                    <td><?= e($di['batch_number']) ?></td>
                    <td><?= format_date($di['expired_date']) ?></td>
                    <td><?= (int) $di['quantity_taken'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($prescription['dispensing']['dispensed_at']): ?>
        <p class="text-caption mb-0">Diserahkan pada <?= format_date($prescription['dispensing']['dispensed_at'], 'd M Y H:i') ?></p>
    <?php endif; ?>
</div>
<?php endif; ?>
