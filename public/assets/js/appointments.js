(function () {
    'use strict';

    const searchInput = document.getElementById('patientSearch');
    const patientIdInput = document.getElementById('patientId');
    const resultsBox = document.getElementById('patientResults');
    const polyclinicSelect = document.getElementById('polyclinicSelect');
    const doctorSelect = document.getElementById('doctorSelect');

    let searchTimeout = null;

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            patientIdInput.value = '';
            clearTimeout(searchTimeout);
            const term = searchInput.value.trim();

            if (term.length < 2) {
                resultsBox.style.display = 'none';
                return;
            }

            searchTimeout = setTimeout(async () => {
                try {
                    const res = await Clinic.request(`/api/patients/search?q=${encodeURIComponent(term)}`);
                    renderResults(res.data);
                } catch (err) {
                    resultsBox.style.display = 'none';
                }
            }, 300);
        });

        document.addEventListener('click', (e) => {
            if (!resultsBox.contains(e.target) && e.target !== searchInput) {
                resultsBox.style.display = 'none';
            }
        });
    }

    function renderResults(patients) {
        if (!patients || patients.length === 0) {
            resultsBox.innerHTML = '<div class="list-group-item text-muted-custom small">Pasien tidak ditemukan.</div>';
            resultsBox.style.display = 'block';
            return;
        }

        resultsBox.innerHTML = patients.map((p) => `
            <button type="button" class="list-group-item list-group-item-action patient-option" data-id="${p.id}" data-label="${p.name} (${p.medical_record_number})">
                <div class="fw-semibold">${p.name}</div>
                <div class="text-caption">${p.medical_record_number} &bull; ${p.phone || '-'}</div>
            </button>
        `).join('');
        resultsBox.style.display = 'block';

        resultsBox.querySelectorAll('.patient-option').forEach((btn) => {
            btn.addEventListener('click', () => {
                patientIdInput.value = btn.dataset.id;
                searchInput.value = btn.dataset.label;
                resultsBox.style.display = 'none';
            });
        });
    }

    if (polyclinicSelect && doctorSelect) {
        polyclinicSelect.addEventListener('change', async () => {
            const polyclinicId = polyclinicSelect.value;
            if (!polyclinicId) return;

            try {
                const res = await Clinic.request(`/api/doctors/search?polyclinic_id=${polyclinicId}`);
                doctorSelect.innerHTML = '<option value="">Pilih Dokter</option>' +
                    res.data.map((d) => `<option value="${d.id}">${d.name}</option>`).join('');
            } catch (err) {
                Clinic.toastError('Gagal memuat daftar dokter.');
            }
        });
    }
})();
