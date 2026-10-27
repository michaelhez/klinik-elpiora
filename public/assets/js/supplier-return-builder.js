(function () {
    'use strict';

    const submitBtn = document.getElementById('srSubmitBtn');
    if (!submitBtn) return;

    const items = [];
    let selectedMedicine = null;
    let availableBatches = [];

    const searchInput = document.getElementById('srMedicineSearch');
    const results = document.getElementById('srMedicineResults');
    const batchSelect = document.getElementById('srBatchSelect');
    let timeout = null;

    searchInput.addEventListener('input', () => {
        clearTimeout(timeout);
        const term = searchInput.value.trim();
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
                    btn.addEventListener('click', () => selectMedicine(data[i]));
                });
            } catch (e) {
                results.style.display = 'none';
            }
        }, 300);
    });

    document.addEventListener('click', (e) => {
        if (!results.contains(e.target) && e.target !== searchInput) {
            results.style.display = 'none';
        }
    });

    async function selectMedicine(medicine) {
        selectedMedicine = medicine;
        searchInput.value = medicine.name;
        results.style.display = 'none';

        batchSelect.disabled = true;
        batchSelect.innerHTML = '<option value="">Memuat batch...</option>';

        try {
            const res = await Clinic.request(`/api/medicines/${medicine.id}/batches`);
            availableBatches = res.data || [];

            batchSelect.innerHTML = availableBatches.length === 0
                ? '<option value="">Tidak ada stok tersedia</option>'
                : '<option value="">-- Pilih Batch --</option>' + availableBatches.map((b) => (
                    `<option value="${b.id}">${b.batch_number} - ${b.branch_name} (Stok: ${b.quantity}, Exp: ${b.expired_date})</option>`
                )).join('');

            batchSelect.disabled = availableBatches.length === 0;
        } catch (e) {
            batchSelect.innerHTML = '<option value="">Gagal memuat batch</option>';
        }
    }

    document.getElementById('srAddItemBtn').addEventListener('click', () => {
        const batchId = batchSelect.value;
        if (!selectedMedicine || !batchId) {
            Clinic.toastError('Pilih obat dan batch terlebih dahulu.');
            return;
        }

        const batch = availableBatches.find((b) => String(b.id) === String(batchId));
        const quantity = parseInt(document.getElementById('srQuantity').value, 10) || 1;

        if (quantity > Number(batch.quantity)) {
            Clinic.toastError('Jumlah retur melebihi stok batch tersebut.');
            return;
        }

        items.push({
            medicine_batch_id: batch.id,
            name: `${selectedMedicine.name} (${batch.batch_number})`,
            quantity,
            notes: document.getElementById('srItemNotes').value,
        });

        renderItems();
        selectedMedicine = null;
        availableBatches = [];
        searchInput.value = '';
        batchSelect.innerHTML = '<option value="">-- Pilih obat terlebih dahulu --</option>';
        batchSelect.disabled = true;
        document.getElementById('srQuantity').value = '1';
        document.getElementById('srItemNotes').value = '';
    });

    function renderItems() {
        const list = document.getElementById('srItemList');
        list.innerHTML = items.map((item, i) => `
            <div class="d-flex justify-content-between align-items-center border rounded-2 px-2 py-1 mb-1">
                <div class="small"><strong>${item.name}</strong> &times; ${item.quantity} ${item.notes ? '(' + item.notes + ')' : ''}</div>
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

    submitBtn.addEventListener('click', async () => {
        const supplierId = document.getElementById('srSupplier').value;
        const reason = document.getElementById('srReason').value.trim();

        if (!supplierId || !reason) {
            Clinic.toastError('Pilih pemasok dan isi alasan retur.');
            return;
        }

        if (items.length === 0) {
            Clinic.toastError('Tambahkan minimal satu item obat.');
            return;
        }

        const payload = {
            supplier_id: supplierId,
            reason,
            items: items.map(({ medicine_batch_id, quantity, notes }) => ({ medicine_batch_id, quantity, notes })),
        };

        Clinic.setButtonLoading(submitBtn, 'Menyimpan...');

        try {
            const res = await Clinic.request('/supplier-returns', { method: 'POST', body: JSON.stringify(payload) });
            Clinic.toastSuccess(res.message);
            window.location.href = `/supplier-returns/${res.data.id}`;
        } catch (err) {
            const firstError = err.payload && err.payload.data && err.payload.data.errors
                ? Object.values(err.payload.data.errors)[0][0]
                : err.message;
            Clinic.toastError(firstError);
            Clinic.resetButton(submitBtn);
        }
    });
})();
