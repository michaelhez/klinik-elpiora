<?php
\App\Core\View::extend('layouts.app');
$labels = ['UNPAID' => 'Belum Dibayar', 'PARTIAL' => 'Dibayar Sebagian', 'PAID' => 'Lunas', 'CANCELLED' => 'Dibatalkan'];
$badge = match ($invoice['status']) {
    'PAID' => 'badge-success', 'CANCELLED' => 'badge-danger', 'PARTIAL' => 'badge-warning', default => 'badge-muted',
};
$remaining = (float) $invoice['total'] - (float) $invoice['paid_amount'];
$paymentTypeLabels = ['SELF_PAY' => 'Bayar Sendiri', 'BPJS' => 'BPJS', 'INSURANCE' => 'Asuransi', 'CORPORATE' => 'Perusahaan Rekanan'];
?>

<div class="breadcrumb-custom"><a href="/dashboard">Dashboard</a> / <a href="/billing">Tagihan</a> / <?= e($invoice['invoice_number']) ?></div>

<div class="surface-card p-4 mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
        <div>
            <h1 class="page-title mb-1"><?= e($invoice['patient_name']) ?></h1>
            <div class="text-muted-custom"><?= e($invoice['medical_record_number']) ?> &bull; No. Registrasi: <?= e($invoice['registration_number']) ?></div>
            <div class="text-caption mt-1">
                No. Tagihan: <?= e($invoice['invoice_number']) ?> &bull;
                Cara Bayar: <?= $paymentTypeLabels[$invoice['payment_type']] ?? $invoice['payment_type'] ?>
                <?= $invoice['insurance_name'] ? ' (' . e($invoice['insurance_name']) . ')' : '' ?>
                <?= $invoice['corporate_name'] ? ' (' . e($invoice['corporate_name']) . ')' : '' ?>
            </div>
        </div>
        <div class="text-md-end">
            <span class="badge-status <?= $badge ?> mb-2 d-inline-block"><?= $labels[$invoice['status']] ?? $invoice['status'] ?></span>
            <div class="d-flex gap-2 justify-content-md-end flex-wrap">
                <?php if (in_array($invoice['status'], ['UNPAID', 'PARTIAL'], true) && can('payment.create')): ?>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#payModal"><i class="bi bi-cash-coin me-1"></i>Bayar</button>
                <?php endif; ?>
                <?php if ((float) $invoice['paid_amount'] > 0 && can('payment.refund')): ?>
                <button type="button" class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#refundModal"><i class="bi bi-arrow-counterclockwise me-1"></i>Refund</button>
                <?php endif; ?>
                <?php if ($invoice['status'] === 'UNPAID' && can('billing.update')): ?>
                <form action="/billing/<?= e($invoice['id']) ?>/cancel" method="POST" onsubmit="return confirm('Batalkan tagihan ini?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-danger btn-sm">Batalkan</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="surface-card p-3 text-center">
            <div class="text-caption">Total Tagihan</div>
            <div class="kpi-value" style="font-size:1.4rem;"><?= format_currency($invoice['total']) ?></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="surface-card p-3 text-center">
            <div class="text-caption">Sudah Dibayar</div>
            <div class="kpi-value text-success" style="font-size:1.4rem;"><?= format_currency($invoice['paid_amount']) ?></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="surface-card p-3 text-center">
            <div class="text-caption">Sisa Tagihan</div>
            <div class="kpi-value <?= $remaining > 0 ? 'text-danger' : '' ?>" style="font-size:1.4rem;"><?= format_currency(max(0, $remaining)) ?></div>
        </div>
    </div>
</div>

<div class="surface-card p-4 mb-4">
    <h2 class="section-title mb-3">Rincian Tagihan</h2>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Jenis</th><th>Deskripsi</th><th>Jumlah</th><th>Harga</th><th>Subtotal</th></tr></thead>
            <tbody>
            <?php
            $itemLabels = ['CONSULTATION' => 'Konsultasi', 'MEDICAL_ACTION' => 'Tindakan Medis', 'LAB_TEST' => 'Laboratorium', 'PRESCRIPTION_ITEM' => 'Obat', 'PRESCRIPTION_COMPOUND' => 'Racikan', 'OTHER' => 'Lainnya'];
            ?>
            <?php foreach ($invoice['items'] as $item): ?>
                <tr>
                    <td><span class="badge-status badge-muted"><?= $itemLabels[$item['item_type']] ?? $item['item_type'] ?></span></td>
                    <td><?= e($item['description']) ?></td>
                    <td><?= (int) $item['quantity'] ?></td>
                    <td><?= format_currency($item['price']) ?></td>
                    <td><?= format_currency($item['subtotal']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr><td colspan="4" class="text-end fw-semibold">Subtotal</td><td class="fw-semibold"><?= format_currency($invoice['subtotal']) ?></td></tr>
                <?php if ((float) $invoice['discount'] > 0): ?>
                <tr><td colspan="4" class="text-end fw-semibold">Diskon</td><td class="fw-semibold text-danger">-<?= format_currency($invoice['discount']) ?></td></tr>
                <?php endif; ?>
                <tr><td colspan="4" class="text-end fw-semibold">Total</td><td class="fw-semibold"><?= format_currency($invoice['total']) ?></td></tr>
            </tfoot>
        </table>
    </div>
</div>

<div class="surface-card p-4">
    <h2 class="section-title mb-3">Riwayat Pembayaran</h2>
    <?php if ($invoice['payments'] === []): ?>
        <p class="text-muted-custom small mb-0">Belum ada pembayaran.</p>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>No. Pembayaran</th><th>Tipe</th><th>Metode</th><th>Jumlah</th><th>Tanggal</th><th>Oleh</th><th>Catatan</th></tr></thead>
            <tbody>
            <?php $methodLabels = ['CASH' => 'Tunai', 'TRANSFER' => 'Transfer', 'DEBIT' => 'Debit', 'CREDIT' => 'Kredit', 'INSURANCE' => 'Asuransi', 'CORPORATE' => 'Perusahaan']; ?>
            <?php foreach ($invoice['payments'] as $pay): ?>
                <tr>
                    <td><?= e($pay['payment_number']) ?></td>
                    <td><span class="badge-status <?= $pay['type'] === 'REFUND' ? 'badge-danger' : 'badge-success' ?>"><?= $pay['type'] === 'REFUND' ? 'Refund' : 'Pembayaran' ?></span></td>
                    <td><?= $methodLabels[$pay['method']] ?? $pay['method'] ?></td>
                    <td><?= format_currency($pay['amount']) ?></td>
                    <td><?= format_date($pay['paid_at'], 'd M Y H:i') ?></td>
                    <td><?= e($pay['paid_by_name'] ?? '-') ?></td>
                    <td><?= e($pay['notes'] ?? '-') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="payModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form id="payForm" data-guard>
            <div class="modal-header"><h5 class="modal-title">Catat Pembayaran</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label form-label-required">Metode Pembayaran</label>
                    <select name="method" class="form-select" required>
                        <option value="CASH">Tunai</option>
                        <option value="TRANSFER">Transfer</option>
                        <option value="DEBIT">Debit</option>
                        <option value="CREDIT">Kredit</option>
                        <option value="INSURANCE">Asuransi</option>
                        <option value="CORPORATE">Perusahaan</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label form-label-required">Jumlah</label>
                    <input type="number" name="amount" class="form-control" min="1" step="1" value="<?= (int) max(0, $remaining) ?>" required>
                    <div class="form-helper">Sisa tagihan: <?= format_currency(max(0, $remaining)) ?></div>
                </div>
                <div class="mb-3">
                    <label class="form-label">No. Referensi</label>
                    <input type="text" name="reference_number" class="form-control" placeholder="No. transfer/kartu (opsional)">
                </div>
                <div class="mb-3">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" data-loading-text="Memproses...">Simpan Pembayaran</button>
            </div>
        </form>
    </div></div>
</div>

<div class="modal fade" id="refundModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form id="refundForm" data-guard>
            <div class="modal-header"><h5 class="modal-title">Proses Refund</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label form-label-required">Jumlah Refund</label>
                    <input type="number" name="amount" class="form-control" min="1" step="1" max="<?= (int) $invoice['paid_amount'] ?>" required>
                    <div class="form-helper">Maksimal: <?= format_currency($invoice['paid_amount']) ?></div>
                </div>
                <div class="mb-3">
                    <label class="form-label form-label-required">Alasan Refund</label>
                    <textarea name="notes" class="form-control" rows="2" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-warning" data-loading-text="Memproses...">Proses Refund</button>
            </div>
        </form>
    </div></div>
</div>

<script>window.INVOICE_ID = <?= (int) $invoice['id'] ?>;</script>
<script src="<?= asset('js/billing.js') ?>"></script>
