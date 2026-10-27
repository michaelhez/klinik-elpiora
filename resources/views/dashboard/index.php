<?php \App\Core\View::extend('layouts.app'); ?>

<div class="breadcrumb-custom">
    <a href="/dashboard">Dashboard</a>
</div>
<h1 class="page-title">Dashboard</h1>
<p class="text-muted-custom mb-4">Ringkasan aktivitas sistem klinik hari ini.</p>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="kpi-card">
            <div>
                <div class="text-caption mb-1">Total Pengguna</div>
                <div class="kpi-value"><?= number_format($totalUsers) ?></div>
                <div class="kpi-trend up mt-1"><i class="bi bi-arrow-up-short"></i>Aktif</div>
            </div>
            <div class="kpi-icon" style="background:var(--color-primary-soft);color:var(--color-primary);">
                <i class="bi bi-people-fill"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="kpi-card">
            <div>
                <div class="text-caption mb-1">Total Peran</div>
                <div class="kpi-value"><?= number_format($totalRoles) ?></div>
                <div class="kpi-trend up mt-1"><i class="bi bi-shield-check"></i>RBAC Aktif</div>
            </div>
            <div class="kpi-icon" style="background:var(--color-success-soft);color:var(--color-success);">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="kpi-card">
            <div>
                <div class="text-caption mb-1">Total Izin Akses</div>
                <div class="kpi-value"><?= number_format($totalPermissions) ?></div>
                <div class="kpi-trend up mt-1"><i class="bi bi-key-fill"></i>Terdaftar</div>
            </div>
            <div class="kpi-icon" style="background:var(--color-warning-soft);color:var(--color-warning);">
                <i class="bi bi-key-fill"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="kpi-card">
            <div>
                <div class="text-caption mb-1">Aktivitas Hari Ini</div>
                <div class="kpi-value"><?= number_format($todayActivity) ?></div>
                <div class="kpi-trend up mt-1"><i class="bi bi-activity"></i>Tercatat</div>
            </div>
            <div class="kpi-icon" style="background:#F1F5F9;color:var(--color-secondary);">
                <i class="bi bi-journal-text"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="surface-card p-4 h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h2 class="section-title mb-0">Tren Aktivitas Sistem</h2>
            </div>
            <canvas id="activityChart" height="220"></canvas>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="surface-card p-4 h-100">
            <h2 class="section-title mb-3">Aktivitas Terbaru</h2>

            <?php if ($recentActivity === []): ?>
                <div class="empty-state py-4">
                    <i class="bi bi-inbox d-block"></i>
                    <h6>Belum ada aktivitas</h6>
                    <p class="mb-0 small">Aktivitas sistem akan muncul di sini.</p>
                </div>
            <?php else: ?>
                <div class="timeline">
                    <?php foreach ($recentActivity as $log): ?>
                        <div class="timeline-item">
                            <span class="timeline-dot"></span>
                            <div class="text-caption"><?= format_date($log['created_at'], 'd M Y - H:i') ?></div>
                            <div class="card-title-sm"><?= e($log['action']) ?></div>
                            <div class="text-muted-custom small"><?= e(ucwords(str_replace('_', ' ', $log['module']))) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('activityChart');
    if (!ctx || typeof Chart === 'undefined') return;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
            datasets: [{
                label: 'Aktivitas',
                data: [0, 0, 0, 0, 0, 0, <?= (int) $todayActivity ?>],
                borderColor: '#2563EB',
                backgroundColor: 'rgba(37, 99, 235, 0.08)',
                tension: 0.35,
                fill: true,
                pointRadius: 3,
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });
});
</script>
