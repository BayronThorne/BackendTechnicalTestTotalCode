const API_BASE = "http://localhost:8080/index.php";
const API_PATH = "/api/orders/summary";
const TOKEN    = "mi_token_super_seguro";


const $tbody   = document.getElementById('tbody');     // <tbody id="tbody">
const $count   = document.getElementById('count');     // span para (N) registros
const $tOrders = document.getElementById('tOrders');   // total de órdenes
const $tAmount = document.getElementById('tAmount');   // total de monto
const $month   = document.getElementById('month');     // <select id="month">
const $status  = document.getElementById('status');    // <select id="status">
const $error   = document.getElementById('error');     // opcional: <div id="error"></div>
const $loading = document.getElementById('loading');   // opcional: <div id="loading"></div>

function money(n) {
  const num = Number(n || 0);
  return '$ ' + Math.round(num).toLocaleString('es-CO');
}
function showLoading(msg = 'Cargando...') { if ($loading) $loading.textContent = msg; }
function hideLoading() { if ($loading) $loading.textContent = ''; }
function showError(msg) { if ($error) $error.textContent = msg; else console.error(msg); }
function clearError() { if ($error) $error.textContent = ''; }

let inflightController = null;
function fetchWithAbort(url, opts = {}) {
  if (inflightController) inflightController.abort();
  inflightController = new AbortController();
  return fetch(url, { ...opts, signal: inflightController.signal });
}

const API_URL = "http://localhost:8080/index.php/api/orders/summary";
async function fetchSummary(month, status) {
  const qs = `?month=${encodeURIComponent(month)}&status=${encodeURIComponent(status)}`;
  const url = `${API_BASE}${API_PATH}${qs}`;

  const res = await fetch(url, {
    headers: { Authorization: `Bearer ${TOKEN}` }
  });

  if (!res.ok) {
    const txt = await res.text().catch(() => '');
    throw new Error(`HTTP ${res.status} – ${txt.slice(0,200)}`);
  }
  return res.json();
}

function renderRows(rows) {
  $tbody.innerHTML = '';
  $count.textContent = `(${rows.length})`;

  if (!rows.length) {
    const tr = document.createElement('tr');
    tr.innerHTML = `<td colspan="3" class="center" style="color:#6b7280">No hay resultados</td>`;
    $tbody.appendChild(tr);
    return;
  }

  for (const r of rows) {
    const tr = document.createElement('tr');

    tr.innerHTML = `
      <td>
        <div style="font-weight:700">${r.client_name || '(Sin nombre)'}</div>
        <div style="color:#6b7280; font-size:12px">${r.email}</div>
      </td>
      <td class="num">${r.orders_count}</td>
      <td class="num">${money(r.total_amount)}</td>
    `;

    $tbody.appendChild(tr);
  }
}

function renderTotals(totals) {
  const t = totals || { orders_count: 0, total_amount: 0 };
  $tOrders.textContent = t.orders_count ?? 0;
  $tAmount.textContent = money(t.total_amount ?? 0);
}

async function load() {
  try {
    clearError();
    showLoading();

    const m = $month.value;
    const s = $status.value;

    const data = await fetchSummary(m, s);

    const rows = Array.isArray(data.rows) ? data.rows : [];
    renderRows(rows);
    renderTotals(data.totals);
  } catch (err) {
    showError('No se pudo cargar la información.');
    console.error('[load] error:', err);
    renderRows([]);
    renderTotals({ orders_count: 0, total_amount: 0 });
  } finally {
    hideLoading();
  }
}

document.addEventListener('DOMContentLoaded', () => {
  load();

  $month.addEventListener('change', load);
  $status.addEventListener('change', load);
});
