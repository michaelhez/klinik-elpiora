(function () {
    'use strict';

    const medicineId = window.MEDICINE_ID;

    document.getElementById('batchForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const payload = Object.fromEntries(new FormData(form).entries());

        try {
            const res = await Clinic.request(`/medicines/${medicineId}/batches`, { method: 'POST', body: JSON.stringify(payload) });
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
