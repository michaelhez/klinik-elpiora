(function () {
    'use strict';

    const employeeId = window.EMPLOYEE_ID;

    const Employees = {
        async remove(id) {
            const confirmed = await Clinic.confirmDialog({
                title: 'Hapus karyawan ini?',
                text: 'Data karyawan akan dihapus dari daftar aktif.',
                confirmText: 'Ya, hapus',
            });
            if (!confirmed) return;

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/employees/${id}/delete`;
            form.innerHTML = `<input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">`;
            document.body.appendChild(form);
            form.submit();
        },

        async removeDocument(documentId) {
            const confirmed = await Clinic.confirmDialog({ title: 'Hapus dokumen ini?', confirmText: 'Ya, hapus' });
            if (!confirmed) return;

            try {
                const res = await Clinic.request(`/employees/${employeeId}/documents/${documentId}/delete`, { method: 'POST' });
                Clinic.toastSuccess(res.message);
                window.location.reload();
            } catch (err) {
                Clinic.toastError(err.message);
            }
        },

        async removeContact(contactId) {
            const confirmed = await Clinic.confirmDialog({ title: 'Hapus kontak darurat ini?', confirmText: 'Ya, hapus' });
            if (!confirmed) return;

            try {
                const res = await Clinic.request(`/employees/${employeeId}/emergency-contacts/${contactId}/delete`, { method: 'POST' });
                Clinic.toastSuccess(res.message);
                window.location.reload();
            } catch (err) {
                Clinic.toastError(err.message);
            }
        },
    };

    const docForm = document.getElementById('docForm');
    if (docForm) {
        docForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(docForm);
            try {
                const res = await fetch(`/employees/${employeeId}/documents`, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: formData,
                });
                const payload = await res.json();
                if (!res.ok) throw new Error(payload.message);
                Clinic.toastSuccess(payload.message);
                window.location.reload();
            } catch (err) {
                Clinic.toastError(err.message);
                Clinic.resetButton(docForm.querySelector('[type="submit"]'));
            }
        });
    }

    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(contactForm);
            const payload = Object.fromEntries(formData.entries());
            try {
                const res = await Clinic.request(`/employees/${employeeId}/emergency-contacts`, { method: 'POST', body: JSON.stringify(payload) });
                Clinic.toastSuccess(res.message);
                window.location.reload();
            } catch (err) {
                Clinic.toastError(err.message);
                Clinic.resetButton(contactForm.querySelector('[type="submit"]'));
            }
        });
    }

    window.Employees = Employees;
})();
