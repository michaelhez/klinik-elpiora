(function () {
    'use strict';

    const userId = window.USER_ID;

    document.getElementById('resetPasswordForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const payload = Object.fromEntries(new FormData(form).entries());

        try {
            const res = await Clinic.request(`/users/${userId}/reset-password`, { method: 'POST', body: JSON.stringify(payload) });
            Clinic.toastSuccess(res.message);
            bootstrap.Modal.getInstance(document.getElementById('resetPasswordModal')).hide();
            form.reset();
        } catch (err) {
            const firstError = err.payload && err.payload.data && err.payload.data.errors
                ? Object.values(err.payload.data.errors)[0][0]
                : err.message;
            Clinic.toastError(firstError);
            Clinic.resetButton(form.querySelector('[type="submit"]'));
        }
    });
})();
