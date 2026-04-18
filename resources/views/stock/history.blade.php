<x-app-layout :title="$config['title'] . ' — Riwayat'">

  {{-- Filter --}}
  <form method="GET" action="{{ route('stock.'.$type.'.history') }}" id="filter-form">
    <div style="display:grid;grid-template-columns:1fr 1fr auto auto;gap:12px;align-items:end;flex-wrap:wrap">
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
            <option value="">— Semua Outlet —</option>
            @foreach($outlets as $o)
            <option value="{{ $o->id }}" @selected($outletId == $o->id)>{{ $o->nama }}</option>
            @endforeach
          </select>
        @endif
      </div>
      <div class="f-group" style="margin-bottom:0">
        <label class="f-label">Tanggal</label>
        <input type="date" name="tanggal" class="f-input" value="{{ request('tanggal', today()->toDateString()) }}">
      </div>
      <button type="submit" class="btn btn-primary" style="padding:9px 16px">
        <i class="fa-solid fa-magnifying-glass"></i> Filter
      </button>
      <a href="{{ route('stock.'.$type) }}" class="btn" style="padding:9px 16px">
        <i class="fa-solid fa-plus"></i> Tambah
      </a>
    </div>
  </form>

  {{-- Stats --}}
  <div class="stat-grid" style="grid-template-columns:repeat(2,1fr)">
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(52,211,153,.15);color:#34d399">
        <i class="fa-solid fa-list-check"></i>
      </div>
      <div>
        <div class="stat-num">{{ $movements->count() }}</div>
        <div class="stat-label">Total Entri</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba({{ $type === 'in' ? '52,211,153' : '248,113,113' }},.15);color:{{ $type === 'in' ? '#34d399' : '#f87171' }}">
        <i class="fa-solid fa-fa-{{ $config['icon'] }}"></i>
      </div>
      <div>
        <div class="stat-num">{{ number_format($totalQty) }}</div>
        <div class="stat-label">Total Qty</div>
      </div>
    </div>
  </div>

  {{-- Table --}}
  <div class="card animate-fadeUp">
    <div class="card-header">
      <div class="card-title">
        <i class="fa-solid fa-{{ $config['icon'] }}" style="color:{{ $config['color'] }};margin-right:8px"></i>
        Riwayat {{ $config['label'] }}
      </div>
    </div>

    @if($movements->isEmpty())
    <div class="card-body" style="text-align:center;padding:56px">
      <i class="fa-solid fa-inbox" style="font-size:40px;color:var(--muted);margin-bottom:14px;display:block"></i>
      <div style="font-size:15px;font-weight:600;color:var(--text);margin-bottom:6px">Belum ada data</div>
      <div style="font-size:13px;color:var(--sub)">Belum ada riwayat untuk filter yang dipilih.</div>
    </div>
    @else
    <div style="overflow-x:auto">
      <table class="tbl">
        <thead>
          <tr>
            <th>Tanggal</th>
            <th>Outlet</th>
            <th>Produk</th>
            <th style="text-align:right">Qty</th>
            <th>Keterangan</th>
            <th>Dicatat Oleh</th>
          </tr>
        </thead>
        <tbody>
          @foreach($movements as $m)
          <tr>
            <td style="white-space:nowrap">
              {{ \Carbon\Carbon::parse($m->tanggal)->translatedFormat('d M Y') }}
            </td>
            <td class="td-main">{{ $m->outlet->nama ?? '—' }}</td>
            <td class="td-main">{{ $m->product->nama ?? '—' }}</td>
            <td style="text-align:right;font-family:monospace;font-weight:700;color:{{ $type === 'in' ? '#34d399' : '#f87171' }}">
              {{ $type === 'in' ? '+' : '-' }}{{ number_format($m->qty) }}
            </td>
            <td style="color:var(--muted);font-size:12px;max-width:200px">
              {{ $m->keterangan ?: '—' }}
            </td>
            <td style="font-size:12px;color:var(--sub)">{{ $m->user->name ?? '—' }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @endif
  </div>

</x-app-layout>
