(function () {
    'use strict';

    const docForm = document.getElementById('docForm');
    if (!docForm) return;

    const recordId = window.MEDICAL_RECORD_ID;

    const FORMS = {
        SICK_NOTE: {
            title: 'Terbitkan Surat Keterangan Sakit',
            fields: [
                { name: 'start_date', label: 'Tanggal Mulai Istirahat', type: 'date', required: true, default: () => new Date().toISOString().slice(0, 10) },
                { name: 'rest_days', label: 'Lama Istirahat (hari)', type: 'number', required: true, default: () => '1' },
                { name: 'notes', label: 'Catatan', type: 'textarea' },
            ],
        },
        REFERRAL_LETTER: {
            title: 'Terbitkan Surat Rujukan',
            fields: [
                { name: 'destination_facility', label: 'Fasilitas Tujuan', type: 'text', required: true },
                { name: 'reason', label: 'Alasan Rujukan', type: 'textarea', required: true },
            ],
        },
        MEDICAL_CERTIFICATE: {
            title: 'Terbitkan Surat Keterangan Sehat',
            fields: [
                { name: 'purpose', label: 'Keperluan', type: 'text', required: true, placeholder: 'Contoh: Melamar pekerjaan' },
                { name: 'notes', label: 'Catatan', type: 'textarea' },
            ],
        },
    };

    function fieldHtml(field) {
        const requiredAttr = field.required ? 'required' : '';
        const labelClass = field.required ? 'form-label form-label-required' : 'form-label';
        const value = field.default ? field.default() : '';
        const placeholder = field.placeholder ? `placeholder="${field.placeholder}"` : '';

        if (field.type === 'textarea') {
            return `<div class="mb-3">
                <label class="${labelClass}">${field.label}</label>
                <textarea class="form-control" name="${field.name}" rows="2" ${requiredAttr}>${value}</textarea>
            </div>`;
        }

        return `<div class="mb-3">
            <label class="${labelClass}">${field.label}</label>
            <input type="${field.type}" class="form-control" name="${field.name}" value="${value}" ${placeholder} ${requiredAttr}>
        </div>`;
    }

    const Doc = {
        current: null,

        openModal(type) {
            const form = FORMS[type];
            this.current = type;

            document.getElementById('docType').value = type;
            document.getElementById('docModalTitle').textContent = form.title;
            document.getElementById('docModalBody').innerHTML = form.fields.map(fieldHtml).join('');

            bootstrap.Modal.getOrCreateInstance(document.getElementById('docModal')).show();
        },
    };

    docForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(docForm);
        const payload = Object.fromEntries(formData.entries());

        try {
            const res = await Clinic.request(`/medical-records/${recordId}/documents`, { method: 'POST', body: JSON.stringify(payload) });
            Clinic.toastSuccess(res.message);
            window.open(`/medical-documents/${res.data.id}/pdf`, '_blank');
            window.location.reload();
        } catch (err) {
            const firstError = err.payload && err.payload.data && err.payload.data.errors
                ? Object.values(err.payload.data.errors)[0][0]
                : err.message;
            Clinic.toastError(firstError);
            Clinic.resetButton(docForm.querySelector('[type="submit"]'));
        }
    });

    window.Doc = Doc;
})();
