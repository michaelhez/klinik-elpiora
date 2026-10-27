(function () {
    'use strict';

    function renderValues(title, values) {
        if (values === null || values === undefined) {
            return `<h6 class="small fw-semibold">${title}</h6><p class="text-muted-custom small">-</p>`;
        }

        const rows = Object.entries(values).map(([key, value]) => `
            <tr><td class="text-nowrap fw-semibold">${key}</td><td>${value === null ? '-' : String(value)}</td></tr>
        `).join('');

        return `<h6 class="small fw-semibold">${title}</h6>
            <table class="table table-sm mb-3"><tbody>${rows}</tbody></table>`;
    }

    const AuditLog = {
        async showDetail(id) {
            const modalEl = document.getElementById('auditDetailModal');
            const body = document.getElementById('auditDetailBody');
            body.innerHTML = 'Memuat...';
            bootstrap.Modal.getOrCreateInstance(modalEl).show();

            try {
                const res = await Clinic.request(`/audit-logs/${id}`);
                const log = res.data;
                const oldValues = log.old_values ? JSON.parse(log.old_values) : null;
                const newValues = log.new_values ? JSON.parse(log.new_values) : null;

                body.innerHTML = `
                    <div class="mb-3 small text-muted-custom">
                        <div><strong>Pengguna:</strong> ${log.user_name || 'Sistem'}</div>
                        <div><strong>Aksi:</strong> ${log.action} &bull; <strong>Modul:</strong> ${log.module}</div>
                        <div><strong>Waktu:</strong> ${log.created_at}</div>
                        <div><strong>IP:</strong> ${log.ip_address || '-'}</div>
                    </div>
                    ${renderValues('Nilai Sebelum', oldValues)}
                    ${renderValues('Nilai Sesudah', newValues)}
                `;
            } catch (err) {
                body.innerHTML = `<p class="text-danger small">${err.message}</p>`;
            }
        },
    };

    window.AuditLog = AuditLog;
})();
