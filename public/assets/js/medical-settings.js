(function () {
    'use strict';

    const REFS = window.MED_REFS || { categories: [] };

    const RESOURCES = {
        icd10: {
            endpoint: '/settings/medical/icd10',
            label: 'Kode ICD-10',
            fields: [
                { name: 'code', label: 'Kode ICD-10', type: 'text', required: true, helper: 'Contoh: J06.9' },
                { name: 'description', label: 'Deskripsi', type: 'text', required: true },
                { name: 'category', label: 'Kategori', type: 'text' },
                { name: 'status', label: 'Aktif', type: 'switch' },
            ],
        },
        category: {
            endpoint: '/settings/medical/action-categories',
            label: 'Kategori Tindakan',
            fields: [
                { name: 'name', label: 'Nama Kategori', type: 'text', required: true },
                { name: 'code', label: 'Kode', type: 'text', required: true },
                { name: 'description', label: 'Deskripsi', type: 'textarea' },
                { name: 'is_active', label: 'Aktif', type: 'switch' },
            ],
        },
        action: {
            endpoint: '/settings/medical/actions',
            label: 'Tindakan Medis',
            fields: [
                { name: 'category_id', label: 'Kategori', type: 'select', options: () => [{ value: '', label: '-- Tanpa kategori --' }].concat(REFS.categories.map((c) => ({ value: c.id, label: c.name }))) },
                { name: 'code', label: 'Kode', type: 'text', required: true },
                { name: 'name', label: 'Nama Tindakan', type: 'text', required: true },
                { name: 'price', label: 'Harga', type: 'number', required: true, value: 0 },
                { name: 'duration', label: 'Durasi (menit)', type: 'number' },
                { name: 'description', label: 'Deskripsi', type: 'textarea' },
                { name: 'status', label: 'Aktif', type: 'switch' },
            ],
        },
    };

    function fieldHtml(field, record) {
        const value = record ? (record[field.name] ?? '') : (field.value ?? '');
        const requiredAttr = field.required ? 'required' : '';
        const labelClass = field.required ? 'form-label form-label-required' : 'form-label';
        const helper = field.helper ? `<div class="form-helper">${field.helper}</div>` : '';

        if (field.type === 'switch') {
            const isEdit = !!record;
            const currentValue = isEdit ? (record[field.name === 'is_active' ? 'is_active' : 'status']) : 1;
            const checked = isEdit ? (currentValue === 1 || currentValue === 'ACTIVE' ? 'checked' : '') : 'checked';
            return `<div class="mb-3 form-check form-switch">
                <input type="checkbox" class="form-check-input" id="f_${field.name}" name="${field.name}" value="1" ${checked}>
                <label class="form-check-label" for="f_${field.name}">${field.label}</label>
            </div>`;
        }

        if (field.type === 'select') {
            const options = field.options().map((opt) => `<option value="${opt.value}" ${String(opt.value) === String(value) ? 'selected' : ''}>${opt.label}</option>`).join('');
            return `<div class="mb-3">
                <label class="${labelClass}" for="f_${field.name}">${field.label}</label>
                <select class="form-select" id="f_${field.name}" name="${field.name}" ${requiredAttr}>${options}</select>
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
            <input type="${field.type}" class="form-control" id="f_${field.name}" name="${field.name}" value="${value}" ${requiredAttr}>
            ${helper}
        </div>`;
    }

    const Med = {
        current: null,

        openModal(resourceKey, record) {
            const resource = RESOURCES[resourceKey];
            this.current = { key: resourceKey, id: record ? record.id : null };

            document.getElementById('medModalTitle').textContent = (record ? 'Edit ' : 'Tambah ') + resource.label;
            document.getElementById('medModalBody').innerHTML = resource.fields.map((f) => fieldHtml(f, record)).join('');

            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('medModal'));
            modal.show();
        },

        async submit(event) {
            event.preventDefault();
            if (!this.current) return;

            const resource = RESOURCES[this.current.key];
            const form = document.getElementById('medForm');
            const formData = new FormData(form);
            const payload = {};

            resource.fields.forEach((field) => {
                if (field.type === 'switch') {
                    const val = formData.has(field.name) ? 1 : 0;
                    payload[field.name] = field.name === 'status' ? (val ? 'ACTIVE' : 'INACTIVE') : val;
                } else {
                    payload[field.name] = formData.get(field.name) || '';
                }
            });

            const isEdit = this.current.id !== null;
            const url = isEdit ? `${resource.endpoint}/${this.current.id}` : resource.endpoint;
            const method = isEdit ? 'PUT' : 'POST';

            try {
                const res = await Clinic.request(url, { method, body: JSON.stringify(payload) });
                bootstrap.Modal.getInstance(document.getElementById('medModal')).hide();
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
            const confirmed = await Clinic.confirmDialog({
                title: 'Hapus data ini?',
                confirmText: 'Ya, hapus',
            });
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

    document.getElementById('medForm').addEventListener('submit', (e) => Med.submit(e));

    window.Med = Med;
})();
