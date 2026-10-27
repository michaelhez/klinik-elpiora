(function () {
    'use strict';

    const submitBtn = document.getElementById('poSubmitBtn');
    if (!submitBtn) return;

    const items = [];
    let selectedMedicine = null;

    function formatCurrency(value) {
        return 'Rp ' + Number(value || 0).toLocaleString('id-ID');
    }

    const searchInput = document.getElementById('poMedicineSearch');
    const results = document.getElementById('poMedicineResults');
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
                    btn.addEventListener('click', () => {
                        selectedMedicine = data[i];
                        searchInput.value = selectedMedicine.name;
                        document.getElementById('poPurchasePrice').value = selectedMedicine.purchase_price || 0;
                        results.style.display = 'none';
                    });
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

    document.getElementById('poAddItemBtn').addEventListener('click', () => {
        if (!selectedMedicine) {
            Clinic.toastError('Pilih obat terlebih dahulu.');
            return;
        }

        const quantity = parseInt(document.getElementById('poQuantity').value, 10) || 1;
        const price = parseFloat(document.getElementById('poPurchasePrice').value) || 0;

        items.push({ medicine_id: selectedMedicine.id, name: selectedMedicine.name, quantity_ordered: quantity, purchase_price: price });

        renderItems();
        selectedMedicine = null;
        searchInput.value = '';
        document.getElementById('poQuantity').value = '1';
        document.getElementById('poPurchasePrice').value = '';
    });

    function renderItems() {
        const list = document.getElementById('poItemList');
        list.innerHTML = items.map((item, i) => `
            <div class="d-flex justify-content-between align-items-center border rounded-2 px-2 py-1 mb-1">
                <div class="small"><strong>${item.name}</strong> &times; ${item.quantity_ordered} @ ${formatCurrency(item.purchase_price)}</div>
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-semibold small">${formatCurrency(item.quantity_ordered * item.purchase_price)}</span>
                    <button type="button" class="btn btn-sm btn-outline-danger py-0" data-remove-item="${i}"><i class="bi bi-x"></i></button>
                </div>
            </div>
        `).join('');

        list.querySelectorAll('[data-remove-item]').forEach((btn) => {
            btn.addEventListener('click', () => {
                items.splice(Number(btn.dataset.removeItem), 1);
                renderItems();
            });
        });

        const total = items.reduce((sum, item) => sum + (item.quantity_ordered * item.purchase_price), 0);
        document.getElementById('poItemTotal').textContent = 'Total: ' + formatCurrency(total);
    }

    submitBtn.addEventListener('click', async () => {
        const supplierId = document.getElementById('poSupplier').value;
        const branchId = document.getElementById('poBranch').value;

        if (!supplierId || !branchId) {
            Clinic.toastError('Pilih pemasok dan cabang terlebih dahulu.');
            return;
        }

        if (items.length === 0) {
            Clinic.toastError('Tambahkan minimal satu item obat.');
            return;
        }

        const payload = {
            supplier_id: supplierId,
            branch_id: branchId,
            order_date: document.getElementById('poOrderDate').value,
            expected_date: document.getElementById('poExpectedDate').value,
            notes: document.getElementById('poNotes').value,
            items: items.map(({ medicine_id, quantity_ordered, purchase_price }) => ({ medicine_id, quantity_ordered, purchase_price })),
        };

        Clinic.setButtonLoading(submitBtn, 'Menyimpan...');

        try {
            const res = await Clinic.request('/purchase-orders', { method: 'POST', body: JSON.stringify(payload) });
            Clinic.toastSuccess(res.message);
            window.location.href = `/purchase-orders/${res.data.id}`;
        } catch (err) {
            const firstError = err.payload && err.payload.data && err.payload.data.errors
                ? Object.values(err.payload.data.errors)[0][0]
                : err.message;
            Clinic.toastError(firstError);
            Clinic.resetButton(submitBtn);
        }
    });
})();
