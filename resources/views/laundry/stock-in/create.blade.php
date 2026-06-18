<x-outlet-layout :outlet="$outlet" pageTitle="Tambah Stok Masuk">

@push('scripts')
<script>
// Track filled products count
function countFilled() {
  const inputs = document.querySelectorAll('.qty-input');
  let filled = 0;
  inputs.forEach(el => {
    const v = parseInt(el.value) || 0;
    if (v > 0) filled++;
  });
  document.getElementById('filledCount').textContent = filled;
  document.getElementById('submitBar').style.display = filled > 0 ? 'flex' : 'none';
}

function stepQty(btn, delta) {
  const input = btn.parentElement.querySelector('.qty-input');
  const current = parseInt(input.value) || 0;
  const next = Math.max(0, current + delta);
  input.value = next;
  input.dispatchEvent(new Event('input'));
  highlightRow(input);
}

function highlightRow(input) {
  const row = input.closest('tr');
  const v = parseInt(input.value) || 0;
  if (v > 0) {
    row.style.background = 'var(--ac-lt)';
    row.querySelector('.qty-badge').style.display = 'inline-flex';
    row.querySelector('.qty-badge').textContent = '+' + v;
  } else {
    row.style.background = '';
    row.querySelector('.qty-badge').style.display = 'none';
  }
  countFilled();
}

function resetCategory(btn) {
  const tbody = btn.closest('.category-block').querySelector('tbody');
  tbody.querySelectorAll('.qty-input').forEach(el => {
    el.value = 0;
    highlightRow(el);
  });
}

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.qty-input').forEach(el => {
    el.addEventListener('input', () => highlightRow(el));
    el.addEventListener('change', () => {
      if ((parseInt(el.value) || 0) < 0) el.value = 0;
      highlightRow(el);
    });
  });
  countFilled();
});

@if($outlet->enable_barcode_scanner)
// ── Barcode scan handler ────────────────────────────────
document.addEventListener('bscan', e => {
  const sku = (e.detail || '').trim().toLowerCase();
  if (!sku) return;

  const row = document.querySelector(`tr[data-sku="${CSS.escape(sku)}"]`);
  if (!row) {
    siToast('Produk tidak ditemukan: ' + sku, '#64748b');
    return;
  }

  row.scrollIntoView({ behavior: 'smooth', block: 'center' });

  const plusBtn = row.querySelector('button:last-of-type');
  if (plusBtn) plusBtn.click();

  row.style.outline = '2px solid var(--ac)';
  row.style.outlineOffset = '-2px';
  row.style.borderRadius = '8px';
  setTimeout(() => { row.style.outline = ''; row.style.outlineOffset = ''; }, 800);

  siToast('✓ ' + (row.dataset.name || sku) + ' +1', 'var(--ac)');
});

function siToast(msg, color) {
  let el = document.getElementById('si-toast');
  if (!el) {
    el = document.createElement('div');
    el.id = 'si-toast';
    el.style.cssText = 'position:fixed;bottom:90px;left:50%;transform:translateX(-50%);z-index:600;padding:10px 20px;border-radius:12px;font-size:13.5px;font-weight:600;color:#fff;pointer-events:none;transition:opacity .3s;white-space:nowrap;max-width:90vw;text-align:center;box-shadow:0 8px 24px rgba(0,0,0,.4)';
    document.body.appendChild(el);
  }
  el.style.background = color || 'var(--ac)';
  el.style.opacity = '1';
  el.textContent = msg;
  clearTimeout(el._t);
  el._t = setTimeout(() => { el.style.opacity = '0'; }, 2000);
}
@endif
</script>
@endpush

{{-- ── HEADER ── --}}
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px" class="animate-fadeUp">
  <div>
    <a href="{{ $outlet->route('stock-in.index') }}"
      style="display:inline-flex;align-items:center;gap:6px;font-size:13px;color:var(--muted);text-decoration:none;margin-bottom:6px;transition:color .15s"
      onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--muted)'">
      <i class="fa-solid fa-arrow-left" style="font-size:11px"></i> Riwayat Tambah Stok
    </a>
    <h2 class="font-display" style="font-size:20px;font-weight:700;color:var(--text)">Tambah Stok Masuk</h2>
    <p style="font-size:13px;color:var(--muted);margin-top:2px">Isi jumlah stok yang diterima per produk</p>
  </div>
</div>

<form action="{{ $outlet->route('stock-in.store') }}" method="POST" class="animate-fadeUp d1">
@csrf

{{-- ── INFO PENERIMAAN ── --}}
<div class="card" style="margin-bottom:20px">
  <div class="card-header">
    <span class="card-title">
      <i class="fa-solid fa-file-invoice" style="color:var(--ac);margin-right:8px;font-size:14px"></i>
      Informasi Penerimaan
    </span>
  </div>
  <div style="padding:20px;display:grid;grid-template-columns:1fr 3fr;gap:16px">

    {{-- Tanggal --}}
    <div>
      <label class="f-label">Tanggal <span style="color:#f87171">*</span></label>
      <input type="date" name="date" value="{{ old('date', now()->format('Y-m-d')) }}"
        class="f-input" style="{{ $errors->has('date') ? 'border-color:#f87171' : '' }}" required>
      @error('date')<p style="font-size:11.5px;color:#f87171;margin-top:4px">{{ $message }}</p>@enderror
    </div>

    {{-- Catatan --}}
    <div>
      <label class="f-label">Catatan <span style="color:var(--muted);font-weight:400;font-size:11px">(opsional)</span></label>
      <textarea name="notes" rows="2" placeholder="Catatan tambahan tentang penerimaan ini..."
        class="f-input" style="resize:vertical;{{ $errors->has('notes') ? 'border-color:#f87171' : '' }}">{{ old('notes') }}</textarea>
      @error('notes')<p style="font-size:11.5px;color:#f87171;margin-top:4px">{{ $message }}</p>@enderror
    </div>

  </div>
</div>

{{-- ── BARCODE SCANNER ── --}}
@if($outlet->enable_barcode_scanner && !$products->isEmpty())
<div class="card animate-fadeUp d1" style="padding:16px 20px;margin-bottom:4px">
  <div style="font-size:12px;color:var(--muted);margin-bottom:8px;display:flex;align-items:center;gap:6px">
    <i class="fa-solid fa-barcode" style="font-size:11px"></i>
    Scan barcode produk untuk langsung menambah qty +1
  </div>
  <x-barcode-scanner />
</div>
@endif

{{-- ── PRODUK PER KATEGORI ── --}}
@if($products->isEmpty())
<div class="card" style="padding:60px 24px;text-align:center">
  <div style="width:60px;height:60px;border-radius:16px;background:var(--ac-lt);display:grid;place-items:center;margin:0 auto 16px;font-size:22px;color:var(--ac)">
    <i class="fa-solid fa-box-open"></i>
  </div>
  <h3 class="font-display" style="font-size:17px;font-weight:700;color:var(--text);margin-bottom:6px">Belum Ada Produk</h3>
  <p style="font-size:13.5px;color:var(--muted);max-width:320px;margin:0 auto 20px">
    Tambahkan produk terlebih dahulu sebelum mencatat stok masuk.
  </p>
  <a href="{{ $outlet->route('products.index') }}"
    style="display:inline-flex;align-items:center;gap:8px;padding:10px 20px;border-radius:12px;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;text-decoration:none;font-size:13.5px;font-weight:700">
    <i class="fa-solid fa-arrow-right" style="font-size:11px"></i> Kelola Produk
  </a>
</div>
@else

@if($errors->has('items'))
<div style="margin-bottom:16px;padding:12px 16px;border-radius:12px;background:rgba(248,113,113,.12);border:1px solid rgba(248,113,113,.3);color:#f87171;font-size:13.5px">
  <i class="fa-solid fa-triangle-exclamation" style="margin-right:6px"></i>{{ $errors->first('items') }}
</div>
@endif

@error('error')
<div style="margin-bottom:16px;padding:12px 16px;border-radius:12px;background:rgba(248,113,113,.12);border:1px solid rgba(248,113,113,.3);color:#f87171;font-size:13.5px">
  <i class="fa-solid fa-triangle-exclamation" style="margin-right:6px"></i>{{ $message }}
</div>
@enderror

@foreach($products as $categoryName => $items)
<div class="card category-block" style="margin-bottom:16px">
  {{-- Category header --}}
  <div style="padding:14px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
    <div style="display:flex;align-items:center;gap:10px">
      <div style="width:8px;height:8px;border-radius:50%;background:var(--ac)"></div>
      <span style="font-weight:700;font-size:14px;color:var(--text)">{{ $categoryName }}</span>
      <span style="font-size:12px;color:var(--muted)">({{ $items->count() }} produk)</span>
    </div>
    <button type="button" onclick="resetCategory(this)"
      style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:8px;border:1px solid var(--border);background:transparent;font-size:11.5px;color:var(--muted);cursor:pointer;transition:all .15s"
      onmouseover="this.style.background='var(--surface2)';this.style.color='var(--text)'"
      onmouseout="this.style.background='transparent';this.style.color='var(--muted)'">
      <i class="fa-solid fa-rotate-left" style="font-size:10px"></i> Reset
    </button>
  </div>

  <div style="overflow-x:auto">
    <table class="tbl">
      <thead>
        <tr>
          <th>Produk</th>
          <th style="text-align:center;width:80px">Stok Saat Ini</th>
          <th style="text-align:center;width:200px">Jumlah Masuk</th>
          <th>Catatan Item</th>
        </tr>
      </thead>
      <tbody>
        @foreach($items as $product)
        <tr style="transition:background .2s" id="si-row-{{ $product->id }}" data-sku="{{ $product->sku ?? '' }}" data-name="{{ $product->name }}">
          <td>
            <div style="display:flex;align-items:center;gap:10px">
              @if($product->image)
                <img src="{{ Storage::url($product->image) }}" alt=""
                  style="width:36px;height:36px;border-radius:8px;object-fit:cover;border:1px solid var(--border)">
              @else
                <div style="width:36px;height:36px;border-radius:8px;background:var(--surface2);display:grid;place-items:center;color:var(--muted);font-size:14px">
                  <i class="fa-solid fa-cube"></i>
                </div>
              @endif
              <div>
                <div style="font-weight:600;font-size:13.5px;color:var(--text)">{{ $product->name }}</div>
                @if($product->sku)
                <div style="font-size:11px;color:var(--muted);font-family:monospace">{{ $product->sku }}</div>
                @endif
              </div>
            </div>
          </td>
          <td style="text-align:center">
            <span style="font-weight:700;font-size:15px;color:{{ $product->isLowStock() ? '#f87171' : 'var(--text)' }}">
              {{ $product->stock }}
            </span>
            <span style="font-size:11px;color:var(--muted)"> {{ $product->unit }}</span>
            @if($product->isLowStock())
              <div style="font-size:10px;font-weight:700;color:#f87171;margin-top:2px">KRITIS</div>
            @endif
          </td>
          <td style="text-align:center;padding:8px 12px">
            <div style="display:flex;align-items:center;justify-content:center;gap:0;position:relative">
              <button type="button" onclick="stepQty(this, -1)"
                style="width:32px;height:36px;border:1px solid var(--border);border-right:none;border-radius:8px 0 0 8px;background:var(--surface2);color:var(--text);font-size:14px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background .15s"
                onmouseover="this.style.background='var(--border)'" onmouseout="this.style.background='var(--surface2)'">
                <i class="fa-solid fa-minus" style="font-size:10px"></i>
              </button>
              <input type="number" name="items[{{ $product->id }}][qty]"
                value="{{ old('items.'.$product->id.'.qty', 0) }}"
                min="0" class="qty-input"
                style="width:64px;height:36px;border:1px solid var(--border);background:var(--bg);color:var(--text);text-align:center;font-size:15px;font-weight:700;outline:none;-moz-appearance:textfield">
              <button type="button" onclick="stepQty(this, 1)"
                style="width:32px;height:36px;border:1px solid var(--border);border-left:none;border-radius:0 8px 8px 0;background:var(--surface2);color:var(--text);font-size:14px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background .15s"
                onmouseover="this.style.background='var(--border)'" onmouseout="this.style.background='var(--surface2)'">
                <i class="fa-solid fa-plus" style="font-size:10px"></i>
              </button>
              <span class="qty-badge" style="display:none;position:absolute;top:-8px;right:-8px;background:var(--ac);color:#fff;font-size:10px;font-weight:800;padding:1px 5px;border-radius:99px"></span>
            </div>
          </td>
          <td>
            <input type="text" name="items[{{ $product->id }}][notes]"
              value="{{ old('items.'.$product->id.'.notes') }}"
              placeholder="Catatan (opsional)"
              class="f-input" style="font-size:12.5px;padding:6px 10px">
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endforeach
@endif

{{-- ── STICKY SUBMIT BAR ── --}}
<div id="submitBar"
  style="display:none;position:sticky;bottom:0;z-index:40;background:var(--card);border-top:1px solid var(--border);padding:14px 24px;margin:0 -24px;align-items:center;justify-content:space-between;gap:16px;box-shadow:0 -4px 20px rgba(0,0,0,.08)">
  <div style="font-size:13.5px;color:var(--sub)">
    <span style="font-weight:700;color:var(--text);font-size:15px" id="filledCount">0</span>
    produk akan ditambah stoknya
  </div>
  <div style="display:flex;gap:12px">
    <a href="{{ $outlet->route('stock-in.index') }}"
      style="display:inline-flex;align-items:center;gap:6px;padding:10px 18px;border-radius:12px;border:1px solid var(--border);background:transparent;color:var(--sub);font-size:13.5px;font-weight:600;text-decoration:none;cursor:pointer;transition:all .15s"
      onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
      Batal
    </a>
    <button type="submit"
      style="display:inline-flex;align-items:center;gap:8px;padding:10px 22px;border-radius:12px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13.5px;font-weight:700;cursor:pointer;transition:opacity .15s"
      onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
      <i class="fa-solid fa-floppy-disk" style="font-size:12px"></i> Simpan Stok Masuk
    </button>
  </div>
</div>

</form>

<style>
input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button { -webkit-appearance:none; margin:0; }
</style>

</x-outlet-layout>
