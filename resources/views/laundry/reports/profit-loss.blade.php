<x-laundry-layout :outlet="$outlet" pageTitle="Laba &amp; Rugi">

@php
  $profitColor  = $netProfit >= 0 ? '#34d399' : '#f87171';
  $profitBg     = $netProfit >= 0 ? 'rgba(52,211,153,.15)' : 'rgba(248,113,113,.15)';
  $profitLabel  = $netProfit >= 0 ? 'Laba Bersih' : 'Rugi Bersih';
  $profitIcon   = $netProfit >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down';
  $marginPct    = $netSales > 0 ? round($netProfit / $netSales * 100, 1) : 0;

  $chartLabels   = $chartData->pluck('label')->values()->toArray();
  $chartSales    = $chartData->pluck('sales')->values()->toArray();
  $chartExpenses = $chartData->pluck('expenses')->values()->toArray();
@endphp

@push('scripts')
<script>
const plLabels   = @json($chartLabels);
const plSales    = @json($chartSales);
const plExpenses = @json($chartExpenses);
let plChart = null;

function rebuildPLChart() {
  const el = document.getElementById('pl-chart');
  if (!el) return;
  const dark      = !document.body.classList.contains('light');
  const textColor = dark ? '#94a3b8' : '#64748b';
  const gridColor = dark ? 'rgba(255,255,255,.06)' : 'rgba(0,0,0,.07)';
  const ac        = getComputedStyle(document.documentElement).getPropertyValue('--ac').trim() || '#e8192c';

  if (plChart) plChart.destroy();

  plChart = new Chart(el, {
    type: 'line',
    data: {
      labels: plLabels,
      datasets: [
        {
          label: 'Pendapatan',
          data: plSales,
          borderColor: '#34d399',
          backgroundColor: 'rgba(52,211,153,.12)',
          borderWidth: 2.5,
          pointRadius: plLabels.length > 30 ? 0 : 3,
          pointBackgroundColor: '#34d399',
          tension: 0.35,
          fill: true,
        },
        {
          label: 'Pengeluaran',
          data: plExpenses,
          borderColor: '#f87171',
          backgroundColor: 'rgba(248,113,113,.1)',
          borderWidth: 2.5,
          pointRadius: plLabels.length > 30 ? 0 : 3,
          pointBackgroundColor: '#f87171',
          tension: 0.35,
          fill: true,
        },
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: {
          display: true,
          labels: { color: textColor, boxWidth: 12, font: { size: 12 } }
        },
        tooltip: {
          backgroundColor: dark ? '#1c2336' : '#fff',
          titleColor: dark ? '#e2e8f0' : '#1e293b',
          bodyColor: textColor,
          borderColor: dark ? '#252d42' : '#e2e8f0',
          borderWidth: 1,
          padding: 12,
          cornerRadius: 10,
          callbacks: {
            label: ctx => '  Rp ' + ctx.raw.toLocaleString('id-ID'),
          }
        }
      },
      scales: {
        x: {
          grid: { color: gridColor },
          ticks: { color: textColor, font: { size: 11 } },
          border: { color: gridColor },
        },
        y: {
          grid: { color: gridColor },
          border: { color: gridColor },
          ticks: {
            color: textColor, font: { size: 11 },
            callback: v => v >= 1_000_000 ? (v/1_000_000).toFixed(1)+'jt' : v >= 1_000 ? (v/1_000).toFixed(0)+'rb' : v,
          }
        }
      }
    }
  });
}

document.addEventListener('DOMContentLoaded', rebuildPLChart);
</script>
@endpush

{{-- ── HEADER ── --}}
<div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px">
  <div>
    <h2 class="font-display" style="font-size:20px;font-weight:700;color:var(--text)">Laba &amp; Rugi</h2>
    <p style="font-size:13px;color:var(--muted);margin-top:2px">
      {{ \Carbon\Carbon::parse($from)->translatedFormat('d M Y') }} –
      {{ \Carbon\Carbon::parse($to)->translatedFormat('d M Y') }}
    </p>
  </div>
  <div style="display:flex;align-items:center;gap:10px">
    <span style="padding:7px 14px;border-radius:99px;font-size:13px;font-weight:700;background:{{ $profitBg }};color:{{ $profitColor }}">
      <i class="fa-solid {{ $profitIcon }}" style="font-size:11px;margin-right:4px"></i>
      Margin {{ $marginPct }}%
    </span>
  </div>
</div>

{{-- ── FILTER ── --}}
<div class="card" style="padding:16px 20px">
  <form method="GET" action="{{ $outlet->route('reports.profit-loss') }}"
    style="display:flex;align-items:flex-end;gap:12px;flex-wrap:wrap">

    <div>
      <label class="f-label">Dari Tanggal</label>
      <input type="date" name="from" value="{{ $from }}" class="f-input" style="width:155px">
    </div>
    <div>
      <label class="f-label">Sampai</label>
      <input type="date" name="to" value="{{ $to }}" class="f-input" style="width:155px">
    </div>

    <button type="submit"
      style="padding:9px 20px;border-radius:12px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:700;cursor:pointer">
      <i class="fa-solid fa-filter" style="font-size:11px;margin-right:5px"></i>Tampilkan
    </button>

    {{-- Shortcuts --}}
    <div style="display:flex;gap:6px;margin-left:auto">
      @php
        $shortcuts = [
          'Minggu Ini'  => [today()->startOfWeek()->format('Y-m-d'), today()->format('Y-m-d')],
          'Bulan Ini'   => [today()->startOfMonth()->format('Y-m-d'), today()->format('Y-m-d')],
          'Bulan Lalu'  => [today()->subMonth()->startOfMonth()->format('Y-m-d'), today()->subMonth()->endOfMonth()->format('Y-m-d')],
          '3 Bulan'     => [today()->subMonths(2)->startOfMonth()->format('Y-m-d'), today()->format('Y-m-d')],
          'Tahun Ini'   => [today()->startOfYear()->format('Y-m-d'), today()->format('Y-m-d')],
        ];
      @endphp
      @foreach($shortcuts as $label => [$f, $t])
      <a href="{{ $outlet->route('reports.profit-loss', ['from' => $f, 'to' => $t]) }}"
         style="padding:7px 12px;border-radius:9px;font-size:12px;font-weight:600;text-decoration:none;border:1px solid {{ ($from===$f && $to===$t) ? 'var(--ac)' : 'var(--border)' }};color:{{ ($from===$f && $to===$t) ? 'var(--ac)' : 'var(--muted)' }};background:{{ ($from===$f && $to===$t) ? 'var(--ac-lt)' : 'transparent' }};white-space:nowrap;transition:all .15s">
        {{ $label }}
      </a>
      @endforeach
    </div>
  </form>
</div>

{{-- ── STAT CARDS ── --}}
@if($trackCogs)
{{-- 5 cards: Pendapatan, HPP, Laba Kotor, Pengeluaran, Laba Bersih --}}
<div style="display:grid;grid-template-columns:repeat(5,1fr);gap:14px">
@else
{{-- 3 cards: Pendapatan, Pengeluaran, Laba Bersih --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px">
@endif

  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(52,211,153,.15);color:#34d399">
      <i class="fa-solid fa-money-bill-trend-up"></i>
    </div>
    <div>
      <div class="stat-num" style="font-size:{{ $trackCogs ? '15px' : '17px' }}">Rp {{ number_format($grossSales) }}</div>
      <div class="stat-label">Pendapatan</div>
    </div>
  </div>

  @if($trackCogs)
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(251,191,36,.15);color:#fbbf24">
      <i class="fa-solid fa-boxes-stacked"></i>
    </div>
    <div>
      <div class="stat-num" style="font-size:15px;color:#fbbf24">Rp {{ number_format($totalHpp) }}</div>
      <div class="stat-label">HPP</div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(96,165,250,.15);color:#60a5fa">
      <i class="fa-solid fa-circle-half-stroke"></i>
    </div>
    <div>
      <div class="stat-num" style="font-size:15px;color:#60a5fa">Rp {{ number_format($grossProfit) }}</div>
      <div class="stat-label">Laba Kotor</div>
    </div>
  </div>
  @endif

  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(248,113,113,.15);color:#f87171">
      <i class="fa-solid fa-file-invoice-dollar"></i>
    </div>
    <div>
      <div class="stat-num" style="font-size:{{ $trackCogs ? '15px' : '17px' }};color:#f87171">Rp {{ number_format($totalExpenses) }}</div>
      <div class="stat-label">Pengeluaran</div>
    </div>
  </div>

  <div class="stat-card" style="background:{{ $netProfit >= 0 ? 'rgba(52,211,153,.08)' : 'rgba(248,113,113,.08)' }};border-color:{{ $profitColor }}33">
    <div class="stat-icon" style="background:{{ $profitBg }};color:{{ $profitColor }}">
      <i class="fa-solid {{ $profitIcon }}"></i>
    </div>
    <div>
      <div class="stat-num" style="font-size:{{ $trackCogs ? '15px' : '17px' }};color:{{ $profitColor }}">
        {{ $netProfit < 0 ? '−' : '' }}Rp {{ number_format(abs($netProfit)) }}
      </div>
      <div class="stat-label" style="font-weight:700;color:{{ $profitColor }}">{{ $profitLabel }}</div>
    </div>
  </div>
</div>

{{-- ── CHART ── --}}
<div class="card">
  <div class="card-header">
    <span class="card-title">
      <i class="fa-solid fa-chart-area" style="color:var(--ac);margin-right:8px;font-size:13px"></i>
      Tren Pendapatan vs Pengeluaran
    </span>
    <span style="font-size:12px;color:var(--muted)">
      per {{ $groupBy === 'day' ? 'hari' : 'bulan' }}
    </span>
  </div>
  @if($chartData->isEmpty())
  <div style="padding:40px;text-align:center;color:var(--muted);font-size:13px">
    <i class="fa-solid fa-chart-area" style="font-size:28px;display:block;margin-bottom:10px;opacity:.35"></i>
    Tidak ada data pada periode ini
  </div>
  @else
  <div style="padding:20px;position:relative;height:260px">
    <canvas id="pl-chart"></canvas>
  </div>
  @endif
</div>

{{-- ── BAWAH: RINGKASAN P&L + BREAKDOWN PENGELUARAN ── --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

  {{-- Income Statement --}}
  <div class="card">
    <div class="card-header">
      <span class="card-title">
        <i class="fa-solid fa-list-check" style="color:var(--ac);margin-right:8px;font-size:13px"></i>
        Ringkasan Laba Rugi
      </span>
    </div>
    <div style="padding:4px 0">

      {{-- PENDAPATAN --}}
      <div style="padding:10px 20px;background:rgba(52,211,153,.06);border-bottom:1px solid var(--border)">
        <p style="font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#34d399;margin-bottom:6px">Pendapatan</p>
        <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--sub);margin-bottom:3px">
          <span>Penjualan Kotor</span>
          <span>Rp {{ number_format($grossSales) }}</span>
        </div>
        @if($totalDiscount > 0)
        <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--muted)">
          <span>(−) Total Diskon</span>
          <span style="color:#fbbf24">Rp {{ number_format($totalDiscount) }}</span>
        </div>
        @endif
        <div style="display:flex;justify-content:space-between;font-size:13.5px;font-weight:700;color:var(--text);margin-top:8px;padding-top:8px;border-top:1px solid var(--border)">
          <span>Penjualan Bersih</span>
          <span style="color:#34d399">Rp {{ number_format($netSales) }}</span>
        </div>
      </div>

      @if($trackCogs)
      {{-- HPP --}}
      <div style="padding:10px 20px;background:rgba(251,191,36,.04);border-bottom:1px solid var(--border)">
        <p style="font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#fbbf24;margin-bottom:6px">Harga Pokok Penjualan</p>
        <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--sub)">
          <span>HPP (cost_price × qty terjual)</span>
          <span style="color:#fbbf24">Rp {{ number_format($totalHpp) }}</span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:13.5px;font-weight:700;color:var(--text);margin-top:8px;padding-top:8px;border-top:1px solid var(--border)">
          <span>Laba Kotor</span>
          <span style="color:#60a5fa">Rp {{ number_format($grossProfit) }}</span>
        </div>
      </div>
      @endif

      {{-- PENGELUARAN --}}
      <div style="padding:10px 20px;background:rgba(248,113,113,.04);border-bottom:1px solid var(--border)">
        <p style="font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#f87171;margin-bottom:6px">Pengeluaran</p>
        @foreach($expenseByCategory as $exp)
        <div style="display:flex;justify-content:space-between;align-items:center;font-size:13px;color:var(--sub);margin-bottom:4px">
          <span style="display:flex;align-items:center;gap:7px">
            <i class="fa-solid {{ $exp['icon'] }}" style="font-size:11px;color:{{ $exp['color'] }};width:14px;text-align:center"></i>
            {{ $exp['label'] }}
          </span>
          <span>Rp {{ number_format($exp['total']) }}</span>
        </div>
        @endforeach
        @if($expenseByCategory->isEmpty())
        <p style="font-size:12.5px;color:var(--muted)">Tidak ada pengeluaran tercatat</p>
        @endif
        <div style="display:flex;justify-content:space-between;font-size:13.5px;font-weight:700;color:var(--text);margin-top:8px;padding-top:8px;border-top:1px solid var(--border)">
          <span>Total Pengeluaran</span>
          <span style="color:#f87171">Rp {{ number_format($totalExpenses) }}</span>
        </div>
      </div>

      {{-- LABA BERSIH --}}
      <div style="padding:14px 20px;background:{{ $netProfit >= 0 ? 'rgba(52,211,153,.08)' : 'rgba(248,113,113,.08)' }}">
        <div style="display:flex;justify-content:space-between;align-items:center">
          <span style="font-size:15px;font-weight:800;color:{{ $profitColor }}">{{ $profitLabel }}</span>
          <span style="font-size:17px;font-weight:800;color:{{ $profitColor }}">
            {{ $netProfit < 0 ? '−' : '' }}Rp {{ number_format(abs($netProfit)) }}
          </span>
        </div>
        @if($netSales > 0)
        <p style="font-size:12px;color:{{ $profitColor }};opacity:.75;margin-top:3px">
          Margin {{ $marginPct }}% dari penjualan bersih
        </p>
        @endif
      </div>

    </div>
  </div>

  {{-- Breakdown Pengeluaran --}}
  <div class="card">
    <div class="card-header">
      <span class="card-title">
        <i class="fa-solid fa-chart-pie" style="color:var(--ac);margin-right:8px;font-size:13px"></i>
        Breakdown Pengeluaran
      </span>
      <span style="font-size:12px;color:var(--muted)">Rp {{ number_format($totalExpenses) }}</span>
    </div>

    @if($expenseByCategory->isEmpty())
    <div style="padding:48px 24px;text-align:center;color:var(--muted);font-size:13px">
      <i class="fa-solid fa-chart-pie" style="font-size:28px;display:block;margin-bottom:10px;opacity:.35"></i>
      Tidak ada pengeluaran pada periode ini
    </div>
    @else
    <div style="padding:12px 0">
      @foreach($expenseByCategory as $exp)
      @php $pct = $totalExpenses > 0 ? round($exp['total'] / $totalExpenses * 100, 1) : 0; @endphp
      <div style="padding:10px 20px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px">
          <div style="display:flex;align-items:center;gap:9px">
            <div style="width:30px;height:30px;border-radius:8px;background:{{ $exp['bg'] }};display:grid;place-items:center;font-size:12px;color:{{ $exp['color'] }}">
              <i class="fa-solid {{ $exp['icon'] }}"></i>
            </div>
            <span style="font-size:13px;font-weight:600;color:var(--text)">{{ $exp['label'] }}</span>
          </div>
          <div style="text-align:right">
            <div style="font-size:13.5px;font-weight:700;color:var(--text)">Rp {{ number_format($exp['total']) }}</div>
            <div style="font-size:11px;color:var(--muted)">{{ $pct }}%</div>
          </div>
        </div>
        {{-- Progress bar --}}
        <div style="height:5px;border-radius:99px;background:var(--border);overflow:hidden">
          <div style="height:100%;width:{{ $pct }}%;border-radius:99px;background:{{ $exp['color'] }};transition:width .4s ease"></div>
        </div>
      </div>
      @endforeach
    </div>
    @endif
  </div>

</div>

</x-laundry-layout>
