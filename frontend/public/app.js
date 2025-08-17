// app.js  (FRONTEND con paginación)

// ====== CONFIG ======
const API_BASE = "http://localhost:8080/index.php"; // backend
const API_PATH = "/api/orders/summary";
const TOKEN    = "mi_token_super_seguro";           // mismo que API_TOKEN en el backend

// ====== STATE ======
let state = {
  page: 1,
  perPage: 10,
  totalPages: 1,
  totalRows: 0,
};

// ====== ELEMENTOS ======
const $tbody     = document.getElementById('tbody');
const $count     = document.getElementById('count');
const $tOrders   = document.getElementById('tOrders');
const $tAmount   = document.getElementById('tAmount');
const $month     = document.getElementById('month');
const $status    = document.getElementById('status');
const $error     = document.getElementById('error');
const $loading   = document.getElementById('loading');

const $prevBtn   = document.getElementById('prevBtn');
const $nextBtn   = document.getElementById('nextBtn');
const $pageInfo  = document.getElementById('pageInfo');
const $perPage   = document.getElementById('perPage');

// ====== HELPERS ======
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

// ====== API ======
async function fetchSummary({ month, status, page, perPage }) {
  const qs = `?month=${encodeURIComponent(month)}&status=${encodeURIComponent(status)}&page=${page}&per_page=${perPage}`;
  const url = `${API_BASE}${API_PATH}${qs}`;

  const res = await fetchWithAbort(url, {
    headers: { Authorization: `Bearer ${TOKEN}` }
  });

  if (!res.ok) {
    const txt = await res.text().catch(() => '');
    throw new Error(`HTTP ${res.status} – ${txt.slice(0,200)}`);
  }

  return res.json();
}

// ====== RENDER ======
function renderRows(rows) {
  $tbody.innerHTML = '';
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
        <div style="color:#6b7280; font-size:12px">${r.email ?? ''}</div>
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

function renderPagination(pagination) {
  const { page, per_page, total_pages, total_rows } = pagination;
  state.page = page;
  state.perPage = per_page;
  state.totalPages = total_pages;
  state.totalRows = total_rows;

  if ($pageInfo) $pageInfo.textContent = `Página ${page} de ${total_pages}`;
  if ($count) $count.textContent = `(${total_rows})`;

  if ($prevBtn) $prevBtn.disabled = page <= 1;
  if ($nextBtn) $nextBtn.disabled = page >= total_pages;

  if ($perPage) {
    const val = String(per_page);
    if ($perPage.value !== val) $perPage.value = val;
  }
}

// ====== FLUJO ======
async function load() {
  try {
    clearError();
    showLoading();

    const month = $month.value;
    const status = $status.value;

    const data = await fetchSummary({
      month,
      status,
      page: state.page,
      perPage: state.perPage
    });

    renderRows(Array.isArray(data.rows) ? data.rows : []);
    renderTotals(data.totals);
    renderPagination(data.pagination);
  } catch (err) {
    showError('No se pudo cargar la información.');
    console.error('[load] error:', err);
    renderRows([]);
    renderTotals({ orders_count: 0, total_amount: 0 });
    renderPagination({ page: 1, per_page: state.perPage, total_pages: 1, total_rows: 0 });
  } finally {
    hideLoading();
  }
}

// ====== INIT ======
document.addEventListener('DOMContentLoaded', () => {
  // Carga inicial
  load();

  // Filtros → resetear a página 1
  $month.addEventListener('change', () => { state.page = 1; load(); });
  $status.addEventListener('change', () => { state.page = 1; load(); });

  // Paginación
  if ($prevBtn) $prevBtn.addEventListener('click', () => {
    if (state.page > 1) { state.page -= 1; load(); }
  });
  if ($nextBtn) $nextBtn.addEventListener('click', () => {
    if (state.page < state.totalPages) { state.page += 1; load(); }
  });
  if ($perPage) $perPage.addEventListener('change', () => {
    const n = parseInt($perPage.value, 10) || 10;
    state.perPage = n;
    state.page = 1;
    load();
  });
});
