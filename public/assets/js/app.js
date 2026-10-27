(function () {
    'use strict';

    window.Clinic = window.Clinic || {};

    /* ---------- CSRF-aware fetch wrapper ---------- */
    function csrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    async function request(url, options = {}) {
        const headers = Object.assign(
            {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken(),
            },
            options.headers || {}
        );

        if (options.body && !(options.body instanceof FormData)) {
            headers['Content-Type'] = 'application/json';
        }

        const response = await fetch(url, Object.assign({}, options, { headers }));
        let payload = null;

        try {
            payload = await response.json();
        } catch (e) {
            payload = { success: response.ok, message: response.statusText, data: {} };
        }

        if (!response.ok) {
            const error = new Error(payload.message || 'Terjadi kesalahan.');
            error.payload = payload;
            error.status = response.status;
            throw error;
        }

        return payload;
    }

    Clinic.request = request;

    /* ---------- Button loading state ---------- */
    Clinic.setButtonLoading = function (button, loadingText) {
        if (!button) return;
        button.dataset.originalText = button.dataset.originalText || button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>' + (loadingText || 'Memproses...');
    };

    Clinic.resetButton = function (button) {
        if (!button) return;
        button.disabled = false;
        if (button.dataset.originalText) {
            button.innerHTML = button.dataset.originalText;
        }
    };

    /* ---------- Toast helpers (SweetAlert2) ---------- */
    Clinic.toastSuccess = function (message) {
        if (typeof Swal === 'undefined') return;
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: message,
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true,
        });
    };

    Clinic.toastError = function (message) {
        if (typeof Swal === 'undefined') return;
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: message,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
    };

    Clinic.confirmDialog = function (options) {
        if (typeof Swal === 'undefined') {
            return Promise.resolve(window.confirm(options.text || 'Apakah Anda yakin?'));
        }

        return Swal.fire({
            title: options.title || 'Apakah Anda yakin?',
            text: options.text || '',
            icon: options.icon || 'warning',
            showCancelButton: true,
            confirmButtonColor: '#DC2626',
            cancelButtonColor: '#64748B',
            confirmButtonText: options.confirmText || 'Ya, lanjutkan',
            cancelButtonText: options.cancelText || 'Batal',
        }).then((result) => result.isConfirmed);
    };

    /* ---------- Sidebar toggle (mobile drawer + desktop collapse) ---------- */
    function initSidebar() {
        const sidebar = document.getElementById('appSidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        const toggleButtons = document.querySelectorAll('[data-sidebar-toggle]');

        if (!sidebar) return;

        function openSidebar() {
            sidebar.classList.add('show');
            if (backdrop) backdrop.classList.add('show');
        }

        function closeSidebar() {
            sidebar.classList.remove('show');
            if (backdrop) backdrop.classList.remove('show');
        }

        toggleButtons.forEach((btn) => {
            btn.addEventListener('click', () => {
                if (sidebar.classList.contains('show')) {
                    closeSidebar();
                } else {
                    openSidebar();
                }
            });
        });

        if (backdrop) {
            backdrop.addEventListener('click', closeSidebar);
        }

        window.addEventListener('resize', () => {
            if (window.innerWidth > 991.98) {
                closeSidebar();
            }
        });
    }

    /* ---------- Active nav highlighting ---------- */
    function highlightActiveNav() {
        const links = document.querySelectorAll('.sidebar-link');
        const currentPath = window.location.pathname;

        links.forEach((link) => {
            const href = link.getAttribute('href');
            if (href && href !== '/' && currentPath.startsWith(href)) {
                link.classList.add('active');
            }
        });
    }

    /* ---------- Flash message auto dismiss via SweetAlert ---------- */
    function initFlashMessages() {
        const flashData = document.getElementById('flashData');
        if (!flashData) return;

        const success = flashData.dataset.success;
        const error = flashData.dataset.error;

        if (success) Clinic.toastSuccess(success);
        if (error) Clinic.toastError(error);
    }

    /* ---------- Prevent double submit ---------- */
    function initFormGuards() {
        document.querySelectorAll('form[data-guard]').forEach((form) => {
            form.addEventListener('submit', () => {
                const btn = form.querySelector('[type="submit"]');
                Clinic.setButtonLoading(btn, btn ? btn.dataset.loadingText : 'Menyimpan...');
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initSidebar();
        highlightActiveNav();
        initFlashMessages();
        initFormGuards();
    });
})();
