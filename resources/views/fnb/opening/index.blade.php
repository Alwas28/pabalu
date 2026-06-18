<x-outlet-layout :outlet="$outlet" pageTitle="Opening Stok">

@php
  $done      = $opname !== null;
  $todayFmt  = $today->translatedFormat('l, d F Y');
  $totalProd = $products->flatten()->count();
@endphp

{{-- ── HEADER ── --}}
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px" class="animate-fadeUp">
  <div>
    <h2 class="font-display" style="font-size:20px;font-weight:700;color:var(--text)">Opening Stok</h2>
    <p style="font-size:13px;color:var(--muted);margin-top:2px">
      <i class="fa-regular fa-calendar" style="margin-right:4px"></i>{{ $todayFmt }}
    </p>
  </div>
  @if($done)
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
      <div style="display:flex;align-items:center;gap:8px;padding:8px 16px;border-radius:12px;background:rgba(16,185,129,.12);border:1px solid rgba(16,185,129,.25)">
        <i class="fa-solid fa-circle-check" style="color:#34d399;font-size:15px"></i>
        <div>
          <div style="font-size:13px;font-weight:700;color:#34d399">Sudah Selesai</div>
          <div style="font-size:11px;color:var(--muted)">{{ $opname->submitted_at?->format('H:i') }} · {{ $opname->user->name }}</div>
        </div>
      </div>
      @if($user->hasPermission('stock.opening') && $opname->date->isToday() && !$closingDone)
      <button onclick="document.getElementById('modal-batal-opening').classList.add('open')"
        style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:12px;border:1px solid rgba(239,68,68,.35);background:transparent;color:#f87171;font-size:12.5px;font-weight:600;cursor:pointer;transition:all .15s"
        onmouseover="this.style.background='rgba(239,68,68,.1)'"
        onmouseout="this.style.background='transparent'">
        <i class="fa-solid fa-rotate-left" style="font-size:11px"></i> Batalkan &amp; Isi Ulang
      </button>
      @elseif($closingDone)
      <div style="display:inline-flex;align-items:center;gap:6px;padding:7px 13px;border-radius:12px;background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.2)">
        <i class="fa-solid fa-lock" style="font-size:11px;color:#818cf8"></i>
        <span style="font-size:12px;font-weight:600;color:#818cf8">Sudah Closing</span>
      </div>
      @endif
    </div>
  @else
    <div style="display:flex;align-items:center;gap:8px;padding:8px 16px;border-radius:12px;background:rgba(251,191,36,.10);border:1px solid rgba(251,191,36,.25)">
      <i class="fa-solid fa-clock" style="color:#fbbf24;font-size:15px"></i>
      <div>
        <div style="font-size:13px;font-weight:700;color:#fbbf24">Belum Dilakukan</div>
        <div style="font-size:11px;color:var(--muted)">{{ $totalProd }} produk perlu dihitung</div>
      </div>
    </div>
  @endif
</div>

{{-- ── SUDAH SELESAI: read-only ── --}}
@if($done)

<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px" class="animate-fadeUp d1">
  <div class="stat-card">
    <div class="stat-icon" style="background:var(--ac-lt);color:var(--ac)"><i class="fa-solid fa-boxes-stacking"></i></div>
    <div><div class="stat-num">{{ $opname->items->count() }}</div><div class="stat-label">Total Produk</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(16,185,129,.15);color:#34d399"><i class="fa-solid fa-circle-check"></i></div>
    <div><div class="stat-num">{{ $opname->items->where('actual_qty', '>', 0)->count() }}</div><div class="stat-label">Produk Tersedia</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(248,113,113,.15);color:#f87171"><i class="fa-solid fa-circle-xmark"></i></div>
    <div><div class="stat-num">{{ $opname->items->where('actual_qty', 0)->count() }}</div><div class="stat-label">Stok Habis</div></div>
  </div>
</div>

<div class="card animate-fadeUp d2">
  <div class="card-header">
    <span class="card-title">
      <i class="fa-solid fa-clipboard-check" style="color:var(--ac);margin-right:8px;font-size:14px"></i>
      Hasil Opening Stok Hari Ini
    </span>
    @if($opname->notes)
      <span style="font-size:12.5px;color:var(--muted);font-style:italic">"{{ $opname->notes }}"</span>
    @endif
  </div>
  <div style="overflow-x:auto">
    <table class="tbl">
      <thead>
        <tr>
          <th style="width:44px">#</th>
          <th>Produk</th>
          <th>Kategori</th>
          <th style="text-align:center;width:100px">Jumlah</th>
          <th style="text-align:center;width:90px">Status</th>
          <th>Catatan</th>
        </tr>
      </thead>
      <tbody>
        @foreach($opname->items->sortBy(fn($i) => $i->product->name) as $idx => $item)
        <tr>
          <td style="color:var(--muted);font-size:12px">{{ $idx + 1 }}</td>
          <td class="td-main">
            <div style="display:flex;align-items:center;gap:9px">
              @if($item->product->image)
                <img src="{{ Storage::url($item->product->image) }}"
                  style="width:32px;height:32px;border-radius:8px;object-fit:cover;flex-shrink:0">
              @else
                <div style="width:32px;height:32px;border-radius:8px;background:var(--ac-lt);color:var(--ac);display:grid;place-items:center;flex-shrink:0;font-size:13px">
                  <i class="fa-solid fa-box"></i>
                </div>
              @endif
              <div>
                <div style="font-weight:600;color:var(--text)">{{ $item->product->name }}</div>
                <div style="font-size:11.5px;color:var(--muted)">{{ $item->product->unit }}</div>
              </div>
            </div>
          </td>
          <td>
            @if($item->product->category)
              <span class="badge" style="background:var(--ac-lt2);color:var(--ac)">{{ $item->product->category->name }}</span>
            @else
              <span style="color:var(--muted);font-size:12px">—</span>
            @endif
          </td>
          <td style="text-align:center">
            <span style="font-size:18px;font-weight:700;color:{{ $item->actual_qty > 0 ? 'var(--text)' : '#f87171' }}">
              {{ $item->actual_qty }}
            </span>
            <span style="font-size:11px;color:var(--muted);margin-left:3px">{{ $item->product->unit }}</span>
          </td>
          <td style="text-align:center">
            @if($item->actual_qty > 0)
              <span class="badge badge-green"><i class="fa-solid fa-circle" style="font-size:6px"></i>Tersedia</span>
            @else
              <span class="badge badge-red"><i class="fa-solid fa-circle" style="font-size:6px"></i>Habis</span>
            @endif
          </td>
          <td style="font-size:12.5px;color:var(--muted)">{{ $item->notes ?: '—' }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

{{-- ── BELUM SELESAI: form ── --}}
@else

@if($products->isEmpty())
<div class="card animate-fadeUp d1" style="padding:56px 24px;text-align:center">
  <div style="width:60px;height:60px;border-radius:16px;background:var(--ac-lt);display:grid;place-items:center;margin:0 auto 16px;font-size:22px;color:var(--ac)">
    <i class="fa-solid fa-boxes-stacking"></i>
  </div>
  <h3 class="font-display" style="font-size:17px;font-weight:700;color:var(--text);margin-bottom:8px">Belum Ada Produk</h3>
  <p style="font-size:13.5px;color:var(--muted);max-width:320px;margin:0 auto 20px">
    Tambahkan produk di menu <strong>Produk</strong> sebelum melakukan opening stok.
  </p>
  <a href="{{ $outlet->route('products.index') }}"
    style="display:inline-flex;align-items:center;gap:8px;padding:10px 20px;border-radius:12px;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;text-decoration:none;font-size:13.5px;font-weight:700">
    <i class="fa-solid fa-cubes" style="font-size:12px"></i>Kelola Produk
  </a>
</div>

@else
<form method="POST" action="{{ $outlet->route('opening.store') }}" id="form-opening">
@csrf

<div class="animate-fadeUp d1" style="display:flex;flex-direction:column;gap:20px">

  {{-- Panduan --}}
  <div style="background:rgba(79,110,247,.08);border:1px solid rgba(79,110,247,.2);border-radius:14px;padding:14px 18px;display:flex;gap:12px;align-items:flex-start">
    <i class="fa-solid fa-circle-info" style="color:#818cf8;font-size:15px;margin-top:1px;flex-shrink:0"></i>
    <div style="font-size:13px;color:var(--sub);line-height:1.6">
      Hitung stok bahan/produk yang tersedia sekarang dan masukkan jumlahnya.
      Isi <strong style="color:var(--text)">0</strong> jika stok habis.
    </div>
  </div>

  {{-- Tabel per kategori --}}
  @foreach($products as $categoryName => $items)
  <div class="card">
    <div class="card-header" style="background:var(--surface2);border-radius:15px 15px 0 0">
      <span class="card-title" style="font-size:14px">
        <i class="fa-solid fa-tag" style="color:var(--ac);margin-right:8px;font-size:12px"></i>
        {{ $categoryName }}
        <span style="font-size:12px;color:var(--muted);font-weight:400;margin-left:6px">({{ $items->count() }} produk)</span>
      </span>
      <button type="button" onclick="fillZero('cat-{{ Str::slug($categoryName) }}')"
        style="font-size:12px;font-weight:600;color:var(--muted);background:none;border:none;cursor:pointer;padding:4px 10px;border-radius:7px;transition:color .15s,background .15s"
        onmouseover="this.style.background='var(--surface)';this.style.color='var(--text)'"
        onmouseout="this.style.background='transparent';this.style.color='var(--muted)'">
        <i class="fa-solid fa-wand-magic-sparkles" style="font-size:10px"></i> Isi 0 Semua
      </button>
    </div>
    <div style="overflow-x:auto">
      <table class="tbl">
        <thead>
          <tr>
            <th>Produk</th>
            <th style="text-align:center;width:160px">Jumlah <span style="color:var(--ac)">*</span></th>
            <th style="width:200px">Catatan <span style="color:var(--muted);font-weight:400;font-size:10px">(opsional)</span></th>
          </tr>
        </thead>
        <tbody class="cat-{{ Str::slug($categoryName) }}">
          @foreach($items as $product)
          <tr>
            <td class="td-main">
              <div style="display:flex;align-items:center;gap:10px">
                @if($product->image)
                  <img src="{{ Storage::url($product->image) }}"
                    style="width:38px;height:38px;border-radius:10px;object-fit:cover;flex-shrink:0;border:1px solid var(--border)">
                @else
                  <div style="width:38px;height:38px;border-radius:10px;background:var(--ac-lt);color:var(--ac);display:grid;place-items:center;flex-shrink:0;font-size:15px">
                    <i class="fa-solid fa-box"></i>
                  </div>
                @endif
                <div>
                  <div style="font-weight:600;color:var(--text)">{{ $product->name }}</div>
                  <div style="font-size:11.5px;color:var(--muted)">{{ $product->unit }}</div>
                </div>
              </div>
            </td>
            <td>
              <div style="display:flex;align-items:center;gap:8px;justify-content:center">
                <button type="button" onclick="stepQty({{ $product->id }}, -1)"
                  style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);cursor:pointer;color:var(--sub);font-size:14px;flex-shrink:0;transition:all .15s"
                  onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--sub)'">−</button>
                <input
                  type="number"
                  name="items[{{ $product->id }}][actual_qty]"
                  id="qty-{{ $product->id }}"
                  class="f-input actual-qty"
                  data-pid="{{ $product->id }}"
                  min="0"
                  required
                  placeholder="0"
                  oninput="updateSummary()"
                  style="width:70px;text-align:center;font-weight:700;font-size:16px;padding:8px 6px">
                <button type="button" onclick="stepQty({{ $product->id }}, 1)"
                  style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);cursor:pointer;color:var(--sub);font-size:14px;flex-shrink:0;transition:all .15s"
                  onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--sub)'">+</button>
              </div>
            </td>
            <td>
              <input
                type="text"
                name="items[{{ $product->id }}][notes]"
                class="f-input"
                maxlength="200"
                placeholder="cth: hampir habis"
                style="font-size:12.5px">
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  @endforeach

  {{-- Catatan umum --}}
  <div class="card">
    <div class="card-header">
      <span class="card-title"><i class="fa-solid fa-note-sticky" style="color:var(--ac);margin-right:8px;font-size:14px"></i>Catatan Umum</span>
    </div>
    <div class="card-body">
      <textarea name="notes" class="f-input" rows="2" maxlength="500"
        placeholder="Catatan untuk opening hari ini (opsional)"></textarea>
    </div>
  </div>

  {{-- Sticky submit bar --}}
  <div style="position:sticky;bottom:20px;z-index:30">
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:14px 20px;display:flex;align-items:center;justify-content:space-between;gap:16px;box-shadow:0 8px 32px rgba(0,0,0,.25);flex-wrap:wrap">
      <div style="display:flex;align-items:center;gap:6px">
        <div style="width:10px;height:10px;border-radius:50%;background:var(--ac)" id="bar-dot"></div>
        <span style="font-size:13px;color:var(--sub)">
          <strong id="summary-filled" style="color:var(--text)">0</strong> / {{ $totalProd }} produk diisi
        </span>
        <span id="summary-warn" style="font-size:12px;color:#fbbf24;margin-left:8px;display:none">
          <i class="fa-solid fa-triangle-exclamation"></i> Masih ada yang kosong
        </span>
      </div>
      <button type="submit"
        style="display:flex;align-items:center;gap:8px;padding:11px 24px;border-radius:12px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:14px;font-weight:700;cursor:pointer;font-family:inherit;transition:opacity .15s"
        onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
        <i class="fa-solid fa-floppy-disk"></i> Simpan Opening Stok
      </button>
    </div>
  </div>

</div>
</form>
@endif
@endif

{{-- ── HISTORI ── --}}
<div class="card animate-fadeUp d3">
  <div class="card-header" style="flex-wrap:wrap;gap:10px">
    <span class="card-title"><i class="fa-solid fa-clock-rotate-left" style="color:var(--ac);margin-right:8px;font-size:14px"></i>Riwayat Opening Stok</span>
    {{-- Filter tanggal --}}
    <form method="GET" action="{{ $outlet->route('opening.index') }}"
      style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
      <input name="hist_from" type="date" value="{{ request('hist_from') }}" class="f-input" style="padding:6px 10px;font-size:12px;width:130px">
      <span style="font-size:12px;color:var(--muted)">s/d</span>
      <input name="hist_to"   type="date" value="{{ request('hist_to') }}"   class="f-input" style="padding:6px 10px;font-size:12px;width:130px">
      <button type="submit" style="padding:6px 12px;border-radius:9px;border:none;background:var(--ac-lt);color:var(--ac);font-size:12px;font-weight:700;cursor:pointer">Filter</button>
      @if(request()->hasAny(['hist_from','hist_to']))
      <a href="{{ $outlet->route('opening.index') }}" style="font-size:12px;color:var(--muted);text-decoration:none">Reset</a>
      @endif
    </form>
  </div>

  @if($history->isEmpty())
  <div style="padding:40px 24px;text-align:center">
    <i class="fa-solid fa-sun" style="font-size:32px;color:var(--muted);display:block;margin-bottom:10px"></i>
    <p style="color:var(--muted);font-size:13.5px">Belum ada riwayat opening stok.</p>
  </div>
  @else
  <div style="overflow-x:auto">
    <table class="tbl">
      <thead>
        <tr>
          <th>Tanggal</th>
          <th>Dilakukan oleh</th>
          <th style="text-align:center;width:80px">Produk</th>
          <th style="text-align:center;width:100px">Stok Habis</th>
          <th>Catatan</th>
          <th style="width:60px"></th>
        </tr>
      </thead>
      <tbody>
        @foreach($history as $h)
        @php
          $habis   = $h->items->where('actual_qty', 0)->count();
          $isToday = $h->date->isToday();
        @endphp
        <tr>
          <td class="td-main">
            <div style="font-weight:600;color:var(--text)">
              {{ $h->date->translatedFormat('d M Y') }}
              @if($isToday)
                <span class="badge" style="background:var(--ac-lt2);color:var(--ac);margin-left:4px">Hari ini</span>
              @endif
            </div>
            <div style="font-size:11.5px;color:var(--muted)">{{ $h->submitted_at?->format('H:i') }}</div>
          </td>
          <td style="color:var(--sub);font-size:12.5px">{{ $h->user->name }}</td>
          <td style="text-align:center;font-weight:600;color:var(--text)">{{ $h->items->count() }}</td>
          <td style="text-align:center">
            @if($habis === 0)
              <span class="badge badge-green"><i class="fa-solid fa-circle-check" style="font-size:9px"></i> Semua Ada</span>
            @else
              <span class="badge badge-red"><i class="fa-solid fa-circle-xmark" style="font-size:9px"></i> {{ $habis }} habis</span>
            @endif
          </td>
          <td style="font-size:12px;color:var(--muted);max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $h->notes ?: '—' }}</td>
          <td>
            <a href="{{ $outlet->route('opening.show', [$h]) }}"
              style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);text-decoration:none;font-size:12px;transition:all .15s"
              title="Lihat detail"
              onmouseover="this.style.borderColor='var(--ac)';this.style.color='var(--ac)'"
              onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--sub)'">
              <i class="fa-solid fa-eye"></i>
            </a>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  @if($history->hasPages())
  <div style="padding:14px 20px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
    <span style="font-size:12.5px;color:var(--muted)">
      Menampilkan {{ $history->firstItem() }}–{{ $history->lastItem() }} dari {{ $history->total() }} catatan
    </span>
    <div style="display:flex;gap:6px">
      @if($history->onFirstPage())
        <span style="padding:6px 12px;border-radius:8px;border:1px solid var(--border);font-size:13px;color:var(--muted);cursor:default">← Prev</span>
      @else
        <a href="{{ $history->previousPageUrl() }}" style="padding:6px 12px;border-radius:8px;border:1px solid var(--border);font-size:13px;color:var(--sub);text-decoration:none;transition:all .15s" onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">← Prev</a>
      @endif
      @if($history->hasMorePages())
        <a href="{{ $history->nextPageUrl() }}" style="padding:6px 12px;border-radius:8px;border:1px solid var(--border);font-size:13px;color:var(--sub);text-decoration:none;transition:all .15s" onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">Next →</a>
      @else
        <span style="padding:6px 12px;border-radius:8px;border:1px solid var(--border);font-size:13px;color:var(--muted);cursor:default">Next →</span>
      @endif
    </div>
  </div>
  @endif
  @endif
</div>

{{-- ── MODAL BATALKAN OPENING ── --}}
@if($done && $opname->date->isToday() && !$closingDone)
<div id="modal-batal-opening" class="modal-backdrop" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-box" style="max-width:420px">
    <div style="padding:28px 24px;text-align:center">
      <div style="width:52px;height:52px;border-radius:14px;background:rgba(251,191,36,.15);display:grid;place-items:center;margin:0 auto 14px;font-size:20px;color:#fbbf24">
        <i class="fa-solid fa-rotate-left"></i>
      </div>
      <h3 class="font-display" style="font-size:17px;font-weight:700;color:var(--text);margin-bottom:8px">Batalkan Opening Stok?</h3>
      <p style="font-size:13.5px;color:var(--muted);max-width:320px;margin:0 auto;line-height:1.6">
        Stok produk akan dikembalikan ke kondisi <strong style="color:var(--text)">sebelum opening</strong> dilakukan, lalu kamu bisa isi ulang.
      </p>
      <p style="font-size:12px;color:var(--muted);margin-top:8px">
        Catatan: jika ada Tambah Stok setelah opening, qty-nya tetap dipertahankan.
      </p>
    </div>
    <form action="{{ $outlet->route('opening.destroy', [$opname]) }}" method="POST">
    @csrf @method('DELETE')
    <div style="padding:0 20px 20px;display:flex;gap:10px">
      <button type="button" onclick="document.getElementById('modal-batal-opening').classList.remove('open')"
        style="flex:1;padding:10px;border-radius:12px;border:1px solid var(--border);background:transparent;color:var(--sub);font-size:13.5px;font-weight:600;cursor:pointer">
        Batal
      </button>
      <button type="submit"
        style="flex:1;padding:10px;border-radius:12px;border:none;background:#f59e0b;color:#fff;font-size:13.5px;font-weight:700;cursor:pointer">
        <i class="fa-solid fa-rotate-left" style="margin-right:6px;font-size:12px"></i>Ya, Batalkan Opening
      </button>
    </div>
    </form>
  </div>
</div>
@endif

@push('scripts')
<script>
/* ── Stepper ± ── */
function stepQty(pid, delta) {
  const inp = document.getElementById('qty-' + pid);
  const val = parseInt(inp.value || '0', 10);
  inp.value = Math.max(0, val + delta);
  updateSummary();
}

/* ── Isi 0 semua pada satu kategori ── */
function fillZero(cls) {
  document.querySelectorAll('.' + cls + ' .actual-qty').forEach(inp => {
    if (inp.value === '') { inp.value = 0; }
  });
  updateSummary();
}

/* ── Summary bar ── */
function updateSummary() {
  const inputs = document.querySelectorAll('.actual-qty');
  let filled = 0;
  inputs.forEach(i => { if (i.value !== '') filled++; });

  document.getElementById('summary-filled').textContent = filled;
  const warn = document.getElementById('summary-warn');
  if (warn) warn.style.display = filled < inputs.length ? 'inline' : 'none';

  const dot = document.getElementById('bar-dot');
  if (dot) dot.style.background = filled === inputs.length ? '#34d399' : 'var(--ac)';
}

/* ── Konfirmasi jika ada yang kosong ── */
document.getElementById('form-opening')?.addEventListener('submit', function (e) {
  const empty = [...document.querySelectorAll('.actual-qty')].filter(i => i.value === '');
  if (empty.length > 0) {
    e.preventDefault();
    if (!confirm(`${empty.length} produk belum diisi. Lanjutkan dan isi dengan 0?`)) return;
    empty.forEach(i => { i.value = 0; });
    this.submit();
  }
});

updateSummary();
</script>
@endpush

</x-outlet-layout>
