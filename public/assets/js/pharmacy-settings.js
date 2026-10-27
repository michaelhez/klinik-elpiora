(function () {
    'use strict';

    const RESOURCES = {
        category: {
            endpoint: '/settings/pharmacy/categories',
            label: 'Kategori Obat',
            fields: [
                { name: 'name', label: 'Nama Kategori', type: 'text', required: true },
                { name: 'code', label: 'Kode', type: 'text', required: true },
                { name: 'description', label: 'Deskripsi', type: 'textarea' },
                { name: 'is_active', label: 'Aktif', type: 'switch' },
            ],
        },
        unit: {
            endpoint: '/settings/pharmacy/units',
            label: 'Satuan',
            fields: [
                { name: 'name', label: 'Nama Satuan', type: 'text', required: true, helper: 'Contoh: Tablet, Botol, Strip' },
                { name: 'code', label: 'Kode', type: 'text', required: true },
                { name: 'is_active', label: 'Aktif', type: 'switch' },
            ],
        },
    };

    function fieldHtml(field, record) {
        const value = record ? (record[field.name] ?? '') : (field.value ?? '');
        const requiredAttr = field.required ? 'required' : '';
        const labelClass = field.required ? 'form-label form-label-required' : 'form-label';
        const helper = field.helper ? `<div class="form-helper">${field.helper}</div>` : '';

        if (field.type === 'switch') {
            const checked = record ? (Number(value) === 1 ? 'checked' : '') : 'checked';
            return `<div class="mb-3 form-check form-switch">
                <input type="checkbox" class="form-check-input" id="f_${field.name}" name="${field.name}" value="1" ${checked}>
                <label class="form-check-label" for="f_${field.name}">${field.label}</label>
            </div>`;
        }

        if (field.type === 'textarea') {
            return `<div class="mb-3">
                <label class="${labelClass}" for="f_${field.name}">${field.label}</label>
                <textarea class="form-control" id="f_${field.name}" name="${field.name}" rows="2" ${requiredAttr}>${value}</textarea>
            </div>`;
        }

        return `<div class="mb-3">
            <label class="${labelClass}" for="f_${field.name}">${field.label}</label>
            <input type="text" class="form-control" id="f_${field.name}" name="${field.name}" value="${value}" ${requiredAttr}>
            ${helper}
        </div>`;
    }

    const Pharm = {
        current: null,

        openModal(resourceKey, record) {
            const resource = RESOURCES[resourceKey];
            this.current = { key: resourceKey, id: record ? record.id : null };

            document.getElementById('pharmModalTitle').textContent = (record ? 'Edit ' : 'Tambah ') + resource.label;
            document.getElementById('pharmModalBody').innerHTML = resource.fields.map((f) => fieldHtml(f, record)).join('');

            bootstrap.Modal.getOrCreateInstance(document.getElementById('pharmModal')).show();
        },

        async submit(event) {
            event.preventDefault();
            if (!this.current) return;

            const resource = RESOURCES[this.current.key];
            const form = document.getElementById('pharmForm');
            const formData = new FormData(form);
            const payload = {};

            resource.fields.forEach((field) => {
                payload[field.name] = field.type === 'switch' ? (formData.has(field.name) ? 1 : 0) : (formData.get(field.name) || '');
            });

            const isEdit = this.current.id !== null;
            const url = isEdit ? `${resource.endpoint}/${this.current.id}` : resource.endpoint;
            const method = isEdit ? 'PUT' : 'POST';

            try {
                const res = await Clinic.request(url, { method, body: JSON.stringify(payload) });
                bootstrap.Modal.getInstance(document.getElementById('pharmModal')).hide();
                Clinic.toastSuccess(res.message);
                window.location.reload();
            } catch (err) {
                const firstError = err.payload && err.payload.data && err.payload.data.errors
                    ? Object.values(err.payload.data.errors)[0][0]
                    : err.message;
                Clinic.toastError(firstError);
            }
        },

        async remove(resourceKey, id) {
            const confirmed = await Clinic.confirmDialog({ title: 'Hapus data ini?', confirmText: 'Ya, hapus' });
            if (!confirmed) return;

            const resource = RESOURCES[resourceKey];
            try {
                const res = await Clinic.request(`${resource.endpoint}/${id}`, { method: 'DELETE' });
                Clinic.toastSuccess(res.message);
                window.location.reload();
            } catch (err) {
                Clinic.toastError(err.message);
            }
        },
    };

    document.getElementById('pharmForm').addEventListener('submit', (e) => Pharm.submit(e));

    window.Pharm = Pharm;
})();
