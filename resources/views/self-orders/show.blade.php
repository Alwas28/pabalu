<x-outlet-layout :outlet="$outlet" pageTitle="Tinjau Pesanan">

<style>
.review-wrap{display:grid;grid-template-columns:1fr 360px;gap:20px;align-items:start}
@media(max-width:860px){.review-wrap{grid-template-columns:1fr}}

/* Sections */
.rev-section{background:var(--surface);border:1px solid var(--border);border-radius:14px;overflow:hidden;margin-bottom:16px}
.rev-section-head{padding:12px 16px;border-bottom:1px solid var(--border);font-size:13px;font-weight:700;color:var(--text);display:flex;align-items:center;gap:8px}
.rev-section-body{padding:16px}

/* Info fields */
.field-row{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px}
.field-row:last-child{margin-bottom:0}
.field-label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:5px}
.field-input{width:100%;padding:9px 12px;border-radius:10px;border:1.5px solid var(--border);background:var(--surface2);color:var(--text);font-size:13.5px;font-family:inherit;outline:none;transition:border-color .15s}
.field-input:focus{border-color:var(--ac);background:var(--surface)}
textarea.field-input{resize:vertical;min-height:64px}

/* Items list */
.item-list{display:flex;flex-direction:column;gap:8px}
.item-row{display:flex;align-items:center;gap:10px;padding:10px 12px;background:var(--surface2);border-radius:10px;border:1px solid var(--border)}
.item-name{flex:1;font-size:13.5px;font-weight:600;color:var(--text)}
.item-price{font-size:12.5px;color:var(--muted);margin-right:4px}
.item-subtotal{font-size:13px;font-weight:700;color:var(--text);min-width:80px;text-align:right}
.qty-ctrl{display:flex;align-items:center;gap:0;background:var(--surface);border:1px solid var(--border);border-radius:8px;overflow:hidden;flex-shrink:0}
.qty-btn{width:28px;height:28px;border:none;background:transparent;color:var(--text);font-size:13px;display:grid;place-items:center;cursor:pointer}
.qty-btn:hover{background:var(--surface2)}
.qty-n{font-size:13px;font-weight:700;min-width:28px;text-align:center;color:var(--ac)}
.btn-remove{width:28px;height:28px;border-radius:7px;border:none;background:rgba(248,113,113,.12);color:#ef4444;cursor:pointer;display:grid;place-items:center;font-size:12px;flex-shrink:0}
.btn-remove:hover{background:rgba(248,113,113,.22)}

.empty-items{text-align:center;padding:24px;color:var(--muted);font-size:13px}

/* Add product */
.add-product-row{display:flex;gap:8px;margin-top:12px;padding-top:12px;border-top:1px dashed var(--border)}
.add-select{flex:1;padding:9px 12px;border-radius:10px;border:1.5px solid var(--border);background:var(--surface2);color:var(--text);font-size:13px;font-family:inherit;outline:none}
.add-select:focus{border-color:var(--ac)}
.btn-add-product{padding:9px 14px;border-radius:10px;border:1.5px solid var(--ac);background:transparent;color:var(--ac);font-size:13px;font-weight:700;cursor:pointer;font-family:inherit;display:flex;align-items:center;gap:5px;white-space:nowrap}
.btn-add-product:hover{background:rgba(var(--ac-rgb),.08)}

/* Summary sidebar */
.summary-card{background:var(--surface);border:1px solid var(--border);border-radius:14px;overflow:hidden;position:sticky;top:16px}
.summary-head{padding:12px 16px;border-bottom:1px solid var(--border);font-size:13px;font-weight:700}
.summary-body{padding:16px}
.summary-row{display:flex;justify-content:space-between;align-items:center;padding:5px 0;font-size:13px}
.summary-row .label{color:var(--muted)}
.summary-total{display:flex;justify-content:space-between;align-items:center;padding:12px 16px;border-top:2px solid var(--border);font-size:15px;font-weight:800;color:var(--text)}

.btn-approve{width:100%;padding:13px;border-radius:12px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:14.5px;font-weight:800;cursor:pointer;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:8px;margin-top:14px}
.btn-approve:hover{opacity:.9}
.btn-approve:disabled{opacity:.5;cursor:not-allowed}
.btn-back{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13px;font-weight:600;text-decoration:none;margin-bottom:16px}
.btn-back:hover{background:var(--surface);color:var(--text)}

.origin-badge{display:inline-flex;align-items:center;gap:6px;padding:4px 12px;border-radius:99px;background:rgba(167,139,250,.12);color:#7c3aed;font-size:11.5px;font-weight:700;margin-bottom:14px}
</style>

<a href="{{ $outlet->route('self-orders.index') }}" class="btn-back">
  <i class="fa-solid fa-arrow-left"></i>Pesanan Masuk
</a>

<div class="origin-badge">
  <i class="fa-solid fa-qrcode" style="font-size:10px"></i>
  Pesanan dari QR Menu &mdash; {{ $order->order_number }}
  &mdash; <span style="opacity:.7">{{ $order->created_at->diffForHumans() }}</span>
</div>

<form method="POST" action="{{ $outlet->route('self-orders.approve', [$order]) }}" id="approve-form">
@csrf

<div class="review-wrap">

  {{-- Kiri: info + items --}}
  <div>
    {{-- Info pelanggan --}}
    <div class="rev-section">
      <div class="rev-section-head">
        <i class="fa-solid fa-user" style="font-size:12px;color:var(--ac)"></i>Info Pelanggan
      </div>
      <div class="rev-section-body">
        <div class="field-row">
          <div>
            <div class="field-label">Nama Pelanggan <span style="color:#ef4444">*</span></div>
            <input type="text" name="customer_name" class="field-input"
              value="{{ old('customer_name', $order->customer_name) }}"
              placeholder="Nama pelanggan" required>
          </div>
          <div>
            <div class="field-label">Nomor Meja</div>
            <input type="text" name="table_number" class="field-input"
              value="{{ old('table_number', $order->table_number) }}"
              placeholder="Contoh: 5 atau A3">
          </div>
        </div>
        <div>
          <div class="field-label">Catatan Tambahan</div>
          <textarea name="notes" class="field-input" placeholder="Catatan khusus (opsional)">{{ old('notes', $order->notes) }}</textarea>
        </div>
      </div>
    </div>

    {{-- Items --}}
    <div class="rev-section">
      <div class="rev-section-head">
        <i class="fa-solid fa-list" style="font-size:12px;color:var(--ac)"></i>Item Pesanan
      </div>
      <div class="rev-section-body">
        <div class="item-list" id="item-list">
          @foreach($order->items as $item)
          @php $pid = $item->product_id ?? 0; @endphp
          <div class="item-row" id="row-{{ $pid }}" data-pid="{{ $pid }}"
               data-price="{{ $item->product_price }}" data-name="{{ $item->product_name }}">
            <div class="item-name">{{ $item->product_name }}</div>
            <div class="item-price">Rp {{ number_format($item->product_price, 0, ',', '.') }}</div>
            <div class="qty-ctrl">
              <button type="button" class="qty-btn" onclick="changeQty({{ $pid }}, -1)">
                <i class="fa-solid fa-minus" style="font-size:10px"></i>
              </button>
              <span class="qty-n" id="qty-disp-{{ $pid }}">{{ $item->qty }}</span>
              <button type="button" class="qty-btn" onclick="changeQty({{ $pid }}, 1)">
                <i class="fa-solid fa-plus" style="font-size:10px"></i>
              </button>
            </div>
            <input type="hidden" name="items[{{ $pid }}][qty]" id="qty-inp-{{ $pid }}" value="{{ $item->qty }}">
            <div class="item-subtotal" id="sub-{{ $pid }}">
              Rp {{ number_format($item->product_price * $item->qty, 0, ',', '.') }}
            </div>
            <button type="button" class="btn-remove" onclick="removeItem({{ $pid }})" title="Hapus item">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>
          @endforeach
        </div>

        <div class="empty-items" id="empty-items" style="{{ $order->items->count() > 0 ? 'display:none' : '' }}">
          <i class="fa-solid fa-triangle-exclamation" style="font-size:24px;display:block;margin-bottom:8px;color:#fbbf24"></i>
          Belum ada item. Tambahkan produk di bawah.
        </div>

        {{-- Tambah produk --}}
        <div class="add-product-row">
          <select id="product-select" class="add-select">
            <option value="">— Pilih produk untuk ditambah —</option>
            @foreach($products as $p)
            <option value="{{ $p->id }}"
              data-name="{{ $p->name }}"
              data-price="{{ $p->price }}">
              {{ $p->name }} — Rp {{ number_format($p->price, 0, ',', '.') }}
            </option>
            @endforeach
          </select>
          <button type="button" class="btn-add-product" onclick="addProduct()">
            <i class="fa-solid fa-plus"></i>Tambah
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- Kanan: summary & actions --}}
  <div>
    <div class="summary-card">
      <div class="summary-head">
        <i class="fa-solid fa-receipt" style="font-size:12px;color:var(--ac);margin-right:6px"></i>Ringkasan
      </div>
      <div class="summary-body">
        <div class="summary-row">
          <span class="label">Nomor Pesanan</span>
          <span style="font-family:monospace;font-weight:700">{{ $order->order_number }}</span>
        </div>
        <div class="summary-row">
          <span class="label">Waktu Pesan</span>
          <span>{{ $order->created_at->format('H:i') }}</span>
        </div>
        <div class="summary-row">
          <span class="label">Jumlah Item</span>
          <span id="summary-items">{{ $order->items->sum('qty') }}</span>
        </div>
      </div>
      <div class="summary-total">
        <span>Total</span>
        <span id="summary-total">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
      </div>

      <div style="padding:0 16px 16px;display:flex;flex-direction:column;gap:8px">
        <button type="submit" form="approve-form" class="btn-approve" id="btn-approve">
          <i class="fa-solid fa-arrow-right"></i>Masukkan ke Antrian
        </button>

        <button type="button" onclick="doReject()"
          style="width:100%;padding:10px;border-radius:12px;border:1.5px solid #fecaca;background:rgba(248,113,113,.08);color:#ef4444;font-size:13.5px;font-weight:700;cursor:pointer;font-family:inherit">
          <i class="fa-solid fa-ban" style="margin-right:5px"></i>Tolak Pesanan
        </button>
      </div>
    </div>
  </div>

</div>
</form>

{{-- Form reject terpisah — tidak boleh nested dalam form approve --}}
<form method="POST" action="{{ $outlet->route('self-orders.reject', [$order]) }}"
  id="reject-form" style="display:none">
  @csrf @method('PATCH')
</form>

@push('scripts')
<script>
const FMT = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');
const prices = {};  // pid → price

// Init prices from existing rows
document.querySelectorAll('#item-list .item-row').forEach(row => {
  prices[row.dataset.pid] = parseInt(row.dataset.price);
});

function getQty(pid) {
  const inp = document.getElementById('qty-inp-' + pid);
  return inp ? parseInt(inp.value) || 0 : 0;
}

function changeQty(pid, delta) {
  const inp  = document.getElementById('qty-inp-' + pid);
  const disp = document.getElementById('qty-disp-' + pid);
  const sub  = document.getElementById('sub-' + pid);
  if (!inp) return;
  let q = Math.max(1, (parseInt(inp.value) || 1) + delta);
  inp.value        = q;
  disp.textContent = q;
  sub.textContent  = FMT((prices[pid] || 0) * q);
  recalc();
}

function removeItem(pid) {
  const row = document.getElementById('row-' + pid);
  if (row) row.remove();
  delete prices[pid];
  // restore product to select
  const sel = document.getElementById('product-select');
  const row2 = document.querySelector('#item-list .item-row[data-pid="' + pid + '"]');
  recalc();
  checkEmpty();
}

function addProduct() {
  const sel = document.getElementById('product-select');
  const pid = sel.value;
  if (!pid) return;
  const opt   = sel.options[sel.selectedIndex];
  const name  = opt.dataset.name;
  const price = parseInt(opt.dataset.price);

  // if already exists, just increase qty
  if (document.getElementById('row-' + pid)) {
    changeQty(pid, 1);
    sel.value = '';
    return;
  }

  prices[pid] = price;
  const list  = document.getElementById('item-list');
  const div   = document.createElement('div');
  div.className = 'item-row';
  div.id        = 'row-' + pid;
  div.dataset.pid   = pid;
  div.dataset.price = price;
  div.dataset.name  = name;
  div.innerHTML = `
    <div class="item-name">${name}</div>
    <div class="item-price">${FMT(price)}</div>
    <div class="qty-ctrl">
      <button type="button" class="qty-btn" onclick="changeQty(${pid}, -1)">
        <i class="fa-solid fa-minus" style="font-size:10px"></i>
      </button>
      <span class="qty-n" id="qty-disp-${pid}">1</span>
      <button type="button" class="qty-btn" onclick="changeQty(${pid}, 1)">
        <i class="fa-solid fa-plus" style="font-size:10px"></i>
      </button>
    </div>
    <input type="hidden" name="items[${pid}][qty]" id="qty-inp-${pid}" value="1">
    <div class="item-subtotal" id="sub-${pid}">${FMT(price)}</div>
    <button type="button" class="btn-remove" onclick="removeItem(${pid})" title="Hapus item">
      <i class="fa-solid fa-xmark"></i>
    </button>
  `;
  list.appendChild(div);
  sel.value = '';
  recalc();
  checkEmpty();
}

function recalc() {
  let total = 0;
  let totalQty = 0;
  document.querySelectorAll('#item-list .item-row').forEach(row => {
    const pid = row.dataset.pid;
    const q   = getQty(pid);
    const p   = prices[pid] || 0;
    total    += p * q;
    totalQty += q;
  });
  document.getElementById('summary-total').textContent = FMT(total);
  document.getElementById('summary-items').textContent = totalQty;
}

function checkEmpty() {
  const rows   = document.querySelectorAll('#item-list .item-row');
  const empty  = document.getElementById('empty-items');
  const approve = document.getElementById('btn-approve');
  empty.style.display   = rows.length === 0 ? '' : 'none';
  approve.disabled      = rows.length === 0;
}

function doReject() {
  if (confirm('Tolak dan batalkan pesanan ini?')) {
    document.getElementById('reject-form').submit();
  }
}

document.getElementById('approve-form').addEventListener('submit', function(e) {
  const rows = document.querySelectorAll('#item-list .item-row');
  if (rows.length === 0) {
    e.preventDefault();
    alert('Tambahkan minimal satu item sebelum memasukkan ke antrian.');
  }
});
</script>
@endpush

</x-outlet-layout>
