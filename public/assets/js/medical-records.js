(function () {
    'use strict';

    const recordId = window.MEDICAL_RECORD_ID;
    const base = `/medical-records/${recordId}`;

    function formToPayload(form) {
        return Object.fromEntries(new FormData(form).entries());
    }

    async function submitAndReload(url, payload, submitBtn) {
        try {
            const res = await Clinic.request(url, { method: 'POST', body: JSON.stringify(payload) });
            Clinic.toastSuccess(res.message);
            window.location.reload();
        } catch (err) {
            const firstError = err.payload && err.payload.data && err.payload.data.errors
                ? Object.values(err.payload.data.errors)[0][0]
                : err.message;
            Clinic.toastError(firstError);
            if (submitBtn) Clinic.resetButton(submitBtn);
        }
    }

    function bindSimpleForm(formId, endpoint) {
        const form = document.getElementById(formId);
        if (!form) return;
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            submitAndReload(endpoint, formToPayload(form), form.querySelector('[type="submit"]'));
        });
    }

    bindSimpleForm('vitalsForm', `${base}/vitals`);
    bindSimpleForm('anamnesisForm', `${base}/anamnesis`);
    bindSimpleForm('physicalExamForm', `${base}/physical-examination`);
    bindSimpleForm('noteForm', `${base}/notes`);

    function setupSearch(inputId, resultsId, hiddenId, endpoint, renderLabel) {
        const input = document.getElementById(inputId);
        const results = document.getElementById(resultsId);
        const hidden = document.getElementById(hiddenId);
        if (!input) return;

        let timeout = null;

        input.addEventListener('input', () => {
            hidden.value = '';
            clearTimeout(timeout);
            const term = input.value.trim();

            if (term.length < 1) {
                results.style.display = 'none';
                return;
            }

            timeout = setTimeout(async () => {
                try {
                    const res = await Clinic.request(`${endpoint}?q=${encodeURIComponent(term)}`);
                    renderSearchResults(res.data, results, input, hidden, renderLabel);
                } catch (e) {
                    results.style.display = 'none';
                }
            }, 300);
        });

        document.addEventListener('click', (e) => {
            if (!results.contains(e.target) && e.target !== input) {
                results.style.display = 'none';
            }
        });
    }

    function renderSearchResults(items, container, input, hidden, renderLabel) {
        if (!items || items.length === 0) {
            container.innerHTML = '<div class="list-group-item text-muted-custom small">Tidak ditemukan.</div>';
            container.style.display = 'block';
            return;
        }

        container.innerHTML = items.map((item, i) => `
            <button type="button" class="list-group-item list-group-item-action result-option" data-index="${i}">
                ${renderLabel(item)}
            </button>
        `).join('');
        container.style.display = 'block';

        container.querySelectorAll('.result-option').forEach((btn, i) => {
            btn.addEventListener('click', () => {
                const item = items[i];
                hidden.value = item.id;
                input.value = renderLabel(item).replace(/<[^>]*>/g, ' ').trim();
                container.style.display = 'none';
            });
        });
    }

    setupSearch('icd10Search', 'icd10Results', 'icd10CodeId', '/api/icd10/search',
        (item) => `<div class="fw-semibold">${item.code}</div><div class="text-caption">${item.description}</div>`);

    setupSearch('actionSearch', 'actionResults', 'medicalActionId', '/api/medical-actions/search',
        (item) => `<div class="fw-semibold">${item.name}</div><div class="text-caption">${item.code} &bull; Rp${Number(item.price).toLocaleString('id-ID')}</div>`);

    document.getElementById('diagnosisForm')?.addEventListener('submit', (e) => {
        e.preventDefault();
        const form = e.target;
        if (!document.getElementById('icd10CodeId').value) {
            Clinic.toastError('Pilih kode ICD-10 dari daftar pencarian.');
            return;
        }
        submitAndReload(`${base}/diagnoses`, formToPayload(form), form.querySelector('[type="submit"]'));
    });

    document.getElementById('actionForm')?.addEventListener('submit', (e) => {
        e.preventDefault();
        const form = e.target;
        if (!document.getElementById('medicalActionId').value) {
            Clinic.toastError('Pilih tindakan dari daftar pencarian.');
            return;
        }
        submitAndReload(`${base}/actions`, formToPayload(form), form.querySelector('[type="submit"]'));
    });

    (function initLabTestSelector() {
        const searchInput = document.getElementById('labTestSearch');
        const resultsBox = document.getElementById('labTestResults');
        const selectedBox = document.getElementById('selectedLabTests');
        const form = document.getElementById('labRequestForm');
        if (!searchInput) return;

        const selected = new Map();
        let timeout = null;

        function renderSelected() {
            selectedBox.innerHTML = Array.from(selected.values()).map((item) => `
                <span class="badge-status badge-info">
                    ${item.name}
                    <input type="hidden" name="lab_test_ids[]" value="${item.id}">
                    <button type="button" class="btn-close btn-close-white ms-1" style="font-size:.6rem;" data-remove="${item.id}"></button>
                </span>
            `).join('');

            selectedBox.querySelectorAll('[data-remove]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    selected.delete(btn.dataset.remove);
                    renderSelected();
                });
            });
        }

        searchInput.addEventListener('input', () => {
            clearTimeout(timeout);
            const term = searchInput.value.trim();
            if (term.length < 1) {
                resultsBox.style.display = 'none';
                return;
            }

            timeout = setTimeout(async () => {
                try {
                    const res = await Clinic.request(`/api/lab-tests/search?q=${encodeURIComponent(term)}`);
                    const items = res.data || [];
                    resultsBox.innerHTML = items.length === 0
                        ? '<div class="list-group-item text-muted-custom small">Tidak ditemukan.</div>'
                        : items.map((item, i) => `
                            <button type="button" class="list-group-item list-group-item-action lab-test-option" data-index="${i}">
                                <div class="fw-semibold">${item.name}</div>
                                <div class="text-caption">${item.code} &bull; Rp${Number(item.price).toLocaleString('id-ID')}</div>
                            </button>
                        `).join('');
                    resultsBox.style.display = 'block';

                    resultsBox.querySelectorAll('.lab-test-option').forEach((btn, i) => {
                        btn.addEventListener('click', () => {
                            const item = items[i];
                            selected.set(String(item.id), item);
                            renderSelected();
                            searchInput.value = '';
                            resultsBox.style.display = 'none';
                        });
                    });
                } catch (e) {
                    resultsBox.style.display = 'none';
                }
            }, 300);
        });

        document.addEventListener('click', (e) => {
            if (!resultsBox.contains(e.target) && e.target !== searchInput) {
                resultsBox.style.display = 'none';
            }
        });

        form?.addEventListener('submit', (e) => {
            e.preventDefault();
            if (selected.size === 0) {
                Clinic.toastError('Pilih minimal satu jenis pemeriksaan.');
                return;
            }

            const payload = {
                medical_record_id: recordId,
                lab_test_ids: Array.from(selected.keys()),
            };
            submitAndReload('/lab-requests', payload, form.querySelector('[type="submit"]'));
        });
    })();

    const Exam = {
        async removeDiagnosis(id) {
            const confirmed = await Clinic.confirmDialog({ title: 'Hapus diagnosis ini?', confirmText: 'Ya, hapus' });
            if (!confirmed) return;
            try {
                const res = await Clinic.request(`${base}/diagnoses/${id}/delete`, { method: 'POST' });
                Clinic.toastSuccess(res.message);
                window.location.reload();
            } catch (err) {
                Clinic.toastError(err.message);
            }
        },

        async removeAction(id) {
            const confirmed = await Clinic.confirmDialog({ title: 'Hapus tindakan ini?', confirmText: 'Ya, hapus' });
            if (!confirmed) return;
            try {
                const res = await Clinic.request(`${base}/actions/${id}/delete`, { method: 'POST' });
                Clinic.toastSuccess(res.message);
                window.location.reload();
            } catch (err) {
                Clinic.toastError(err.message);
            }
        },

        async finalize() {
            const confirmed = await Clinic.confirmDialog({
                title: 'Finalisasi rekam medis ini?',
                text: 'Setelah difinalisasi, perubahan hanya dapat dilakukan melalui amendemen.',
                confirmText: 'Ya, finalisasi',
            });
            if (!confirmed) return;
            try {
                const res = await Clinic.request(`${base}/finalize`, { method: 'POST' });
                Clinic.toastSuccess(res.message);
                window.location.reload();
            } catch (err) {
                Clinic.toastError(err.message);
            }
        },

        openAmend(section) {
            document.getElementById('amendSection').value = section;
            const title = section === 'anamnesis' ? 'Amendemen Anamnesis' : 'Amendemen Pemeriksaan Fisik';
            document.getElementById('amendModalTitle').textContent = title;

            const current = section === 'anamnesis' ? window.CURRENT_ANAMNESIS : window.CURRENT_PHYSICAL_EXAM;
            const fields = section === 'anamnesis'
                ? [
                    { name: 'chief_complaint', label: 'Keluhan Utama', type: 'text' },
                    { name: 'present_illness_history', label: 'Riwayat Penyakit Sekarang', type: 'textarea' },
                    { name: 'past_medical_history', label: 'Riwayat Penyakit Dahulu', type: 'textarea' },
                    { name: 'notes', label: 'Catatan Tambahan', type: 'textarea' },
                ]
                : [
                    { name: 'general_condition', label: 'Keadaan Umum', type: 'text' },
                    { name: 'consciousness', label: 'Kesadaran', type: 'text' },
                    { name: 'head_neck', label: 'Kepala & Leher', type: 'textarea' },
                    { name: 'chest', label: 'Dada', type: 'textarea' },
                    { name: 'abdomen', label: 'Abdomen', type: 'textarea' },
                    { name: 'extremities', label: 'Ekstremitas', type: 'textarea' },
                    { name: 'notes', label: 'Catatan', type: 'textarea' },
                ];

            const fieldsHtml = fields.map((f) => {
                const value = current ? (current[f.name] || '') : '';
                return f.type === 'textarea'
                    ? `<div class="mb-3"><label class="form-label">${f.label}</label><textarea class="form-control" name="${f.name}" rows="2">${value}</textarea></div>`
                    : `<div class="mb-3"><label class="form-label">${f.label}</label><input type="text" class="form-control" name="${f.name}" value="${value}"></div>`;
            }).join('');

            document.getElementById('amendModalBody').innerHTML = fieldsHtml + `
                <div class="mb-1">
                    <label class="form-label form-label-required">Alasan Amendemen</label>
                    <textarea class="form-control" name="reason" rows="2" required placeholder="Jelaskan alasan perubahan data ini"></textarea>
                </div>`;

            bootstrap.Modal.getOrCreateInstance(document.getElementById('amendModal')).show();
        },
    };

    document.getElementById('amendForm')?.addEventListener('submit', (e) => {
        e.preventDefault();
        const form = e.target;
        submitAndReload(`${base}/amend`, formToPayload(form), form.querySelector('[type="submit"]'));
    });

    window.Exam = Exam;
})();
