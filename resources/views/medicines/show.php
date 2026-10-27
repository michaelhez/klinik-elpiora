<?php
\App\Core\View::extend('layouts.app');
$totalStock = array_sum(array_column($batches, 'quantity'));
?>

<div class="breadcrumb-custom"><a href="/dashboard">Dashboard</a> / <a href="/medicines">Obat</a> / <?= e($medicine['name']) ?></div>

<div class="surface-card p-4 mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
        <div>
            <h1 class="page-title mb-1"><?= e($medicine['name']) ?></h1>
            <div class="text-muted-custom"><?= e($medicine['code']) ?> &bull; <?= e($medicine['category_name'] ?? '-') ?> &bull; <?= e($medicine['unit_name'] ?? '-') ?></div>
            <div class="text-caption mt-1">Beli: <?= format_currency($medicine['purchase_price']) ?> &bull; Jual: <?= format_currency($medicine['selling_price']) ?></div>
        </div>
        <div class="d-flex align-items-start gap-2">
            <div class="text-center me-2">
                <div class="kpi-value" style="font-size:1.5rem;"><?= $totalStock ?></div>
                <div class="text-caption">Total Stok</div>
            </div>
            <span class="badge-status <?= $medicine['status'] === 'ACTIVE' ? 'badge-success' : 'badge-muted' ?> align-self-center"><?= $medicine['status'] === 'ACTIVE' ? 'Aktif' : 'Nonaktif' ?></span>
            <?php if (can('medicine.update')): ?><a href="/medicines/<?= e($medicine['id']) ?>/edit" class="btn btn-outline-secondary btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a><?php endif; ?>
        </div>
    </div>
</div>

<ul class="nav nav-pills gap-1 mb-3">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-batches">Batch Stok</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-movements">Riwayat Pergerakan</button></li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade show active" id="tab-batches">
        <div class="surface-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="section-title mb-0">Batch Stok</h2>
                <?php if (can('inventory.adjust')): ?>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#batchModal"><i class="bi bi-plus-lg me-1"></i>Tambah Batch</button>
                <?php endif; ?>
            </div>
            <?php if ($batches === []): ?>
                <div class="empty-state"><i class="bi bi-box d-block"></i><h6>Belum ada batch stok</h6></div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>No. Batch</th><th>Cabang</th><th>Kadaluwarsa</th><th>Jumlah</th><th>Harga Beli</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($batches as $b): ?>
                        <?php
                        $isExpired = strtotime($b['expired_date']) < strtotime('today');
                        $isExpiringSoon = !$isExpired && strtotime($b['expired_date']) < strtotime('+90 days');
                        ?>
                        <tr>
                            <td><?= e($b['batch_number']) ?></td>
                            <td><?= e($b['branch_name']) ?></td>
                            <td>
                                <?= format_date($b['expired_date']) ?>
                                <?php if ($isExpired): ?><span class="badge-status badge-danger ms-1">Kadaluwarsa</span>
                                <?php elseif ($isExpiringSoon): ?><span class="badge-status badge-warning ms-1">Segera Kadaluwarsa</span><?php endif; ?>
                            </td>
                            <td><?= (int) $b['quantity'] ?></td>
                            <td><?= format_currency($b['purchase_price']) ?></td>
                            <td class="text-end">
                                <?php if (can('inventory.adjust')): ?>
                                <button class="btn btn-sm btn-outline-secondary" onclick='Inv.openAdjust(<?= json_encode($b) ?>)' title="Sesuaikan Stok"><i class="bi bi-sliders"></i></button>
                                <?php endif; ?>
                                <?php if (can('inventory.transfer')): ?>
                                <button class="btn btn-sm btn-outline-secondary" onclick='Inv.openTransfer(<?= json_encode($b) ?>)' title="Transfer"><i class="bi bi-arrow-left-right"></i></button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="tab-pane fade" id="tab-movements">
        <div class="surface-card p-4">
            <h2 class="section-title mb-3">Riwayat Pergerakan Stok</h2>
            <?php if ($movements === []): ?>
                <div class="empty-state"><i class="bi bi-arrow-left-right d-block"></i><h6>Belum ada pergerakan stok</h6></div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Tanggal</th><th>Tipe</th><th>Batch</th><th>Perubahan</th><th>Catatan</th><th>Oleh</th></tr></thead>
                    <tbody>
                    <?php foreach ($movements as $mv): ?>
                        <?php
                        $typeLabels = ['PURCHASE' => 'Pembelian', 'DISPENSING' => 'Dispensing', 'SALE' => 'Penjualan', 'RETURN' => 'Retur', 'ADJUSTMENT' => 'Penyesuaian', 'EXPIRED' => 'Kadaluwarsa', 'DAMAGED' => 'Rusak', 'TRANSFER' => 'Transfer'];
                        ?>
                        <tr>
                            <td><?= format_date($mv['created_at'], 'd M Y H:i') ?></td>
                            <td><span class="badge-status badge-info"><?= $typeLabels[$mv['type']] ?? $mv['type'] ?></span></td>
                            <td><?= e($mv['batch_number'] ?? '-') ?></td>
                            <td class="fw-semibold <?= $mv['quantity_change'] >= 0 ? 'text-success' : 'text-danger' ?>"><?= $mv['quantity_change'] >= 0 ? '+' : '' ?><?= $mv['quantity_change'] ?></td>
                            <td><?= e($mv['notes'] ?? '-') ?></td>
                            <td><?= e($mv['user_name'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="batchModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form id="batchForm" data-guard>
            <div class="modal-header"><h5 class="modal-title">Tambah Batch Stok</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label form-label-required">Cabang</label>
                    <select name="branch_id" class="form-select" required>
                        <?php foreach ($branches as $branch): ?>
                        <option value="<?= e($branch['id']) ?>"><?= e($branch['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label form-label-required">Nomor Batch</label>
                    <input type="text" name="batch_number" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label form-label-required">Tanggal Kadaluwarsa</label>
                    <input type="date" name="expired_date" class="form-control" required>
                </div>
                <div class="row g-2">
                    <div class="col-6 mb-3">
                        <label class="form-label form-label-required">Jumlah</label>
                        <input type="number" name="quantity" class="form-control" min="1" required>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label form-label-required">Harga Beli</label>
                        <input type="number" name="purchase_price" class="form-control" value="<?= e($medicine['purchase_price']) ?>" min="0" step="100" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" data-loading-text="Menyimpan...">Simpan</button>
            </div>
        </form>
    </div></div>
</div>

<div class="modal fade" id="adjustModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form id="adjustForm" data-guard>
            <div class="modal-header"><h5 class="modal-title">Sesuaikan Stok Batch</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" name="medicine_batch_id">
                <div class="mb-3">
                    <label class="form-label">Batch</label>
                    <input type="text" class="form-control" id="adjustBatchLabel" disabled>
                </div>
                <div class="mb-3">
                    <label class="form-label form-label-required">Jenis Penyesuaian</label>
                    <select name="adjustment_type" class="form-select" required>
                        <option value="ADD">Tambah (ADD)</option>
                        <option value="REMOVE">Kurangi (REMOVE)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label form-label-required">Jumlah</label>
                    <input type="number" name="quantity" class="form-control" min="1" required>
                </div>
                <div class="mb-3">
                    <label class="form-label form-label-required">Alasan</label>
                    <textarea name="reason" class="form-control" rows="2" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" data-loading-text="Menyimpan...">Simpan</button>
            </div>
        </form>
    </div></div>
</div>

<div class="modal fade" id="transferModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form id="transferForm" data-guard>
            <div class="modal-header"><h5 class="modal-title">Transfer Stok Batch</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" name="medicine_batch_id">
                <div class="mb-3">
                    <label class="form-label">Batch</label>
                    <input type="text" class="form-control" id="transferBatchLabel" disabled>
                </div>
                <div class="mb-3">
                    <label class="form-label form-label-required">Cabang Tujuan</label>
                    <select name="to_branch_id" id="transferToBranch" class="form-select" required>
                        <?php foreach ($branches as $branch): ?>
                        <option value="<?= e($branch['id']) ?>"><?= e($branch['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label form-label-required">Jumlah</label>
                    <input type="number" name="quantity" class="form-control" min="1" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" data-loading-text="Memproses...">Transfer</button>
            </div>
        </form>
    </div></div>
</div>

<script>window.MEDICINE_ID = <?= (int) $medicine['id'] ?>;</script>
<script src="<?= asset('js/medicine-batches.js') ?>"></script>
<script src="<?= asset('js/inventory.js') ?>"></script>
