<x-app-layout>
<x-slot name="pageTitle">Dashboard</x-slot>

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- ADMIN DASHBOARD                                           --}}
{{-- ══════════════════════════════════════════════════════════ --}}
@if($role === 'admin')

@php
  $chartDays     = collect(range(6, 0))->map(fn ($i) => now()->subDays($i));
  $chartLabels   = $chartDays->map(fn ($d) => $d->translatedFormat('D, d/m'))->values()->toArray();
  $chartCounts   = $chartDays->map(fn ($d) => (int) ($weeklyStats[$d->toDateString()]->trx_count ?? 0))->values()->toArray();
  $chartRevenues = $chartDays->map(fn ($d) => (int) ($weeklyStats[$d->toDateString()]->revenue   ?? 0))->values()->toArray();
@endphp

<div style="display:flex;flex-direction:column;gap:20px">

  {{-- Greeting banner --}}
  <div class="card animate-fadeUp" style="padding:22px 28px;background:linear-gradient(135deg,var(--ac),var(--ac2));border:none">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px">
      <div>
        <div style="font-size:11.5px;color:rgba(255,255,255,.7);margin-bottom:5px">
          <i class="fa-regular fa-calendar" style="margin-right:5px"></i>{{ now()->translatedFormat('l, d F Y') }}
        </div>
        <h2 class="font-display" style="font-size:22px;font-weight:700;color:#fff;margin-bottom:4px">
          Selamat datang, {{ $user->name }}
        </h2>
        <div style="font-size:13px;color:rgba(255,255,255,.8)">
          Anda mengelola <strong>{{ $ownerCount }}</strong> owner dan <strong>{{ $outletCount }}</strong> outlet terdaftar.
        </div>
      </div>
      <div style="text-align:right">
        <div style="font-size:11px;color:rgba(255,255,255,.65);margin-bottom:3px">Pendapatan Hari Ini</div>
        <div class="font-display" style="font-size:26px;font-weight:700;color:#fff;line-height:1">
          Rp {{ number_format($revToday, 0, ',', '.') }}
        </div>
        <div style="font-size:12px;color:rgba(255,255,255,.7);margin-top:3px">
          <i class="fa-solid fa-receipt" style="margin-right:4px"></i>{{ $txToday }} transaksi selesai
        </div>
      </div>
    </div>
  </div>

  {{-- Stat cards --}}
  <div class="stat-grid animate-fadeUp d1">
    <div class="stat-card">
      <div class="stat-icon" style="background:var(--ac-lt)">
        <i class="fa-solid fa-user-tie" style="color:var(--ac)"></i>
      </div>
      <div>
        <div class="stat-num">{{ $ownerCount }}</div>
        <div class="stat-label">Total Owner</div>
        <div class="stat-trend" style="color:var(--muted)">
          <i class="fa-solid fa-circle" style="font-size:5px"></i> Terdaftar di sistem
        </div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(99,102,241,.14)">
        <i class="fa-solid fa-shop" style="color:#818cf8"></i>
      </div>
      <div>
        <div class="stat-num">{{ $outletCount }}</div>
        <div class="stat-label">Total Outlet</div>
        <div class="stat-trend" style="color:var(--muted)">
          <i class="fa-solid fa-circle" style="font-size:5px"></i> Semua outlet
        </div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(16,185,129,.14)">
        <i class="fa-solid fa-receipt" style="color:#34d399"></i>
      </div>
      <div>
        <div class="stat-num">{{ $txMonth }}</div>
        <div class="stat-label">Transaksi Bulan Ini</div>
        <div class="stat-trend" style="color:var(--muted)">
          <i class="fa-solid fa-circle" style="font-size:5px"></i> Hari ini: {{ $txToday }}
        </div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(245,158,11,.14)">
        <i class="fa-solid fa-money-bill-trend-up" style="color:#fbbf24"></i>
      </div>
      <div>
        <div class="stat-num" style="font-size:19px">Rp {{ number_format($revMonth / 1000000, 1, ',', '.') }}Jt</div>
        <div class="stat-label">Pendapatan Bulan Ini</div>
        <div class="stat-trend" style="color:var(--muted)">
          <i class="fa-solid fa-circle" style="font-size:5px"></i> {{ now()->translatedFormat('F Y') }}
        </div>
      </div>
    </div>
  </div>

  {{-- Chart + Owner list --}}
  <div class="two-col animate-fadeUp d2">

    {{-- Weekly chart --}}
    <div class="card">
      <div class="card-header">
        <span class="card-title">Grafik Transaksi 7 Hari Terakhir</span>
        <span style="font-size:11px;color:var(--muted)">Semua outlet</span>
      </div>
      <div style="padding:20px">
        <canvas id="chart-weekly" height="200"></canvas>
      </div>
    </div>

    {{-- Owner list --}}
    <div class="card">
      <div class="card-header">
        <span class="card-title">Daftar Owner</span>
        <a href="{{ route('users.index') }}" style="font-size:12px;color:var(--ac);text-decoration:none;font-weight:600">
          Kelola User <i class="fa-solid fa-arrow-right" style="font-size:10px"></i>
        </a>
      </div>
      <div style="padding:8px 10px">
        @forelse($owners as $owner)
        <div style="display:flex;align-items:center;gap:12px;padding:10px 8px;border-radius:10px;transition:background .12s"
             onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
          <div class="a-grad" style="width:38px;height:38px;border-radius:10px;display:grid;place-items:center;color:#fff;font-size:14px;font-weight:700;flex-shrink:0">
            {{ strtoupper(substr($owner->name, 0, 1)) }}
          </div>
          <div style="flex:1;min-width:0">
            <div style="font-size:13px;font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
              {{ $owner->name }}
            </div>
            <div style="font-size:11px;color:var(--muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
              {{ $owner->business_name ?: $owner->email }}
            </div>
          </div>
          <div style="text-align:right;flex-shrink:0">
            <div style="font-size:14px;font-weight:700;color:var(--text)">{{ $owner->outlets_count }}</div>
            <div style="font-size:10px;color:var(--muted)">outlet</div>
          </div>
        </div>
        @empty
        <div style="padding:32px;text-align:center;color:var(--muted);font-size:13px">
          <i class="fa-solid fa-user-slash" style="display:block;font-size:24px;margin-bottom:10px;opacity:.3"></i>
          Belum ada owner terdaftar
        </div>
        @endforelse
      </div>
    </div>

  </div>

  {{-- Recent transactions --}}
  @if($recentTransactions->isNotEmpty())
  <div class="card animate-fadeUp d3">
    <div class="card-header">
      <span class="card-title">Transaksi Terbaru</span>
      <span style="font-size:11px;color:var(--muted)">Seluruh sistem</span>
    </div>
    <div style="overflow-x:auto">
      <table class="tbl">
        <thead>
          <tr>
            <th>No. Transaksi</th>
            <th>Outlet</th>
            <th>Kasir</th>
            <th>Total</th>
            <th>Metode</th>
            <th>Waktu</th>
          </tr>
        </thead>
        <tbody>
          @foreach($recentTransactions as $tx)
          <tr>
            <td class="td-main">{{ $tx->transaction_number }}</td>
            <td>{{ $tx->outlet?->name ?? '—' }}</td>
            <td>{{ $tx->user?->name ?? '—' }}</td>
            <td class="td-main">Rp {{ number_format($tx->total, 0, ',', '.') }}</td>
            <td>
              <span class="badge badge-blue">{{ strtoupper($tx->payment_method ?? '—') }}</span>
            </td>
            <td>{{ $tx->created_at->diffForHumans() }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  @endif

</div>

@push('scripts')
<script>
(function(){
  const labels   = @json($chartLabels);
  const counts   = @json($chartCounts);
  const revenues = @json($chartRevenues);
  const dark = !document.body.classList.contains('light');
  const gc = dark ? 'rgba(255,255,255,.06)' : 'rgba(0,0,0,.06)';
  const tc = dark ? '#64748b' : '#94a3b8';
  const ac = getComputedStyle(document.documentElement).getPropertyValue('--ac').trim();
  const acRgb = getComputedStyle(document.documentElement).getPropertyValue('--ac-rgb').trim();
  const ctx = document.getElementById('chart-weekly').getContext('2d');
  const chart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels,
      datasets: [
        {
          label: 'Pendapatan (Rp)',
          data: revenues,
          backgroundColor: `rgba(${acRgb},.2)`,
          borderColor: `rgba(${acRgb},1)`,
          borderWidth: 2,
          borderRadius: 6,
          yAxisID: 'y',
        },
        {
          label: 'Jumlah Transaksi',
          data: counts,
          type: 'line',
          borderColor: '#818cf8',
          backgroundColor: 'rgba(129,140,248,.08)',
          pointBackgroundColor: '#818cf8',
          pointRadius: 4,
          tension: .4,
          fill: true,
          yAxisID: 'y2',
        }
      ]
    },
    options: {
      responsive: true,
      interaction: { mode: 'index', intersect: false },
      plugins: { legend: { labels: { color: tc, font: { size: 12, family: "'Plus Jakarta Sans',sans-serif" } } } },
      scales: {
        x:  { ticks: { color: tc }, grid: { color: gc } },
        y:  { ticks: { color: tc, callback: v => 'Rp '+new Intl.NumberFormat('id').format(v) }, grid: { color: gc } },
        y2: { position: 'right', ticks: { color: tc }, grid: { drawOnChartArea: false } },
      }
    }
  });
  window.rebuildChart = function(){
    const d = document.body.classList.contains('light');
    const g = d ? 'rgba(0,0,0,.06)' : 'rgba(255,255,255,.06)';
    const t = d ? '#94a3b8' : '#64748b';
    chart.options.plugins.legend.labels.color = t;
    ['x','y','y2'].forEach(k => {
      chart.options.scales[k].ticks.color = t;
      if (k !== 'y2') chart.options.scales[k].grid.color = g;
    });
    const rgb = getComputedStyle(document.documentElement).getPropertyValue('--ac-rgb').trim();
    chart.data.datasets[0].backgroundColor = `rgba(${rgb},.2)`;
    chart.data.datasets[0].borderColor = `rgba(${rgb},1)`;
    chart.update();
  };
})();
</script>
@endpush


{{-- ══════════════════════════════════════════════════════════ --}}
{{-- OWNER DASHBOARD                                           --}}
{{-- ══════════════════════════════════════════════════════════ --}}
@elseif($role === 'owner')

@php
  $chartDays     = collect(range(6, 0))->map(fn ($i) => now()->subDays($i));
  $chartLabels   = $chartDays->map(fn ($d) => $d->translatedFormat('D, d/m'))->values()->toArray();
  $chartCounts   = $chartDays->map(fn ($d) => (int) ($weeklyStats[$d->toDateString()]->trx_count ?? 0))->values()->toArray();
  $chartRevenues = $chartDays->map(fn ($d) => (int) ($weeklyStats[$d->toDateString()]->revenue   ?? 0))->values()->toArray();
@endphp

<div style="display:flex;flex-direction:column;gap:20px">

  {{-- Greeting banner --}}
  <div class="card animate-fadeUp" style="padding:22px 28px;background:linear-gradient(135deg,var(--ac),var(--ac2));border:none">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px">
      <div>
        <div style="font-size:11.5px;color:rgba(255,255,255,.7);margin-bottom:5px">
          <i class="fa-regular fa-calendar" style="margin-right:5px"></i>{{ now()->translatedFormat('l, d F Y') }}
        </div>
        <h2 class="font-display" style="font-size:22px;font-weight:700;color:#fff;margin-bottom:4px">
          Halo, {{ $user->name }}!
        </h2>
        <div style="font-size:13px;color:rgba(255,255,255,.8)">
          {{ $outlets->count() }} outlet · {{ $employeeCount }} karyawan aktif
        </div>
      </div>
      <div style="text-align:right">
        <div style="font-size:11px;color:rgba(255,255,255,.65);margin-bottom:3px">
          Pendapatan {{ now()->translatedFormat('F Y') }}
        </div>
        <div class="font-display" style="font-size:26px;font-weight:700;color:#fff;line-height:1">
          Rp {{ number_format($revMonth, 0, ',', '.') }}
        </div>
        <div style="font-size:12px;color:rgba(255,255,255,.7);margin-top:3px">
          <i class="fa-solid fa-receipt" style="margin-right:4px"></i>{{ $txMonth }} transaksi
        </div>
      </div>
    </div>
  </div>

  {{-- Stats --}}
  <div class="stat-grid animate-fadeUp d1">
    <div class="stat-card">
      <div class="stat-icon" style="background:var(--ac-lt)">
        <i class="fa-solid fa-shop" style="color:var(--ac)"></i>
      </div>
      <div>
        <div class="stat-num">{{ $outlets->count() }}</div>
        <div class="stat-label">Jumlah Outlet</div>
        <div class="stat-trend" style="color:var(--muted)">
          <i class="fa-solid fa-circle" style="font-size:5px"></i>
          {{ $outlets->where('is_active', true)->count() }} aktif
        </div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(99,102,241,.14)">
        <i class="fa-solid fa-users" style="color:#818cf8"></i>
      </div>
      <div>
        <div class="stat-num">{{ $employeeCount }}</div>
        <div class="stat-label">Karyawan Aktif</div>
        <div class="stat-trend" style="color:var(--muted)">
          <i class="fa-solid fa-circle" style="font-size:5px"></i> Semua outlet
        </div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(16,185,129,.14)">
        <i class="fa-solid fa-receipt" style="color:#34d399"></i>
      </div>
      <div>
        <div class="stat-num">{{ $txToday }}</div>
        <div class="stat-label">Transaksi Hari Ini</div>
        <div class="stat-trend" style="color:var(--muted)">
          <i class="fa-solid fa-circle" style="font-size:5px"></i>
          Bulan ini: {{ $txMonth }}
        </div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(245,158,11,.14)">
        <i class="fa-solid fa-money-bill-trend-up" style="color:#fbbf24"></i>
      </div>
      <div>
        <div class="stat-num" style="font-size:19px">Rp {{ number_format($revToday, 0, ',', '.') }}</div>
        <div class="stat-label">Pendapatan Hari Ini</div>
        <div class="stat-trend" style="color:var(--muted)">
          <i class="fa-solid fa-circle" style="font-size:5px"></i>
          {{ now()->translatedFormat('d M Y') }}
        </div>
      </div>
    </div>
  </div>

  {{-- Outlet cards --}}
  @if($outlets->isNotEmpty())
  <div class="animate-fadeUp d2">
    <div style="font-size:14px;font-weight:700;color:var(--text);margin-bottom:12px" class="font-display">
      Outlet Saya
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:14px">
      @foreach($outlets as $outlet)
      @php
        $ots     = $outletStats[$outlet->id] ?? null;
        $txCount = $ots?->tx_count ?? 0;
        $revenue = $ots?->revenue  ?? 0;
      @endphp
      <a href="{{ $outlet->route('show') }}" style="text-decoration:none">
        <div class="card" style="padding:18px 20px;transition:border-color .2s,box-shadow .2s"
             onmouseover="this.style.borderColor='var(--ac)';this.style.boxShadow='0 0 0 1px var(--ac-lt),0 4px 16px rgba(0,0,0,.12)'"
             onmouseout="this.style.borderColor='var(--border)';this.style.boxShadow=''">
          <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:14px">
            <div style="flex:1;min-width:0">
              <div style="font-size:15px;font-weight:700;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis" class="font-display">
                {{ $outlet->name }}
              </div>
              <div style="font-size:11px;color:var(--muted);margin-top:2px">
                {{ $outlet->outletType?->name ?? 'Outlet' }}
              </div>
            </div>
            <div style="width:9px;height:9px;border-radius:50%;background:{{ $outlet->is_active ? '#34d399' : '#f87171' }};margin-top:5px;flex-shrink:0"></div>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
            <div style="background:var(--surface2);border-radius:10px;padding:10px 12px">
              <div class="font-display" style="font-size:20px;font-weight:700;color:var(--text)">{{ $txCount }}</div>
              <div style="font-size:10.5px;color:var(--muted)">Transaksi Hari Ini</div>
            </div>
            <div style="background:var(--surface2);border-radius:10px;padding:10px 12px">
              <div class="font-display" style="font-size:13px;font-weight:700;color:var(--text)">
                Rp {{ number_format($revenue / 1000, 0, ',', '.') }}K
              </div>
              <div style="font-size:10.5px;color:var(--muted)">Pendapatan Hari Ini</div>
            </div>
          </div>
        </div>
      </a>
      @endforeach

      {{-- Add outlet card --}}
      <a href="{{ route('outlets.index') }}" style="text-decoration:none">
        <div style="border:2px dashed var(--border);border-radius:16px;padding:18px 20px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;min-height:120px;transition:border-color .2s,background .2s"
             onmouseover="this.style.borderColor='var(--ac)';this.style.background='var(--ac-lt2)'"
             onmouseout="this.style.borderColor='var(--border)';this.style.background='transparent'">
          <i class="fa-solid fa-plus" style="font-size:20px;color:var(--muted)"></i>
          <div style="font-size:12.5px;font-weight:600;color:var(--muted)">Kelola Outlet</div>
        </div>
      </a>
    </div>
  </div>
  @endif

  {{-- Chart + Recent tx --}}
  <div class="two-col animate-fadeUp d3">

    <div class="card">
      <div class="card-header">
        <span class="card-title">Grafik Penjualan 7 Hari Terakhir</span>
        <span style="font-size:11px;color:var(--muted)">Semua outlet</span>
      </div>
      <div style="padding:20px">
        <canvas id="chart-weekly" height="200"></canvas>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <span class="card-title">Transaksi Terbaru</span>
      </div>
      <div style="padding:8px 10px">
        @forelse($recentTransactions as $tx)
        <div style="display:flex;align-items:center;gap:12px;padding:10px 8px;border-radius:10px;transition:background .12s"
             onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
          <div style="width:36px;height:36px;border-radius:10px;background:var(--ac-lt);display:grid;place-items:center;flex-shrink:0">
            <i class="fa-solid fa-receipt" style="color:var(--ac);font-size:13px"></i>
          </div>
          <div style="flex:1;min-width:0">
            <div style="font-size:12.5px;font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
              {{ $tx->transaction_number }}
            </div>
            <div style="font-size:11px;color:var(--muted)">
              {{ $tx->outlet?->name }} · {{ $tx->created_at->diffForHumans() }}
            </div>
          </div>
          <div style="font-size:13px;font-weight:700;color:var(--ac);flex-shrink:0;white-space:nowrap">
            Rp {{ number_format($tx->total, 0, ',', '.') }}
          </div>
        </div>
        @empty
        <div style="padding:32px;text-align:center;color:var(--muted);font-size:13px">
          <i class="fa-solid fa-receipt" style="display:block;font-size:24px;margin-bottom:10px;opacity:.3"></i>
          Belum ada transaksi
        </div>
        @endforelse
      </div>
    </div>

  </div>

</div>

@push('scripts')
<script>
(function(){
  const labels   = @json($chartLabels);
  const counts   = @json($chartCounts);
  const revenues = @json($chartRevenues);
  const dark = !document.body.classList.contains('light');
  const gc = dark ? 'rgba(255,255,255,.06)' : 'rgba(0,0,0,.06)';
  const tc = dark ? '#64748b' : '#94a3b8';
  const acRgb = getComputedStyle(document.documentElement).getPropertyValue('--ac-rgb').trim();
  const ctx = document.getElementById('chart-weekly').getContext('2d');
  const chart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels,
      datasets: [
        {
          label: 'Pendapatan (Rp)',
          data: revenues,
          backgroundColor: `rgba(${acRgb},.2)`,
          borderColor: `rgba(${acRgb},1)`,
          borderWidth: 2,
          borderRadius: 6,
          yAxisID: 'y',
        },
        {
          label: 'Jumlah Transaksi',
          data: counts,
          type: 'line',
          borderColor: '#818cf8',
          backgroundColor: 'rgba(129,140,248,.08)',
          pointBackgroundColor: '#818cf8',
          pointRadius: 4,
          tension: .4,
          fill: true,
          yAxisID: 'y2',
        }
      ]
    },
    options: {
      responsive: true,
      interaction: { mode: 'index', intersect: false },
      plugins: { legend: { labels: { color: tc, font: { size: 12, family: "'Plus Jakarta Sans',sans-serif" } } } },
      scales: {
        x:  { ticks: { color: tc }, grid: { color: gc } },
        y:  { ticks: { color: tc, callback: v => 'Rp '+new Intl.NumberFormat('id').format(v) }, grid: { color: gc } },
        y2: { position: 'right', ticks: { color: tc }, grid: { drawOnChartArea: false } },
      }
    }
  });
  window.rebuildChart = function(){
    const d   = document.body.classList.contains('light');
    const g   = d ? 'rgba(0,0,0,.06)' : 'rgba(255,255,255,.06)';
    const t   = d ? '#94a3b8' : '#64748b';
    const rgb = getComputedStyle(document.documentElement).getPropertyValue('--ac-rgb').trim();
    chart.options.plugins.legend.labels.color = t;
    ['x','y','y2'].forEach(k => {
      chart.options.scales[k].ticks.color = t;
      if (k !== 'y2') chart.options.scales[k].grid.color = g;
    });
    chart.data.datasets[0].backgroundColor = `rgba(${rgb},.2)`;
    chart.data.datasets[0].borderColor = `rgba(${rgb},1)`;
    chart.update();
  };
})();
</script>
@endpush


{{-- ══════════════════════════════════════════════════════════ --}}
{{-- KASIR / ADMIN OUTLET DASHBOARD                            --}}
{{-- ══════════════════════════════════════════════════════════ --}}
@elseif($role === 'kasir' || $role === 'admin_outlet')

@php
  $selId  = request('outlet', $assignedOutlets->count() === 1 ? $assignedOutlets->first()?->id : null);
  $selOut = $assignedOutlets->firstWhere('id', $selId);
  $p_     = fn (string $s) => Auth::user()->hasPermission($s);
@endphp

<div style="display:flex;flex-direction:column;gap:20px">

  {{-- Outlet selector (jika lebih dari 1) --}}
  @if($assignedOutlets->count() > 1)
  <div class="card animate-fadeUp" style="padding:16px 20px">
    <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap">
      <div>
        <label class="f-label">Pilih Outlet</label>
        <select class="f-input" style="width:auto;min-width:220px"
          onchange="window.location='{{ route('dashboard') }}?outlet='+this.value">
          <option value="">— Pilih outlet —</option>
          @foreach($assignedOutlets as $ao)
          <option value="{{ $ao->id }}" {{ $selId == $ao->id ? 'selected' : '' }}>
            {{ $ao->name }} ({{ $ao->outletType?->name ?? 'Outlet' }})
          </option>
          @endforeach
        </select>
      </div>
      @if($selOut)
      <div style="display:flex;align-items:center;gap:8px;padding:7px 14px;border-radius:10px;background:var(--ac-lt)">
        <i class="fa-solid fa-circle-dot" style="font-size:8px;color:var(--ac)"></i>
        <span style="font-size:13px;font-weight:600;color:var(--ac)">{{ $selOut->name }}</span>
      </div>
      @endif
    </div>
  </div>
  @endif

  {{-- Belum ada outlet --}}
  @if($assignedOutlets->isEmpty())
  <div style="padding:60px;text-align:center;background:var(--surface);border:1px solid var(--border);border-radius:16px">
    <i class="fa-solid fa-shop" style="font-size:36px;color:var(--muted);opacity:.3;display:block;margin-bottom:14px"></i>
    <h3 class="font-display" style="font-size:16px;color:var(--text);margin-bottom:6px">Belum Ada Outlet</h3>
    <p style="font-size:13px;color:var(--muted)">Akun Anda belum ditugaskan ke outlet manapun. Hubungi pemilik toko.</p>
  </div>

  @elseif(!$selOut && $assignedOutlets->count() > 1)
  <div style="padding:48px;text-align:center;background:var(--surface);border:1px solid var(--border);border-radius:16px">
    <i class="fa-solid fa-hand-pointer" style="font-size:28px;color:var(--muted);opacity:.4;display:block;margin-bottom:12px"></i>
    <p style="font-size:13px;color:var(--muted)">Pilih outlet di atas untuk melanjutkan.</p>
  </div>

  @else
  @php $selOut ??= $assignedOutlets->first(); @endphp

  {{-- Outlet info + stats --}}
  <div class="card animate-fadeUp" style="padding:22px 28px;background:linear-gradient(135deg,var(--ac),var(--ac2));border:none">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px">
      <div style="display:flex;align-items:center;gap:16px">
        <div style="width:52px;height:52px;border-radius:14px;background:rgba(255,255,255,.15);display:grid;place-items:center;font-size:22px;color:#fff;flex-shrink:0">
          <i class="fa-solid {{ $selOut->outletType?->icon ?? 'fa-shop' }}"></i>
        </div>
        <div>
          <div style="font-size:11px;color:rgba(255,255,255,.7);margin-bottom:3px">
            {{ $selOut->outletType?->name ?? 'Outlet' }}
          </div>
          <h2 class="font-display" style="font-size:20px;font-weight:700;color:#fff;line-height:1.2">
            {{ $selOut->name }}
          </h2>
          @if($selOut->address)
          <div style="font-size:11px;color:rgba(255,255,255,.7);margin-top:3px">
            <i class="fa-solid fa-location-dot" style="margin-right:4px"></i>{{ \Illuminate\Support\Str::limit($selOut->address, 50) }}
          </div>
          @endif
        </div>
      </div>
      <div style="display:flex;gap:20px">
        <div style="text-align:right">
          <div style="font-size:11px;color:rgba(255,255,255,.65);margin-bottom:2px">
            {{ $role === 'kasir' ? 'Transaksi Saya' : 'Transaksi' }} Hari Ini
          </div>
          <div class="font-display" style="font-size:28px;font-weight:700;color:#fff;line-height:1">{{ $txToday }}</div>
        </div>
        <div style="text-align:right">
          <div style="font-size:11px;color:rgba(255,255,255,.65);margin-bottom:2px">
            {{ $role === 'kasir' ? 'Pendapatan Saya' : 'Pendapatan' }} Hari Ini
          </div>
          <div class="font-display" style="font-size:20px;font-weight:700;color:#fff;line-height:1.2">
            Rp {{ number_format($revToday, 0, ',', '.') }}
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Quick action grid --}}
  <div class="animate-fadeUp d1">
    <div style="font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.7px;margin-bottom:12px">
      Menu Cepat
    </div>
    <div class="action-grid">

      @if($p_('pos.access'))
      <a href="{{ $selOut->route('pos.index') }}" class="action-card d1">
        <div class="action-card-icon a-bg-lt">
          <i class="fa-solid fa-cash-register" style="color:var(--ac)"></i>
        </div>
        <div class="action-card-label">POS / Kasir</div>
        <div class="action-card-sub">Catat penjualan</div>
      </a>
      @endif

      @if($p_('order.view') && $selOut->isKitchenMode())
      <a href="{{ $selOut->route('orders.index') }}" class="action-card d1">
        <div class="action-card-icon" style="background:rgba(99,102,241,.14)">
          <i class="fa-solid fa-list-check" style="color:#818cf8"></i>
        </div>
        <div class="action-card-label">Antrian Order</div>
        <div class="action-card-sub">Proses pesanan masuk</div>
      </a>
      @endif

      @if($p_('transaction.view'))
      <a href="{{ $selOut->route('transactions.index') }}" class="action-card d2">
        <div class="action-card-icon" style="background:rgba(16,185,129,.14)">
          <i class="fa-solid fa-clock-rotate-left" style="color:#34d399"></i>
        </div>
        <div class="action-card-label">Transaksi</div>
        <div class="action-card-sub">Riwayat penjualan</div>
      </a>
      @endif

      @if($p_('product.view'))
      <a href="{{ $selOut->route('products.index') }}" class="action-card d2">
        <div class="action-card-icon" style="background:rgba(245,158,11,.14)">
          <i class="fa-solid fa-cubes" style="color:#fbbf24"></i>
        </div>
        <div class="action-card-label">Produk</div>
        <div class="action-card-sub">Lihat produk &amp; harga</div>
      </a>
      @endif

      @if($p_('stock.view'))
      <a href="{{ $selOut->route('stock.index') }}" class="action-card d3">
        <div class="action-card-icon" style="background:rgba(96,165,250,.14)">
          <i class="fa-solid fa-warehouse" style="color:#60a5fa"></i>
        </div>
        <div class="action-card-label">Stok</div>
        <div class="action-card-sub">Pergerakan stok</div>
      </a>
      @endif

      @if($p_('expense.view'))
      <a href="{{ $selOut->route('expenses.index') }}" class="action-card d3">
        <div class="action-card-icon" style="background:rgba(248,113,113,.14)">
          <i class="fa-solid fa-money-bill-wave" style="color:#f87171"></i>
        </div>
        <div class="action-card-label">Pengeluaran</div>
        <div class="action-card-sub">Catat pengeluaran</div>
      </a>
      @endif

      <a href="{{ route('my-salary') }}" class="action-card d4">
        <div class="action-card-icon" style="background:rgba(52,211,153,.14)">
          <i class="fa-solid fa-hand-holding-dollar" style="color:#34d399"></i>
        </div>
        <div class="action-card-label">Riwayat Gaji</div>
        <div class="action-card-sub">Info gaji saya</div>
      </a>

    </div>
  </div>

  @endif

</div>

@else
{{-- Fallback untuk role tidak dikenali --}}
<div style="padding:60px;text-align:center">
  <i class="fa-solid fa-circle-question" style="font-size:36px;color:var(--muted);opacity:.3;display:block;margin-bottom:14px"></i>
  <p style="color:var(--muted);font-size:13px">Role tidak dikenali. Hubungi administrator.</p>
</div>
@endif

</x-app-layout>
