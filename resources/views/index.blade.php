<!doctype html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Main Dashboard</title>
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
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
              <h1 class="mb-4">Dashboard</h1>

      <div class="row mb-3">
        <div class="col-md-4">
          <div class="card" style="background-color: #8A244B; color: white;">
            <div class="card-body">
              <h5 class="card-title">Balance</h5>
              <p class="card-text fs-3" id="balanceValue">—</p>
            </div>
          </div>
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-12">
          <div class="card">
            <div class="card-header">Filter</div>
            <div class="card-body">
              <div class="row g-2 align-items-end">
                <div class="col-auto">
                  <label class="form-label">Start</label>
                  <input type="date" id="filterStart" class="form-control">
                </div>
                <div class="col-auto">
                  <label class="form-label">Finish</label>
                  <input type="date" id="filterFinish" class="form-control">
                </div>
                <!-- only date range filter required -->
                <div class="col-auto">
                  <button id="applyFiltersBtn" class="btn btn-primary">Apply</button>
                  <button id="resetFiltersBtn" class="btn btn-primary"">Reset</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="card mb-4">
            <div class="card-header">Data Pendatan</div>
            <div class="card-body">
              <canvas id="chartData" width="400" height="250"></canvas>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="card mb-4">
            <div class="card-header">Pendapatan</div>
            <div class="card-body">
              <canvas id="chartRevenue" width="400" height="250"></canvas>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">Tabel Statistik</div>
            <div class="card-body table-responsive">
              <table class="table table-sm table-striped" id="statsTable">
                <thead>
                  <tr>
                    <th>Tanggal</th>
                    <th>Impression</th>
                    <th>Clicks</th>
                    <th>CTR</th>
                    <th>CPM</th>
                    <th>Revenue</th>
                  </tr>
                </thead>
                <tbody>
                  <tr><td colspan="6">Loading...</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
      // fetch real data from our API which proxies Adstera
      // Use cache-busting and disable cache so reloads always fetch fresh data.
      let chartDataInstance = null;
      let chartRevenueInstance = null;

      async function loadStats() {
        const qs = buildQueryFromFilters();
        const sep = qs ? '?' : '?';
        const url = '/api/dashboard/stats' + (qs ? '?' + qs + '&_=' + Date.now() : '?_=' + Date.now());
        const resp = await fetch(url, { cache: 'no-store', headers: { 'Cache-Control': 'no-store' } });
        if (!resp.ok) {
          console.error('Failed to load stats');
          return;
        }
        const json = await resp.json();
        const labels = json.labels || [];
        const dataCounts = json.impressions || [];
        const dataRevenue = json.revenue || [];

        // show balance if provided by API
        const balanceEl = document.getElementById('balanceValue');
        if (balanceEl) {
          const bal = json.balance ?? null;
          balanceEl.textContent = (typeof bal === 'number' ? '$' + bal.toFixed(2) : (bal ? String(bal) : '—'));
        }

        const dataCanvas = document.getElementById('chartData');
        if (dataCanvas) {
          const ctxData = dataCanvas.getContext('2d');
          if (chartDataInstance) { chartDataInstance.destroy(); }
          chartDataInstance = new Chart(ctxData, {
            type: 'line',
            data: {
              labels: labels,
              datasets: [{
                label: 'Jumlah Data',
                data: dataCounts,
                borderColor: 'rgba(54,162,235,1)',
                backgroundColor: 'rgba(54,162,235,0.2)',
                fill: true
              }]
            },
            options: {
              responsive: true,
              plugins: { legend: { labels: { color: 'white' } } },
              scales: { x: { ticks: { color: 'white' } }, y: { ticks: { color: 'white' } } }
            }
          });
        }

        const revCanvas = document.getElementById('chartRevenue');
        if (revCanvas) {
          const ctxRev = revCanvas.getContext('2d');
          if (chartRevenueInstance) { chartRevenueInstance.destroy(); }
          chartRevenueInstance = new Chart(ctxRev, {
            type: 'bar',
            data: {
              labels: labels,
              datasets: [{
                label: 'Pendapatan (USD)',
                data: dataRevenue,
                backgroundColor: 'rgba(75,192,192,0.6)'
              }]
            },
            options: {
              responsive: true,
              plugins: { legend: { labels: { color: 'white' } } },
              scales: { x: { ticks: { color: 'white' } }, y: { ticks: { color: 'white' } } }
            }
          });
        }

        // render table rows if items present
        const items = json.items || [];
        // sort by date descending so newest (today) shows first
        items.sort((a, b) => {
          const ta = Date.parse(a.date || a.day || '') || 0;
          const tb = Date.parse(b.date || b.day || '') || 0;
          return tb - ta;
        });

        // Prefill date inputs from the returned items range if inputs are empty
        try {
          const startInput = document.getElementById('filterStart');
          const finishInput = document.getElementById('filterFinish');
          if (items.length > 0) {
            const newest = items[0].date || items[0].day || '';
            const oldest = items[items.length - 1].date || items[items.length - 1].day || '';
            if (finishInput && !finishInput.value && newest) finishInput.value = newest;
            if (startInput && !startInput.value && oldest) startInput.value = oldest;
          }
        } catch (e) {
          // ignore DOM errors
        }
        const tbody = document.querySelector('#statsTable tbody');
        tbody.innerHTML = '';
        if (items.length === 0) {
          tbody.innerHTML = '<tr><td colspan="6">Tidak ada data</td></tr>';
        } else {
          for (const it of items) {
            const tr = document.createElement('tr');
            const date = it.date || it.day || '';
            const impression = it.impression ?? it.impressions ?? 0;
            const clicks = it.clicks ?? 0;
            const ctr = it.ctr ?? '';
            const cpm = it.cpm ?? '';
            const revenue = it.revenue ?? 0;
            tr.innerHTML = `<td>${date}</td><td>${impression}</td><td>${clicks}</td><td>${ctr}</td><td>${cpm}</td><td>${revenue}</td>`;
            tbody.appendChild(tr);
          }
        }
      }

      function buildQueryFromFilters() {
        const qp = new URLSearchParams();
        const s = document.getElementById('filterStart')?.value;
        const f = document.getElementById('filterFinish')?.value;
        if (s) qp.set('start_date', s);
        if (f) qp.set('finish_date', f);
        return qp.toString();
      }

      document.getElementById('applyFiltersBtn')?.addEventListener('click', (e) => {
        e.preventDefault();
        loadStats().catch(err => console.error(err));
      });

      document.getElementById('resetFiltersBtn')?.addEventListener('click', (e) => {
        e.preventDefault();
        const sEl = document.getElementById('filterStart'); if (sEl) sEl.value = '';
        const fEl = document.getElementById('filterFinish'); if (fEl) fEl.value = '';
        loadStats().catch(err => console.error(err));
      });

      loadStats().catch(err => console.error(err));
    </script>
    <script>
      (function(){
        function loadCalculateRateData(){
          try{ return JSON.parse(localStorage.getItem('calculateRateData') || '[]'); }catch(e){ return []; }
        }
        function formatHasil(v){
          const n = Number(v) || 0;
          return 'Rp' + n.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
        function refreshCalculatorTables(){
          const data = loadCalculateRateData();
          document.querySelectorAll('.calculatorTableBody').forEach(tbody=>{
            tbody.innerHTML = '';
            if(!data || data.length === 0){
              tbody.innerHTML = '<tr><td colspan="3">Tidak ada data</td></tr>';
              const t = tbody.closest('table');
              if(t) t.querySelector('.calculatorTotal').textContent = formatHasil(0);
              return;
            }
            data.forEach((r, idx)=>{
              const tr = document.createElement('tr');
              const tdChk = document.createElement('td');
              const chk = document.createElement('input');
              chk.type = 'checkbox'; chk.className = 'calc-row-checkbox'; chk.dataset.idx = idx;
              tdChk.appendChild(chk);
              const tdName = document.createElement('td'); tdName.textContent = r.nama || '';
              const tdHasil = document.createElement('td'); tdHasil.textContent = formatHasil(r.hasil ?? r.value ?? 0);
              tr.appendChild(tdChk); tr.appendChild(tdName); tr.appendChild(tdHasil);
              tbody.appendChild(tr);
            });
            const table = tbody.closest('table');
            if(table){
              const totalEl = table.querySelector('.calculatorTotal');
              if(totalEl) totalEl.textContent = formatHasil(0);
            }
            tbody.querySelectorAll('.calc-row-checkbox').forEach(cb=>{
              cb.addEventListener('change', ()=>{
                const table = cb.closest('table');
                let sum = 0;
                table.querySelectorAll('tbody tr').forEach(tr=>{
                  const ch = tr.querySelector('.calc-row-checkbox');
                  if(ch && ch.checked){
                    const vText = tr.cells[2]?.textContent || '';
                    // remove non-numeric except comma and dot, normalize
                    const cleaned = vText.replace(/[^0-9,-]+/g,'').replace(/\./g,'').replace(/,/g,'.');
                    const num = Number(cleaned) || 0;
                    sum += num;
                  }
                });
                const totalEl = table.querySelector('.calculatorTotal');
                if(totalEl) totalEl.textContent = formatHasil(sum);
              });
            });
          });
        }
        document.addEventListener('DOMContentLoaded', refreshCalculatorTables);
        window.addEventListener('storage', refreshCalculatorTables);
      })();
    </script>
    @include('footer')
            </main>
        </div>
    </div>
  </body>
</html>
