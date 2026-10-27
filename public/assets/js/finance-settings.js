(function () {
    'use strict';

    const RESOURCES = {
        insurance: {
            endpoint: '/settings/finance/insurances',
            label: 'Asuransi',
            fields: [
                { name: 'name', label: 'Nama Asuransi', type: 'text', required: true },
                { name: 'code', label: 'Kode', type: 'text', required: true },
                { name: 'type', label: 'Tipe', type: 'select', options: [['BPJS', 'BPJS'], ['PRIVATE', 'Swasta']], required: true },
                { name: 'contact_person', label: 'Kontak Person', type: 'text' },
                { name: 'phone', label: 'Telepon', type: 'text' },
                { name: 'address', label: 'Alamat', type: 'textarea' },
                { name: 'status', label: 'Status', type: 'select', options: [['ACTIVE', 'Aktif'], ['INACTIVE', 'Nonaktif']], required: true },
            ],
        },
        corporate: {
            endpoint: '/settings/finance/corporates',
            label: 'Perusahaan Rekanan',
            fields: [
                { name: 'name', label: 'Nama Perusahaan', type: 'text', required: true },
                { name: 'code', label: 'Kode', type: 'text', required: true },
                { name: 'pic_name', label: 'PIC', type: 'text' },
                { name: 'phone', label: 'Telepon', type: 'text' },
                { name: 'email', label: 'Email', type: 'text' },
                { name: 'address', label: 'Alamat', type: 'textarea' },
                { name: 'credit_limit', label: 'Limit Kredit', type: 'text', helper: 'Batas maksimal tagihan berjalan (Rupiah)' },
                { name: 'status', label: 'Status', type: 'select', options: [['ACTIVE', 'Aktif'], ['INACTIVE', 'Nonaktif']], required: true },
            ],
        },
    };

    function fieldHtml(field, record) {
        const value = record ? (record[field.name] ?? '') : (field.value ?? '');
        const requiredAttr = field.required ? 'required' : '';
        const labelClass = field.required ? 'form-label form-label-required' : 'form-label';
        const helper = field.helper ? `<div class="form-helper">${field.helper}</div>` : '';

        if (field.type === 'select') {
            const options = field.options.map(([val, label]) => `<option value="${val}" ${String(value) === val ? 'selected' : ''}>${label}</option>`).join('');
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
            <input type="text" class="form-control" id="f_${field.name}" name="${field.name}" value="${value}" ${requiredAttr}>
            ${helper}
        </div>`;
    }

    const Fin = {
        current: null,

        openModal(resourceKey, record) {
            const resource = RESOURCES[resourceKey];
            this.current = { key: resourceKey, id: record ? record.id : null };

            document.getElementById('finModalTitle').textContent = (record ? 'Edit ' : 'Tambah ') + resource.label;
            document.getElementById('finModalBody').innerHTML = resource.fields.map((f) => fieldHtml(f, record)).join('');

            bootstrap.Modal.getOrCreateInstance(document.getElementById('finModal')).show();
        },

        async submit(event) {
            event.preventDefault();
            if (!this.current) return;

            const resource = RESOURCES[this.current.key];
            const form = document.getElementById('finForm');
            const formData = new FormData(form);
            const payload = {};

            resource.fields.forEach((field) => {
                payload[field.name] = formData.get(field.name) || '';
            });

            const isEdit = this.current.id !== null;
            const url = isEdit ? `${resource.endpoint}/${this.current.id}` : resource.endpoint;
            const method = isEdit ? 'PUT' : 'POST';

            try {
                const res = await Clinic.request(url, { method, body: JSON.stringify(payload) });
                bootstrap.Modal.getInstance(document.getElementById('finModal')).hide();
                Clinic.toastSuccess(res.message);
                window.location.reload();
            } catch (err) {
                const firstError = err.payload && err.payload.data && err.payload.data.errors
                    ? Object.values(err.payload.data.errors)[0][0]
                    : err.message;
                Clinic.toastError(firstError);
            }
        },
    };

    document.getElementById('finForm').addEventListener('submit', (e) => Fin.submit(e));

    window.Fin = Fin;
})();
