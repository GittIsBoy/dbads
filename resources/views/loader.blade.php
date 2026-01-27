<!-- Reusable loader partial: spinner overlay + table-row placeholder + JS helpers -->
<style>
	.app-loader-overlay {
		position: fixed;
		inset: 0;
		background: rgba(0,0,0,0.6);
		display: flex;
		align-items: center;
		justify-content: center;
		z-index: 1050;
		visibility: hidden;
		opacity: 0;
		transition: opacity .15s ease, visibility .15s ease;
	}
	.app-loader-overlay.show { visibility: visible; opacity: 1; }
	.app-loader-card {
		background: rgba(255,255,255,0.06);
		border-radius: 8px;
		padding: 18px 22px;
		text-align: center;
		color: white;
		display: inline-flex;
		gap: 12px;
		align-items: center;
	}
	.app-loader-spinner {
		width: 28px;
		height: 28px;
		border: 4px solid rgba(255,255,255,0.12);
		border-top-color: #8A244B;
		border-radius: 50%;
		animation: spin 0.8s linear infinite;
	}
	@keyframes spin { to { transform: rotate(360deg); } }
	.table-loading-row td { text-align: center; opacity: .9; }
</style>

<div id="appLoaderOverlay" class="app-loader-overlay" aria-hidden="true">
	<div class="app-loader-card" role="status" aria-live="polite">
		<div class="app-loader-spinner" aria-hidden="true"></div>
		<div class="loader-text">Loading…</div>
	</div>
</div>

<script>
	window.appLoader = (function(){
		const overlay = document.getElementById('appLoaderOverlay');
		function show() { if (overlay) overlay.classList.add('show'); }
		function hide() { if (overlay) overlay.classList.remove('show'); }

		// Insert a table-loading row into the given table body selector.
		function showTableLoading(tableBodySelector, colspan){
			try {
				const tbody = document.querySelector(tableBodySelector);
				if (!tbody) return;
				const cols = colspan || (tbody.closest('table')?.querySelectorAll('thead th').length || 1);
				tbody.dataset.prevHtml = tbody.innerHTML;
				tbody.innerHTML = `<tr class="table-loading-row"><td colspan="${cols}">Loading...</td></tr>`;
			} catch (e) {}
		}

		function hideTableLoading(tableBodySelector){
			try {
				const tbody = document.querySelector(tableBodySelector);
				if (!tbody) return;
				// restore previous content if present, otherwise clear
				if (tbody.dataset.prevHtml !== undefined) {
					tbody.innerHTML = tbody.dataset.prevHtml;
					delete tbody.dataset.prevHtml;
				}
			} catch (e) {}
		}

		return { show, hide, showTableLoading, hideTableLoading };
	})();
</script>
