<?php \App\Core\View::extend('layouts.app'); ?>

<div class="breadcrumb-custom"><a href="/dashboard">Dashboard</a> / <a href="/settings/lab-tests">Jenis Pemeriksaan Lab</a> / <?= e($test['name']) ?></div>

<div class="surface-card p-4 mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
        <div>
            <h1 class="page-title mb-1"><?= e($test['name']) ?></h1>
            <div class="text-muted-custom"><?= e($test['code']) ?> &bull; <?= e($test['category'] ?? '-') ?> &bull; Sampel: <?= e($test['sample_type'] ?? '-') ?></div>
            <div class="text-caption mt-1"><?= format_currency($test['price']) ?></div>
        </div>
        <div class="d-flex align-items-start gap-2">
            <span class="badge-status <?= $test['status'] === 'ACTIVE' ? 'badge-success' : 'badge-muted' ?> align-self-center"><?= $test['status'] === 'ACTIVE' ? 'Aktif' : 'Nonaktif' ?></span>
            <?php if (can('lab_test.update')): ?><a href="/settings/lab-tests/<?= e($test['id']) ?>/edit" class="btn btn-outline-secondary btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a><?php endif; ?>
        </div>
    </div>
</div>

<div class="surface-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="section-title mb-0">Parameter Pemeriksaan</h2>
        <?php if (can('lab_test.update')): ?>
        <button type="button" class="btn btn-primary btn-sm" onclick="LabParams.openModal()"><i class="bi bi-plus-lg me-1"></i>Tambah Parameter</button>
        <?php endif; ?>
    </div>

    <?php if ($parameters === []): ?>
        <div class="empty-state"><i class="bi bi-list-columns d-block"></i><h6>Belum ada parameter</h6><p class="small mb-0">Tambahkan parameter yang diukur pada pemeriksaan ini beserta nilai rujukannya.</p></div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table align-middle" id="paramTable">
            <thead><tr><th>Nama Parameter</th><th>Satuan</th><th>Tipe Nilai</th><th>Nilai Rujukan</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($parameters as $p): ?>
                <tr data-id="<?= e($p['id']) ?>">
                    <td><?= e($p['name']) ?></td>
                    <td><?= e($p['unit'] ?? '-') ?></td>
                    <td><?= $p['value_type'] === 'NUMERIC' ? 'Numerik' : 'Teks' ?></td>
                    <td>
                        <?php if ($p['value_type'] === 'NUMERIC' && ($p['reference_min'] !== null || $p['reference_max'] !== null)): ?>
                            <?= e($p['reference_min'] ?? '-') ?> - <?= e($p['reference_max'] ?? '-') ?>
                        <?php else: ?>
                            <?= e($p['reference_text'] ?? '-') ?>
                        <?php endif; ?>
                    </td>
                    <td class="text-end">
                        <?php if (can('lab_test.update')): ?><button class="btn btn-sm btn-outline-secondary" onclick='LabParams.openModal(<?= json_encode($p) ?>)'><i class="bi bi-pencil"></i></button><?php endif; ?>
                        <?php if (can('lab_test.delete')): ?><button class="btn btn-sm btn-outline-danger" onclick="LabParams.remove(<?= e($p['id']) ?>)"><i class="bi bi-trash"></i></button><?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="paramModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form id="paramForm" data-guard>
            <div class="modal-header"><h5 class="modal-title" id="paramModalTitle">Tambah Parameter</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label form-label-required">Nama Parameter</label>
                    <input type="text" name="name" id="p_name" class="form-control" required>
                </div>
                <div class="row g-2">
                    <div class="col-6 mb-3">
                        <label class="form-label">Satuan</label>
                        <input type="text" name="unit" id="p_unit" class="form-control" placeholder="Contoh: g/dL">
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label form-label-required">Tipe Nilai</label>
                        <select name="value_type" id="p_value_type" class="form-select" onchange="LabParams.toggleReferenceFields()">
                            <option value="NUMERIC">Numerik</option>
                            <option value="TEXT">Teks</option>
                        </select>
                    </div>
                </div>
                <div class="row g-2" id="p_numeric_fields">
                    <div class="col-6 mb-3">
                        <label class="form-label">Nilai Rujukan Min</label>
                        <input type="number" step="0.01" name="reference_min" id="p_reference_min" class="form-control">
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">Nilai Rujukan Max</label>
                        <input type="number" step="0.01" name="reference_max" id="p_reference_max" class="form-control">
                    </div>
                </div>
                <div class="mb-1" id="p_text_field" style="display:none;">
                    <label class="form-label">Nilai Rujukan (Teks)</label>
                    <input type="text" name="reference_text" id="p_reference_text" class="form-control" placeholder="Contoh: Negatif">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" data-loading-text="Menyimpan...">Simpan</button>
            </div>
        </form>
    </div></div>
</div>

<script>window.LAB_TEST_ID = <?= (int) $test['id'] ?>;</script>
<script src="<?= asset('js/lab-test-parameters.js') ?>"></script>
