(function () {
    'use strict';

    const doctorId = window.DOCTOR_ID;

    const Doctors = {
        async remove(id) {
            const confirmed = await Clinic.confirmDialog({
                title: 'Hapus dokter ini?',
                text: 'Data dokter akan dihapus dari daftar aktif.',
                confirmText: 'Ya, hapus',
            });
            if (!confirmed) return;

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/doctors/${id}/delete`;
            form.innerHTML = `<input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">`;
            document.body.appendChild(form);
            form.submit();
        },

        async removeSchedule(scheduleId) {
            const confirmed = await Clinic.confirmDialog({ title: 'Hapus jadwal ini?', confirmText: 'Ya, hapus' });
            if (!confirmed) return;

            try {
                const res = await Clinic.request(`/doctors/${doctorId}/schedules/${scheduleId}/delete`, { method: 'POST' });
                Clinic.toastSuccess(res.message);
                window.location.reload();
            } catch (err) {
                Clinic.toastError(err.message);
            }
        },
    };

    const scheduleForm = document.getElementById('scheduleForm');
    if (scheduleForm) {
        scheduleForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(scheduleForm);
            const payload = Object.fromEntries(formData.entries());
            try {
                const res = await Clinic.request(`/doctors/${doctorId}/schedules`, { method: 'POST', body: JSON.stringify(payload) });
                Clinic.toastSuccess(res.message);
                window.location.reload();
            } catch (err) {
                Clinic.toastError(err.message);
                Clinic.resetButton(scheduleForm.querySelector('[type="submit"]'));
            }
        });
    }

    window.Doctors = Doctors;
})();
