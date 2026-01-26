<div class="my-4">
    <div class="card">
        <div class="card-header fw-bold d-flex justify-content-between align-items-center">
            Calculate Rate
            <button type="button" class="btn btn-light btn-sm" onclick="resetData()" id="resetCalculateRateBtn">Reset Data</button>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-sm table-striped">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Item</th>
                        <th>Total Item</th>
                        <th>Rate</th>
                        <th>Hasil</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="calculateRateBody"></tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Small UI tweaks: make action buttons consistent width
    const styleEl = document.createElement('style');
    styleEl.textContent = `
    .calculate-actions .btn {
        min-width: 84px;
        padding-left: .5rem;
        padding-right: .5rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    @media (max-width: 576px) {
        .calculate-actions .btn { min-width: 64px; font-size: .85rem; }
    }
    `;
    document.head.appendChild(styleEl);
    /* ===============================
       GLOBAL STATE
    ================================ */
    window.calculateRateData = [];
    window.editingIndex = undefined;
    window.checkedRevenues = new Set();
    window.currentInpItem = null;
    window.currentInpTotal = null;
    window.currentInpRate = null;
    window.currentInpHasil = null;
    window.currentHitung = null;

    window.updateFromCheckboxes = function() {
        if (!window.currentInpItem || !window.currentInpTotal || !window.currentHitung) return;
        let sumRevenue = 0;
        let itemList = Array.from(window.checkedRevenues);
        sumRevenue = itemList.reduce((sum, rev) => sum + rev, 0);
        window.currentInpItem.value = itemList.length > 0 ? itemList.join(' + ') : '';
        window.currentInpTotal.value = sumRevenue.toFixed(3);
        window.currentHitung();
    };

    function saveCalculateRateData() {
        try {
            localStorage.setItem('calculateRateData', JSON.stringify(window.calculateRateData || []));
        } catch (err) {
            console.error('saveCalculateRateData error', err);
        }
    }

    // Render the calculator summary table(s) (partial) if present — supports multiple instances
    function refreshCalculatorTable() {
        try {
            const bodies = Array.from(document.querySelectorAll('.calculatorTableBody'));
            if (!bodies.length) return;
            const data = window.calculateRateData || JSON.parse(localStorage.getItem('calculateRateData') || '[]');

            if (!data.length) {
                bodies.forEach(body => {
                    body.innerHTML = '<tr><td colspan="3">Tidak ada data</td></tr>';
                    const totalEl = body.closest('.card')?.querySelector('.calculatorTotal');
                    if (totalEl) totalEl.textContent = 'Rp0.00';
                });
                return;
            }

            bodies.forEach(body => {
                let html = '';
                for (let i = 0; i < data.length; i++) {
                    const row = data[i];
                    const nama = row.nama ?? '';
                    const hasil = Number(row.hasil || 0);
                    html += `<tr>` +
                        `<td><input type="checkbox" class="calculator-row-checkbox" data-idx="${i}"></td>` +
                        `<td>${escapeHtml(nama)}</td>` +
                        `<td>${formatHasil(hasil)}</td>` +
                        `</tr>`;
                }
                body.innerHTML = html;

                const totalEl = body.closest('.card')?.querySelector('.calculatorTotal');
                if (!totalEl) return;

                const checkboxes = Array.from(body.querySelectorAll('.calculator-row-checkbox'));
                function recalcForTable() {
                    let sumChecked = 0;
                    checkboxes.forEach(cb => {
                        const idx = Number(cb.getAttribute('data-idx'));
                        const checked = cb.checked;
                        const tr = cb.closest('tr');
                        if (tr) tr.classList.toggle('table-active', checked);
                        if (checked) {
                            const val = Number((data[idx] && data[idx].hasil) || 0);
                            sumChecked += val;
                        }
                    });
                    totalEl.textContent = formatHasil(sumChecked);
                }

                checkboxes.forEach(cb => cb.addEventListener('change', recalcForTable));
                // default: all unchecked => total 0
                totalEl.textContent = formatHasil(0);
            });
        } catch (e) {
            console.error('refreshCalculatorTable error', e);
        }
    }

    function loadCalculateRateData() {
        try {
            const raw = localStorage.getItem('calculateRateData');
            if (raw) window.calculateRateData = JSON.parse(raw) || [];
            // Migrate old data
            window.calculateRateData = window.calculateRateData.map(row => {
                if (!row.selectedRevenues || row.selectedRevenues.length === 0) {
                    if (row.item) {
                        row.selectedRevenues = row.item.split(' + ').map(s => parseFloat(s.replace(/,/g, '')) ||
                            0);
                    } else {
                        row.selectedRevenues = [];
                    }
                }
                return row;
            });
        } catch (err) {
            console.error('loadCalculateRateData error', err);
            window.calculateRateData = [];
        }
    }

    /* ===============================
       UTILS
    ================================ */
    function escapeHtml(str) {
        return String(str).replace(/[&<>\"']/g, s => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '\"': '&quot;',
            "'": '&#39;'
        })[s]);
    }

    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('Data berhasil disalin!');
        }).catch(err => {
            console.error('Failed to copy: ', err);
        });
    }

    function formatHasil(num) {
        return 'Rp' + num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    window.formatHasil = formatHasil;

    /* ===============================
       RENDER TABLE
    ================================ */
    function renderCalculateRate() {
        const tbody = document.getElementById('calculateRateBody');
        let html = '';

        console.log('Rendering, data length:', window.calculateRateData.length, 'data:', window.calculateRateData);

        if (!window.calculateRateData.length) {
            html = `
            <tr>
                <td colspan="6" class="text-center">
                    <button class="btn btn-primary btn-sm" onclick="addCalculateRateRow()">
                        Buat
                    </button>
                </td>
            </tr>
        `;
        } else {
            window.calculateRateData.forEach((row, i) => {
                html += `
                <tr>
                    <td>${escapeHtml(row.nama)}</td>
                    <td>${escapeHtml(row.item)}</td>
                    <td>${row.total_item}</td>
                    <td>${row.rate}</td>
                    <td>${formatHasil(row.hasil)}</td>
                            <td>
                                <div class="calculate-actions d-flex gap-1">
                                    <button class="btn btn-warning btn-sm" onclick="editRow(${i})">Edit</button>
                                    <button class="btn btn-info btn-sm" onclick="copyRowData(${i})">Salin</button>
                                    <button class="btn btn-danger btn-sm" onclick="removeRow(${i})">Hapus</button>
                                </div>
                            </td>
                </tr>
            `;
            });

            html += `
            <tr>
                <td colspan="6" class="text-end">
                    <button class="btn btn-primary btn-sm" onclick="addCalculateRateRow()">
                        Tambah
                    </button>
                </td>
            </tr>
        `;
        }

        tbody.innerHTML = html;
    }

    /* ===============================
       ADD ROW
    ================================ */
    function addCalculateRateRow() {
        const tbody = document.getElementById('calculateRateBody');
        tbody.innerHTML = '';

        window.isEditingCalculateRate = true;
        if (window.renderPage) window.renderPage();

        const tr = document.createElement('tr');
        tr.innerHTML = `
        <td><input class="form-control form-control-sm cr-nama" placeholder="Nama"></td>
        <td><input class="form-control form-control-sm cr-item" placeholder="Item" readonly></td>
        <td><input class="form-control form-control-sm cr-total" type="number" value="0" readonly></td>
        <td><input class="form-control form-control-sm cr-rate" type="number" step="any"></td>
        <td><input class="form-control form-control-sm cr-hasil" type="text" readonly></td>
        <td>
            <div class="calculate-actions d-flex gap-1">
                <button type="button" class="btn btn-success btn-sm">Simpan</button>
                <button type="button" class="btn btn-secondary btn-sm">Batal</button>
            </div>
        </td>
    `;
        tbody.appendChild(tr);

        const inpNama = tr.querySelector('.cr-nama');
        const inpItem = tr.querySelector('.cr-item');
        const inpTotal = tr.querySelector('.cr-total');
        const inpRate = tr.querySelector('.cr-rate');
        const inpHasil = tr.querySelector('.cr-hasil');

        window.currentInpItem = inpItem;
        window.currentInpTotal = inpTotal;
        window.currentInpRate = inpRate;
        window.currentInpHasil = inpHasil;

        const row = window.editingIndex !== undefined ? window.calculateRateData[window.editingIndex] : null;

        // Preload if editing
        if (row) {
            inpNama.value = row.nama;
            inpItem.value = row.item;
            inpTotal.value = row.total_item;
            inpRate.value = row.rate;
            hitung();
            window.checkedRevenues = new Set(row.selectedRevenues);
        } else {
            window.checkedRevenues = new Set();
        }

        function hitung() {
            const total = Number(inpTotal.value || 0);
            const rate = Number(inpRate.value || 0);
            const hasil = total * rate;
            const formatted = 'Rp' + hasil.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            inpHasil.value = formatted;
        }

        window.currentHitung = hitung;

        // Update inpItem and inpTotal saat checkbox berubah is handled by window.updateFromCheckboxes

        inpTotal.addEventListener('input', hitung);
        inpRate.addEventListener('input', hitung);
        hitung();

        tr.querySelector('.btn-secondary').onclick = () => {
            window.isEditingCalculateRate = false;
            window.editingIndex = undefined;
            window.selectedRevenues = [];
            window.checkedRevenues = new Set();
            window.currentInpItem = null;
            window.currentInpTotal = null;
            window.currentInpRate = null;
            window.currentInpHasil = null;
            window.currentHitung = null;
            if (window.renderPage) window.renderPage();
            renderCalculateRate();
        };

        tr.querySelector('.btn-success').onclick = () => {
            if (!inpNama.value.trim()) {
                alert('Nama wajib diisi');
                return;
            }

            // Hitung sum Revenue dan buat keterangan item dari checkbox yang dicentang
            let sumRevenue = 0;
            let itemList = Array.from(window.checkedRevenues);
            sumRevenue = itemList.reduce((sum, rev) => sum + rev, 0);

            // Set Item sebagai keterangan "rev1 + rev2 + ...", Total Item sebagai sum dengan pembulatan 3 desimal
            inpItem.value = itemList.length > 0 ? itemList.join(' + ') : '';
            inpTotal.value = sumRevenue.toFixed(3);
            hitung(); // Update hasil

            if (window.editingIndex !== undefined) {
                window.calculateRateData[window.editingIndex] = {
                    nama: inpNama.value.trim(),
                    item: inpItem.value.trim(),
                    total_item: Number(inpTotal.value),
                    rate: Number(inpRate.value),
                    hasil: Number(inpTotal.value) * Number(inpRate.value),
                    selectedRevenues: itemList
                };
            } else {
                window.calculateRateData.push({
                    nama: inpNama.value.trim(),
                    item: inpItem.value.trim(),
                    total_item: Number(inpTotal.value),
                    rate: Number(inpRate.value),
                    hasil: Number(inpTotal.value) * Number(inpRate.value),
                    selectedRevenues: itemList
                });
            }

            console.log('Data saved, current data:', window.calculateRateData);

            saveCalculateRateData();

            renderCalculateRate();

            if (window.refreshCalculatorTable) window.refreshCalculatorTable();

            window.isEditingCalculateRate = false;
            window.editingIndex = undefined;
            window.selectedRevenues = [];
            window.checkedRevenues = new Set();
            window.currentInpItem = null;
            window.currentInpTotal = null;
            window.currentInpRate = null;
            window.currentInpHasil = null;
            window.currentHitung = null;
            if (window.renderPage) window.renderPage();
        };
    }

    /* ===============================
       EDIT ROW
    ================================ */
    function editRow(i) {
        window.editingIndex = i;
        const row = window.calculateRateData[i];
        window.selectedRevenues = row.selectedRevenues || [];
        addCalculateRateRow();
    }

    /* ===============================
       COPY ROW DATA
    ================================ */
    function copyRowData(i) {
        const row = window.calculateRateData[i];
        const text = `${row.nama} : ${row.item} = ${row.total_item} x ${row.rate} = ${formatHasil(row.hasil)}`;
        copyToClipboard(text);
    }

    /* ===============================
       REMOVE ROW
    ================================ */
    function removeRow(i) {
        window.calculateRateData.splice(i, 1);
        saveCalculateRateData();
        renderCalculateRate();
        if (window.refreshCalculatorTable) window.refreshCalculatorTable();
    }

    /* ===============================
       RESET DATA
    ================================ */
    function resetData() {
        if (confirm('Yakin ingin menghapus semua data?')) {
            window.calculateRateData = [];
            saveCalculateRateData();
            renderCalculateRate();
            if (window.refreshCalculatorTable) window.refreshCalculatorTable();
        }
    }

    /* ===============================
       INIT
    ================================ */
    document.addEventListener('DOMContentLoaded', () => {
        window.isEditingCalculateRate = false;
        window.editingIndex = undefined;
        window.selectedRevenues = [];
        window.checkedRevenues = new Set();
        loadCalculateRateData();
        renderCalculateRate();
        if (window.refreshCalculatorTable) window.refreshCalculatorTable();
    });
</script>
