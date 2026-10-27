(function () {
    'use strict';

    const testId = window.LAB_TEST_ID;
    const base = `/settings/lab-tests/${testId}/parameters`;

    const LabParams = {
        current: null,

        openModal(record) {
            this.current = record ? record.id : null;
            document.getElementById('paramModalTitle').textContent = record ? 'Edit Parameter' : 'Tambah Parameter';

            const form = document.getElementById('paramForm');
            form.reset();

            document.getElementById('p_name').value = record ? record.name : '';
            document.getElementById('p_unit').value = record ? (record.unit || '') : '';
            document.getElementById('p_value_type').value = record ? record.value_type : 'NUMERIC';
            document.getElementById('p_reference_min').value = record ? (record.reference_min ?? '') : '';
            document.getElementById('p_reference_max').value = record ? (record.reference_max ?? '') : '';
            document.getElementById('p_reference_text').value = record ? (record.reference_text || '') : '';

            this.toggleReferenceFields();
            bootstrap.Modal.getOrCreateInstance(document.getElementById('paramModal')).show();
        },

        toggleReferenceFields() {
            const isNumeric = document.getElementById('p_value_type').value === 'NUMERIC';
            document.getElementById('p_numeric_fields').style.display = isNumeric ? 'flex' : 'none';
            document.getElementById('p_text_field').style.display = isNumeric ? 'none' : 'block';
        },

        async submit(event) {
            event.preventDefault();
            const form = document.getElementById('paramForm');
            const payload = Object.fromEntries(new FormData(form).entries());

            const isEdit = this.current !== null;
            const url = isEdit ? `${base}/${this.current}` : base;
            const method = isEdit ? 'PUT' : 'POST';

            try {
                const res = await Clinic.request(url, { method, body: JSON.stringify(payload) });
                bootstrap.Modal.getInstance(document.getElementById('paramModal')).hide();
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
            const confirmed = await Clinic.confirmDialog({ title: 'Hapus parameter ini?', confirmText: 'Ya, hapus' });
            if (!confirmed) return;

            try {
                const res = await Clinic.request(`${base}/${id}`, { method: 'DELETE' });
                Clinic.toastSuccess(res.message);
                window.location.reload();
            } catch (err) {
                Clinic.toastError(err.message);
            }
        },
    };

    document.getElementById('paramForm').addEventListener('submit', (e) => LabParams.submit(e));

    window.LabParams = LabParams;
})();
