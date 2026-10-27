(function () {
    'use strict';

    const rxForm = document.getElementById('rxSubmitBtn');
    if (!rxForm) return;

    const recordId = window.MEDICAL_RECORD_ID;
    const items = [];
    const compounds = [];
    let currentCompoundItems = [];
    let selectedCompoundMedicine = null;

    function setupSearch(inputId, resultsId, onSelect) {
        const input = document.getElementById(inputId);
        const results = document.getElementById(resultsId);
        if (!input) return;

        let timeout = null;
        input.addEventListener('input', () => {
            clearTimeout(timeout);
            const term = input.value.trim();
            if (term.length < 1) {
                results.style.display = 'none';
                return;
            }

            timeout = setTimeout(async () => {
                try {
                    const res = await Clinic.request(`/api/medicines/search?q=${encodeURIComponent(term)}`);
                    const data = res.data || [];
                    results.innerHTML = data.length === 0
                        ? '<div class="list-group-item text-muted-custom small">Tidak ditemukan.</div>'
                        : data.map((item, i) => `
                            <button type="button" class="list-group-item list-group-item-action" data-index="${i}">
                                <div class="fw-semibold">${item.name}</div>
                                <div class="text-caption">${item.code}${item.generic_name ? ' &bull; ' + item.generic_name : ''}</div>
                            </button>
                        `).join('');
                    results.style.display = 'block';

                    results.querySelectorAll('button').forEach((btn, i) => {
                        btn.addEventListener('click', () => {
                            onSelect(data[i]);
                            results.style.display = 'none';
                        });
                    });
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

    let selectedMedicine = null;
    setupSearch('rxMedicineSearch', 'rxMedicineResults', (medicine) => {
        selectedMedicine = medicine;
        document.getElementById('rxMedicineSearch').value = medicine.name;
    });

    document.getElementById('rxAddItemBtn').addEventListener('click', () => {
        if (!selectedMedicine) {
            Clinic.toastError('Pilih obat terlebih dahulu.');
            return;
        }

        const quantity = parseInt(document.getElementById('rxQuantity').value, 10) || 1;
        items.push({
            medicine_id: selectedMedicine.id,
            name: selectedMedicine.name,
            dosage: document.getElementById('rxDosage').value,
            quantity,
            instructions: document.getElementById('rxInstructions').value,
        });

        renderItems();
        selectedMedicine = null;
        document.getElementById('rxMedicineSearch').value = '';
        document.getElementById('rxDosage').value = '';
        document.getElementById('rxQuantity').value = '1';
        document.getElementById('rxInstructions').value = '';
    });

    function renderItems() {
        const list = document.getElementById('rxItemList');
        list.innerHTML = items.map((item, i) => `
            <div class="d-flex justify-content-between align-items-center border rounded-2 px-2 py-1 mb-1">
                <div class="small"><strong>${item.name}</strong> &mdash; ${item.dosage || '-'} &times; ${item.quantity} ${item.instructions ? '(' + item.instructions + ')' : ''}</div>
                <button type="button" class="btn btn-sm btn-outline-danger py-0" data-remove-item="${i}"><i class="bi bi-x"></i></button>
            </div>
        `).join('');

        list.querySelectorAll('[data-remove-item]').forEach((btn) => {
            btn.addEventListener('click', () => {
                items.splice(Number(btn.dataset.removeItem), 1);
                renderItems();
            });
        });
    }

    setupSearch('rxCompoundMedicineSearch', 'rxCompoundMedicineResults', (medicine) => {
        selectedCompoundMedicine = medicine;
        document.getElementById('rxCompoundSelectedMedicine').textContent = medicine.name;
        document.getElementById('rxCompoundMedicineSearch').value = '';
    });

    document.getElementById('rxAddCompoundItemBtn').addEventListener('click', () => {
        const qtyPerDose = parseFloat(document.getElementById('rxCompoundQtyPerDose').value);
        if (!selectedCompoundMedicine || !qtyPerDose) {
            Clinic.toastError('Pilih obat dan isi jumlah per bungkus.');
            return;
        }

        currentCompoundItems.push({ medicine_id: selectedCompoundMedicine.id, name: selectedCompoundMedicine.name, quantity_per_dose: qtyPerDose });
        renderCompoundItems();
        selectedCompoundMedicine = null;
        document.getElementById('rxCompoundSelectedMedicine').textContent = 'Belum memilih obat';
        document.getElementById('rxCompoundQtyPerDose').value = '';
    });

    function renderCompoundItems() {
        const list = document.getElementById('rxCompoundItemList');
        list.innerHTML = currentCompoundItems.map((item, i) => `
            <div class="d-flex justify-content-between align-items-center border rounded-2 px-2 py-1 mb-1">
                <div class="small">${item.name} &mdash; ${item.quantity_per_dose}/bungkus</div>
                <button type="button" class="btn btn-sm btn-outline-danger py-0" data-remove-comp-item="${i}"><i class="bi bi-x"></i></button>
            </div>
        `).join('');

        list.querySelectorAll('[data-remove-comp-item]').forEach((btn) => {
            btn.addEventListener('click', () => {
                currentCompoundItems.splice(Number(btn.dataset.removeCompItem), 1);
                renderCompoundItems();
            });
        });
    }

    document.getElementById('rxAddCompoundBtn').addEventListener('click', () => {
        const name = document.getElementById('rxCompoundName').value.trim();
        const quantity = parseInt(document.getElementById('rxCompoundQuantity').value, 10) || 1;

        if (!name || currentCompoundItems.length === 0) {
            Clinic.toastError('Isi nama racikan dan minimal satu komposisi obat.');
            return;
        }

        compounds.push({
            compound_name: name,
            dosage_instructions: document.getElementById('rxCompoundDosage').value,
            quantity,
            items: currentCompoundItems.map(({ medicine_id, quantity_per_dose }) => ({ medicine_id, quantity_per_dose })),
            _display: currentCompoundItems.map((i) => i.name).join(', '),
        });

        renderCompounds();
        currentCompoundItems = [];
        renderCompoundItems();
        document.getElementById('rxCompoundName').value = '';
        document.getElementById('rxCompoundDosage').value = '';
        document.getElementById('rxCompoundQuantity').value = '1';
    });

    function renderCompounds() {
        const list = document.getElementById('rxCompoundList');
        list.innerHTML = compounds.map((c, i) => `
            <div class="d-flex justify-content-between align-items-center border rounded-2 px-2 py-1 mb-1">
                <div class="small"><strong>${c.compound_name}</strong> &times; ${c.quantity} bungkus &mdash; ${c._display}</div>
                <button type="button" class="btn btn-sm btn-outline-danger py-0" data-remove-compound="${i}"><i class="bi bi-x"></i></button>
            </div>
        `).join('');

        list.querySelectorAll('[data-remove-compound]').forEach((btn) => {
            btn.addEventListener('click', () => {
                compounds.splice(Number(btn.dataset.removeCompound), 1);
                renderCompounds();
            });
        });
    }

    document.getElementById('rxSubmitBtn').addEventListener('click', async () => {
        if (items.length === 0 && compounds.length === 0) {
            Clinic.toastError('Tambahkan minimal satu obat atau racikan.');
            return;
        }

        const payload = {
            medical_record_id: recordId,
            items: items.map(({ medicine_id, dosage, quantity, instructions }) => ({ medicine_id, dosage, quantity, instructions })),
            compounds: compounds.map(({ compound_name, dosage_instructions, quantity, items: compItems }) => ({ compound_name, dosage_instructions, quantity, items: compItems })),
        };

        const btn = document.getElementById('rxSubmitBtn');
        Clinic.setButtonLoading(btn, 'Mengirim...');

        try {
            const res = await Clinic.request('/prescriptions', { method: 'POST', body: JSON.stringify(payload) });
            Clinic.toastSuccess(res.message);
            window.location.reload();
        } catch (err) {
            Clinic.toastError(err.message);
            Clinic.resetButton(btn);
        }
    });
})();
