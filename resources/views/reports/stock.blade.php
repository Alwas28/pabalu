<x-app-layout title="Laporan Stok">

  {{-- Filter --}}
  <form method="GET" action="{{ route('reports.stock') }}" id="filter-form">
    <div style="display:grid;grid-template-columns:1fr 1fr auto auto;gap:10px;align-items:end">
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
          <select name="outlet_id" class="f-input">
            <option value="">— Pilih Outlet —</option>
            @foreach($outlets as $o)
            <option value="{{ $o->id }}" @selected($outletId == $o->id)>{{ $o->nama }}</option>
            @endforeach
          </select>
        @endif
      </div>
      <div class="f-group" style="margin-bottom:0">
        <label class="f-label">Tanggal</label>
        <input type="date" name="tanggal" class="f-input" value="{{ $tanggal }}">
      </div>
      <button type="submit" class="btn btn-primary" style="padding:9px 16px">
        <i class="fa-solid fa-magnifying-glass"></i> Lihat
      </button>
      <button type="button" onclick="window.print()" class="btn no-print" style="padding:9px 14px">
        <i class="fa-solid fa-print"></i>
      </button>
    </div>
  </form>

  @if(!$outletId)
  <div class="card"><div class="card-body" style="text-align:center;padding:56px;color:var(--muted)">
    <i class="fa-solid fa-warehouse" style="font-size:40px;display:block;margin-bottom:14px;opacity:.3"></i>
    Pilih outlet untuk melihat laporan stok.
  </div></div>

  @elseif($summary->isEmpty())
  <div class="card"><div class="card-body" style="text-align:center;padding:56px;color:var(--muted)">
    <i class="fa-solid fa-inbox" style="font-size:40px;display:block;margin-bottom:14px;opacity:.3"></i>
    Belum ada produk pada outlet ini.
  </div></div>

  @else
  {{-- Stats --}}
  @php
    $habis   = $summary->where('akhir', 0)->count();
    $menipis = $summary->where('akhir', '>', 0)->where('akhir', '<=', 5)->count();
    $aman    = $summary->where('akhir', '>', 5)->count();
  @endphp
  <div class="stat-grid animate-fadeUp" style="grid-template-columns:repeat(4,1fr)">
    <div class="stat-card">
      <div class="stat-icon a-bg-lt" style="color:var(--ac)"><i class="fa-solid fa-cubes"></i></div>
      <div><div class="stat-num">{{ $summary->count() }}</div><div class="stat-label">Total Produk</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(52,211,153,.15);color:#34d399"><i class="fa-solid fa-circle-check"></i></div>
      <div><div class="stat-num">{{ $aman }}</div><div class="stat-label">Stok Aman</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(251,191,36,.15);color:#fbbf24"><i class="fa-solid fa-triangle-exclamation"></i></div>
      <div><div class="stat-num">{{ $menipis }}</div><div class="stat-label">Menipis (≤5)</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(248,113,113,.15);color:#f87171"><i class="fa-solid fa-circle-xmark"></i></div>
      <div><div class="stat-num">{{ $habis }}</div><div class="stat-label">Habis</div></div>
    </div>
  </div>

  <div class="card animate-fadeUp d1">
    <div class="card-header">
      <div class="card-title">
        <i class="fa-solid fa-boxes-stacking a-text" style="margin-right:8px"></i>
        Laporan Stok — {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}
      </div>
    </div>
    <div style="overflow-x:auto">
      <table class="tbl">
        <thead>
          <tr>
            <th>Produk</th>
            <th>Kategori</th>
            <th>Satuan</th>
            <th style="text-align:center">Opening</th>
            <th style="text-align:center">Masuk (+)</th>
            <th style="text-align:center">Terjual (−)</th>
            <th style="text-align:center">Waste (−)</th>
            <th style="text-align:center;color:var(--ac)">Stok Akhir</th>
            <th style="text-align:center">Status</th>
          </tr>
        </thead>
        <tbody>
          @foreach($summary->sortBy('kategori') as $s)
          <tr>
            <td class="td-main">{{ $s['nama'] }}</td>
            <td style="font-size:12px;color:var(--muted)">{{ $s['kategori'] }}</td>
            <td style="font-size:12px;color:var(--muted)">{{ $s['satuan'] }}</td>
            <td style="text-align:center">{{ $s['opening'] ?: '—' }}</td>
            <td style="text-align:center;color:{{ $s['in'] ? '#34d399' : 'var(--muted)' }};font-weight:{{ $s['in'] ? '700':'400' }}">
              {{ $s['in'] ? '+'.$s['in'] : '—' }}
            </td>
            <td style="text-align:center;color:{{ $s['sold'] ? 'var(--ac)' : 'var(--muted)' }};font-weight:{{ $s['sold'] ? '700':'400' }}">
              {{ $s['sold'] ? '-'.$s['sold'] : '—' }}
            </td>
            <td style="text-align:center;color:{{ $s['waste'] ? '#f87171' : 'var(--muted)' }};font-weight:{{ $s['waste'] ? '700':'400' }}">
              {{ $s['waste'] ? '-'.$s['waste'] : '—' }}
            </td>
            <td style="text-align:center">
              <span style="font-family:'Clash Display',sans-serif;font-size:18px;font-weight:700;
                color:{{ $s['akhir'] > 5 ? '#34d399' : ($s['akhir'] > 0 ? '#fbbf24' : '#f87171') }}">
                {{ $s['akhir'] }}
              </span>
            </td>
            <td style="text-align:center">
              @if($s['akhir'] == 0) <span class="badge badge-red">Habis</span>
              @elseif($s['akhir'] <= 5) <span class="badge badge-amber">Menipis</span>
              @else <span class="badge badge-green">Aman</span>
              @endif
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  @endif

@push('scripts')
<style>
@media print {
  #sb,#header,#ov,#toast-container,.no-print{display:none!important}
  #main{margin-left:0!important}
  #content{padding:0!important}
  body{background:white!important;color:black!important}
  .card{border:1px solid #ddd!important;border-radius:0!important;page-break-inside:avoid}
  .tbl thead th{background:#f5f5f5!important;color:#555!important}
  .tbl tbody td{color:#333!important}
  .animate-fadeUp{animation:none!important}
  .badge{border:1px solid #ccc}
}
</style>
@endpush

</x-app-layout>
