<x-app-layout title="Opening Stok">

  {{-- Header --}}
  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
    <div style="font-size:13px;color:var(--sub)">Input stok awal setiap hari sebelum operasional dimulai.</div>
    @if($outlet && $existing->isNotEmpty())
    <span class="badge badge-green"><i class="fa-solid fa-circle-check"></i> Opening hari ini sudah diisi</span>
    @endif
  </div>

  {{-- Filter Outlet & Tanggal --}}
  <form method="GET" action="{{ route('opening.index') }}" id="filter-form">
    <div style="display:grid;grid-template-columns:1fr 1fr auto;gap:12px;align-items:end">
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
      <div class="f-group" style="margin-bottom:0">
        <label class="f-label">Tanggal</label>
        <input type="date" name="tanggal" class="f-input" value="{{ $tanggal }}"
          onchange="document.getElementById('filter-form').submit()">
      </div>
      <button type="submit" class="btn" style="padding:9px 16px">
        <i class="fa-solid fa-rotate"></i> Muat
      </button>
    </div>
  </form>

  @if(!$outletId)
  <div class="card">
    <div class="card-body" style="text-align:center;padding:56px">
      <i class="fa-solid fa-box-open" style="font-size:40px;color:var(--muted);margin-bottom:14px;display:block"></i>
      <div style="font-size:15px;font-weight:600;color:var(--text);margin-bottom:6px">Pilih Outlet</div>
      <div style="font-size:13px;color:var(--sub)">Pilih outlet terlebih dahulu untuk mengisi opening stok.</div>
    </div>
  </div>

  @elseif($products->isEmpty())
  <div class="card">
    <div class="card-body" style="text-align:center;padding:56px">
      <i class="fa-solid fa-cubes-stacked" style="font-size:40px;color:var(--muted);margin-bottom:14px;display:block"></i>
      <div style="font-size:15px;font-weight:600;color:var(--text);margin-bottom:6px">Belum ada produk</div>
      <div style="font-size:13px;color:var(--sub)">Tambahkan produk aktif untuk outlet <strong>{{ $outlet->nama }}</strong> terlebih dahulu.</div>
    </div>
  </div>

  @else
  <form method="POST" action="{{ route('opening.store') }}">
    @csrf
    <input type="hidden" name="outlet_id" value="{{ $outletId }}">
    <input type="hidden" name="tanggal" value="{{ $tanggal }}">

    <div class="card animate-fadeUp">
      <div class="card-header">
        <div>
          <div class="card-title">
            <i class="fa-solid fa-box-open" style="color:var(--ac);margin-right:8px"></i>
            Opening Stok — {{ $outlet->nama }}
          </div>
          <div style="font-size:12px;color:var(--muted);margin-top:2px">
            {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}
          </div>
        </div>
        <div style="display:flex;gap:8px;align-items:center">
          <button type="button" onclick="isiSemua()" class="btn" style="font-size:12px;padding:7px 12px">
            <i class="fa-solid fa-wand-magic-sparkles"></i> Salin dari kemarin
          </button>
          <button type="button" onclick="resetSemua()" class="btn" style="font-size:12px;padding:7px 12px">
            <i class="fa-solid fa-eraser"></i> Reset
          </button>
        </div>
      </div>

      {{-- Group by category --}}
      @php
        $grouped = $products->groupBy(fn($p) => $p->category?->nama ?? 'Tanpa Kategori');
      @endphp

      @foreach($grouped as $kategori => $items)
      <div style="border-bottom:1px solid var(--border)">
        <div style="padding:10px 20px;background:var(--surface2);font-size:11px;font-weight:700;
                    letter-spacing:.8px;text-transform:uppercase;color:var(--muted)">
          <i class="fa-solid fa-tag" style="margin-right:6px"></i>{{ $kategori }}
        </div>
        @foreach($items as $i => $product)
        @php $stok = $currentStock[$product->id] ?? 0; @endphp
        <div class="opn-row"
          onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">

          {{-- Info produk --}}
          <div class="opn-info" style="display:flex;align-items:center;gap:10px">
            <div style="width:36px;height:36px;border-radius:10px;flex-shrink:0;
                        background:var(--ac-lt);color:var(--ac);display:grid;place-items:center;font-size:15px">
              <i class="fa-solid fa-cube"></i>
            </div>
            <div>
              <div style="font-weight:600;color:var(--text);font-size:13.5px">{{ $product->nama }}</div>
              <div style="font-size:11.5px;color:var(--muted)">
                Rp {{ number_format($product->harga_jual, 0, ',', '.') }} / {{ $product->satuan }}
              </div>
            </div>
          </div>

          {{-- Stok saat ini --}}
          <div class="opn-stok" style="text-align:center;min-width:70px">
            <div style="font-size:11px;color:var(--muted);margin-bottom:2px">Stok skrg</div>
            <div style="font-family:'Clash Display',sans-serif;font-size:18px;font-weight:700;
                        color:{{ $stok > 0 ? 'var(--ac)' : '#f87171' }}">
              {{ $stok }}
            </div>
          </div>

          {{-- Input opening --}}
          <div class="opn-input" style="display:flex;align-items:center;gap:8px">
            <input type="hidden" name="items[{{ $loop->parent->index * 100 + $loop->index }}][product_id]" value="{{ $product->id }}">
            <button type="button" onclick="adjustQty({{ $product->id }}, -1)"
              style="width:32px;height:32px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);
                     cursor:pointer;color:var(--sub);font-size:14px;flex-shrink:0;transition:background .15s"
              onmouseover="this.style.background='var(--border)'" onmouseout="this.style.background='var(--surface2)'">
              −
            </button>
            <input type="number" id="qty-{{ $product->id }}"
              name="items[{{ $loop->parent->index * 100 + $loop->index }}][qty]"
              value="{{ $existing[$product->id] ?? 0 }}"
              min="0" class="f-input"
              style="width:70px;text-align:center;padding:7px 8px;font-weight:700;font-size:15px"
              data-product="{{ $product->id }}">
            <button type="button" onclick="adjustQty({{ $product->id }}, 1)"
              style="width:32px;height:32px;border-radius:8px;background:var(--ac-lt);color:var(--ac);
                     border:1px solid transparent;cursor:pointer;font-size:14px;font-weight:700;flex-shrink:0;transition:background .15s"
              onmouseover="this.style.background='var(--ac)';this.style.color='#fff'"
              onmouseout="this.style.background='var(--ac-lt)';this.style.color='var(--ac)'">
              +
            </button>
          </div>

        </div>
        @endforeach
      </div>
      @endforeach

      {{-- Footer --}}
      <div style="padding:16px 20px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
        <div style="font-size:12.5px;color:var(--muted)">
          <i class="fa-solid fa-circle-info" style="margin-right:5px;color:var(--ac)"></i>
          Produk dengan qty 0 tidak akan disimpan.
        </div>
        <div style="display:flex;gap:10px">
          <button type="reset" class="btn" style="padding:9px 18px;font-size:13px">
            <i class="fa-solid fa-rotate-left"></i> Reset Form
          </button>
          <button type="submit" class="btn btn-primary" style="padding:9px 20px;font-size:13px">
            <i class="fa-solid fa-floppy-disk"></i> Simpan Opening Stok
          </button>
        </div>
      </div>

    </div>
  </form>
  @endif

  @push('scripts')
  <script>
  function adjustQty(productId, delta) {
    var input = document.getElementById('qty-' + productId);
    var val   = parseInt(input.value) || 0;
    input.value = Math.max(0, val + delta);
  }
  function resetSemua() {
    document.querySelectorAll('input[data-product]').forEach(function(el) {
      el.value = 0;
    });
  }
  function isiSemua() {
    // Placeholder — akan diisi dengan AJAX dari data kemarin
    showToast('info', 'Fitur salin dari kemarin segera hadir.');
  }
  </script>
  @endpush

</x-app-layout>
