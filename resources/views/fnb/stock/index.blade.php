<x-outlet-layout :outlet="$outlet" pageTitle="Stok & Pergerakan">

{{-- ── HEADER ── --}}
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px" class="animate-fadeUp">
  <div>
    <h2 class="font-display" style="font-size:20px;font-weight:700;color:var(--text)">Stok &amp; Pergerakan</h2>
    <p style="font-size:13px;color:var(--muted);margin-top:2px">Pantau stok dan riwayat pergerakan produk</p>
  </div>
</div>

{{-- ── STAT CARDS ── --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px" class="animate-fadeUp d1">
  <div class="stat-card">
    <div class="stat-icon" style="background:var(--ac-lt);color:var(--ac)"><i class="fa-solid fa-cubes"></i></div>
    <div>
      <div class="stat-num">{{ $statTotal }}</div>
      <div class="stat-label">Produk Aktif</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(249,115,22,.15);color:#f97316"><i class="fa-solid fa-triangle-exclamation"></i></div>
    <div>
      <div class="stat-num" style="{{ $statKritis > 0 ? 'color:#f97316' : '' }}">{{ $statKritis }}</div>
      <div class="stat-label">Stok Kritis</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(239,68,68,.15);color:#f87171"><i class="fa-solid fa-box-open"></i></div>
    <div>
      <div class="stat-num" style="{{ $statHabis > 0 ? 'color:#f87171' : '' }}">{{ $statHabis }}</div>
      <div class="stat-label">Stok Habis</div>
    </div>
  </div>
</div>

{{-- ── TAB BAR ── --}}
<div style="display:flex;gap:4px;background:var(--surface2);border-radius:12px;padding:4px;width:fit-content" class="animate-fadeUp d2">
  <button id="tab-btn-stok" onclick="switchTab('stok')"
    style="padding:8px 20px;border-radius:9px;border:none;font-size:13.5px;font-weight:600;cursor:pointer;transition:all .18s">
    <i class="fa-solid fa-warehouse" style="margin-right:6px;font-size:12px"></i>Stok Produk
  </button>
  <button id="tab-btn-pergerakan" onclick="switchTab('pergerakan')"
    style="padding:8px 20px;border-radius:9px;border:none;font-size:13.5px;font-weight:600;cursor:pointer;transition:all .18s">
    <i class="fa-solid fa-right-left" style="margin-right:6px;font-size:12px"></i>Pergerakan Stok
    <span style="margin-left:4px;background:var(--ac);color:#fff;font-size:10px;font-weight:800;padding:1px 6px;border-radius:99px">{{ $movements->count() }}</span>
  </button>
</div>

{{-- ════════ TAB: STOK PRODUK ════════ --}}
<div id="tab-stok" class="animate-fadeUp d3">

  {{-- Filter bar --}}
  <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:14px 18px;display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end">
    <div style="flex:1;min-width:180px">
      <label class="f-label">Cari Produk</label>
      <div style="position:relative">
        <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:11px"></i>
        <input id="stok-search" type="text" placeholder="Nama produk..."
          class="f-input" style="padding-left:32px" oninput="filterStock()">
      </div>
    </div>
    <div style="min-width:150px">
      <label class="f-label">Kategori</label>
      <select id="stok-cat" class="f-input" onchange="filterStock()">
        <option value="">Semua Kategori</option>
        @foreach($categories as $cat)
        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
        @endforeach
      </select>
    </div>
    <div style="min-width:140px">
      <label class="f-label">Status Stok</label>
      <select id="stok-status" class="f-input" onchange="filterStock()">
        <option value="">Semua Status</option>
        <option value="normal">Normal</option>
        <option value="kritis">Kritis</option>
        <option value="habis">Habis</option>
        <option value="nonaktif">Nonaktif</option>
      </select>
    </div>
    <button onclick="document.getElementById('stok-search').value='';document.getElementById('stok-cat').value='';document.getElementById('stok-status').value='';filterStock()"
      style="padding:9px 14px;border-radius:12px;border:1px solid var(--border);background:transparent;color:var(--sub);font-size:13px;font-weight:600;cursor:pointer;white-space:nowrap">
      Reset
    </button>
  </div>

  {{-- Tabel Stok --}}
  <div class="card">
    <div class="card-header">
      <span class="card-title"><i class="fa-solid fa-boxes-stacking" style="color:var(--ac);margin-right:8px;font-size:13px"></i>Semua Produk</span>
      <span id="stok-count" style="font-size:12px;color:var(--muted)">{{ $products->count() }} produk</span>
    </div>
    @if($products->isEmpty())
    <div style="padding:60px 24px;text-align:center">
      <i class="fa-solid fa-boxes-stacking" style="font-size:36px;color:var(--muted);display:block;margin-bottom:12px"></i>
      <p style="color:var(--muted);font-size:14px">Belum ada produk. Tambahkan produk terlebih dahulu.</p>
    </div>
    @else
    <div style="overflow-x:auto">
      <table class="tbl" id="stok-table">
        <thead>
          <tr>
            <th style="width:44px"></th>
            <th>Produk</th>
            <th>Kategori</th>
            <th style="text-align:center;width:110px">Stok Saat Ini</th>
            <th style="text-align:center;width:90px">Stok Min</th>
            <th style="width:110px">Status</th>
            <th style="text-align:right;width:130px">Harga Jual</th>
          </tr>
        </thead>
        <tbody>
          @foreach($products as $p)
          @php
            if (!$p->is_active)         $statusKey = 'nonaktif';
            elseif ($p->stock <= 0)     $statusKey = 'habis';
            elseif ($p->isLowStock())   $statusKey = 'kritis';
            else                        $statusKey = 'normal';
          @endphp
          <tr data-name="{{ strtolower($p->name) }}"
              data-cat="{{ $p->category_id ?? '' }}"
              data-status="{{ $statusKey }}">
            <td>
              @if($p->image)
                <img src="{{ Storage::url($p->image) }}" alt=""
                  style="width:36px;height:36px;border-radius:8px;object-fit:cover;display:block">
              @else
                <div style="width:36px;height:36px;border-radius:8px;background:var(--surface2);display:grid;place-items:center;color:var(--muted);font-size:13px">
                  <i class="fa-solid fa-cube"></i>
                </div>
              @endif
            </td>
            <td class="td-main">
              {{ $p->name }}
              @if($p->sku)<div style="font-size:11px;color:var(--muted);font-family:monospace">{{ $p->sku }}</div>@endif
            </td>
            <td style="font-size:12.5px">{{ $p->category->name ?? '—' }}</td>
            <td style="text-align:center">
              @php
                $stockColor = match($statusKey) {
                  'habis'    => '#f87171',
                  'kritis'   => '#f97316',
                  default    => 'var(--text)',
                };
              @endphp
              <span style="font-size:18px;font-weight:800;font-family:'Clash Display',sans-serif;color:{{ $stockColor }}">{{ $p->stock }}</span>
              <span style="font-size:11px;color:var(--muted);display:block">{{ $p->unit }}</span>
            </td>
            <td style="text-align:center;font-size:13px;color:var(--muted)">{{ $p->min_stock }} {{ $p->unit }}</td>
            <td>
              @if($statusKey === 'habis')
                <span class="badge badge-red"><i class="fa-solid fa-circle-xmark" style="font-size:9px"></i> Habis</span>
              @elseif($statusKey === 'kritis')
                <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:99px;font-size:11px;font-weight:600;background:rgba(249,115,22,.15);color:#f97316">
                  <i class="fa-solid fa-triangle-exclamation" style="font-size:9px"></i> Kritis
                </span>
              @elseif($statusKey === 'nonaktif')
                <span class="badge badge-gray"><i class="fa-solid fa-eye-slash" style="font-size:9px"></i> Nonaktif</span>
              @else
                <span class="badge badge-green"><i class="fa-solid fa-circle-check" style="font-size:9px"></i> Normal</span>
              @endif
            </td>
            <td style="text-align:right;font-weight:600;color:var(--text)">
              Rp {{ number_format($p->price, 0, ',', '.') }}
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div id="stok-no-results" style="display:none;padding:40px 24px;text-align:center">
      <i class="fa-solid fa-magnifying-glass" style="font-size:28px;color:var(--muted);display:block;margin-bottom:10px"></i>
      <p style="color:var(--muted)">Tidak ada produk yang cocok</p>
    </div>
    @endif
  </div>
</div>{{-- end tab-stok --}}

{{-- ════════ TAB: PERGERAKAN STOK ════════ --}}
<div id="tab-pergerakan" style="display:none">

  {{-- Filter form --}}
  <form method="GET" action="{{ $outlet->route('stock.index') }}"
    style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:14px 18px;display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end">
    <input type="hidden" name="tab" value="pergerakan">

    <div style="min-width:200px">
      <label class="f-label">Produk</label>
      <select name="mov_product" class="f-input">
        <option value="">Semua Produk</option>
        @foreach($products->where('is_active', true)->sortBy('name') as $p)
        <option value="{{ $p->id }}" @selected($movProduct == $p->id)>{{ $p->name }}</option>
        @endforeach
      </select>
    </div>

    <div style="min-width:140px">
      <label class="f-label">Jenis</label>
      <select name="mov_type" class="f-input">
        <option value="">Semua Jenis</option>
        <option value="masuk"       @selected($movType==='masuk')>Masuk (Tambah Stok)</option>
        <option value="keluar"      @selected($movType==='keluar')>Keluar (Penjualan)</option>
        <option value="waste"       @selected($movType==='waste')>Waste / Rusak</option>
        <option value="penyesuaian" @selected($movType==='penyesuaian')>Penyesuaian</option>
      </select>
    </div>

    <div style="min-width:130px">
      <label class="f-label">Dari Tanggal</label>
      <input name="mov_from" type="date" value="{{ $movFrom }}" class="f-input">
    </div>

    <div style="min-width:130px">
      <label class="f-label">Sampai Tanggal</label>
      <input name="mov_to" type="date" value="{{ $movTo }}" class="f-input">
    </div>

    <div style="display:flex;gap:8px;align-items:flex-end">
      <button type="submit"
        style="padding:9px 18px;border-radius:12px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:700;cursor:pointer;white-space:nowrap">
        <i class="fa-solid fa-filter" style="font-size:11px;margin-right:5px"></i>Filter
      </button>
      <a href="{{ $outlet->route('stock.index', ['tab' => 'pergerakan']) }}"
        href="{{ url()->current() }}?tab=pergerakan"
        style="padding:9px 14px;border-radius:12px;border:1px solid var(--border);background:transparent;color:var(--sub);font-size:13px;font-weight:600;text-decoration:none;white-space:nowrap">
        Reset
      </a>
    </div>
  </form>

  {{-- Movement timeline --}}
  <div class="card">
    <div class="card-header">
      <span class="card-title">
        <i class="fa-solid fa-right-left" style="color:var(--ac);margin-right:8px;font-size:13px"></i>
        Pergerakan Stok
      </span>
      <span style="font-size:12px;color:var(--muted)">
        {{ \Carbon\Carbon::parse($movFrom)->translatedFormat('d M') }} – {{ \Carbon\Carbon::parse($movTo)->translatedFormat('d M Y') }}
        · {{ $movements->count() }} catatan
      </span>
    </div>

    @if($movements->isEmpty())
    <div style="padding:60px 24px;text-align:center">
      <i class="fa-solid fa-right-left" style="font-size:36px;color:var(--muted);display:block;margin-bottom:12px"></i>
      <p style="font-size:14px;font-weight:600;color:var(--text);margin-bottom:6px">Tidak Ada Pergerakan</p>
      <p style="color:var(--muted);font-size:13px">Tidak ditemukan pergerakan stok dalam rentang tanggal ini.</p>
    </div>
    @else
    <div style="overflow-x:auto">
      <table class="tbl">
        <thead>
          <tr>
            <th style="width:140px">Waktu</th>
            <th style="width:110px">Jenis</th>
            <th>Produk</th>
            <th>Referensi</th>
            <th style="text-align:center;width:90px">Qty</th>
            <th style="width:120px">Dicatat oleh</th>
          </tr>
        </thead>
        <tbody>
          @foreach($movements as $m)
          @php
            $isIn    = $m['type'] === 'masuk';
            $isOut   = $m['type'] === 'keluar';
            $isAdj   = $m['type'] === 'penyesuaian';
            $isWaste = $m['type'] === 'waste';
            $qty     = (int) $m['qty'];
          @endphp
          <tr>
            <td>
              <div style="font-size:13px;font-weight:500;color:var(--text)">
                {{ \Carbon\Carbon::parse($m['date'])->translatedFormat('d M Y') }}
              </div>
              <div style="font-size:11.5px;color:var(--muted)">
                {{ \Carbon\Carbon::parse($m['logged_at'])->format('H:i') }}
              </div>
            </td>
            <td>
              @if($isIn)
                <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:99px;font-size:11px;font-weight:600;background:rgba(16,185,129,.15);color:#34d399">
                  <i class="fa-solid fa-arrow-down" style="font-size:9px"></i> Masuk
                </span>
              @elseif($isOut)
                <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:99px;font-size:11px;font-weight:600;background:rgba(239,68,68,.12);color:#f87171">
                  <i class="fa-solid fa-arrow-up" style="font-size:9px"></i> Keluar
                </span>
              @elseif($isWaste)
                <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:99px;font-size:11px;font-weight:600;background:rgba(245,158,11,.12);color:#f59e0b">
                  <i class="fa-solid fa-trash-can" style="font-size:9px"></i> Waste
                </span>
              @else
                @php $adjColor = $qty >= 0 ? '#60a5fa' : '#f97316'; $adjBg = $qty >= 0 ? 'rgba(99,162,241,.12)' : 'rgba(249,115,22,.12)'; @endphp
                <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:99px;font-size:11px;font-weight:600;background:{{ $adjBg }};color:{{ $adjColor }}">
                  <i class="fa-solid fa-sliders" style="font-size:9px"></i> Sesuaian
                </span>
              @endif
            </td>
            <td class="td-main">{{ $m['product_name'] }}</td>
            <td style="font-size:12.5px;color:var(--muted)">{{ $m['reference'] }}</td>
            <td style="text-align:center">
              @if($isIn || ($isAdj && $qty > 0))
                <span style="font-size:15px;font-weight:800;color:#34d399">+{{ abs($qty) }}</span>
              @elseif($isWaste)
                <span style="font-size:15px;font-weight:800;color:#f59e0b">−{{ abs($qty) }}</span>
              @elseif($isOut || ($isAdj && $qty < 0))
                <span style="font-size:15px;font-weight:800;color:#f87171">−{{ abs($qty) }}</span>
              @else
                <span style="font-size:13px;color:var(--muted)">0</span>
              @endif
            </td>
            <td style="font-size:12.5px;color:var(--sub)">{{ $m['actor'] }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @if($movements->count() >= 500)
    <div style="padding:12px 20px;border-top:1px solid var(--border);font-size:12.5px;color:var(--muted);text-align:center">
      Menampilkan 500 catatan pertama. Perkecil rentang tanggal untuk hasil lebih spesifik.
    </div>
    @endif
    @endif
  </div>
</div>{{-- end tab-pergerakan --}}

@push('scripts')
<script>
// ── Tab switcher ──────────────────────────────────────
function switchTab(tab) {
  const tabs = ['stok','pergerakan'];
  tabs.forEach(t => {
    document.getElementById('tab-' + t).style.display = t === tab ? '' : 'none';
    const btn = document.getElementById('tab-btn-' + t);
    if (t === tab) {
      btn.style.background = 'var(--surface)';
      btn.style.color      = 'var(--ac)';
      btn.style.boxShadow  = '0 1px 4px rgba(0,0,0,.18)';
    } else {
      btn.style.background = 'transparent';
      btn.style.color      = 'var(--sub)';
      btn.style.boxShadow  = 'none';
    }
  });
}

// ── Stock filter (client-side) ────────────────────────
function filterStock() {
  const q      = document.getElementById('stok-search').value.toLowerCase();
  const cat    = document.getElementById('stok-cat').value;
  const status = document.getElementById('stok-status').value;

  let visible = 0;
  document.querySelectorAll('#stok-table tbody tr').forEach(row => {
    const nameMatch   = !q      || row.dataset.name.includes(q);
    const catMatch    = !cat    || row.dataset.cat === cat;
    const statusMatch = !status || row.dataset.status === status;
    const show = nameMatch && catMatch && statusMatch;
    row.style.display = show ? '' : 'none';
    if (show) visible++;
  });

  document.getElementById('stok-count').textContent = visible + ' produk';
  const noRes = document.getElementById('stok-no-results');
  const table = document.getElementById('stok-table');
  if (noRes) noRes.style.display = visible === 0 ? 'block' : 'none';
  if (table) table.style.display = visible === 0 ? 'none' : '';
}

// ── Init ──────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  switchTab('{{ $activeTab }}');
});
</script>
@endpush

</x-outlet-layout>
