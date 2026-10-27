<?php
$user = auth_user() ?? ['name' => 'Guest', 'email' => ''];
$roles = App\Core\Session::get('auth_roles', []);
$roleLabel = $roles !== [] ? ucwords(str_replace('_', ' ', $roles[0])) : '-';
$initials = mb_strtoupper(mb_substr((string) ($user['name'] ?? 'U'), 0, 1));
?>
<header class="topbar">
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="topbar-icon-btn d-lg-none" data-sidebar-toggle aria-label="Buka menu">
            <i class="bi bi-list fs-4"></i>
        </button>
        <div class="topbar-search d-none d-md-block">
            <i class="bi bi-search"></i>
            <input type="text" placeholder="Cari pasien, dokter, invoice... (segera hadir)" disabled
                   title="Fitur pencarian global akan tersedia pada fase berikutnya">
        </div>
    </div>

    <div class="d-flex align-items-center gap-1">
        <button type="button" class="topbar-icon-btn" aria-label="Notifikasi" title="Notifikasi">
            <i class="bi bi-bell fs-5"></i>
        </button>

        <div class="dropdown">
            <div class="user-chip" data-bs-toggle="dropdown" aria-expanded="false" role="button">
                <div class="avatar-circle"><?= e($initials) ?></div>
                <div class="d-none d-sm-block text-start">
                    <div style="font-size:.8125rem;font-weight:600;line-height:1.1;"><?= e($user['name']) ?></div>
                    <div class="text-caption" style="line-height:1.1;"><?= e($roleLabel) ?></div>
                </div>
                <i class="bi bi-chevron-down text-muted-custom" style="font-size:.7rem;"></i>
            </div>
            <ul class="dropdown-menu dropdown-menu-end mt-2 shadow-sm">
                <li><h6 class="dropdown-header"><?= e($user['email'] ?? '') ?></h6></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form action="/logout" method="POST">
                        <?= csrf_field() ?>
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
