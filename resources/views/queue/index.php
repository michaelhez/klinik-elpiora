<?php \App\Core\View::extend('layouts.app'); ?>

<div class="breadcrumb-custom"><a href="/dashboard">Dashboard</a> / Antrian</div>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
    <div>
        <h1 class="page-title mb-1">Manajemen Antrian</h1>
        <p class="text-muted-custom mb-0">Panggil, panggil ulang, atau selesaikan antrian pasien hari ini.</p>
    </div>
    <a href="/queue/display" target="_blank" class="btn btn-outline-secondary"><i class="bi bi-display me-1"></i>Buka Papan Antrian</a>
</div>

<div class="surface-card p-3 mb-3">
    <form method="GET" action="/queue" class="row g-2 align-items-end">
        <div class="col-12 col-md-4">
            <label class="form-label">Poliklinik</label>
            <select name="polyclinic_id" class="form-select" onchange="this.form.submit()">
                <option value="">Semua Poliklinik</option>
                <?php foreach ($polyclinics as $poly): ?>
                <option value="<?= e($poly['id']) ?>" <?= (string) $selectedPolyclinic === (string) $poly['id'] ? 'selected' : '' ?>><?= e($poly['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>
</div>

<div class="surface-card p-3">
    <?php if ($queues === []): ?>
        <div class="empty-state">
            <i class="bi bi-hourglass-split d-block"></i>
            <h6>Belum ada antrian hari ini</h6>
        </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table align-middle" id="queueTable">
            <thead><tr><th>No. Antrian</th><th>Pasien</th><th>Poliklinik</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($queues as $q): ?>
                <tr data-queue-id="<?= e($q['id']) ?>">
                    <td class="fw-semibold fs-5"><?= e($q['queue_number']) ?></td>
                    <td><?= e($q['patient_name']) ?></td>
                    <td><?= e($q['polyclinic_name']) ?></td>
                    <td>
                        <?php
                        $badge = match ($q['status']) {
                            'WAITING' => 'badge-muted', 'CALLED' => 'badge-warning', 'IN_SERVICE' => 'badge-info',
                            'COMPLETED' => 'badge-success', default => 'badge-danger',
                        };
                        $labels = ['WAITING' => 'Menunggu', 'CALLED' => 'Dipanggil', 'IN_SERVICE' => 'Dilayani', 'COMPLETED' => 'Selesai', 'SKIPPED' => 'Dilewati', 'CANCELLED' => 'Dibatalkan'];
                        ?>
                        <span class="badge-status <?= $badge ?> queue-status"><?= $labels[$q['status']] ?? $q['status'] ?></span>
                    </td>
                    <td class="text-end queue-actions">
                        <?php if (can('queue.call')): ?><button class="btn btn-sm btn-primary" data-action="call" <?= $q['status'] !== 'WAITING' ? 'disabled' : '' ?>>Panggil</button><?php endif; ?>
                        <?php if (can('queue.recall')): ?><button class="btn btn-sm btn-outline-secondary" data-action="recall" <?= $q['status'] !== 'CALLED' ? 'disabled' : '' ?>>Ulangi</button><?php endif; ?>
                        <?php if (can('queue.complete')): ?><button class="btn btn-sm btn-outline-success" data-action="complete" <?= !in_array($q['status'], ['CALLED', 'IN_SERVICE'], true) ? 'disabled' : '' ?>>Selesai</button><?php endif; ?>
                        <?php if (can('queue.skip')): ?><button class="btn btn-sm btn-outline-danger" data-action="skip" <?= !in_array($q['status'], ['WAITING', 'CALLED'], true) ? 'disabled' : '' ?>>Lewati</button><?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<script src="<?= asset('js/queue.js') ?>"></script>
