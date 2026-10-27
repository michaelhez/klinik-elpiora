<?php \App\Core\View::extend('layouts.app'); ?>

<div class="breadcrumb-custom"><a href="/dashboard">Dashboard</a> / <a href="/purchase-orders">Purchase Order</a> / Buat Baru</div>
<h1 class="page-title mb-4">Buat Purchase Order</h1>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="surface-card p-4">
            <h2 class="section-title mb-3">Informasi Pemesanan</h2>
            <div class="mb-3">
                <label class="form-label form-label-required">Pemasok</label>
                <select id="poSupplier" class="form-select" required>
                    <option value="">-- Pilih Pemasok --</option>
                    <?php foreach ($suppliers as $supplier): ?>
                    <option value="<?= e($supplier['id']) ?>"><?= e($supplier['name']) ?> (<?= e($supplier['code']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label form-label-required">Cabang Penerima</label>
                <select id="poBranch" class="form-select" required>
                    <?php foreach ($branches as $branch): ?>
                    <option value="<?= e($branch['id']) ?>"><?= e($branch['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="row g-2">
                <div class="col-6 mb-3">
                    <label class="form-label">Tanggal Pesan</label>
                    <input type="date" id="poOrderDate" class="form-control" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-6 mb-3">
                    <label class="form-label">Estimasi Tiba</label>
                    <input type="date" id="poExpectedDate" class="form-control">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Catatan</label>
                <textarea id="poNotes" class="form-control" rows="2"></textarea>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="surface-card p-4">
            <h2 class="section-title mb-3">Item Obat</h2>
            <div class="position-relative mb-2">
                <input type="text" id="poMedicineSearch" class="form-control" placeholder="Cari obat berdasarkan nama/kode...">
                <div id="poMedicineResults" class="list-group position-absolute w-100 shadow-sm" style="z-index:20; display:none; max-height:220px; overflow-y:auto;"></div>
            </div>
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="form-label">Jumlah</label>
                    <input type="number" id="poQuantity" class="form-control" min="1" value="1">
                </div>
                <div class="col-6">
                    <label class="form-label">Harga Beli</label>
                    <input type="number" id="poPurchasePrice" class="form-control" min="0" step="100">
                </div>
            </div>
            <button type="button" id="poAddItemBtn" class="btn btn-outline-primary btn-sm mb-3"><i class="bi bi-plus-lg me-1"></i>Tambah Item</button>

            <div id="poItemList" class="mb-3"></div>
            <div class="text-end fw-semibold" id="poItemTotal">Total: Rp 0</div>

            <hr>
            <div class="text-end">
                <button type="button" id="poSubmitBtn" class="btn btn-primary"><i class="bi bi-check2 me-1"></i>Simpan Purchase Order</button>
            </div>
        </div>
    </div>
</div>

<script src="<?= asset('js/purchase-order-builder.js') ?>"></script>
