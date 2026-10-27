<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

$canViewMedicalMaster = can('icd10.view') || can('medical_action_category.view') || can('medical_action.view') || can('lab_test.view');

$canViewOrganization = can('clinic.view') || can('branch.view') || can('department.view')
    || can('polyclinic.view') || can('room.view') || can('position.view') || can('specialization.view');
?>
<aside class="sidebar" id="appSidebar">
    <div class="sidebar-brand">
        <div class="sidebar-brand-icon"><i class="bi bi-heart-pulse-fill"></i></div>
        <div class="sidebar-brand-text">Clinic MS</div>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section-label">Menu Utama</div>
        <a href="/dashboard" class="sidebar-link<?= str_starts_with($currentPath, '/dashboard') ? ' active' : '' ?>">
            <i class="bi bi-grid-1x2-fill"></i>
            <span>Dashboard</span>
        </a>

        <div class="nav-section-label">Pelayanan Pasien</div>
        <?php if (can('patient.view')): ?>
        <a href="/patients" class="sidebar-link<?= str_starts_with($currentPath, '/patients') ? ' active' : '' ?>">
            <i class="bi bi-people"></i>
            <span>Pasien</span>
        </a>
        <?php endif; ?>
        <?php if (can('appointment.view')): ?>
        <a href="/appointments" class="sidebar-link<?= str_starts_with($currentPath, '/appointments') ? ' active' : '' ?>">
            <i class="bi bi-calendar-check"></i>
            <span>Janji Temu</span>
        </a>
        <?php endif; ?>
        <?php if (can('registration.view')): ?>
        <a href="/registrations" class="sidebar-link<?= str_starts_with($currentPath, '/registrations') ? ' active' : '' ?>">
            <i class="bi bi-clipboard-plus"></i>
            <span>Pendaftaran</span>
        </a>
        <?php endif; ?>
        <?php if (can('queue.view')): ?>
        <a href="/queue" class="sidebar-link<?= $currentPath === '/queue' ? ' active' : '' ?>">
            <i class="bi bi-hourglass-split"></i>
            <span>Antrian</span>
        </a>
        <?php endif; ?>

        <div class="nav-section-label">Master Data</div>
        <?php if (can('employee.view')): ?>
        <a href="/employees" class="sidebar-link<?= str_starts_with($currentPath, '/employees') ? ' active' : '' ?>">
            <i class="bi bi-people-fill"></i>
            <span>Karyawan</span>
        </a>
        <?php endif; ?>
        <?php if (can('doctor.view')): ?>
        <a href="/doctors" class="sidebar-link<?= str_starts_with($currentPath, '/doctors') ? ' active' : '' ?>">
            <i class="bi bi-heart-pulse"></i>
            <span>Dokter</span>
        </a>
        <?php endif; ?>
        <?php if ($canViewOrganization): ?>
        <a href="/settings/organization" class="sidebar-link<?= str_starts_with($currentPath, '/settings/organization') ? ' active' : '' ?>">
            <i class="bi bi-diagram-3-fill"></i>
            <span>Data Organisasi</span>
        </a>
        <?php endif; ?>

        <div class="nav-section-label">Medis</div>
        <?php if (can('medical_record.view')): ?>
        <a href="/medical-records" class="sidebar-link<?= str_starts_with($currentPath, '/medical-records') ? ' active' : '' ?>">
            <i class="bi bi-file-medical"></i>
            <span>Rekam Medis</span>
        </a>
        <?php endif; ?>
        <?php if ($canViewMedicalMaster): ?>
        <a href="/settings/medical" class="sidebar-link<?= str_starts_with($currentPath, '/settings/medical') ? ' active' : '' ?>">
            <i class="bi bi-clipboard2-pulse"></i>
            <span>Data Medis</span>
        </a>
        <?php endif; ?>
        <?php if (can('laboratory.view')): ?>
        <a href="/lab-requests" class="sidebar-link<?= str_starts_with($currentPath, '/lab-requests') ? ' active' : '' ?>">
            <i class="bi bi-flask"></i>
            <span>Laboratorium</span>
        </a>
        <?php endif; ?>

        <div class="nav-section-label">Farmasi & Inventori</div>
        <?php if (can('medicine.view')): ?>
        <a href="/medicines" class="sidebar-link<?= str_starts_with($currentPath, '/medicines') ? ' active' : '' ?>">
            <i class="bi bi-capsule"></i>
            <span>Farmasi & Obat</span>
        </a>
        <?php endif; ?>
        <?php if (can('prescription.view')): ?>
        <a href="/pharmacy" class="sidebar-link<?= str_starts_with($currentPath, '/pharmacy') || str_starts_with($currentPath, '/prescriptions') ? ' active' : '' ?>">
            <i class="bi bi-clipboard2-pulse-fill"></i>
            <span>Antrian Farmasi</span>
        </a>
        <?php endif; ?>
        <?php if (can('inventory.view')): ?>
        <a href="/inventory" class="sidebar-link<?= str_starts_with($currentPath, '/inventory') ? ' active' : '' ?>">
            <i class="bi bi-box-seam"></i>
            <span>Inventori</span>
        </a>
        <?php endif; ?>
        <?php if (can('supplier.view')): ?>
        <a href="/suppliers" class="sidebar-link<?= str_starts_with($currentPath, '/suppliers') ? ' active' : '' ?>">
            <i class="bi bi-truck"></i>
            <span>Pemasok</span>
        </a>
        <?php endif; ?>
        <?php if (can('purchase_order.view')): ?>
        <a href="/purchase-orders" class="sidebar-link<?= str_starts_with($currentPath, '/purchase-orders') ? ' active' : '' ?>">
            <i class="bi bi-cart-check"></i>
            <span>Purchase Order</span>
        </a>
        <?php endif; ?>
        <?php if (can('supplier_return.view')): ?>
        <a href="/supplier-returns" class="sidebar-link<?= str_starts_with($currentPath, '/supplier-returns') ? ' active' : '' ?>">
            <i class="bi bi-arrow-counterclockwise"></i>
            <span>Retur Pemasok</span>
        </a>
        <?php endif; ?>
        <?php if (can('medicine_category.view') || can('medicine_unit.view')): ?>
        <a href="/settings/pharmacy" class="sidebar-link<?= str_starts_with($currentPath, '/settings/pharmacy') ? ' active' : '' ?>">
            <i class="bi bi-gear"></i>
            <span>Data Farmasi</span>
        </a>
        <?php endif; ?>

        <div class="nav-section-label">Keuangan & Laporan</div>
        <?php if (can('billing.view')): ?>
        <a href="/billing" class="sidebar-link<?= str_starts_with($currentPath, '/billing') ? ' active' : '' ?>">
            <i class="bi bi-receipt"></i>
            <span>Billing</span>
        </a>
        <?php endif; ?>
        <?php if (can('insurance.view') || can('corporate.view')): ?>
        <a href="/settings/finance" class="sidebar-link<?= str_starts_with($currentPath, '/settings/finance') ? ' active' : '' ?>">
            <i class="bi bi-shield-check"></i>
            <span>Data Keuangan</span>
        </a>
        <?php endif; ?>
        <?php if (can('report.view')): ?>
        <a href="/reports" class="sidebar-link<?= str_starts_with($currentPath, '/reports') ? ' active' : '' ?>">
            <i class="bi bi-bar-chart-line"></i>
            <span>Laporan</span>
        </a>
        <?php endif; ?>

        <div class="nav-section-label">Sistem</div>
        <?php if (can('user.view')): ?>
        <a href="/users" class="sidebar-link<?= str_starts_with($currentPath, '/users') ? ' active' : '' ?>">
            <i class="bi bi-people-fill"></i>
            <span>Pengguna</span>
        </a>
        <?php endif; ?>
        <?php if (can('role.view')): ?>
        <a href="/roles" class="sidebar-link<?= str_starts_with($currentPath, '/roles') ? ' active' : '' ?>">
            <i class="bi bi-shield-lock"></i>
            <span>Peran & Akses</span>
        </a>
        <?php endif; ?>
        <?php if (can('audit.view')): ?>
        <a href="/audit-logs" class="sidebar-link<?= str_starts_with($currentPath, '/audit-logs') ? ' active' : '' ?>">
            <i class="bi bi-journal-text"></i>
            <span>Audit Log</span>
        </a>
        <?php endif; ?>
        <?php if (can('setting.view')): ?>
        <a href="/settings/system" class="sidebar-link<?= str_starts_with($currentPath, '/settings/system') ? ' active' : '' ?>">
            <i class="bi bi-gear-fill"></i>
            <span>Pengaturan Sistem</span>
        </a>
        <?php endif; ?>
        <?php if (can('backup.view')): ?>
        <a href="/settings/backup" class="sidebar-link<?= str_starts_with($currentPath, '/settings/backup') ? ' active' : '' ?>">
            <i class="bi bi-database"></i>
            <span>Backup Database</span>
        </a>
        <?php endif; ?>
    </nav>
</aside>
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>
