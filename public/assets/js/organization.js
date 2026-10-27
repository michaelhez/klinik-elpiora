(function () {
    'use strict';

    const REFS = window.ORG_REFS || { clinics: [], branches: [], departments: [], polyclinics: [] };

    const RESOURCES = {
        clinic: {
            endpoint: '/settings/organization/clinics',
            label: 'Klinik',
            fields: [
                { name: 'name', label: 'Nama Klinik', type: 'text', required: true },
                { name: 'code', label: 'Kode', type: 'text', required: true, helper: 'Kode unik, contoh: KLN01' },
                { name: 'phone', label: 'Telepon', type: 'text' },
                { name: 'email', label: 'Email', type: 'email' },
                { name: 'address', label: 'Alamat', type: 'textarea' },
                { name: 'is_active', label: 'Aktif', type: 'switch' },
            ],
        },
        branch: {
            endpoint: '/settings/organization/branches',
            label: 'Cabang',
            fields: [
                { name: 'clinic_id', label: 'Klinik', type: 'select', required: true, options: () => REFS.clinics.map((c) => ({ value: c.id, label: c.name })) },
                { name: 'name', label: 'Nama Cabang', type: 'text', required: true },
                { name: 'code', label: 'Kode', type: 'text', required: true },
                { name: 'phone', label: 'Telepon', type: 'text' },
                { name: 'email', label: 'Email', type: 'email' },
                { name: 'address', label: 'Alamat', type: 'textarea' },
                { name: 'is_main', label: 'Cabang Utama', type: 'switch' },
                { name: 'is_active', label: 'Aktif', type: 'switch' },
            ],
        },
        department: {
            endpoint: '/settings/organization/departments',
            label: 'Departemen',
            fields: [
                { name: 'branch_id', label: 'Cabang', type: 'select', required: true, options: () => REFS.branches.map((b) => ({ value: b.id, label: b.name })) },
                { name: 'name', label: 'Nama Departemen', type: 'text', required: true },
                { name: 'code', label: 'Kode', type: 'text', required: true },
                { name: 'description', label: 'Deskripsi', type: 'textarea' },
                { name: 'is_active', label: 'Aktif', type: 'switch' },
            ],
        },
        polyclinic: {
            endpoint: '/settings/organization/polyclinics',
            label: 'Poliklinik',
            fields: [
                { name: 'branch_id', label: 'Cabang', type: 'select', required: true, options: () => REFS.branches.map((b) => ({ value: b.id, label: b.name })) },
                { name: 'name', label: 'Nama Poliklinik', type: 'text', required: true },
                { name: 'code', label: 'Kode', type: 'text', required: true },
                { name: 'description', label: 'Deskripsi', type: 'textarea' },
                { name: 'is_active', label: 'Aktif', type: 'switch' },
            ],
        },
        room: {
            endpoint: '/settings/organization/rooms',
            label: 'Ruangan',
            fields: [
                { name: 'branch_id', label: 'Cabang', type: 'select', required: true, options: () => REFS.branches.map((b) => ({ value: b.id, label: b.name })) },
                { name: 'polyclinic_id', label: 'Poliklinik', type: 'select', options: () => [{ value: '', label: '-- Tidak terikat poliklinik --' }].concat(REFS.polyclinics.map((p) => ({ value: p.id, label: p.name }))) },
                { name: 'name', label: 'Nama Ruangan', type: 'text', required: true },
                { name: 'code', label: 'Kode', type: 'text', required: true },
                { name: 'floor', label: 'Lantai', type: 'text' },
                { name: 'capacity', label: 'Kapasitas', type: 'number', required: true, value: 1 },
                { name: 'is_active', label: 'Aktif', type: 'switch' },
            ],
        },
        position: {
            endpoint: '/settings/organization/positions',
            label: 'Jabatan',
            fields: [
                { name: 'department_id', label: 'Departemen', type: 'select', options: () => [{ value: '', label: '-- Tidak terikat departemen --' }].concat(REFS.departments.map((d) => ({ value: d.id, label: d.name }))) },
                { name: 'name', label: 'Nama Jabatan', type: 'text', required: true },
                { name: 'code', label: 'Kode', type: 'text', required: true },
                { name: 'level', label: 'Level', type: 'text', helper: 'Contoh: Staff, Supervisor, Manager' },
                { name: 'description', label: 'Deskripsi', type: 'textarea' },
                { name: 'is_active', label: 'Aktif', type: 'switch' },
            ],
        },
        specialization: {
            endpoint: '/settings/organization/specializations',
            label: 'Spesialisasi',
            fields: [
                { name: 'name', label: 'Nama Spesialisasi', type: 'text', required: true },
                { name: 'code', label: 'Kode', type: 'text', required: true },
                { name: 'description', label: 'Deskripsi', type: 'textarea' },
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

        if (field.type === 'select') {
            const options = field.options().map((opt) => `<option value="${opt.value}" ${String(opt.value) === String(value) ? 'selected' : ''}>${opt.label}</option>`).join('');
            return `<div class="mb-3">
                <label class="${labelClass}" for="f_${field.name}">${field.label}</label>
                <select class="form-select" id="f_${field.name}" name="${field.name}" ${requiredAttr}>${options}</select>
                ${helper}
            </div>`;
        }

        if (field.type === 'textarea') {
            return `<div class="mb-3">
                <label class="${labelClass}" for="f_${field.name}">${field.label}</label>
                <textarea class="form-control" id="f_${field.name}" name="${field.name}" rows="2" ${requiredAttr}>${value}</textarea>
                ${helper}
            </div>`;
        }

        return `<div class="mb-3">
            <label class="${labelClass}" for="f_${field.name}">${field.label}</label>
            <input type="${field.type}" class="form-control" id="f_${field.name}" name="${field.name}" value="${value}" ${requiredAttr}>
            ${helper}
        </div>`;
    }

    const Org = {
        current: null,

        openModal(resourceKey, record) {
            const resource = RESOURCES[resourceKey];
            this.current = { key: resourceKey, id: record ? record.id : null };

            document.getElementById('orgModalTitle').textContent = (record ? 'Edit ' : 'Tambah ') + resource.label;
            document.getElementById('orgModalBody').innerHTML = resource.fields.map((f) => fieldHtml(f, record)).join('');

            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('orgModal'));
            modal.show();
        },

        async submit(event) {
            event.preventDefault();
            if (!this.current) return;

            const resource = RESOURCES[this.current.key];
            const form = document.getElementById('orgForm');
            const formData = new FormData(form);
            const payload = {};

            resource.fields.forEach((field) => {
                if (field.type === 'switch') {
                    payload[field.name] = formData.has(field.name) ? 1 : 0;
                } else {
                    payload[field.name] = formData.get(field.name) || '';
                }
            });

            const isEdit = this.current.id !== null;
            const url = isEdit ? `${resource.endpoint}/${this.current.id}` : resource.endpoint;
            const method = isEdit ? 'PUT' : 'POST';

            try {
                const res = await Clinic.request(url, { method, body: JSON.stringify(payload) });
                bootstrap.Modal.getInstance(document.getElementById('orgModal')).hide();
                Clinic.toastSuccess(res.message);
                await this.refresh(this.current.key);
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
                text: 'Data yang dihapus dapat dipulihkan oleh administrator.',
                confirmText: 'Ya, hapus',
            });
            if (!confirmed) return;

            const resource = RESOURCES[resourceKey];
            try {
                const res = await Clinic.request(`${resource.endpoint}/${id}`, { method: 'DELETE' });
                Clinic.toastSuccess(res.message);
                await this.refresh(resourceKey);
            } catch (err) {
                Clinic.toastError(err.message);
            }
        },

        async refresh(resourceKey) {
            window.location.reload();
        },
    };

    document.getElementById('orgForm').addEventListener('submit', (e) => Org.submit(e));

    window.Org = Org;
})();
