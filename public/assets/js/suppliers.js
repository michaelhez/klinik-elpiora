(function () {
    'use strict';

    const FIELDS = ['code', 'name', 'contact_person', 'phone', 'email', 'address', 'status'];

    const Sup = {
        current: null,

        openModal(record) {
            this.current = record ? record.id : null;
            const form = document.getElementById('supplierForm');

            document.getElementById('supplierModalTitle').textContent = record ? 'Edit Pemasok' : 'Tambah Pemasok';

            FIELDS.forEach((name) => {
                const el = form.querySelector(`[name="${name}"]`);
                el.value = record ? (record[name] ?? '') : (name === 'status' ? 'ACTIVE' : '');
            });

            bootstrap.Modal.getOrCreateInstance(document.getElementById('supplierModal')).show();
        },

        async submit(event) {
            event.preventDefault();

            const form = document.getElementById('supplierForm');
            const formData = new FormData(form);
            const payload = {};
            FIELDS.forEach((name) => { payload[name] = formData.get(name) || ''; });

            const isEdit = this.current !== null;
            const url = isEdit ? `/suppliers/${this.current}` : '/suppliers';
            const method = isEdit ? 'PUT' : 'POST';

            try {
                const res = await Clinic.request(url, { method, body: JSON.stringify(payload) });
                bootstrap.Modal.getInstance(document.getElementById('supplierModal')).hide();
                Clinic.toastSuccess(res.message);
                window.location.reload();
            } catch (err) {
                const firstError = err.payload && err.payload.data && err.payload.data.errors
                    ? Object.values(err.payload.data.errors)[0][0]
                    : err.message;
                Clinic.toastError(firstError);
            }
        },

        async remove(id) {
            const confirmed = await Clinic.confirmDialog({ title: 'Hapus pemasok ini?', confirmText: 'Ya, hapus' });
            if (!confirmed) return;

            try {
                const res = await Clinic.request(`/suppliers/${id}`, { method: 'DELETE' });
                Clinic.toastSuccess(res.message);
                window.location.reload();
            } catch (err) {
                Clinic.toastError(err.message);
            }
        },
    };

    document.getElementById('supplierForm').addEventListener('submit', (e) => Sup.submit(e));

    window.Sup = Sup;
})();
