(function () {
    'use strict';

    const medicineId = window.MEDICINE_ID;
    const adjustModalEl = document.getElementById('adjustModal');
    const transferModalEl = document.getElementById('transferModal');
    const adjustModal = adjustModalEl ? new bootstrap.Modal(adjustModalEl) : null;
    const transferModal = transferModalEl ? new bootstrap.Modal(transferModalEl) : null;

    function openAdjust(batch) {
        const form = document.getElementById('adjustForm');
        form.querySelector('[name="medicine_batch_id"]').value = batch.id;
        form.querySelector('[name="adjustment_type"]').value = 'ADD';
        form.querySelector('[name="quantity"]').value = '';
        form.querySelector('[name="reason"]').value = '';
        document.getElementById('adjustBatchLabel').value = `${batch.batch_number} (${batch.branch_name}) - Stok saat ini: ${batch.quantity}`;
        adjustModal.show();
    }

    function openTransfer(batch) {
        const form = document.getElementById('transferForm');
        form.querySelector('[name="medicine_batch_id"]').value = batch.id;
        form.querySelector('[name="quantity"]').value = '';
        form.querySelector('[name="notes"]').value = '';
        document.getElementById('transferBatchLabel').value = `${batch.batch_number} (${batch.branch_name}) - Stok saat ini: ${batch.quantity}`;

        const toBranchSelect = document.getElementById('transferToBranch');
        Array.from(toBranchSelect.options).forEach((opt) => {
            opt.disabled = String(opt.value) === String(batch.branch_id);
        });
        if (String(toBranchSelect.value) === String(batch.branch_id)) {
            const firstEnabled = Array.from(toBranchSelect.options).find((opt) => !opt.disabled);
            if (firstEnabled) {
                toBranchSelect.value = firstEnabled.value;
            }
        }

        transferModal.show();
    }

    document.getElementById('adjustForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const payload = Object.fromEntries(new FormData(form).entries());

        try {
            const res = await Clinic.request(`/medicines/${medicineId}/batches/adjust`, { method: 'POST', body: JSON.stringify(payload) });
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

    document.getElementById('transferForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const payload = Object.fromEntries(new FormData(form).entries());

        try {
            const res = await Clinic.request(`/medicines/${medicineId}/batches/transfer`, { method: 'POST', body: JSON.stringify(payload) });
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

    window.Inv = { openAdjust, openTransfer };
})();
