<x-outlet-layout :outlet="$outlet" pageTitle="Buat Laporan Waste">

<style>
.waste-row{display:grid;grid-template-columns:1fr 80px 140px 1fr auto;gap:10px;align-items:end;padding:12px;border-radius:12px;background:var(--surface2);border:1px solid var(--border);margin-bottom:8px}
@media(max-width:900px){.waste-row{grid-template-columns:1fr 1fr}}
.btn-remove-row{width:34px;height:34px;border-radius:8px;border:1px solid rgba(239,68,68,.3);background:rgba(239,68,68,.08);color:#f87171;cursor:pointer;display:grid;place-items:center;font-size:13px;flex-shrink:0;transition:background .15s}
.btn-remove-row:hover{background:rgba(239,68,68,.18)}
</style>

{{-- Back --}}
<div>
  <a href="{{ $outlet->route('waste.index') }}"
     style="display:inline-flex;align-items:center;gap:6px;font-size:13px;color:var(--muted);text-decoration:none;transition:color .15s"
     onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--muted)'">
    <i class="fa-solid fa-arrow-left" style="font-size:11px"></i> Kembali ke Waste / Rusak
  </a>
</div>

<form method="POST" action="{{ $outlet->route('waste.store') }}" id="waste-form">
@csrf

<div class="two-col" style="align-items:start">

  {{-- Left: items --}}
  <div style="display:flex;flex-direction:column;gap:16px">

    {{-- Header info --}}
    <div class="card">
      <div class="card-header">
        <span class="card-title"><i class="fa-solid fa-trash-can" style="color:var(--ac);margin-right:6px"></i>Informasi Laporan</span>
      </div>
      <div class="card-body" style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
        <div>
          <label class="f-label">Tanggal <span style="color:var(--ac)">*</span></label>
          <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}"
                 class="f-input" required>
          @error('date') <p style="color:#f87171;font-size:11px;margin-top:4px">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="f-label">Catatan (opsional)</label>
          <input type="text" name="notes" value="{{ old('notes') }}" class="f-input"
                 placeholder="Misal: bahan sisa shift malam..." maxlength="500">
        </div>
      </div>
    </div>

    {{-- Items --}}
    <div class="card">
      <div class="card-header">
        <span class="card-title">Daftar Produk / Bahan Waste</span>
        <button type="button" onclick="addRow()"
          style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:9px;background:var(--ac-lt);color:var(--ac);border:1px solid rgba(var(--ac-rgb),.25);font-size:12px;font-weight:600;cursor:pointer;font-family:inherit">
          <i class="fa-solid fa-plus"></i> Tambah Baris
        </button>
      </div>
      <div class="card-body" style="padding-bottom:8px">

        {{-- Column labels --}}
        <div style="display:grid;grid-template-columns:1fr 80px 140px 1fr auto;gap:10px;padding:0 12px 6px;font-size:11px;font-weight:600;color:var(--muted);letter-spacing:.5px;text-transform:uppercase">
          <span>Produk</span>
          <span>Qty</span>
          <span>Alasan</span>
          <span>Catatan</span>
          <span style="width:34px"></span>
        </div>

        <div id="rows-container">
          {{-- Initial row --}}
          <div class="waste-row">
            <div>
              <select name="items[0][product_id]" class="f-input" required onchange="updateProductName(this,0)">
                <option value="">— Pilih Produk —</option>
                @foreach($products as $p)
                <option value="{{ $p->id }}" data-name="{{ $p->name }}" data-stock="{{ $p->stock }}">
                  {{ $p->name }} (stok: {{ $p->stock }})
                </option>
                @endforeach
              </select>
            </div>
            <div>
              <input type="number" name="items[0][qty]" min="1" value="1"
                     class="f-input" required style="text-align:center">
            </div>
            <div>
              <select name="items[0][reason]" class="f-input" required>
                @foreach($reasons as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <input type="text" name="items[0][notes]" class="f-input"
                     placeholder="Opsional..." maxlength="200">
            </div>
            <button type="button" class="btn-remove-row" onclick="removeRow(this)" title="Hapus baris">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>
        </div>

        @error('items') <p style="color:#f87171;font-size:11px;margin-top:8px;padding:0 12px">{{ $message }}</p> @enderror
      </div>
    </div>

  </div>

  {{-- Right: summary + submit --}}
  <div style="display:flex;flex-direction:column;gap:16px;position:sticky;top:80px">
    <div class="card">
      <div class="card-header">
        <span class="card-title">Ringkasan</span>
      </div>
      <div class="card-body" style="display:flex;flex-direction:column;gap:12px">
        <div style="display:flex;justify-content:space-between;align-items:center;font-size:13px">
          <span style="color:var(--muted)">Total Baris</span>
          <span id="summary-rows" style="font-weight:600;color:var(--text)">1 produk</span>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;font-size:13px">
          <span style="color:var(--muted)">Total Qty</span>
          <span id="summary-qty" style="font-weight:600;color:var(--text)">1 pcs</span>
        </div>
        <div style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);border-radius:10px;padding:10px 12px;font-size:12px;color:#f87171;display:flex;gap:8px;align-items:flex-start">
          <i class="fa-solid fa-triangle-exclamation" style="margin-top:1px;flex-shrink:0"></i>
          <span>Stok produk akan dikurangi sesuai jumlah yang dicatat. Proses ini tidak dapat diurungkan secara otomatis.</span>
        </div>
        <button type="button" onclick="confirmSubmit()"
          style="width:100%;padding:11px;border-radius:12px;background:var(--ac);color:#fff;border:none;font-size:14px;font-weight:600;cursor:pointer;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:8px;transition:opacity .15s"
          onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
          <i class="fa-solid fa-floppy-disk"></i> Simpan Laporan
        </button>
        <a href="{{ $outlet->route('waste.index') }}"
           style="display:flex;align-items:center;justify-content:center;gap:6px;padding:10px;border-radius:12px;background:var(--surface2);color:var(--muted);border:1px solid var(--border);font-size:13px;font-weight:600;text-decoration:none;text-align:center">
          Batal
        </a>
      </div>
    </div>
  </div>

</div>

</form>

{{-- Confirm modal --}}
<div class="modal-backdrop" id="modal-confirm">
  <div class="modal-box" style="max-width:400px">
    <div style="padding:24px">
      <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px">
        <div style="width:44px;height:44px;border-radius:12px;background:rgba(239,68,68,.12);display:grid;place-items:center;font-size:18px;color:#f87171;flex-shrink:0">
          <i class="fa-solid fa-trash-can"></i>
        </div>
        <div>
          <h3 class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">Konfirmasi Simpan Waste</h3>
          <p style="font-size:12.5px;color:var(--muted);margin-top:2px">Stok produk akan langsung dikurangi</p>
        </div>
      </div>
      <p style="font-size:13px;color:var(--sub);line-height:1.6;margin-bottom:20px">
        Laporan ini akan menyimpan <strong id="confirm-rows" style="color:var(--text)"></strong>
        dengan total <strong id="confirm-qty" style="color:var(--text)"></strong>.
        Stok produk yang terdaftar akan berkurang secara otomatis.
      </p>
      <div style="display:flex;gap:10px">
        <button onclick="closeModal('modal-confirm')"
          style="flex:1;padding:10px;border-radius:10px;background:var(--surface2);color:var(--sub);border:1px solid var(--border);font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
          Batal
        </button>
        <button onclick="document.getElementById('waste-form').submit()"
          style="flex:1;padding:10px;border-radius:10px;background:var(--ac);color:#fff;border:none;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
          Ya, Simpan
        </button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
const products = @json($products->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'stock' => $p->stock]));
const reasons  = @json($reasons);
let rowCount   = 1;

function buildSelect(index, selectedId = '') {
  let opts = '<option value="">— Pilih Produk —</option>';
  products.forEach(p => {
    const sel = (String(p.id) === String(selectedId)) ? ' selected' : '';
    opts += `<option value="${p.id}"${sel} data-name="${p.name}" data-stock="${p.stock}">${p.name} (stok: ${p.stock})</option>`;
  });
  return `<select name="items[${index}][product_id]" class="f-input" required onchange="updateSummary()">${opts}</select>`;
}

function buildReasonSelect(index) {
  let opts = Object.entries(reasons).map(([k,v]) => `<option value="${k}">${v}</option>`).join('');
  return `<select name="items[${index}][reason]" class="f-input" required>${opts}</select>`;
}

function addRow() {
  const i = rowCount++;
  const div = document.createElement('div');
  div.className = 'waste-row';
  div.innerHTML = `
    <div>${buildSelect(i)}</div>
    <div><input type="number" name="items[${i}][qty]" min="1" value="1" class="f-input" required style="text-align:center" oninput="updateSummary()"></div>
    <div>${buildReasonSelect(i)}</div>
    <div><input type="text" name="items[${i}][notes]" class="f-input" placeholder="Opsional..." maxlength="200"></div>
    <button type="button" class="btn-remove-row" onclick="removeRow(this)" title="Hapus baris"><i class="fa-solid fa-xmark"></i></button>
  `;
  document.getElementById('rows-container').appendChild(div);
  updateSummary();
}

function removeRow(btn) {
  const rows = document.querySelectorAll('.waste-row');
  if (rows.length <= 1) { showToast('error','Minimal 1 produk harus dicatat'); return; }
  btn.closest('.waste-row').remove();
  updateSummary();
}

function updateSummary() {
  const rows = document.querySelectorAll('.waste-row');
  let totalQty = 0;
  rows.forEach(row => {
    const qtyInput = row.querySelector('input[type="number"]');
    totalQty += parseInt(qtyInput?.value || 0, 10);
  });
  document.getElementById('summary-rows').textContent = rows.length + ' produk';
  document.getElementById('summary-qty').textContent  = totalQty + ' pcs';
}

function confirmSubmit() {
  const rows = document.querySelectorAll('.waste-row');
  let valid = true;
  rows.forEach(row => {
    const sel = row.querySelector('select');
    if (!sel.value) { valid = false; }
  });
  if (!valid) { showToast('error', 'Pilih produk untuk setiap baris'); return; }

  const rowsTxt = document.getElementById('summary-rows').textContent;
  const qtyTxt  = document.getElementById('summary-qty').textContent;
  document.getElementById('confirm-rows').textContent = rowsTxt;
  document.getElementById('confirm-qty').textContent  = qtyTxt;
  openModal('modal-confirm');
}

// Add change listener to initial row qty input
document.querySelectorAll('input[type="number"]').forEach(el => el.addEventListener('input', updateSummary));
document.querySelectorAll('select').forEach(el => el.addEventListener('change', updateSummary));
updateSummary();
</script>
@endpush

</x-outlet-layout>
