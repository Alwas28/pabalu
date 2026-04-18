<x-app-layout title="Stok & Pergerakan">

  {{-- Filter --}}
  <form method="GET" action="{{ route('stock.index') }}" id="filter-form">
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
      <i class="fa-solid fa-warehouse" style="font-size:40px;color:var(--muted);margin-bottom:14px;display:block"></i>
      <div style="font-size:15px;font-weight:600;color:var(--text);margin-bottom:6px">Pilih Outlet</div>
      <div style="font-size:13px;color:var(--sub)">Pilih outlet untuk melihat status stok.</div>
    </div>
  </div>

  @else

  {{-- Stat cards --}}
  @php
    $totalProduk  = $summary->count();
    $habis        = $summary->where('akhir', 0)->count();
    $menipis      = $summary->where('akhir', '>', 0)->where('akhir', '<=', $threshold)->count();
    $aman         = $summary->where('akhir', '>', $threshold)->count();
  @endphp
  <div class="stat-grid animate-fadeUp">
    <div class="stat-card">
      <div class="stat-icon a-bg-lt" style="color:var(--ac)"><i class="fa-solid fa-cubes"></i></div>
      <div>
        <div class="stat-num">{{ $totalProduk }}</div>
        <div class="stat-label">Total Produk</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(52,211,153,.15);color:#34d399"><i class="fa-solid fa-circle-check"></i></div>
      <div>
        <div class="stat-num">{{ $aman }}</div>
        <div class="stat-label">Stok Aman</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(251,191,36,.15);color:#fbbf24"><i class="fa-solid fa-triangle-exclamation"></i></div>
      <div>
        <div class="stat-num">{{ $menipis }}</div>
        <div class="stat-label">Stok Menipis (≤{{ $threshold }})</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(248,113,113,.15);color:#f87171"><i class="fa-solid fa-circle-xmark"></i></div>
      <div>
        <div class="stat-num">{{ $habis }}</div>
        <div class="stat-label">Stok Habis</div>
      </div>
    </div>
  </div>

  {{-- Tabel stok per produk --}}
  <div class="card animate-fadeUp d1">
    <div class="card-header">
      <div>
        <div class="card-title">
          <i class="fa-solid fa-boxes-stacking" style="color:var(--ac);margin-right:8px"></i>
          Status Stok — {{ $outlet->nama }}
        </div>
        <div style="font-size:12px;color:var(--muted);margin-top:2px">
          {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}
        </div>
      </div>
      <div style="display:flex;gap:8px">
        @can('stock.in')
        <a href="{{ route('stock.in', ['outlet_id' => $outletId]) }}" class="btn" style="padding:7px 13px;font-size:12px">
          <i class="fa-solid fa-cart-plus" style="color:#34d399"></i> Tambah Stok
        </a>
        @endcan
        @can('stock.waste')
        <a href="{{ route('stock.waste', ['outlet_id' => $outletId]) }}" class="btn" style="padding:7px 13px;font-size:12px">
          <i class="fa-solid fa-trash-can-arrow-up" style="color:#f87171"></i> Catat Waste
        </a>
        @endcan
      </div>
    </div>

    {{-- Filter tabs: semua / habis / menipis --}}
    <div style="padding:10px 20px;border-bottom:1px solid var(--border);display:flex;gap:8px;flex-wrap:wrap">
      <button onclick="filterStatus('all')" id="ftab-all"
        class="cat-tab active" style="padding:5px 14px;border-radius:99px;font-size:12px;font-weight:600;border:none;cursor:pointer;font-family:inherit;transition:background .15s,color .15s">
        Semua ({{ $totalProduk }})
      </button>
      <button onclick="filterStatus('aman')" id="ftab-aman"
        class="cat-tab" style="padding:5px 14px;border-radius:99px;font-size:12px;font-weight:600;border:none;cursor:pointer;font-family:inherit;transition:background .15s,color .15s">
        <i class="fa-solid fa-circle-check" style="color:#34d399;font-size:10px"></i> Aman ({{ $aman }})
      </button>
      <button onclick="filterStatus('menipis')" id="ftab-menipis"
        class="cat-tab" style="padding:5px 14px;border-radius:99px;font-size:12px;font-weight:600;border:none;cursor:pointer;font-family:inherit;transition:background .15s,color .15s">
        <i class="fa-solid fa-triangle-exclamation" style="color:#fbbf24;font-size:10px"></i> Menipis ({{ $menipis }})
      </button>
      <button onclick="filterStatus('habis')" id="ftab-habis"
        class="cat-tab" style="padding:5px 14px;border-radius:99px;font-size:12px;font-weight:600;border:none;cursor:pointer;font-family:inherit;transition:background .15s,color .15s">
        <i class="fa-solid fa-circle-xmark" style="color:#f87171;font-size:10px"></i> Habis ({{ $habis }})
      </button>
    </div>

    @if($summary->isEmpty())
    <div class="card-body" style="text-align:center;padding:48px;color:var(--muted)">
      <i class="fa-solid fa-inbox" style="font-size:36px;display:block;margin-bottom:12px;opacity:.4"></i>
      <div style="font-size:13px">Belum ada produk untuk outlet ini.</div>
    </div>
    @else
    <div style="overflow-x:auto">
      <table class="tbl" id="stok-table">
        <thead>
          <tr>
            <th>Produk</th>
            <th>Kategori</th>
            <th style="text-align:center">Opening</th>
            <th style="text-align:center">Masuk</th>
            <th style="text-align:center">Terjual</th>
            <th style="text-align:center">Waste</th>
            <th style="text-align:center">Stok Akhir</th>
            <th style="text-align:center">Status</th>
          </tr>
        </thead>
        <tbody>
          @foreach($summary as $s)
          <tr data-status="{{ $s['akhir'] == 0 ? 'habis' : ($s['akhir'] <= $threshold ? 'menipis' : 'aman') }}">
            <td class="td-main" style="white-space:nowrap">
              {{ $s['nama'] }}
              @if(!$s['is_active'])
                <span class="badge badge-gray" style="font-size:10px;margin-left:4px">Nonaktif</span>
              @endif
            </td>
            <td style="font-size:12px;color:var(--muted)">{{ $s['kategori'] }}</td>
            <td style="text-align:center;color:var(--sub)">{{ $s['opening'] ?: '—' }}</td>
            <td style="text-align:center;font-weight:{{ $s['in'] ? '700' : '400' }};color:{{ $s['in'] ? '#34d399' : 'var(--muted)' }}">
              {{ $s['in'] ? '+' . $s['in'] : '—' }}
            </td>
            <td style="text-align:center;font-weight:{{ $s['sold'] ? '700' : '400' }};color:{{ $s['sold'] ? 'var(--ac)' : 'var(--muted)' }}">
              {{ $s['sold'] ? '-' . $s['sold'] : '—' }}
            </td>
            <td style="text-align:center;font-weight:{{ $s['waste'] ? '700' : '400' }};color:{{ $s['waste'] ? '#f87171' : 'var(--muted)' }}">
              {{ $s['waste'] ? '-' . $s['waste'] : '—' }}
            </td>
            <td style="text-align:center">
              <span style="font-family:'Clash Display',sans-serif;font-size:18px;font-weight:700;
                color:{{ $s['akhir'] > $threshold ? '#34d399' : ($s['akhir'] > 0 ? '#fbbf24' : '#f87171') }}">
                {{ $s['akhir'] }}
              </span>
              <span style="font-size:11px;color:var(--muted);margin-left:2px">{{ $s['satuan'] }}</span>
            </td>
            <td style="text-align:center">
              @if($s['akhir'] == 0)
                <span class="badge badge-red">Habis</span>
              @elseif($s['akhir'] <= $threshold)
                <span class="badge badge-amber">Menipis</span>
              @else
                <span class="badge badge-green">Aman</span>
              @endif
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @endif
  </div>

  {{-- Log pergerakan hari ini --}}
  @if($movements->isNotEmpty())
  <div class="card animate-fadeUp d2">
    <div class="card-header">
      <div class="card-title">
        <i class="fa-solid fa-clock-rotate-left" style="color:var(--ac);margin-right:8px"></i>
        Log Pergerakan Stok Hari Ini
      </div>
      <span class="badge badge-blue">{{ $movements->count() }} entri</span>
    </div>
    <div style="overflow-x:auto">
      <table class="tbl">
        <thead>
          <tr>
            <th>Waktu</th>
            <th>Jenis</th>
            <th>Produk</th>
            <th style="text-align:center">Qty</th>
            <th>Keterangan</th>
            <th>Oleh</th>
          </tr>
        </thead>
        <tbody>
          @foreach($movements as $mv)
          @php
            $typeConf = [
              'opening' => ['label'=>'Opening',    'color'=>'badge-blue',  'sign'=>''],
              'in'      => ['label'=>'Masuk',       'color'=>'badge-green', 'sign'=>'+'],
              'waste'   => ['label'=>'Waste',       'color'=>'badge-red',   'sign'=>'-'],
            ][$mv->type] ?? ['label'=>$mv->type,'color'=>'badge-gray','sign'=>''];
          @endphp
          <tr>
            <td style="font-size:12px;color:var(--muted);white-space:nowrap">{{ $mv->created_at->format('H:i:s') }}</td>
            <td><span class="badge {{ $typeConf['color'] }}">{{ $typeConf['label'] }}</span></td>
            <td class="td-main">{{ $mv->product->nama ?? '—' }}</td>
            <td style="text-align:center;font-weight:700;font-family:monospace;
              color:{{ $mv->type==='waste' ? '#f87171' : ($mv->type==='in' ? '#34d399' : 'var(--sub)') }}">
              {{ $typeConf['sign'] }}{{ $mv->qty }}
            </td>
            <td style="font-size:12px;color:var(--muted)">{{ $mv->keterangan ?: '—' }}</td>
            <td style="font-size:12px;color:var(--sub)">{{ $mv->user->name ?? '—' }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  @endif

  @endif

  @push('scripts')
  <script>
  function filterStatus(status) {
    // Update tab active state
    ['all','aman','menipis','habis'].forEach(function(s) {
      document.getElementById('ftab-' + s).classList.toggle('active', s === status);
    });
    // Filter rows
    document.querySelectorAll('#stok-table tbody tr').forEach(function(tr) {
      var show = (status === 'all') || (tr.dataset.status === status);
      tr.style.display = show ? '' : 'none';
    });
  }
  </script>
  @endpush

</x-app-layout>
