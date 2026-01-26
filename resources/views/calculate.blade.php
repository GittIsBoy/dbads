<!doctype html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Calculate</title>
    <link rel="icon" href="/Image/logo.jpg" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
        <style>
            body { color: white !important; }
            @media (max-width: 767.98px) {
                body { padding-top: 56px; }
            }
      .card { color: white; }
      .table { color: white; }
      .table td, .table th { color: white !important; }
      .card-header { background-color: #8A244B; color: white; }
      .btn-primary { background-color: #8A244B; border-color: #8A244B; transition: background-color 0.3s ease; }
      .btn-primary:hover { background-color: #6A1A37; border-color: #6A1A37; }
      .form-control { color: white; }
      .page-link { color: white; background-color: #8A244B; border-color: #8A244B; }
      .page-link:hover { color: white; background-color: #6A1A37; border-color: #6A1A37; }
      .page-item.disabled .page-link { color: #ccc; background-color: #8A244B; }
      #resetCalculateRateBtn { background-color: white; color: red; border-color: white; }
      #resetCalculateRateBtn:hover { background-color: red; color: white; border-color: red; }
      #resetCalculateRateBtn:active { background-color: red; color: white; }
    </style>
</head>

<body>
        <nav class="navbar navbar-dark bg-dark fixed-top d-md-none">
            <div class="container-fluid">
                <button class="btn btn-outline-light d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar" aria-label="Toggle sidebar">
                    <i class="bi bi-list"></i>
                </button>
                <a class="navbar-brand ms-2" href="/">
                    <img src="/Image/logo.jpg" alt="Logo" width="30" height="30" class="d-inline-block align-text-top rounded-circle">
                    <span class="ms-2">DBADS</span>
                </a>
            </div>
        </nav>
        <div class="container-fluid">
        <div class="row">
                        @include('sidebar')
                        <main class="col-md-10 ms-sm-auto px-md-4 py-4">
                <h1 class="mb-4">Calculate</h1>
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">Filter</div>
                            <div class="card-body">
                                <div class="row g-2 align-items-end">
                                    <div class="col-auto">
                                        <label class="form-label">Start</label>
                                        <input type="date" id="filterStart" name="start_date" class="form-control"
                                            value="{{ request('start_date') }}">
                                    </div>
                                    <div class="col-auto">
                                        <label class="form-label">Finish</label>
                                        <input type="date" id="filterFinish" name="finish_date" class="form-control"
                                            value="{{ request('finish_date') ?? request('end_date') }}">
                                    </div>
                                    <div class="col-auto">
                                        <label for="filter_type" class="form-label">Filter by</label>
                                        <select class="form-select" id="filter_type" name="filter_type">
                                            <option value="" {{ request('filter_type') == '' ? '' : '' }}>-- None
                                                --
                                            </option>
                                            <option value="domain"
                                                {{ request('filter_type') == '' || request('filter_type') == 'domain' ? 'selected' : '' }}>
                                                Domain</option>
                                            <option value="placement"
                                                {{ request('filter_type') == 'placement' ? 'selected' : '' }}>Placement
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-auto">
                                        <label for="filterValue" class="form-label">Value</label>
                                        <select id="filterValue" class="form-select">
                                            <option value="">-- All --</option>
                                        </select>
                                    </div>
                                    <div class="col-auto" id="domainSelectorWrap" style="display:none">
                                        <label for="domainSelector" class="form-label">Domain</label>
                                        <select id="domainSelector" class="form-select">
                                            <option value="">-- Select domain --</option>
                                        </select>
                                    </div>
                                    <!-- values come from Adstera API -->
                                    <div class="col-auto">
                                        <button id="applyFiltersBtn" class="btn btn-primary">Apply</button>
                                        <button id="resetFiltersBtn" class="btn btn-primary">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">Calculate Items</div>
                            <div class="card-body table-responsive">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <label class="form-label me-2">Rows per page:</label>
                                        <select id="rowsPerPage" class="form-select d-inline-block" style="width:100px">
                                            <option value="10">10</option>
                                            <option value="25">25</option>
                                            <option value="50">50</option>
                                            <option value="100">100</option>
                                        </select>
                                    </div>
                                    <div>
                                        <nav aria-label="Table pagination">
                                            <ul class="pagination pagination-sm mb-0" id="paginationControls">
                                                <li class="page-item"><button class="page-link"
                                                        id="pagePrev">Back</button>
                                                </li>
                                                <li class="page-item disabled"><span class="page-link" id="pageInfo">1
                                                        /
                                                        1</span></li>
                                                <li class="page-item"><button class="page-link"
                                                        id="pageNext">Next</button>
                                                </li>
                                            </ul>
                                        </nav>
                                    </div>
                                </div>
                                <table class="table table-sm table-striped" id="calculateTable">
                                    <thead>
                                        <tr>
                                            <th style="width:36px"></th>
                                            <th id="colEntity">Domain</th>
                                            <th>Impression</th>
                                            <th>CPM</th>
                                            <th>Revenue</th>
                                        </tr>
                                    </thead>
                                    <tbody id="calculateTableBody">
                                        <tr>
                                            <td colspan="5">Loading...</td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr id="calculateTotals">
                                            <td></td>
                                            <td><strong>Total</strong></td>
                                            <td id="totalImpression">0</td>
                                            <td id="totalCpm">0</td>
                                            <td id="totalRevenue">0</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @include('calculaterate')

            </main>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
            <script>
                async function loadStats() {
                    const qs = buildQueryFromFilters();
                    const url = '/api/dashboard/stats' + (qs ? '?' + qs + '&_=' + Date.now() : '?_=' + Date.now());
                    const resp = await fetch(url, {
                        cache: 'no-store',
                        headers: {
                            'Cache-Control': 'no-store'
                        }
                    });
                    if (!resp.ok) {
                        console.error('Failed to load stats');
                        return;
                    }
                    const json = await resp.json();

                    // Prefill date inputs from returned items range if present
                    try {
                        const items = json.items || [];
                        // sort items by revenue descending (largest revenue first)
                        items.sort((a, b) => {
                            const parseRev = (x) => {
                                if (x === undefined || x === null) return 0;
                                const s = String(x).replace(/,/g, '');
                                const n = parseFloat(s);
                                return Number.isFinite(n) ? n : 0;
                            };
                            const ra = parseRev(a.revenue ?? a.revenue_usd ?? 0);
                            const rb = parseRev(b.revenue ?? b.revenue_usd ?? 0);
                            return rb - ra;
                        });
                        items.sort((a, b) => {
                            const ta = Date.parse(a.date || a.day || '') || 0;
                            const tb = Date.parse(b.date || b.day || '') || 0;
                            return tb - ta;
                        });
                        const startInput = document.getElementById('filterStart');
                        const finishInput = document.getElementById('filterFinish');
                        if (items.length > 0) {
                            const newest = items[0].date || items[0].day || '';
                            const oldest = items[items.length - 1].date || items[items.length - 1].day || '';
                            if (finishInput && !finishInput.value && newest) finishInput.value = newest;
                            if (startInput && !startInput.value && oldest) startInput.value = oldest;
                        }
                    } catch (e) {}

                    // render calculate table rows
                    try {
                        const items = json.items || [];
                        const tbody = document.getElementById('calculateTableBody');
                        const colEntity = document.getElementById('colEntity');
                        const filterType = document.getElementById('filter_type')?.value || 'domain';
                        const filterValue = document.getElementById('filterValue')?.value || '';
                        // set header
                        colEntity.textContent = filterType === 'placement' ? 'Placement' : 'Domain';
                        tbody.innerHTML = '';
                        // pagination + totals logic (items already sorted by revenue desc)
                        allItems = items;
                        currentPage = 1;
                        renderPage();

                        // no select-all or per-row checkboxes (removed)
                    } catch (e) {
                        console.error('Failed to render calculate table', e);
                    }
                }
                let allItems = [];
                let currentPage = 1;
                let pageSize = parseInt(document.getElementById('rowsPerPage')?.value || '10', 10);

                window.renderPage = function() {
                    const tbody = document.getElementById('calculateTableBody');
                    const start = (currentPage - 1) * pageSize;
                    const pageItems = allItems.slice(start, start + pageSize);
                    const showCheckbox = window.isEditingCalculateRate || false;
                    tbody.innerHTML = '';
                    if (pageItems.length === 0) {
                        const colspan = showCheckbox ? 5 : 4;
                        tbody.innerHTML = `<tr><td colspan="${colspan}">No data</td></tr>`;
                    } else {
                        const filterType = document.getElementById('filter_type')?.value || 'domain';
                        pageItems.forEach((it, idx) => {
                            let entityVal = '-';
                            if (filterType === 'placement') {
                                entityVal = it.alias ?? it.title ?? it.placement ?? it.placement_id ?? it.id ?? '-';
                            } else {
                                entityVal = it.alias ?? it.title ?? it.domain ?? it.id ?? '-';
                            }
                            const impression = it.impression ?? it.impressions ?? 0;
                            const cpm = it.cpm ?? '';
                            const revenue = it.revenue ?? 0;
                            const rowId = it.id ?? `${start + idx}-${entityVal}`;
                            const tr = document.createElement('tr');
                            let checkboxTd = showCheckbox ?
                                `<td><input type="checkbox" class="rowCheckbox" data-row-id="${rowId}"></td>` :
                                '<td></td>';
                            tr.innerHTML = checkboxTd + `
                        <td>${entityVal}</td>
                        <td>${impression}</td>
                        <td>${cpm}</td>
                        <td>${revenue}</td>
                    `;
                            tbody.appendChild(tr);

                            // Handle checkbox state and events if editing
                            if (showCheckbox) {
                                const cb = tr.querySelector('.rowCheckbox');
                                const rev = parseFloat(revenue) || 0;
                                if (window.checkedRevenues && window.checkedRevenues.has(rev)) {
                                    cb.checked = true;
                                }
                                cb.addEventListener('change', () => {
                                    if (cb.checked) {
                                        window.checkedRevenues.add(rev);
                                    } else {
                                        window.checkedRevenues.delete(rev);
                                    }
                                    if (window.updateFromCheckboxes) window.updateFromCheckboxes();
                                });
                            }
                        });
                    }

                    // update totals (sum over allItems)
                    let totalImpr = 0;
                    let totalRev = 0;
                    allItems.forEach(it => {
                        totalImpr += Number(it.impression ?? it.impressions ?? 0);
                        totalRev += Number(it.revenue ?? 0);
                    });
                    document.getElementById('totalImpression').textContent = totalImpr.toLocaleString();
                    const totalCpm = totalImpr > 0 ? (totalRev / (totalImpr / 1000)) : 0;
                    document.getElementById('totalCpm').textContent = totalCpm ? totalCpm.toFixed(2) : '0';
                    document.getElementById('totalRevenue').textContent = Number(totalRev).toFixed(2);

                    // update thead and tfoot visibility
                    const colCheckbox = document.getElementById('colCheckbox');
                    if (colCheckbox) colCheckbox.style.display = showCheckbox ? '' : 'none';
                    // Note: tfoot first td always visible for alignment

                    // update pagination info
                    const rows = Number(document.getElementById('rowsPerPage')?.value || 10);
                    pageSize = rows;
                    const totalPages = Math.max(1, Math.ceil(allItems.length / pageSize));
                    document.getElementById('pageInfo').textContent = currentPage + ' / ' + totalPages;
                    document.getElementById('pagePrev').disabled = currentPage <= 1;
                    document.getElementById('pageNext').disabled = currentPage >= totalPages;
                }

                function buildQueryFromFilters() {
                    const qp = new URLSearchParams();
                    const s = document.getElementById('filterStart')?.value;
                    const f = document.getElementById('filterFinish')?.value;
                    const type = document.getElementById('filter_type')?.value;
                    const val = document.getElementById('filterValue')?.value;
                    const domainSel = document.getElementById('domainSelector')?.value;
                    if (s) qp.set('start_date', s);
                    if (f) qp.set('finish_date', f);
                    if (type) qp.set('filter_type', type);
                    // include domain selection even when Value is All
                    if (domainSel) qp.set('domain', domainSel);
                    if (val) {
                        if (type === 'domain') qp.set('domain', val);
                        else if (type === 'placement') qp.set('placement', val);
                    }
                    return qp.toString();
                }

                document.getElementById('applyFiltersBtn')?.addEventListener('click', (e) => {
                    e.preventDefault();
                    loadStats().catch(err => console.error(err));
                });

                document.getElementById('resetFiltersBtn')?.addEventListener('click', (e) => {
                    e.preventDefault();
                    const sEl = document.getElementById('filterStart');
                    if (sEl) sEl.value = '';
                    const fEl = document.getElementById('filterFinish');
                    if (fEl) fEl.value = '';
                    const tEl = document.getElementById('filter_type');
                    if (tEl) tEl.value = '';
                    const vEl = document.getElementById('filterValue');
                    if (vEl) vEl.innerHTML = '<option value="">-- All --</option>';
                    loadStats().catch(err => console.error(err));
                });

                // Load domain/placement options based on selected filter type
                async function loadEntityOptions(type, domainId = '') {
                    const sel = document.getElementById('filterValue');
                    if (!sel) return;
                    sel.disabled = true;
                    sel.innerHTML = '<option>Loading...</option>';
                    try {
                        let url;
                        if (type === 'placement') {
                            url = domainId ? '/api/adstera/domain/' + encodeURIComponent(domainId) + '/placements' :
                                '/api/adstera/placements';
                        } else {
                            url = '/api/adstera/domains';
                        }
                        const resp = await fetch(url, {
                            cache: 'no-store'
                        });
                        if (!resp.ok) throw new Error('Failed');
                        const json = await resp.json();
                        const items = json.items || [];
                        sel.innerHTML = '<option value="">-- All --</option>';
                        for (const it of items) {
                            const id = it.id ?? it.domain_id ?? '';
                            // build label using possible owner/sub fields + alias/title + id
                            const parts = [];
                            const ownerFields = ['owner', 'user', 'username', 'user_name', 'owner_name', 'publisher', 'sub',
                                'sub_name', 'author', 'seller', 'display_name', 'label'
                            ];
                            for (const f of ownerFields) {
                                if (it[f]) {
                                    parts.push(it[f]);
                                    break;
                                }
                            }
                            if (it.alias && !parts.includes(it.alias)) parts.push(it.alias);
                            if (it.title && !parts.includes(it.title)) parts.push(it.title);
                            if (!parts.length && it.name) parts.push(it.name);
                            if (id) parts.push(String(id));
                            const opt = document.createElement('option');
                            opt.value = id;
                            opt.textContent = parts.join(' − ');
                            sel.appendChild(opt);
                        }
                    } catch (e) {
                        sel.innerHTML = '<option value="">-- All --</option>';
                    } finally {
                        sel.disabled = false;
                    }
                }

                // when filter_type changes, reload options
                document.getElementById('filter_type')?.addEventListener('change', (e) => {
                    const t = e.target.value || 'domain';
                    const domainWrap = document.getElementById('domainSelectorWrap');
                    if (t === 'domain') {
                        domainWrap.style.display = 'none';
                        document.getElementById('domainSelector').value = '';
                        loadEntityOptions('domain');
                    } else if (t === 'placement') {
                        // show domain selector and load domains
                        domainWrap.style.display = '';
                        loadDomainsIntoSelector();
                        loadEntityOptions('placement');
                    }
                    // reset page selection
                    currentPage = 1;
                });

                // initial load: ensure default filter_type is domain when empty
                const initialType = document.getElementById('filter_type')?.value || 'domain';
                if (!document.getElementById('filter_type')?.value) {
                    document.getElementById('filter_type').value = 'domain';
                }
                loadEntityOptions(initialType).catch(() => {});

                // helper to load domains into domainSelector
                async function loadDomainsIntoSelector() {
                    const sel = document.getElementById('domainSelector');
                    if (!sel) return;
                    sel.disabled = true;
                    sel.innerHTML = '<option>Loading...</option>';
                    try {
                        const resp = await fetch('/api/adstera/domains', {
                            cache: 'no-store'
                        });
                        if (!resp.ok) throw new Error('failed');
                        const json = await resp.json();
                        const items = json.items || [];
                        sel.innerHTML = '<option value="">-- Select domain --</option>';
                        for (const it of items) {
                            const id = it.id ?? it.domain_id ?? '';
                            const title = it.title ?? it.alias ?? it.name ?? String(id);
                            const opt = document.createElement('option');
                            opt.value = id;
                            opt.textContent = title;
                            sel.appendChild(opt);
                        }
                    } catch (e) {
                        sel.innerHTML = '<option value="">-- Select domain --</option>';
                    } finally {
                        sel.disabled = false;
                    }
                }

                // when domain selector changes, load placements for that domain and refresh stats
                document.getElementById('domainSelector')?.addEventListener('change', (e) => {
                    const d = e.target.value || '';
                    loadEntityOptions('placement', d);
                    currentPage = 1;
                    loadStats().catch(err => console.error(err));
                });

                // pagination controls
                document.getElementById('rowsPerPage')?.addEventListener('change', (e) => {
                    pageSize = Number(e.target.value || 10);
                    currentPage = 1;
                    renderPage();
                });

                document.getElementById('pagePrev')?.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (currentPage > 1) {
                        currentPage -= 1;
                        renderPage();
                    }
                });

                document.getElementById('pageNext')?.addEventListener('click', (e) => {
                    e.preventDefault();
                    const totalPages = Math.max(1, Math.ceil(allItems.length / pageSize));
                    if (currentPage < totalPages) {
                        currentPage += 1;
                        renderPage();
                    }
                });

                loadStats().catch(err => console.error(err));
            </script>
            @include('footer')
            </main>
        </div>
    </div>
</body>

</html>
