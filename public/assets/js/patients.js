(function () {
    'use strict';

    const patientId = window.PATIENT_ID;

    async function submitJsonForm(form, endpoint) {
        const formData = new FormData(form);
        const payload = Object.fromEntries(formData.entries());
        try {
            const res = await Clinic.request(endpoint, { method: 'POST', body: JSON.stringify(payload) });
            Clinic.toastSuccess(res.message);
            window.location.reload();
        } catch (err) {
            Clinic.toastError(err.message);
            Clinic.resetButton(form.querySelector('[type="submit"]'));
        }
    }

    async function removeItem(endpoint, confirmTitle) {
        const confirmed = await Clinic.confirmDialog({ title: confirmTitle, confirmText: 'Ya, hapus' });
        if (!confirmed) return;

        try {
            const res = await Clinic.request(endpoint, { method: 'POST' });
            Clinic.toastSuccess(res.message);
            window.location.reload();
        } catch (err) {
            Clinic.toastError(err.message);
        }
    }

    const Patients = {
        removeAllergy: (id) => removeItem(`/patients/${patientId}/allergies/${id}/delete`, 'Hapus alergi ini?'),
        removeHistory: (id) => removeItem(`/patients/${patientId}/medical-histories/${id}/delete`, 'Hapus riwayat ini?'),
        removeContact: (id) => removeItem(`/patients/${patientId}/emergency-contacts/${id}/delete`, 'Hapus kontak ini?'),
    };

    document.getElementById('allergyForm')?.addEventListener('submit', (e) => {
        e.preventDefault();
        submitJsonForm(e.target, `/patients/${patientId}/allergies`);
    });

    document.getElementById('historyForm')?.addEventListener('submit', (e) => {
        e.preventDefault();
        submitJsonForm(e.target, `/patients/${patientId}/medical-histories`);
    });

    document.getElementById('contactForm')?.addEventListener('submit', (e) => {
        e.preventDefault();
        submitJsonForm(e.target, `/patients/${patientId}/emergency-contacts`);
    });

    window.Patients = Patients;
})();
