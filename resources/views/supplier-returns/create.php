<?php \App\Core\View::extend('layouts.app'); ?>

<div class="breadcrumb-custom"><a href="/dashboard">Dashboard</a> / <a href="/supplier-returns">Retur Pemasok</a> / Buat Baru</div>
<h1 class="page-title mb-4">Buat Retur Pemasok</h1>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="surface-card p-4">
            <h2 class="section-title mb-3">Informasi Retur</h2>
            <div class="mb-3">
                <label class="form-label form-label-required">Pemasok</label>
                <select id="srSupplier" class="form-select" required>
                    <option value="">-- Pilih Pemasok --</option>
                    <?php foreach ($suppliers as $supplier): ?>
                    <option value="<?= e($supplier['id']) ?>"><?= e($supplier['name']) ?> (<?= e($supplier['code']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label form-label-required">Alasan Retur</label>
                <textarea id="srReason" class="form-control" rows="3" placeholder="Contoh: obat rusak saat pengiriman" required></textarea>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="surface-card p-4">
            <h2 class="section-title mb-3">Item Obat yang Diretur</h2>
            <div class="position-relative mb-2">
                <input type="text" id="srMedicineSearch" class="form-control" placeholder="Cari obat berdasarkan nama/kode...">
                <div id="srMedicineResults" class="list-group position-absolute w-100 shadow-sm" style="z-index:20; display:none; max-height:220px; overflow-y:auto;"></div>
            </div>
            <div class="mb-3">
                <label class="form-label">Batch</label>
                <select id="srBatchSelect" class="form-select" disabled>
                    <option value="">-- Pilih obat terlebih dahulu --</option>
                </select>
            </div>
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="form-label">Jumlah</label>
                    <input type="number" id="srQuantity" class="form-control" min="1" value="1">
                </div>
                <div class="col-6">
                    <label class="form-label">Catatan Item</label>
                    <input type="text" id="srItemNotes" class="form-control">
                </div>
            </div>
            <button type="button" id="srAddItemBtn" class="btn btn-outline-primary btn-sm mb-3"><i class="bi bi-plus-lg me-1"></i>Tambah Item</button>

            <div id="srItemList" class="mb-3"></div>

            <hr>
            <div class="text-end">
                <button type="button" id="srSubmitBtn" class="btn btn-primary"><i class="bi bi-check2 me-1"></i>Simpan Retur</button>
            </div>
        </div>
    </div>
</div>

<script src="<?= asset('js/supplier-return-builder.js') ?>"></script>
