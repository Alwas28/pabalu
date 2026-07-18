<x-laundry-layout :outlet="$outlet" pageTitle="Pesanan Laundry">

<style>
.status-tab{display:flex;align-items:center;gap:8px;padding:9px 16px;border-radius:10px;font-size:13px;font-weight:600;text-decoration:none;border:1px solid var(--border);color:var(--sub);transition:all .15s;white-space:nowrap}
.status-tab:hover{background:var(--surface2);color:var(--text)}
.status-tab.active{border-color:var(--ac);background:var(--ac-lt);color:var(--ac)}
.status-tab .cnt{background:var(--ac);color:#fff;border-radius:99px;font-size:10px;font-weight:700;padding:1px 7px;min-width:18px;text-align:center}
.status-tab:not(.active) .cnt{background:var(--surface2);color:var(--sub)}

/* Tabel pesanan */
.orders-table{width:100%;border-collapse:collapse}
.orders-table th{font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.6px;padding:10px 14px;border-bottom:2px solid var(--border);white-space:nowrap;text-align:left}
.orders-table td{padding:11px 14px;border-bottom:1px solid var(--border);vertical-align:middle;font-size:13px;color:var(--text)}
.orders-table tr:last-child td{border-bottom:none}
.orders-table tbody tr{cursor:pointer;transition:background .12s}
.orders-table tbody tr:hover{background:var(--surface2)}
.order-num{font-family:'Clash Display',sans-serif;font-size:12px;font-weight:700;color:var(--ac);letter-spacing:.5px}

/* Modal item rows — semua dalam satu card */
.item-row{display:grid;grid-template-columns:1fr 65px 75px 90px 80px 32px;gap:8px;align-items:center;padding:8px 12px;border-bottom:1px solid var(--border)}
.item-row:last-child{border-bottom:none}
.item-col-hdr{font-size:10.5px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.6px}
</style>

{{-- Header --}}
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
  <div>
    <h2 class="font-display" style="font-size:20px;font-weight:700;color:var(--text)">Pesanan Laundry</h2>
    <p style="font-size:13px;color:var(--muted);margin-top:2px">Kelola status cucian pelanggan dari masuk hingga diambil</p>
  </div>
  <button onclick="openModal('modal-new-order')"
    style="display:flex;align-items:center;gap:8px;padding:10px 18px;background:var(--ac);color:#fff;border:none;border-radius:12px;font-size:13.5px;font-weight:600;cursor:pointer;font-family:inherit">
    <i class="fa-solid fa-plus"></i> Terima Pesanan Baru
  </button>
</div>

{{-- Status Tabs --}}
<div style="display:flex;gap:8px;overflow-x:auto;padding-bottom:2px">
  @php
    $tabs = [
      'masuk'   => ['label'=>'Masuk',           'icon'=>'fa-inbox',        'color'=>'#60a5fa'],
      'proses'  => ['label'=>'Sedang Diproses',  'icon'=>'fa-rotate',       'color'=>'#fbbf24'],
      'selesai' => ['label'=>'Selesai',          'icon'=>'fa-circle-check', 'color'=>'#34d399'],
      'diambil' => ['label'=>'Sudah Diambil',    'icon'=>'fa-check-double', 'color'=>'#94a3b8'],
    ];
  @endphp
  @foreach($tabs as $key => $tab)
  <a href="{{ $outlet->route('laundry-orders.index') }}?status={{ $key }}"
     class="status-tab {{ $status === $key ? 'active' : '' }}">
    <i class="fa-solid {{ $tab['icon'] }}" style="font-size:12px;color:{{ $tab['color'] }}"></i>
    {{ $tab['label'] }}
    <span class="cnt">{{ $counts[$key] ?? 0 }}</span>
  </a>
  @endforeach
</div>

{{-- Search Bar --}}
<form method="GET" action="{{ $outlet->route('laundry-orders.index') }}" id="search-form">
  <input type="hidden" name="status" value="{{ $status }}">
  <div style="display:flex;gap:8px;align-items:center">
    <div style="position:relative;flex:1;max-width:400px">
      <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:13px;color:var(--muted)"></i>
      <input id="search-input" type="text" name="q" value="{{ $q }}" placeholder="Cari no. pesanan, nama, atau HP…"
        autocomplete="off"
        style="width:100%;padding:9px 12px 9px 36px;border:1px solid var(--border);border-radius:10px;background:var(--surface);color:var(--text);font-size:13px;font-family:inherit;outline:none"
        oninput="onSearchInput(this.value)">
      @if($q)
      <a href="{{ $outlet->route('laundry-orders.index') }}?status={{ $status }}"
        style="position:absolute;right:10px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:12px;text-decoration:none"
        title="Hapus pencarian">
        <i class="fa-solid fa-xmark"></i>
      </a>
      @endif
    </div>
    @if($q)
    <span style="font-size:12px;color:var(--muted)">{{ $orders->total() }} hasil untuk "<b>{{ $q }}</b>"</span>
    @endif
  </div>
</form>

{{-- Order Table --}}
@if($orders->isEmpty())
<div style="text-align:center;padding:60px 20px;background:var(--surface);border:1px dashed var(--border);border-radius:16px">
  <i class="fa-solid fa-basket-shopping" style="font-size:36px;color:var(--muted);margin-bottom:12px"></i>
  <p style="font-size:15px;font-weight:600;color:var(--sub)">Tidak ada pesanan di status ini</p>
  @if($status === 'masuk')
  <p style="font-size:13px;color:var(--muted);margin-top:4px">Klik "Terima Pesanan Baru" untuk menambahkan pesanan masuk</p>
  @endif
</div>
@else
<div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;overflow:hidden">
  <div style="overflow-x:auto">
    <table class="orders-table">
      <thead>
        <tr>
          <th>No. Pesanan</th>
          <th>Pelanggan</th>
          <th>Layanan</th>
          <th>Tgl Masuk</th>
          <th>Est. Selesai</th>
          <th style="text-align:right">Total</th>
          <th style="text-align:center">Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach($orders as $order)
        <tr onclick="window.location='{{ $outlet->route('laundry-orders.show', [$order->id]) }}'">
          <td>
            <span class="order-num">{{ $order->order_number }}</span>
          </td>
          <td>
            <div style="font-weight:600;color:var(--text)">{{ $order->customer_name }}</div>
            @if($order->customer_phone)
            <div style="font-size:11px;color:var(--muted)">{{ $order->customer_phone }}</div>
            @endif
          </td>
          <td style="max-width:200px">
            @foreach($order->items->take(2) as $item)
            <div style="font-size:12px;color:var(--sub)">
              {{ $item->product_name }}
              <span style="color:var(--muted)">× {{ number_format($item->qty, $item->qty == (int)$item->qty ? 0 : 1) }} {{ $item->unit }}</span>
            </div>
            @endforeach
            @if($order->items->count() > 2)
            <div style="font-size:11px;color:var(--muted)">+{{ $order->items->count() - 2 }} lainnya</div>
            @endif
          </td>
          <td style="white-space:nowrap;color:var(--sub)">{{ $order->created_at->format('d/m/Y H:i') }}</td>
          <td style="white-space:nowrap">
            @if($order->estimated_done_at)
            <span style="{{ $order->status !== 'diambil' && $order->estimated_done_at->isPast() ? 'color:#f87171' : 'color:var(--sub)' }}">
              {{ $order->estimated_done_at->format('d/m/Y H:i') }}
            </span>
            @else
            <span style="color:var(--muted)">—</span>
            @endif
          </td>
          <td style="text-align:right;font-family:'Clash Display',sans-serif;font-weight:700">
            Rp {{ number_format($order->total) }}
          </td>
          <td style="text-align:center">
            <span class="badge {{ \App\Models\LaundryOrder::$statusColors[$order->status] ?? 'badge-gray' }}">
              {{ \App\Models\LaundryOrder::$statusLabels[$order->status] ?? $order->status }}
            </span>
            @if($order->status === 'selesai')
            <div style="font-size:10px;font-weight:600;color:#34d399;margin-top:3px">
              <i class="fa-solid fa-hand-holding-dollar"></i> Siap Bayar
            </div>
            @endif
          </td>
          <td>
            <a href="{{ $outlet->route('laundry-orders.show', [$order->id]) }}"
               onclick="event.stopPropagation()"
               style="display:inline-flex;align-items:center;gap:5px;padding:5px 12px;background:var(--ac-lt);color:var(--ac);border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;white-space:nowrap">
              <i class="fa-solid fa-eye" style="font-size:11px"></i> Detail
            </a>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

{{-- Pagination --}}
@if($orders->hasPages())
<div style="display:flex;justify-content:center">
  {{ $orders->links() }}
</div>
@endif
@endif

{{-- ═══════════════════════════════════════════════════ --}}
{{-- MODAL TERIMA PESANAN BARU                           --}}
{{-- ═══════════════════════════════════════════════════ --}}
<div id="modal-new-order" class="modal-backdrop">
  <div class="modal-box" style="max-width:680px;max-height:90vh;overflow-y:auto">
    <div style="padding:20px 24px;border-bottom:1px solid var(--border);position:sticky;top:0;background:var(--surface);z-index:1;border-radius:20px 20px 0 0">
      <div style="display:flex;align-items:center;justify-content:space-between">
        <div>
          <div class="font-display" style="font-size:17px;font-weight:700;color:var(--text)">Terima Pesanan Laundry</div>
          <div style="font-size:12px;color:var(--muted);margin-top:1px">Isi data pelanggan dan item cucian</div>
        </div>
        <button onclick="closeModal('modal-new-order')" style="width:32px;height:32px;border-radius:8px;border:1px solid var(--border);background:none;cursor:pointer;color:var(--sub);font-size:13px">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
    </div>

    <form id="form-new-order" style="padding:20px 24px;display:flex;flex-direction:column;gap:16px">
      @csrf

      {{-- Data Pelanggan --}}
      <input type="hidden" id="f-customer-id">
      <div style="background:var(--surface2);border-radius:12px;padding:16px;display:flex;flex-direction:column;gap:12px">
        <div style="display:flex;align-items:center;justify-content:space-between">
          <div style="font-size:12px;font-weight:700;color:var(--sub);letter-spacing:.5px;text-transform:uppercase">Data Pelanggan</div>
          {{-- Badge: pelanggan terdaftar --}}
          <div id="customer-registered-badge" style="display:none;font-size:11px;font-weight:600;color:#34d399;background:rgba(16,185,129,.12);border:1px solid rgba(16,185,129,.25);padding:2px 10px;border-radius:99px">
            <i class="fa-solid fa-user-check" style="margin-right:3px"></i>Terdaftar
          </div>
        </div>

        {{-- Input Nama + Autocomplete --}}
        <div style="position:relative">
          <label class="f-label">Nama Pelanggan <span style="color:#f87171">*</span></label>
          <input id="f-customer-name" type="text" class="f-input" placeholder="Ketik nama untuk mencari…" required
            autocomplete="off" oninput="onCustomerNameInput(this.value)" onfocus="onCustomerNameInput(this.value)">
          {{-- Dropdown hasil pencarian --}}
          <div id="customer-dropdown"
            style="display:none;position:absolute;top:100%;left:0;right:0;z-index:200;background:var(--surface);border:1px solid var(--border);border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,.3);margin-top:4px;overflow:hidden;max-height:220px;overflow-y:auto">
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
          <div>
            <label class="f-label">Nomor HP</label>
            <input id="f-customer-phone" type="text" class="f-input" placeholder="cth: 0812-3456-7890">
          </div>
          {{-- Toggle Simpan Pelanggan --}}
          <div id="save-customer-wrap" style="display:flex;align-items:flex-end;padding-bottom:1px">
            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;width:100%;padding:9px 13px;background:var(--surface);border:1px solid var(--border);border-radius:12px">
              <div id="toggle-save" onclick="toggleSaveCustomer()"
                style="width:36px;height:20px;border-radius:99px;background:var(--ac);position:relative;flex-shrink:0;cursor:pointer;transition:background .2s">
                <div id="toggle-save-thumb"
                  style="position:absolute;top:2px;left:16px;width:16px;height:16px;border-radius:50%;background:#fff;transition:left .2s;box-shadow:0 1px 3px rgba(0,0,0,.3)"></div>
              </div>
              <div>
                <div style="font-size:12.5px;font-weight:600;color:var(--text)">Simpan pelanggan</div>
                <div id="save-customer-sub" style="font-size:11px;color:var(--muted)">Data tersimpan untuk pesanan berikutnya</div>
              </div>
            </label>
          </div>
        </div>
      </div>

      {{-- Estimasi & Catatan --}}
      <div style="background:var(--surface2);border-radius:12px;padding:16px;display:flex;flex-direction:column;gap:12px">
        <div>
          <label class="f-label">Estimasi Selesai</label>
          <input id="f-estimated-done" type="datetime-local" class="f-input">
        </div>
        <div>
          <label class="f-label">Catatan <span style="font-size:11px;font-weight:400;color:var(--muted)">(warna, perlakuan khusus, dll)</span></label>
          <textarea id="f-notes" class="f-input" rows="2" placeholder="cth: Jangan dicampur, warna gelap pisah..."></textarea>
        </div>
      </div>

      {{-- Item / Layanan — semua dalam satu card --}}
      <div style="border:1px solid var(--border);border-radius:12px;overflow:hidden">
        {{-- Header kolom --}}
        <div style="display:grid;grid-template-columns:1fr 65px 75px 90px 80px 32px;gap:8px;padding:8px 12px;background:var(--surface2);border-bottom:1px solid var(--border)">
          <span class="item-col-hdr">Layanan</span>
          <span class="item-col-hdr">Qty</span>
          <span class="item-col-hdr">Satuan</span>
          <span class="item-col-hdr">Harga/Unit</span>
          <span class="item-col-hdr" style="text-align:right">Jumlah</span>
          <span></span>
        </div>
        {{-- Baris item --}}
        <div id="items-container">
          {{-- Diisi via JS --}}
        </div>
        {{-- Footer: tambah item + total --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;background:var(--surface2);border-top:1px solid var(--border)">
          <button type="button" onclick="addItemRow()"
            style="display:flex;align-items:center;gap:6px;padding:6px 12px;background:var(--ac-lt);color:var(--ac);border:1px solid var(--ac);border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;font-family:inherit">
            <i class="fa-solid fa-plus"></i> Tambah Item
          </button>
          <div id="total-section" style="display:none">
            <span style="font-size:12px;color:var(--sub);margin-right:8px">Total</span>
            <span id="total-display" class="font-display" style="font-size:17px;font-weight:700;color:var(--text)">Rp 0</span>
          </div>
        </div>
      </div>

      {{-- Actions --}}
      <div style="display:flex;gap:10px;justify-content:flex-end;padding-top:4px">
        <button type="button" onclick="closeModal('modal-new-order')"
          style="padding:10px 20px;border-radius:10px;border:1px solid var(--border);background:none;color:var(--sub);font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
          Batal
        </button>
        <button type="submit" id="btn-save-order"
          style="display:flex;align-items:center;gap:8px;padding:10px 22px;background:var(--ac);color:#fff;border:none;border-radius:10px;font-size:13.5px;font-weight:600;cursor:pointer;font-family:inherit">
          <i class="fa-solid fa-basket-shopping"></i> Terima Pesanan
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Modal Struk Berhasil --}}
<div id="modal-success" class="modal-backdrop">
  <div class="modal-box" style="max-width:380px;text-align:center;padding:32px 28px">
    <div style="width:60px;height:60px;border-radius:50%;background:rgba(16,185,129,.15);color:#34d399;display:grid;place-items:center;font-size:24px;margin:0 auto 16px">
      <i class="fa-solid fa-circle-check"></i>
    </div>
    <div class="font-display" style="font-size:18px;font-weight:700;color:var(--text);margin-bottom:6px">Pesanan Diterima!</div>
    <div id="success-order-num" style="font-size:13px;color:var(--muted);margin-bottom:20px"></div>
    <div style="display:flex;flex-direction:column;gap:8px">
      <a id="btn-print-receipt" href="#" target="_blank"
        style="display:flex;align-items:center;justify-content:center;gap:8px;padding:10px 20px;background:var(--ac);color:#fff;border-radius:10px;font-size:13.5px;font-weight:600;text-decoration:none">
        <i class="fa-solid fa-print"></i> Cetak Struk Masuk
      </a>
      <a id="btn-view-detail" href="#"
        style="display:flex;align-items:center;justify-content:center;gap:8px;padding:10px 20px;background:var(--surface2);color:var(--text);border:1px solid var(--border);border-radius:10px;font-size:13.5px;font-weight:600;text-decoration:none">
        <i class="fa-solid fa-eye"></i> Lihat Detail Pesanan
      </a>
      <button onclick="closeModal('modal-success');location.reload()"
        style="padding:10px 20px;background:none;border:none;color:var(--muted);font-size:13px;cursor:pointer;font-family:inherit">
        Tutup
      </button>
    </div>
  </div>
</div>

@php
  $productsJs = $products->map(fn($p) => [
    'id'    => $p->id,
    'name'  => $p->name,
    'price' => (int) $p->price,
    'unit'  => $p->unit ?? '',
  ])->values()->all();
@endphp

@push('scripts')
<script>
/* ── Search debounce ── */
let searchDebounce = null;
function onSearchInput(val) {
  clearTimeout(searchDebounce);
  searchDebounce = setTimeout(() => {
    document.getElementById('search-form').submit();
  }, 500);
}

const PRODUCTS   = @json($productsJs);
const STORE_URL  = '{{ $outlet->route('laundry-orders.store') }}';
const SEARCH_URL = '{{ $outlet->route('customers.search') }}';
const CSRF       = document.querySelector('meta[name="csrf-token"]').content;

let itemCounter       = 0;
let selectedCustomerId = null; // null = pelanggan baru
let saveCustomerOn    = true;  // state toggle
let searchTimer       = null;

function fmtRp(n){ return 'Rp '+Number(n).toLocaleString('id-ID'); }

/* ──────────────────────────────────
   CUSTOMER AUTOCOMPLETE
────────────────────────────────── */
function onCustomerNameInput(val) {
  // Reset customer terpilih jika user mengetik ulang
  if (selectedCustomerId) {
    selectedCustomerId = null;
    document.getElementById('f-customer-id').value = '';
    document.getElementById('customer-registered-badge').style.display = 'none';
    setSaveToggleVisible(true);
  }

  clearTimeout(searchTimer);
  if (val.trim().length < 1) { hideDropdown(); return; }
  searchTimer = setTimeout(() => fetchCustomers(val.trim()), 250);
}

async function fetchCustomers(q) {
  try {
    const res  = await fetch(SEARCH_URL + '?q=' + encodeURIComponent(q), {
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
    });
    const data = await res.json();
    renderDropdown(data);
  } catch(e) { hideDropdown(); }
}

function renderDropdown(customers) {
  const dd = document.getElementById('customer-dropdown');
  if (!customers.length) { hideDropdown(); return; }

  dd.innerHTML = customers.map(c => {
    const phoneStr = c.phone ? `<span style="font-size:11px;color:var(--muted);margin-left:6px">${c.phone}</span>` : '';
    return `<div class="cust-item" onclick="selectCustomer(${c.id},'${escHtml(c.name)}','${escHtml(c.phone||'')}')"
      style="display:flex;align-items:center;gap:10px;padding:10px 14px;cursor:pointer;transition:background .12s"
      onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
      <div style="width:30px;height:30px;border-radius:8px;background:var(--ac-lt);color:var(--ac);display:grid;place-items:center;flex-shrink:0;font-size:12px">
        <i class="fa-solid fa-user"></i>
      </div>
      <div>
        <div style="font-size:13px;font-weight:600;color:var(--text)">${escHtml(c.name)}${phoneStr}</div>
      </div>
      <div style="margin-left:auto;font-size:10px;color:var(--muted)">Terdaftar</div>
    </div>`;
  }).join('');

  dd.style.display = 'block';
}

function selectCustomer(id, name, phone) {
  selectedCustomerId = id;
  document.getElementById('f-customer-id').value    = id;
  document.getElementById('f-customer-name').value  = name;
  document.getElementById('f-customer-phone').value = phone;
  hideDropdown();
  // Tampilkan badge terdaftar
  document.getElementById('customer-registered-badge').style.display = 'flex';
  // Sembunyikan toggle simpan (sudah terdaftar)
  setSaveToggleVisible(false);
}

function hideDropdown() {
  document.getElementById('customer-dropdown').style.display = 'none';
}

function escHtml(str) {
  return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

// Tutup dropdown klik di luar
document.addEventListener('click', function(e) {
  if (!e.target.closest('#f-customer-name') && !e.target.closest('#customer-dropdown')) {
    hideDropdown();
  }
});

/* ──────────────────────────────────
   TOGGLE SIMPAN PELANGGAN
────────────────────────────────── */
function setSaveToggleVisible(show) {
  document.getElementById('save-customer-wrap').style.display = show ? 'flex' : 'none';
}

function toggleSaveCustomer() {
  saveCustomerOn = !saveCustomerOn;
  const track = document.getElementById('toggle-save');
  const thumb = document.getElementById('toggle-save-thumb');
  const sub   = document.getElementById('save-customer-sub');
  track.style.background       = saveCustomerOn ? 'var(--ac)' : 'var(--border)';
  thumb.style.left              = saveCustomerOn ? '16px' : '2px';
  sub.textContent               = saveCustomerOn
    ? 'Data tersimpan untuk pesanan berikutnya'
    : 'Tidak akan disimpan ke daftar pelanggan';
}

/* ──────────────────────────────────
   ITEM ROWS
────────────────────────────────── */
function addItemRow() {
  itemCounter++;
  const id   = itemCounter;
  const opts = PRODUCTS.map(p =>
    `<option value="${p.id}" data-price="${p.price}" data-unit="${p.unit}">${p.name}</option>`
  ).join('');

  const row = document.createElement('div');
  row.className = 'item-row';
  row.id        = `item-row-${id}`;
  row.innerHTML = `
    <div>
      <select id="item-sel-${id}" onchange="onProductChange(${id},this)" class="f-input" style="padding:6px 8px;font-size:12.5px">
        <option value="" disabled selected>— Pilih Layanan —</option>
        ${opts}
      </select>
    </div>
    <div>
      <input id="item-qty-${id}" type="number" class="f-input" value="1" min="0.01" step="any"
        style="font-size:13px;text-align:center;padding:6px 6px" oninput="calcTotal()">
    </div>
    <div>
      <input id="item-unit-${id}" type="text" class="f-input" placeholder="—"
        style="font-size:12.5px;padding:6px 8px;background:var(--surface2);color:var(--sub);cursor:default" readonly>
    </div>
    <div>
      <input id="item-price-${id}" type="number" class="f-input" placeholder="—"
        style="font-size:13px;padding:6px 8px;text-align:right;background:var(--surface2);color:var(--sub);cursor:default" readonly>
    </div>
    <div style="display:flex;align-items:center;justify-content:flex-end">
      <span id="item-subtotal-${id}" style="font-size:13px;font-weight:600;color:var(--text)">—</span>
    </div>
    <div style="display:flex;justify-content:center">
      <button type="button" onclick="removeItemRow(${id})"
        style="width:28px;height:28px;border-radius:7px;border:1px solid var(--border);background:none;cursor:pointer;color:#f87171;font-size:11px;display:flex;align-items:center;justify-content:center">
        <i class="fa-solid fa-trash"></i>
      </button>
    </div>
  `;
  document.getElementById('items-container').appendChild(row);
  calcTotal();
}

function onProductChange(id, sel) {
  if (!sel.value) return;
  const opt = sel.options[sel.selectedIndex];
  document.getElementById(`item-price-${id}`).value = opt.dataset.price || '0';
  document.getElementById(`item-unit-${id}`).value  = opt.dataset.unit  || '';
  calcTotal();
}

function removeItemRow(id) {
  document.getElementById(`item-row-${id}`)?.remove();
  calcTotal();
}

function calcTotal() {
  let total = 0;
  document.querySelectorAll('[id^="item-row-"]').forEach(row => {
    const id    = row.id.replace('item-row-','');
    const qty   = parseFloat(document.getElementById(`item-qty-${id}`)?.value) || 0;
    const price = parseInt(document.getElementById(`item-price-${id}`)?.value)  || 0;
    const sub   = Math.round(qty * price);
    total += sub;
    const subEl = document.getElementById(`item-subtotal-${id}`);
    if (subEl) subEl.textContent = sub > 0 ? fmtRp(sub) : '—';
  });
  document.getElementById('total-display').textContent = fmtRp(total);
  document.getElementById('total-section').style.display = total > 0 ? 'block' : 'none';
}

function collectItems() {
  const items = [];
  document.querySelectorAll('[id^="item-row-"]').forEach(row => {
    const id     = row.id.replace('item-row-','');
    const sel    = document.getElementById(`item-sel-${id}`);
    if (!sel?.value) return; // skip baris yang belum dipilih
    const prodId = parseInt(sel.value);
    const name   = sel.options[sel.selectedIndex]?.text?.trim();
    const price  = parseInt(document.getElementById(`item-price-${id}`)?.value) || 0;
    const qty    = parseFloat(document.getElementById(`item-qty-${id}`)?.value) || 1;
    const unit   = document.getElementById(`item-unit-${id}`)?.value.trim();
    items.push({ product_id: prodId, product_name: name, product_price: price, qty, unit });
  });
  return items;
}

/* ──────────────────────────────────
   FORM SUBMIT
────────────────────────────────── */
document.getElementById('form-new-order').addEventListener('submit', async function(e) {
  e.preventDefault();
  const btn = document.getElementById('btn-save-order');
  const items = collectItems();
  if (!items.length) { showToast('error','Tambahkan minimal 1 item layanan.'); return; }

  const name = document.getElementById('f-customer-name').value.trim();
  if (!name) { showToast('error','Nama pelanggan wajib diisi.'); return; }

  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';

  try {
    const payload = {
      _token:            CSRF,
      customer_id:       selectedCustomerId || null,
      customer_name:     name,
      customer_phone:    document.getElementById('f-customer-phone').value.trim(),
      save_customer:     !selectedCustomerId && saveCustomerOn,
      weight_kg:         null,
      notes:             document.getElementById('f-notes').value.trim(),
      estimated_done_at: document.getElementById('f-estimated-done').value || null,
      items,
    };
    const res  = await fetch(STORE_URL, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'}, body:JSON.stringify(payload) });
    const data = await res.json();
    if (!res.ok) throw new Error(data.message || 'Gagal menyimpan.');

    closeModal('modal-new-order');
    document.getElementById('success-order-num').textContent = 'Nomor Pesanan: ' + data.order_number;
    document.getElementById('btn-print-receipt').href = data.receipt_url + '?autoprint=1';
    document.getElementById('btn-view-detail').href  = data.show_url;
    openModal('modal-success');

    // Reset form & state
    document.getElementById('form-new-order').reset();
    document.getElementById('items-container').innerHTML = '';
    document.getElementById('customer-registered-badge').style.display = 'none';
    document.getElementById('customer-dropdown').style.display = 'none';
    document.getElementById('f-customer-id').value = '';
    selectedCustomerId = null;
    saveCustomerOn = true;
    // Reset toggle visual
    document.getElementById('toggle-save').style.background = 'var(--ac)';
    document.getElementById('toggle-save-thumb').style.left = '16px';
    document.getElementById('save-customer-sub').textContent = 'Data tersimpan untuk pesanan berikutnya';
    setSaveToggleVisible(true);
    itemCounter = 0;
    calcTotal();

  } catch(err) {
    showToast('error', err.message);
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<i class="fa-solid fa-basket-shopping"></i> Terima Pesanan';
  }
});

// Buka modal → reset + tambah item pertama
document.getElementById('modal-new-order').addEventListener('transitionend', function() {
  if (this.classList.contains('open') && !document.querySelectorAll('[id^="item-row-"]').length) {
    addItemRow();
  }
});
</script>

<datalist id="unit-list">
  <option value="kg">
  <option value="helai">
  <option value="paket">
  <option value="pcs">
  <option value="item">
  <option value="pasang">
  <option value="set">
</datalist>
@endpush

</x-laundry-layout>
