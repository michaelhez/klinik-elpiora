(function () {
    'use strict';

    const form = document.getElementById('receiveForm');
    if (!form) return;

    const orderId = window.PURCHASE_ORDER_ID;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const items = [];
        form.querySelectorAll('tr[data-po-item-id]').forEach((row) => {
            const quantity = parseInt(row.querySelector('[data-field="quantity_received"]').value, 10) || 0;
            if (quantity <= 0) return;

            items.push({
                purchase_order_item_id: row.dataset.poItemId,
                quantity_received: quantity,
                batch_number: row.querySelector('[data-field="batch_number"]').value,
                expired_date: row.querySelector('[data-field="expired_date"]').value,
            });
        });

        if (items.length === 0) {
            Clinic.toastError('Isi jumlah minimal satu obat yang diterima.');
            return;
        }

        for (const item of items) {
            if (!item.batch_number || !item.expired_date) {
                Clinic.toastError('Nomor batch dan tanggal kadaluwarsa wajib diisi untuk obat yang diterima.');
                return;
            }
        }

        const payload = { notes: document.getElementById('receiveNotes').value, items };

        try {
            const res = await Clinic.request(`/purchase-orders/${orderId}/receive`, { method: 'POST', body: JSON.stringify(payload) });
            Clinic.toastSuccess(res.message);
            window.location.reload();
        } catch (err) {
            const firstError = err.payload && err.payload.data && err.payload.data.errors
                ? Object.values(err.payload.data.errors)[0][0]
                : err.message;
            Clinic.toastError(firstError);
            Clinic.resetButton(form.querySelector('[type="submit"]'));
        }
    });
})();
