<x-app-layout :title="$config['title']">

  {{-- Flash errors --}}
  @if($errors->any())
  <div style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);border-radius:12px;padding:12px 16px;font-size:13px;color:#f87171">
    <i class="fa-solid fa-circle-exclamation" style="margin-right:6px"></i>
    <strong>Terjadi kesalahan:</strong>
    <ul style="margin-top:6px;padding-left:18px">
      @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
  </div>
  @endif

  {{-- Filter Outlet --}}
  <form method="GET" action="{{ route('stock.'.$type) }}" id="filter-form">
    <div style="display:grid;grid-template-columns:1fr auto;gap:12px;align-items:end">
      <div class="f-group" style="margin-bottom:0">
        <label class="f-label">Outlet</label>
        @if ($assignedOutletId ?? false)
          @php $assignedOutlet = $outlets->firstWhere('id', $assignedOutletId); @endphp
          <div class="f-input" style="display:flex;align-items:center;gap:8px;color:var(--ac);font-weight:600;pointer-events:none">
            <i class="fa-solid fa-store"></i>{{ $assignedOutlet?->nama ?? 'Outlet #'.$assignedOutletId }}
            <i class="fa-solid fa-lock" style="font-size:10px;opacity:.7;margin-left:auto"></i>
          </div>
          <input type="hidden" name="outlet_id" value="{{ $assignedOutletId }}">
        @else
          <select name="outlet_id" class="f-input" onchange="document.getElementById('filter-form').submit()">
            <option value="">— Pilih Outlet —</option>
            @foreach($outlets as $o)
            <option value="{{ $o->id }}" @selected($outletId == $o->id)>{{ $o->nama }}</option>
            @endforeach
          </select>
        @endif
      </div>
      <button type="submit" class="btn" style="padding:9px 16px">
        <i class="fa-solid fa-rotate"></i> Muat
      </button>
    </div>
  </form>

  @if(!$outletId)
  <div class="card">
    <div class="card-body" style="text-align:center;padding:56px">
      <i class="fa-solid fa-{{ $config['icon'] }}" style="font-size:40px;color:var(--muted);margin-bottom:14px;display:block"></i>
      <div style="font-size:15px;font-weight:600;color:var(--text);margin-bottom:6px">Pilih Outlet</div>
      <div style="font-size:13px;color:var(--sub)">Pilih outlet terlebih dahulu.</div>
    </div>
  </div>

  @elseif($products->isEmpty())
  <div class="card">
    <div class="card-body" style="text-align:center;padding:56px">
      <i class="fa-solid fa-cubes-stacked" style="font-size:40px;color:var(--muted);margin-bottom:14px;display:block"></i>
      <div style="font-size:15px;font-weight:600;color:var(--text);margin-bottom:6px">Belum ada produk aktif</div>
      <div style="font-size:13px;color:var(--sub)">Tambahkan produk aktif untuk outlet ini terlebih dahulu.</div>
    </div>
  </div>

  @else
  <form method="POST" action="{{ route('stock.'.$type.'.store') }}" id="movement-form">
    @csrf
    <input type="hidden" name="outlet_id" value="{{ $outletId }}">

    <div class="card animate-fadeUp">
      {{-- Card Header --}}
      <div class="card-header">
        <div>
          <div class="card-title" style="color:{{ $config['color'] }}">
            <i class="fa-solid fa-{{ $config['icon'] }}" style="margin-right:8px"></i>
            {{ $config['title'] }}
          </div>
          <div style="font-size:12px;color:var(--muted);margin-top:2px">
            {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}
          </div>
        </div>
        <button type="button" onclick="addRow()" class="btn btn-primary" style="font-size:12px;padding:7px 14px">
          <i class="fa-solid fa-plus"></i> Tambah Produk
        </button>
      </div>

      {{-- Date --}}
      <div style="padding:14px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px">
        <label class="f-label" style="margin-bottom:0;white-space:nowrap;min-width:80px">Tanggal</label>
        <input type="date" name="tanggal" class="f-input" style="max-width:200px" value="{{ $tanggal }}">
      </div>

      {{-- Rows container --}}
      <div id="rows-container">
        {{-- initial row added by JS --}}
      </div>

      {{-- Empty state --}}
      <div id="empty-state" style="text-align:center;padding:48px;color:var(--muted)">
        <i class="fa-solid fa-plus-circle" style="font-size:36px;margin-bottom:12px;display:block;opacity:.4"></i>
        <div style="font-size:13px">Klik <strong style="color:var(--text)">Tambah Produk</strong> untuk mulai mengisi.</div>
      </div>

      {{-- Footer --}}
      <div id="form-footer" style="display:none;padding:16px 20px;border-top:1px solid var(--border);display:none;align-items:center;justify-content:flex-end;gap:10px">
        <button type="button" onclick="clearRows()" class="btn" style="padding:9px 18px;font-size:13px">
          <i class="fa-solid fa-eraser"></i> Bersihkan
        </button>
        <button type="submit" class="btn btn-primary" style="padding:9px 22px;font-size:13px">
          <i class="fa-solid fa-floppy-disk"></i> Simpan
        </button>
      </div>
    </div>
  </form>
  @endif

  {{-- Waste History Today --}}
  @if($type === 'waste' && $outletId)
  <div class="card animate-fadeUp d2">
    <div class="card-header">
      <div class="card-title">
        <i class="fa-solid fa-triangle-exclamation" style="color:#f87171;margin-right:8px"></i>
        Waste Hari Ini
      </div>
      <a href="{{ route('stock.waste.history', ['outlet_id' => $outletId]) }}" class="btn" style="padding:6px 12px;font-size:12px;text-decoration:none">
        <i class="fa-solid fa-clock-rotate-left"></i> Semua Riwayat
      </a>
    </div>
    @if($wasteToday->isEmpty())
    <div class="card-body" style="text-align:center;padding:32px;color:var(--muted);font-size:13px">
      Belum ada catatan waste hari ini.
    </div>
    @else
    <div style="overflow-x:auto">
      <table class="tbl">
        <thead>
          <tr>
            <th style="width:40px;text-align:center">#</th>
            <th>Produk</th>
            <th style="text-align:right;width:80px">Qty</th>
            <th>Keterangan</th>
            <th>Dicatat Oleh</th>
            <th>Waktu</th>
          </tr>
        </thead>
        <tbody>
          @foreach($wasteToday as $i => $w)
          <tr>
            <td style="text-align:center;color:var(--muted);font-size:12px">{{ $i + 1 }}</td>
            <td class="td-main">{{ $w->product->nama ?? '—' }}</td>
            <td style="text-align:right;font-family:monospace;font-weight:700;color:#f87171;white-space:nowrap">
              -{{ number_format($w->qty) }}
            </td>
            <td style="color:var(--muted);font-size:12px;max-width:180px">{{ $w->keterangan ?: '—' }}</td>
            <td style="font-size:12px;color:var(--sub)">{{ $w->user->name ?? '—' }}</td>
            <td style="font-size:11.5px;color:var(--muted);white-space:nowrap">{{ $w->created_at->format('H:i') }}</td>
          </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr>
            <td colspan="6" style="padding:12px 20px;background:rgba(248,113,113,.07);border-top:2px solid rgba(248,113,113,.3)">
              <div style="display:flex;align-items:center;justify-content:space-between">
                <span style="font-weight:700;color:var(--text);font-size:13px">Total Waste Hari Ini</span>
                <span style="font-family:monospace;font-weight:700;color:#f87171;font-size:14px">
                  -{{ number_format($wasteToday->sum('qty')) }}
                  <span style="font-family:inherit;font-size:12px;color:var(--muted);font-weight:400;margin-left:8px">{{ $wasteToday->count() }} entri</span>
                </span>
              </div>
            </td>
          </tr>
        </tfoot>
      </table>
    </div>
    @endif
  </div>
  @endif

  @push('scripts')
  @php
    $stockDataJson = $products->keyBy('id')->map(fn($p) => [
        'nama'   => $p->nama,
        'stok'   => $p->stok_sekarang,
        'satuan' => $p->satuan,
    ]);
    $productOptionsJson = $products->map(fn($p) => [
        'id'     => $p->id,
        'nama'   => $p->nama,
        'stok'   => $p->stok_sekarang,
        'satuan' => $p->satuan,
    ]);
  @endphp
  <script>
  var stockData = @json($stockDataJson);
  var productOptions = @json($productOptionsJson);
  var isWaste = {{ $type === 'waste' ? 'true' : 'false' }};
  var rowCount = 0;

  function renderProductOptions(selectedId) {
    return productOptions.map(function(p) {
      var sel = (p.id == selectedId) ? ' selected' : '';
      return '<option value="' + p.id + '"' + sel + '>' + p.nama + ' (Stok: ' + p.stok + ' ' + p.satuan + ')</option>';
    }).join('');
  }

  function addRow(productId, qty, keterangan) {
    productId   = productId   || '';
    qty         = qty         || '';
    keterangan  = keterangan  || '';

    var idx = rowCount++;
    var row = document.createElement('div');
    row.id  = 'row-' + idx;
    row.style.cssText = 'display:grid;grid-template-columns:1fr 140px 1fr auto;gap:10px;align-items:end;padding:12px 20px;border-bottom:1px solid var(--border)';
    row.innerHTML = `
      <div>
        <label class="f-label">Produk</label>
        <select name="rows[${idx}][product_id]" class="f-input" onchange="updateStokInfo(${idx})" required>
          <option value="">— Pilih Produk —</option>
          ${renderProductOptions(productId)}
        </select>
      </div>
      <div>
        <label class="f-label" id="qty-label-${idx}">Qty</label>
        <input type="number" id="qty-${idx}" name="rows[${idx}][qty]" class="f-input" min="1" placeholder="0" value="${qty}" oninput="clampQty(this)" required>
        <div id="stok-hint-${idx}" style="font-size:11px;margin-top:3px;color:var(--muted)"></div>
      </div>
      <div>
        <label class="f-label">Keterangan <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
        <input type="text" name="rows[${idx}][keterangan]" class="f-input" placeholder="Misal: dari supplier A" value="${keterangan}" maxlength="200">
      </div>
      <div style="padding-bottom:2px">
        <button type="button" onclick="removeRow(${idx})" class="btn btn-danger" style="padding:9px 13px">
          <i class="fa-solid fa-trash"></i>
        </button>
      </div>
    `;
    document.getElementById('rows-container').appendChild(row);
    if (productId) updateStokInfo(idx);
    updateUI();
  }

  function removeRow(idx) {
    var el = document.getElementById('row-' + idx);
    if (el) el.remove();
    updateUI();
  }

  function clearRows() {
    document.getElementById('rows-container').innerHTML = '';
    rowCount = 0;
    updateUI();
  }

  function updateUI() {
    var hasRows = document.getElementById('rows-container').children.length > 0;
    document.getElementById('empty-state').style.display   = hasRows ? 'none' : 'block';
    document.getElementById('form-footer').style.display   = hasRows ? 'flex' : 'none';
  }

  function clampQty(el) {
    var val = parseInt(el.value);
    var max = parseInt(el.getAttribute('max'));
    if (!isNaN(max) && val > max) el.value = max;
    if (val < 1 && el.value !== '') el.value = 1;
  }

  function updateStokInfo(idx) {
    if (!isWaste) return;
    var sel    = document.querySelector('[name="rows[' + idx + '][product_id]"]');
    var qtyEl  = document.getElementById('qty-' + idx);
    var hint   = document.getElementById('stok-hint-' + idx);
    if (!sel || !qtyEl) return;

    var pid  = sel.value;
    var info = pid ? stockData[pid] : null;
    var stok = info ? info.stok : 0;
    var sat  = info ? info.satuan : '';

    if (!pid) {
      qtyEl.removeAttribute('max');
      qtyEl.disabled = false;
      hint.textContent = '';
      return;
    }

    qtyEl.setAttribute('max', stok);

    if (stok <= 0) {
      qtyEl.value    = '';
      qtyEl.disabled = true;
      qtyEl.style.borderColor = '#f87171';
      hint.innerHTML = '<span style="color:#f87171"><i class="fa-solid fa-circle-xmark"></i> Stok habis</span>';
    } else {
      qtyEl.disabled = false;
      qtyEl.style.borderColor = '';
      hint.innerHTML = '<span style="color:var(--muted)">Maks: <strong style="color:var(--text)">' + stok + ' ' + sat + '</strong></span>';
      if (qtyEl.value > stok) qtyEl.value = stok;
    }
  }

  // Init with one empty row
  addRow();
  </script>
  @endpush

</x-app-layout>
