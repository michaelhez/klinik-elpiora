<?php
\App\Core\View::extend('layouts.app');
$labels = ['DRAFT' => 'Draf', 'SUBMITTED' => 'Diajukan', 'APPROVED' => 'Disetujui', 'ORDERED' => 'Dipesan', 'RECEIVED' => 'Diterima Sebagian', 'COMPLETED' => 'Selesai', 'CANCELLED' => 'Dibatalkan'];
$badge = match ($order['status']) {
    'COMPLETED' => 'badge-success', 'CANCELLED' => 'badge-danger', 'RECEIVED', 'ORDERED' => 'badge-info', 'APPROVED', 'SUBMITTED' => 'badge-warning', default => 'badge-muted',
};
$canCancel = in_array($order['status'], ['DRAFT', 'SUBMITTED', 'APPROVED', 'ORDERED'], true);
$remainingItems = array_values(array_filter($order['items'], fn ($item) => (int) $item['quantity_received'] < (int) $item['quantity_ordered']));
?>

<div class="breadcrumb-custom"><a href="/dashboard">Dashboard</a> / <a href="/purchase-orders">Purchase Order</a> / <?= e($order['po_number']) ?></div>

<div class="surface-card p-4 mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
        <div>
            <h1 class="page-title mb-1"><?= e($order['po_number']) ?></h1>
            <div class="text-muted-custom"><?= e($order['supplier_name']) ?> (<?= e($order['supplier_code']) ?>) &bull; <?= e($order['branch_name']) ?></div>
            <div class="text-caption mt-1">
                Tanggal Pesan: <?= $order['order_date'] ? format_date($order['order_date']) : '-' ?>
                &bull; Estimasi Tiba: <?= $order['expected_date'] ? format_date($order['expected_date']) : '-' ?>
            </div>
            <?php if ($order['notes']): ?><div class="text-caption">Catatan: <?= e($order['notes']) ?></div><?php endif; ?>
        </div>
        <div class="text-md-end">
            <span class="badge-status <?= $badge ?> mb-2 d-inline-block"><?= $labels[$order['status']] ?? $order['status'] ?></span>
            <div class="d-flex gap-2 justify-content-md-end flex-wrap">
                <?php if ($order['status'] === 'DRAFT' && can('purchase_order.create')): ?>
                <form action="/purchase-orders/<?= e($order['id']) ?>/submit" method="POST" onsubmit="return confirm('Ajukan purchase order ini untuk persetujuan?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-send me-1"></i>Ajukan</button>
                </form>
                <?php endif; ?>
                <?php if ($order['status'] === 'SUBMITTED' && can('purchase_order.approve')): ?>
                <form action="/purchase-orders/<?= e($order['id']) ?>/approve" method="POST" onsubmit="return confirm('Setujui purchase order ini?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check2 me-1"></i>Setujui</button>
                </form>
                <?php endif; ?>
                <?php if ($order['status'] === 'APPROVED' && can('purchase_order.receive')): ?>
                <form action="/purchase-orders/<?= e($order['id']) ?>/mark-ordered" method="POST" onsubmit="return confirm('Tandai purchase order ini sebagai sudah dipesan ke pemasok?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-truck me-1"></i>Tandai Dipesan</button>
                </form>
                <?php endif; ?>
                <?php if (in_array($order['status'], ['ORDERED', 'RECEIVED'], true) && $remainingItems !== [] && can('purchase_order.receive')): ?>
                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#receiveModal"><i class="bi bi-box-seam me-1"></i>Terima Barang</button>
                <?php endif; ?>
                <?php if ($canCancel && can('purchase_order.create')): ?>
                <form action="/purchase-orders/<?= e($order['id']) ?>/cancel" method="POST" onsubmit="return confirm('Batalkan purchase order ini?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-danger btn-sm">Batalkan</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="surface-card p-4 mb-4">
    <h2 class="section-title mb-3">Item Pesanan</h2>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Obat</th><th>Dipesan</th><th>Diterima</th><th>Sisa</th><th>Harga Beli</th><th>Subtotal</th></tr></thead>
            <tbody>
            <?php foreach ($order['items'] as $item): ?>
                <?php $remaining = (int) $item['quantity_ordered'] - (int) $item['quantity_received']; ?>
                <tr>
                    <td><?= e($item['medicine_name']) ?> <span class="text-caption">(<?= e($item['medicine_code']) ?>)</span></td>
                    <td><?= (int) $item['quantity_ordered'] ?></td>
                    <td><?= (int) $item['quantity_received'] ?></td>
                    <td><?= $remaining > 0 ? $remaining : '<span class="text-success">Lengkap</span>' ?></td>
                    <td><?= format_currency($item['purchase_price']) ?></td>
                    <td><?= format_currency($item['subtotal']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($order['receipts'] !== []): ?>
<div class="surface-card p-4">
    <h2 class="section-title mb-3">Riwayat Penerimaan Barang</h2>
    <?php foreach ($order['receipts'] as $receipt): ?>
    <div class="border rounded-3 p-3 mb-3">
        <div class="d-flex justify-content-between">
            <div class="card-title-sm"><?= e($receipt['receipt_number']) ?></div>
            <div class="text-caption"><?= format_date($receipt['received_at'], 'd M Y H:i') ?></div>
        </div>
        <?php if ($receipt['notes']): ?><div class="text-caption mb-2"><?= e($receipt['notes']) ?></div><?php endif; ?>
        <table class="table table-sm mb-0">
            <thead><tr><th>Obat</th><th>No. Batch</th><th>Kadaluwarsa</th><th>Jumlah</th></tr></thead>
            <tbody>
            <?php foreach ($receipt['items'] as $ri): ?>
                <tr>
                    <td><?= e($ri['medicine_name']) ?></td>
                    <td><?= e($ri['batch_number']) ?></td>
                    <td><?= format_date($ri['expired_date']) ?></td>
                    <td><?= (int) $ri['quantity_received'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="modal fade" id="receiveModal" tabindex="-1">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <form id="receiveForm" data-guard>
            <div class="modal-header"><h5 class="modal-title">Terima Barang</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <p class="text-muted-custom small">Isi jumlah, nomor batch, dan tanggal kadaluwarsa untuk setiap obat yang diterima. Biarkan kosong (0) jika belum diterima.</p>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead><tr><th>Obat</th><th>Sisa</th><th>Jumlah Diterima</th><th>No. Batch</th><th>Kadaluwarsa</th></tr></thead>
                        <tbody>
                        <?php foreach ($remainingItems as $item): ?>
                            <?php $remaining = (int) $item['quantity_ordered'] - (int) $item['quantity_received']; ?>
                            <tr data-po-item-id="<?= e($item['id']) ?>">
                                <td><?= e($item['medicine_name']) ?></td>
                                <td><?= $remaining ?></td>
                                <td><input type="number" class="form-control form-control-sm" data-field="quantity_received" min="0" max="<?= $remaining ?>" value="0"></td>
                                <td><input type="text" class="form-control form-control-sm" data-field="batch_number"></td>
                                <td><input type="date" class="form-control form-control-sm" data-field="expired_date"></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mb-0">
                    <label class="form-label">Catatan</label>
                    <textarea id="receiveNotes" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" data-loading-text="Memproses...">Simpan Penerimaan</button>
            </div>
        </form>
    </div></div>
</div>

<script>window.PURCHASE_ORDER_ID = <?= (int) $order['id'] ?>;</script>
<script src="<?= asset('js/purchase-order-receive.js') ?>"></script>
