<?php \App\Core\View::extend('layouts.app'); ?>

<div class="breadcrumb-custom"><a href="/dashboard">Dashboard</a> / Dokter</div>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
    <div>
        <h1 class="page-title mb-1">Dokter</h1>
        <p class="text-muted-custom mb-0">Kelola data dokter dan jadwal praktik.</p>
    </div>
    <?php if (can('doctor.create')): ?>
    <a href="/doctors/create" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah Dokter</a>
    <?php endif; ?>
</div>

<div class="surface-card p-3 mb-3">
    <form method="GET" action="/doctors" class="row g-2 align-items-end">
        <div class="col-12 col-md-5">
            <label class="form-label">Cari</label>
            <input type="text" name="search" class="form-control" placeholder="Nama atau nomor dokter" value="<?= e($filters['search']) ?>">
        </div>
        <div class="col-6 col-md-4">
            <label class="form-label">Spesialisasi</label>
            <select name="specialization_id" class="form-select">
                <option value="">Semua Spesialisasi</option>
                <?php foreach ($specializations as $spec): ?>
                <option value="<?= e($spec['id']) ?>" <?= (string) $filters['specialization_id'] === (string) $spec['id'] ? 'selected' : '' ?>><?= e($spec['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">Semua</option>
                <option value="ACTIVE" <?= $filters['status'] === 'ACTIVE' ? 'selected' : '' ?>>Aktif</option>
                <option value="INACTIVE" <?= $filters['status'] === 'INACTIVE' ? 'selected' : '' ?>>Nonaktif</option>
            </select>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="bi bi-funnel me-1"></i>Terapkan Filter</button>
            <a href="/doctors" class="btn btn-link btn-sm">Reset</a>
        </div>
    </form>
</div>

<div class="row g-3">
    <?php if ($doctors === []): ?>
        <div class="col-12">
            <div class="surface-card empty-state">
                <i class="bi bi-heart-pulse d-block"></i>
                <h6>Tidak ada data dokter</h6>
                <?php if (can('doctor.create')): ?><a href="/doctors/create" class="btn btn-primary btn-sm mt-2">Tambah Dokter</a><?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($doctors as $doc): ?>
        <div class="col-md-6 col-lg-4">
            <a href="/doctors/<?= e($doc['id']) ?>" class="text-decoration-none">
                <div class="surface-card p-3 h-100">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <?php if (!empty($doc['photo'])): ?>
                            <img src="<?= e(url('uploads/' . $doc['photo'])) ?>" class="rounded-circle" style="width:52px;height:52px;object-fit:cover;">
                        <?php else: ?>
                            <div class="avatar-circle" style="width:52px;height:52px;"><?= e(mb_strtoupper(mb_substr($doc['name'], 0, 1))) ?></div>
                        <?php endif; ?>
                        <div>
                            <div class="card-title-sm text-truncate" style="max-width:180px;"><?= e($doc['name']) ?></div>
                            <div class="text-caption"><?= e($doc['doctor_number']) ?></div>
                        </div>
                    </div>
                    <div class="text-caption mb-2"><i class="bi bi-building me-1"></i><?= e($doc['branch_name']) ?></div>
                    <span class="badge-status <?= $doc['status'] === 'ACTIVE' ? 'badge-success' : 'badge-muted' ?>"><?= $doc['status'] === 'ACTIVE' ? 'Aktif' : 'Nonaktif' ?></span>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php if ($doctors !== []): ?>
<div class="surface-card p-3 mt-1">
    <?php \App\Core\View::include('components.pagination', ['pagination' => $pagination, 'baseUrl' => '/doctors']); ?>
</div>
<?php endif; ?>
