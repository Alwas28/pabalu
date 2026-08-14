<x-outlet-layout :outlet="$outlet" pageTitle="Laporan Barang">

{{-- ── HEADER ── --}}
<div class="animate-fadeUp">
  <h2 class="font-display" style="font-size:20px;font-weight:700;color:var(--text)">Laporan Barang</h2>
  <p style="font-size:13px;color:var(--muted);margin-top:2px">
    Barang paling sering disewa &amp; tingkat utilisasi unit · {{ \Carbon\Carbon::parse($from)->translatedFormat('d M Y') }} – {{ \Carbon\Carbon::parse($to)->translatedFormat('d M Y') }}
  </p>
</div>

{{-- ── FILTER ── --}}
<div class="card animate-fadeUp d1" style="padding:16px 20px">
  <form method="GET" action="{{ $outlet->route('reports.items') }}" style="display:flex;align-items:flex-end;gap:12px;flex-wrap:wrap">
    <div>
      <label class="f-label">Dari Tanggal</label>
      <input type="date" name="from" value="{{ $from }}" class="f-input" style="width:155px">
    </div>
    <div>
      <label class="f-label">Sampai</label>
      <input type="date" name="to" value="{{ $to }}" class="f-input" style="width:155px">
    </div>
    <button type="submit" style="padding:9px 20px;border-radius:12px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:700;cursor:pointer">
      <i class="fa-solid fa-filter" style="font-size:11px;margin-right:5px"></i>Tampilkan
    </button>
    <div style="display:flex;gap:6px;margin-left:auto">
      @php
        $shortcuts = [
          'Minggu Ini' => [today()->startOfWeek()->format('Y-m-d'), today()->format('Y-m-d')],
          'Bulan Ini'  => [today()->startOfMonth()->format('Y-m-d'), today()->format('Y-m-d')],
          'Bulan Lalu' => [today()->subMonth()->startOfMonth()->format('Y-m-d'), today()->subMonth()->endOfMonth()->format('Y-m-d')],
        ];
      @endphp
      @foreach($shortcuts as $label => [$f, $t])
      <a href="{{ $outlet->route('reports.items', ['from' => $f, 'to' => $t]) }}"
         style="padding:7px 12px;border-radius:9px;font-size:12px;font-weight:600;text-decoration:none;border:1px solid {{ ($from===$f && $to===$t) ? 'var(--ac)' : 'var(--border)' }};color:{{ ($from===$f && $to===$t) ? 'var(--ac)' : 'var(--muted)' }};background:{{ ($from===$f && $to===$t) ? 'var(--ac-lt)' : 'transparent' }};white-space:nowrap">
        {{ $label }}
      </a>
      @endforeach
    </div>
  </form>
</div>

{{-- ── STATS ── --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px" class="animate-fadeUp d2">
  <div class="stat-card">
    <div class="stat-icon" style="background:var(--ac-lt);color:var(--ac)"><i class="fa-solid fa-box"></i></div>
    <div><div class="stat-num">{{ $totalItems }}</div><div class="stat-label">Jenis Barang</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(96,165,250,.15);color:#60a5fa"><i class="fa-solid fa-cubes"></i></div>
    <div><div class="stat-num">{{ $totalUnits }}</div><div class="stat-label">Total Unit</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(52,211,153,.15);color:#34d399"><i class="fa-solid fa-gauge-high"></i></div>
    <div><div class="stat-num">{{ $avgUtilization }}%</div><div class="stat-label">Rata-rata Utilisasi Unit</div></div>
  </div>
</div>

{{-- ── STATUS UNIT SAAT INI ── --}}
<div class="card animate-fadeUp d2">
  <div class="card-header">
    <span class="card-title"><i class="fa-solid fa-cubes" style="color:var(--ac);margin-right:8px;font-size:13px"></i>Status Unit Saat Ini</span>
  </div>
  <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1px;background:var(--border)">
    @php
      $statusMeta = [
        'tersedia'    => ['label' => 'Tersedia',    'color' => '#34d399'],
        'disewa'      => ['label' => 'Disewa',      'color' => '#60a5fa'],
        'maintenance' => ['label' => 'Maintenance', 'color' => '#fbbf24'],
        'rusak'       => ['label' => 'Rusak',       'color' => '#f87171'],
      ];
    @endphp
    @foreach($statusMeta as $slug => $meta)
    <div style="background:var(--surface);padding:16px;text-align:center">
      <div style="font-size:22px;font-weight:800;color:{{ $meta['color'] }}">{{ $statusBreakdown[$slug] ?? 0 }}</div>
      <div style="font-size:11.5px;color:var(--muted);margin-top:3px">{{ $meta['label'] }}</div>
    </div>
    @endforeach
  </div>
</div>

{{-- ── TOP BARANG + UTILISASI UNIT ── --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px" class="animate-fadeUp d3">

  <div class="card">
    <div class="card-header">
      <span class="card-title"><i class="fa-solid fa-ranking-star" style="color:var(--ac);margin-right:8px;font-size:13px"></i>Barang Paling Sering Disewa</span>
      <span style="font-size:12px;color:var(--muted)">{{ $topItems->count() }} barang</span>
    </div>
    <div style="overflow-x:auto">
      <table class="tbl">
        <thead><tr><th style="width:28px">#</th><th>Barang</th><th style="text-align:center;width:80px">Sewa</th><th style="text-align:right;width:140px">Nilai</th></tr></thead>
        <tbody>
          @forelse($topItems as $name => $item)
          <tr>
            <td style="font-size:11px;font-weight:700;color:{{ $loop->iteration <= 3 ? 'var(--ac)' : 'var(--muted)' }}">{{ $loop->iteration }}</td>
            <td class="td-main">{{ $name }}</td>
            <td style="text-align:center;font-weight:700;color:var(--text)">{{ $item['count'] }}</td>
            <td style="text-align:right;font-weight:700;color:var(--text)">Rp {{ number_format($item['total']) }}</td>
          </tr>
          @empty
          <tr><td colspan="4" style="text-align:center;padding:32px;color:var(--muted)">Tidak ada data</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <span class="card-title"><i class="fa-solid fa-gauge-high" style="color:var(--ac);margin-right:8px;font-size:13px"></i>Utilisasi per Unit</span>
      <span style="font-size:12px;color:var(--muted)">{{ $utilization->count() }} unit</span>
    </div>
    <div style="padding:14px 20px;max-height:360px;overflow-y:auto">
      @forelse($utilization as $u)
      <div style="margin-bottom:14px">
        <div style="display:flex;justify-content:space-between;margin-bottom:5px">
          <span style="font-size:12.5px;color:var(--sub)">
            {{ $u['unit']->rentalItem->name }} <span style="color:var(--muted);font-family:monospace">({{ $u['unit']->code }})</span>
          </span>
          <span style="font-size:12.5px;font-weight:700;color:var(--text)">{{ $u['utilization'] }}%</span>
        </div>
        <div style="height:6px;border-radius:99px;background:var(--border);overflow:hidden">
          <div style="height:100%;width:{{ $u['utilization'] }}%;border-radius:99px;background:{{ $u['utilization'] >= 60 ? '#34d399' : ($u['utilization'] >= 25 ? '#fbbf24' : '#f87171') }}"></div>
        </div>
      </div>
      @empty
      <p style="text-align:center;color:var(--muted);font-size:13px;padding:16px 0">Belum ada unit</p>
      @endforelse
    </div>
  </div>

</div>

</x-outlet-layout>
