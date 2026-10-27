<?php
\App\Core\View::extend('layouts.app');
$labels = ['REQUESTED' => 'Diminta', 'SAMPLE_COLLECTED' => 'Sampel Diambil', 'PROCESSING' => 'Diproses', 'COMPLETED' => 'Selesai', 'VALIDATED' => 'Tervalidasi', 'CANCELLED' => 'Dibatalkan'];
$badge = match ($labRequest['status']) {
    'VALIDATED' => 'badge-success', 'COMPLETED' => 'badge-info', 'CANCELLED' => 'badge-danger', default => 'badge-warning',
};
$errors = \App\Core\Session::getFlash('_errors') ?? [];
?>

<div class="breadcrumb-custom"><a href="/dashboard">Dashboard</a> / <a href="/lab-requests">Laboratorium</a> / <?= e($labRequest['request_number']) ?></div>

<div class="surface-card p-4 mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
        <div>
            <h1 class="page-title mb-1"><?= e($labRequest['patient_name']) ?></h1>
            <div class="text-muted-custom"><?= e($labRequest['medical_record_number']) ?> &bull; Dokter: <?= e($labRequest['doctor_name']) ?></div>
            <div class="text-caption mt-1">No. Permintaan: <?= e($labRequest['request_number']) ?> &bull; Rekam Medis: <a href="/medical-records/<?= e($labRequest['medical_record_id']) ?>"><?= e($labRequest['record_number']) ?></a></div>
        </div>
        <div class="text-md-end">
            <span class="badge-status <?= $badge ?> mb-2 d-inline-block"><?= $labels[$labRequest['status']] ?? $labRequest['status'] ?></span>
            <div class="d-flex gap-2 justify-content-md-end">
                <?php if ($labRequest['status'] === 'COMPLETED' && can('laboratory.validate')): ?>
                <form action="/lab-requests/<?= e($labRequest['id']) ?>/validate" method="POST" onsubmit="return confirm('Validasi seluruh hasil pemeriksaan ini?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-check-circle me-1"></i>Validasi Hasil</button>
                </form>
                <?php endif; ?>
                <?php if (!in_array($labRequest['status'], ['COMPLETED', 'VALIDATED', 'CANCELLED'], true) && can('laboratory.process')): ?>
                <form action="/lab-requests/<?= e($labRequest['id']) ?>/cancel" method="POST" onsubmit="return confirm('Batalkan permintaan lab ini?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-danger btn-sm">Batalkan</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if ($labRequest['status'] === 'REQUESTED' && can('laboratory.process')): ?>
<div class="surface-card p-4 mb-4">
    <h2 class="section-title mb-3">Pengambilan Sampel</h2>
    <form action="/lab-requests/<?= e($labRequest['id']) ?>/collect-sample" method="POST" data-guard class="row g-3">
        <?= csrf_field() ?>
        <div class="col-md-4">
            <label class="form-label form-label-required">Jenis Sampel</label>
            <input type="text" name="sample_type" class="form-control<?= isset($errors['sample_type']) ? ' is-invalid' : '' ?>" placeholder="Contoh: Darah Vena" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Catatan</label>
            <input type="text" name="notes" class="form-control">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100" data-loading-text="Menyimpan...">Catat Sampel</button>
        </div>
    </form>
</div>
<?php elseif ($labRequest['samples'] !== []): ?>
<div class="surface-card p-4 mb-4">
    <h2 class="section-title mb-3">Sampel</h2>
    <?php foreach ($labRequest['samples'] as $s): ?>
    <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
        <div>
            <div class="card-title-sm"><?= e($s['sample_number']) ?> &bull; <?= e($s['sample_type']) ?></div>
            <div class="text-caption">Diambil: <?= format_date($s['collected_at'], 'd M Y H:i') ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="surface-card p-4">
    <h2 class="section-title mb-3">Hasil Pemeriksaan</h2>
    <?php foreach ($labRequest['items'] as $item): ?>
    <div class="border rounded-3 p-3 mb-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="card-title-sm"><?= e($item['test_name']) ?> <span class="text-caption">(<?= e($item['test_code']) ?>)</span></div>
            <?php if (!empty($item['validated_at'])): ?>
                <span class="badge-status badge-success">Tervalidasi</span>
            <?php elseif (!empty($item['performed_at'])): ?>
                <span class="badge-status badge-info">Selesai Diproses</span>
            <?php else: ?>
                <span class="badge-status badge-muted">Belum Diproses</span>
            <?php endif; ?>
        </div>

        <?php if ($item['parameters'] === []): ?>
            <p class="text-muted-custom small mb-0">Jenis pemeriksaan ini belum memiliki parameter. Tambahkan parameter melalui <a href="/settings/lab-tests/<?= e($item['lab_test_id']) ?>">Data Laboratorium</a>.</p>
        <?php elseif (in_array($labRequest['status'], ['SAMPLE_COLLECTED', 'PROCESSING'], true) && empty($item['validated_at']) && can('laboratory.process')): ?>
            <form action="/lab-requests/<?= e($labRequest['id']) ?>/items/<?= e($item['id']) ?>/result" method="POST" data-guard>
                <?= csrf_field() ?>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead><tr><th>Parameter</th><th>Nilai</th><th>Satuan</th><th>Nilai Rujukan</th></tr></thead>
                        <tbody>
                        <?php foreach ($item['parameters'] as $param): ?>
                            <?php
                            $existingValue = '';
                            foreach ($item['result_values'] as $rv) {
                                if ($rv['lab_parameter_id'] === $param['id']) { $existingValue = $rv['value']; }
                            }
                            ?>
                            <tr>
                                <td><?= e($param['name']) ?></td>
                                <td style="max-width:140px;">
                                    <input type="text" name="values[<?= e($param['id']) ?>]" class="form-control form-control-sm" value="<?= e($existingValue) ?>">
                                </td>
                                <td><?= e($param['unit'] ?? '-') ?></td>
                                <td class="text-caption">
                                    <?php if ($param['value_type'] === 'NUMERIC'): ?>
                                        <?= e($param['reference_min'] ?? '-') ?> - <?= e($param['reference_max'] ?? '-') ?>
                                    <?php else: ?>
                                        <?= e($param['reference_text'] ?? '-') ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <button type="submit" class="btn btn-primary btn-sm" data-loading-text="Menyimpan...">Simpan Hasil</button>
            </form>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead><tr><th>Parameter</th><th>Nilai</th><th>Satuan</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($item['result_values'] as $rv): ?>
                        <?php
                        $flagBadge = match ($rv['flag']) {
                            'CRITICAL' => 'badge-danger', 'HIGH' => 'badge-warning', 'LOW' => 'badge-warning', 'NORMAL' => 'badge-success', default => 'badge-muted',
                        };
                        $flagLabel = ['LOW' => 'Rendah', 'NORMAL' => 'Normal', 'HIGH' => 'Tinggi', 'CRITICAL' => 'Kritis'][$rv['flag']] ?? '-';
                        ?>
                        <tr>
                            <td><?= e($rv['parameter_name']) ?></td>
                            <td class="fw-semibold"><?= e($rv['value']) ?></td>
                            <td><?= e($rv['unit'] ?? '-') ?></td>
                            <td><span class="badge-status <?= $flagBadge ?>"><?= $flagLabel ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($item['result_values'] === []): ?>
                        <tr><td colspan="4" class="text-muted-custom small">Belum ada hasil yang diinput.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
