<?php \App\Core\View::extend('layouts.app'); ?>

<div class="breadcrumb-custom"><a href="/dashboard">Dashboard</a> / Peran & Akses</div>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
    <div>
        <h1 class="page-title mb-1">Peran & Akses</h1>
        <p class="text-muted-custom mb-0">Kelola peran (role) dan izin akses untuk setiap peran.</p>
    </div>
    <?php if (can('role.create')): ?>
    <a href="/roles/create" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah Peran</a>
    <?php endif; ?>
</div>

<div class="surface-card p-3">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Nama Peran</th><th>Deskripsi</th><th class="text-end">Jumlah Izin</th><th class="text-end">Jumlah Pengguna</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($roles as $role): ?>
                <tr>
                    <td>
                        <?= e($role['name']) ?>
                        <?php if ($role['is_system']): ?><span class="badge-status badge-muted ms-1" style="font-size:.65rem;">Sistem</span><?php endif; ?>
                    </td>
                    <td><?= e($role['description'] ?? '-') ?></td>
                    <td class="text-end"><?= (int) $role['permission_count'] ?></td>
                    <td class="text-end"><?= (int) $role['user_count'] ?></td>
                    <td class="text-end">
                        <?php if (can('role.update')): ?><a href="/roles/<?= e($role['id']) ?>/edit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a><?php endif; ?>
                        <?php if (can('role.delete') && !$role['is_system']): ?>
                        <form action="/roles/<?= e($role['id']) ?>/delete" method="POST" class="d-inline" onsubmit="return confirm('Hapus peran ini?');">
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
</div>
