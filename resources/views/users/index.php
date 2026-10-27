<?php \App\Core\View::extend('layouts.app'); ?>

<div class="breadcrumb-custom"><a href="/dashboard">Dashboard</a> / Pengguna</div>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
    <div>
        <h1 class="page-title mb-1">Pengguna</h1>
        <p class="text-muted-custom mb-0">Kelola akun pengguna sistem dan peran akses mereka.</p>
    </div>
    <?php if (can('user.create')): ?>
    <a href="/users/create" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah Pengguna</a>
    <?php endif; ?>
</div>

<div class="surface-card p-3 mb-3">
    <form method="GET" action="/users" class="row g-2 align-items-end">
        <div class="col-12 col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Nama atau email..." value="<?= e($filters['search']) ?>">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
        </div>
    </form>
</div>

<div class="surface-card p-3">
    <?php if ($users === []): ?>
        <div class="empty-state"><i class="bi bi-people d-block"></i><h6>Tidak ada pengguna</h6></div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Nama</th><th>Email</th><th>Peran</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($users as $row): ?>
                <tr>
                    <td><?= e($row['name']) ?></td>
                    <td><?= e($row['email']) ?></td>
                    <td><?= e($row['role_names'] ?? '-') ?></td>
                    <td><span class="badge-status <?= $row['is_active'] ? 'badge-success' : 'badge-muted' ?>"><?= $row['is_active'] ? 'Aktif' : 'Nonaktif' ?></span></td>
                    <td class="text-end">
                        <?php if (can('user.update')): ?><a href="/users/<?= e($row['id']) ?>/edit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a><?php endif; ?>
                        <?php if (can('user.delete')): ?>
                        <form action="/users/<?= e($row['id']) ?>/delete" method="POST" class="d-inline" onsubmit="return confirm('Hapus pengguna ini?');">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
