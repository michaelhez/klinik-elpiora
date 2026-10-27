(function () {
    'use strict';

    const table = document.getElementById('queueTable');
    if (!table) return;

    const actionLabels = {
        call: { confirm: 'Panggil pasien ini?', success: 'Antrian dipanggil.' },
        recall: { confirm: 'Panggil ulang pasien ini?', success: 'Antrian dipanggil ulang.' },
        complete: { confirm: 'Tandai antrian ini selesai?', success: 'Antrian selesai.' },
        skip: { confirm: 'Lewati antrian ini?', success: 'Antrian dilewati.' },
    };

    table.addEventListener('click', async (e) => {
        const btn = e.target.closest('button[data-action]');
        if (!btn || btn.disabled) return;

        const action = btn.dataset.action;
        const row = btn.closest('tr');
        const queueId = row.dataset.queueId;
        const meta = actionLabels[action];

        const confirmed = await Clinic.confirmDialog({ title: meta.confirm, confirmText: 'Ya, lanjutkan' });
        if (!confirmed) return;

        try {
            const res = await Clinic.request(`/api/queue/${action}/${queueId}`, { method: 'POST' });
            Clinic.toastSuccess(meta.success);
            window.location.reload();
        } catch (err) {
            Clinic.toastError(err.message);
        }
    });
})();
