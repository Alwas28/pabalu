<x-outlet-layout :outlet="$outlet" pageTitle="Laporan Penjualan">

@php
  $paymentMeta = [
    'cash'     => ['label'=>'Tunai',    'icon'=>'fa-money-bill-wave',  'color'=>'#34d399'],
    'qris'     => ['label'=>'QRIS',     'icon'=>'fa-qrcode',           'color'=>'#818cf8'],
    'transfer' => ['label'=>'Transfer', 'icon'=>'fa-building-columns', 'color'=>'#60a5fa'],
    'card'     => ['label'=>'Kartu',    'icon'=>'fa-credit-card',      'color'=>'#f59e0b'],
  ];

  // Untuk chart harian
  $chartDates  = $byDay->keys()->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m'))->values()->toArray();
  $chartTotals = $byDay->map(fn($d) => $d['total'])->values()->toArray();
  $chartCounts = $byDay->map(fn($d) => $d['count'])->values()->toArray();
@endphp

@push('scripts')
<script>
const salesDates  = @json($chartDates);
const salesTotals = @json($chartTotals);
const salesCounts = @json($chartCounts);
let trendChart = null;

function rebuildChart() {
  const el = document.getElementById('trend-chart');
  if (!el) return;
  const dark      = !document.body.classList.contains('light');
  const textColor = dark ? '#94a3b8' : '#64748b';
  const gridColor = dark ? 'rgba(255,255,255,.06)' : 'rgba(0,0,0,.07)';
  const ac        = getComputedStyle(document.documentElement).getPropertyValue('--ac').trim() || '#e8192c';

  if (trendChart) trendChart.destroy();

  trendChart = new Chart(el, {
    type: 'bar',
    data: {
      labels: salesDates,
      datasets: [
        {
          type: 'line',
          label: 'Transaksi',
          data: salesCounts,
          borderColor: '#60a5fa',
          backgroundColor: 'transparent',
          borderWidth: 2,
          pointRadius: 3,
          pointBackgroundColor: '#60a5fa',
          tension: 0.35,
          yAxisID: 'yCount',
        },
        {
          type: 'bar',
          label: 'Pendapatan',
          data: salesTotals,
          backgroundColor: ac + '33',
          borderColor: ac,
          borderWidth: 1.5,
          borderRadius: 5,
          yAxisID: 'yRevenue',
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
            label: ctx => ctx.dataset.label === 'Pendapatan'
              ? '  Rp ' + ctx.raw.toLocaleString('id-ID')
              : '  ' + ctx.raw + ' transaksi',
          }
        }
      },
      scales: {
        x: {
          grid: { color: gridColor },
          ticks: { color: textColor, font: { size: 11 } },
          border: { color: gridColor },
        },
        yRevenue: {
          position: 'left',
          grid: { color: gridColor },
          border: { color: gridColor },
          ticks: {
            color: textColor, font: { size: 11 },
            callback: v => v >= 1_000_000 ? (v/1_000_000).toFixed(1)+'jt' : v >= 1_000 ? (v/1_000).toFixed(0)+'rb' : v,
          }
        },
        yCount: {
          position: 'right',
          grid: { display: false },
          border: { display: false },
          ticks: { color: '#60a5fa', font: { size: 11 } },
        }
      }
    }
  });
}

document.addEventListener('DOMContentLoaded', rebuildChart);
</script>
@endpush

{{-- ── HEADER ── --}}
<div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px">
  <div>
    <h2 class="font-display" style="font-size:20px;font-weight:700;color:var(--text)">Laporan Penjualan</h2>
    <p style="font-size:13px;color:var(--muted);margin-top:2px">
      {{ \Carbon\Carbon::parse($from)->translatedFormat('d M Y') }} –
      {{ \Carbon\Carbon::parse($to)->translatedFormat('d M Y') }}
    </p>
  </div>
  <a href="{{ $outlet->route('reports.sales.export', request()->only(['from','to','payment_method','user_id'])) }}"
     style="display:inline-flex;align-items:center;gap:8px;padding:10px 18px;border-radius:12px;background:#16a34a;color:#fff;font-size:13.5px;font-weight:700;text-decoration:none;transition:opacity .15s"
     onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
    <i class="fa-solid fa-file-excel" style="font-size:13px"></i> Export Excel
  </a>
</div>

{{-- ── FILTER ── --}}
<div class="card" style="padding:16px 20px">
  <form method="GET" action="{{ $outlet->route('reports.sales') }}"
    style="display:flex;align-items:flex-end;gap:12px;flex-wrap:wrap">

    <div>
      <label class="f-label">Dari Tanggal</label>
      <input type="date" name="from" value="{{ $from }}" class="f-input" style="width:155px">
    </div>
    <div>
      <label class="f-label">Sampai</label>
      <input type="date" name="to" value="{{ $to }}" class="f-input" style="width:155px">
    </div>
    <div>
      <label class="f-label">Metode Bayar</label>
      <select name="payment_method" class="f-input" style="width:145px">
        <option value="">Semua</option>
        @foreach($paymentMeta as $key => $pm)
        <option value="{{ $key }}" @selected($paymentMethod === $key)>{{ $pm['label'] }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="f-label">Kasir</label>
      <select name="user_id" class="f-input" style="width:145px">
        <option value="">Semua</option>
        @foreach($cashiers as $kasir)
        <option value="{{ $kasir->id }}" @selected((string)$userId === (string)$kasir->id)>{{ $kasir->name }}</option>
        @endforeach
      </select>
    </div>

    <button type="submit"
      style="padding:9px 20px;border-radius:12px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:700;cursor:pointer;white-space:nowrap">
      <i class="fa-solid fa-filter" style="font-size:11px;margin-right:5px"></i>Tampilkan
    </button>

    {{-- Shortcut periode --}}
    <div style="display:flex;gap:6px;margin-left:auto">
      @php
        $shortcuts = [
          'Hari Ini'    => [today()->format('Y-m-d'), today()->format('Y-m-d')],
          'Minggu Ini'  => [today()->startOfWeek()->format('Y-m-d'), today()->format('Y-m-d')],
          'Bulan Ini'   => [today()->startOfMonth()->format('Y-m-d'), today()->format('Y-m-d')],
          'Bulan Lalu'  => [today()->subMonth()->startOfMonth()->format('Y-m-d'), today()->subMonth()->endOfMonth()->format('Y-m-d')],
        ];
      @endphp
      @foreach($shortcuts as $label => [$f, $t])
      <a href="{{ $outlet->route('reports.sales', ['from' => $f, 'to' => $t]) }}"
         style="padding:7px 12px;border-radius:9px;font-size:12px;font-weight:600;text-decoration:none;border:1px solid {{ ($from===$f && $to===$t) ? 'var(--ac)' : 'var(--border)' }};color:{{ ($from===$f && $to===$t) ? 'var(--ac)' : 'var(--muted)' }};background:{{ ($from===$f && $to===$t) ? 'var(--ac-lt)' : 'transparent' }};white-space:nowrap;transition:all .15s">
        {{ $label }}
      </a>
      @endforeach
    </div>
  </form>
</div>

{{-- ── STAT CARDS ── --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px">
  <div class="stat-card">
    <div class="stat-icon" style="background:var(--ac-lt);color:var(--ac)">
      <i class="fa-solid fa-receipt"></i>
    </div>
    <div>
      <div class="stat-num">{{ number_format($totalCount) }}</div>
      <div class="stat-label">Total Transaksi</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(52,211,153,.15);color:#34d399">
      <i class="fa-solid fa-money-bill-trend-up"></i>
    </div>
    <div>
      <div class="stat-num" style="font-size:17px">Rp {{ number_format($totalRevenue) }}</div>
      <div class="stat-label">Total Pendapatan</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(251,191,36,.15);color:#fbbf24">
      <i class="fa-solid fa-tag"></i>
    </div>
    <div>
      <div class="stat-num" style="font-size:17px;color:#fbbf24">Rp {{ number_format($totalDiscount) }}</div>
      <div class="stat-label">Total Diskon</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(99,102,241,.15);color:#818cf8">
      <i class="fa-solid fa-calculator"></i>
    </div>
    <div>
      <div class="stat-num" style="font-size:17px">Rp {{ number_format($avgTransaction) }}</div>
      <div class="stat-label">Rata-rata / Transaksi</div>
    </div>
  </div>
</div>

{{-- ── TREND CHART ── --}}
<div class="card">
  <div class="card-header">
    <span class="card-title">
      <i class="fa-solid fa-chart-mixed" style="color:var(--ac);margin-right:8px;font-size:13px"></i>
      Tren Penjualan Harian
    </span>
    <span style="font-size:12px;color:var(--muted)">
      {{ $byDay->count() }} hari · {{ \Carbon\Carbon::parse($from)->translatedFormat('d M') }} – {{ \Carbon\Carbon::parse($to)->translatedFormat('d M Y') }}
    </span>
  </div>
  @if($byDay->isEmpty())
  <div style="padding:40px;text-align:center;color:var(--muted);font-size:13px">
    <i class="fa-solid fa-chart-mixed" style="font-size:28px;display:block;margin-bottom:10px;opacity:.35"></i>
    Tidak ada data pada periode ini
  </div>
  @else
  <div style="padding:20px;position:relative;height:260px">
    <canvas id="trend-chart"></canvas>
  </div>
  @endif
</div>

{{-- ── MIDDLE: METODE + PRODUK ── --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

  {{-- Metode Bayar --}}
  <div class="card">
    <div class="card-header">
      <span class="card-title">
        <i class="fa-solid fa-credit-card" style="color:var(--ac);margin-right:8px;font-size:13px"></i>
        Per Metode Bayar
      </span>
    </div>
    <div style="overflow-x:auto">
      <table class="tbl">
        <thead>
          <tr>
            <th>Metode</th>
            <th style="text-align:center;width:80px">Trx</th>
            <th style="text-align:right;width:150px">Total</th>
            <th style="text-align:right;width:65px">%</th>
          </tr>
        </thead>
        <tbody>
          @foreach($byMethod as $key => $m)
          @php $pm = $paymentMeta[$key]; @endphp
          <tr>
            <td>
              <div style="display:flex;align-items:center;gap:9px">
                <div style="width:30px;height:30px;border-radius:8px;background:{{ $pm['color'] }}1a;display:grid;place-items:center;font-size:12px;color:{{ $pm['color'] }}">
                  <i class="fa-solid {{ $pm['icon'] }}"></i>
                </div>
                <span style="font-weight:600;color:var(--text)">{{ $pm['label'] }}</span>
              </div>
            </td>
            <td style="text-align:center;font-weight:600;color:var(--sub)">{{ $m['count'] ?: '—' }}</td>
            <td style="text-align:right;font-weight:700;color:{{ $m['total'] > 0 ? 'var(--text)' : 'var(--muted)' }}">
              {{ $m['total'] > 0 ? 'Rp '.number_format($m['total']) : '—' }}
            </td>
            <td style="text-align:right">
              @php $pct = $totalRevenue > 0 ? round($m['total'] / $totalRevenue * 100) : 0; @endphp
              <span style="font-size:12px;font-weight:700;color:{{ $pct > 0 ? $pm['color'] : 'var(--muted)' }}">
                {{ $pct > 0 ? $pct.'%' : '—' }}
              </span>
            </td>
          </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr style="border-top:2px solid var(--border)">
            <td style="padding:10px 14px;font-weight:700;color:var(--text)">Total</td>
            <td style="text-align:center;padding:10px 14px;font-weight:800;color:var(--text)">{{ $totalCount }}</td>
            <td style="text-align:right;padding:10px 14px;font-weight:800;color:var(--text)">Rp {{ number_format($totalRevenue) }}</td>
            <td style="text-align:right;padding:10px 14px;font-weight:700;color:var(--muted)">100%</td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

  {{-- Top Produk --}}
  <div class="card">
    <div class="card-header">
      <span class="card-title">
        <i class="fa-solid fa-ranking-star" style="color:var(--ac);margin-right:8px;font-size:13px"></i>
        Top Produk Terjual
      </span>
      <span style="font-size:12px;color:var(--muted)">{{ $byProduct->count() }} produk</span>
    </div>
    <div style="overflow-x:auto">
      <table class="tbl">
        <thead>
          <tr>
            <th style="width:28px">#</th>
            <th>Produk</th>
            <th style="text-align:center;width:70px">Qty</th>
            <th style="text-align:right;width:150px">Pendapatan</th>
            <th style="text-align:right;width:55px">%</th>
          </tr>
        </thead>
        <tbody>
          @forelse($byProduct as $i => $prod)
          @php $pct = $totalRevenue > 0 ? round($prod->total_revenue / $totalRevenue * 100, 1) : 0; @endphp
          <tr>
            <td style="font-size:11px;font-weight:700;color:{{ $i < 3 ? 'var(--ac)' : 'var(--muted)' }}">
              {{ $i + 1 }}
            </td>
            <td class="td-main">{{ $prod->product_name }}</td>
            <td style="text-align:center;font-weight:700;color:var(--text)">{{ number_format($prod->total_qty) }}</td>
            <td style="text-align:right;font-weight:700;color:var(--text)">Rp {{ number_format($prod->total_revenue) }}</td>
            <td style="text-align:right;font-size:12px;color:var(--muted)">{{ $pct }}%</td>
          </tr>
          @empty
          <tr>
            <td colspan="5" style="text-align:center;padding:32px;color:var(--muted)">Tidak ada data</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>

{{-- ── DETAIL TRANSAKSI ── --}}
<div class="card">
  <div class="card-header">
    <span class="card-title">
      <i class="fa-solid fa-list-ul" style="color:var(--ac);margin-right:8px;font-size:13px"></i>
      Detail Transaksi
    </span>
    <span style="font-size:12px;color:var(--muted)">{{ number_format($totalCount) }} transaksi</span>
  </div>

  @if($transactions->isEmpty())
  <div style="padding:48px 24px;text-align:center;color:var(--muted);font-size:13px">
    <i class="fa-solid fa-receipt" style="font-size:28px;display:block;margin-bottom:10px;opacity:.35"></i>
    Tidak ada transaksi pada periode ini
  </div>
  @else
  <div style="overflow-x:auto">
    <table class="tbl">
      <thead>
        <tr>
          <th style="width:130px">Tanggal</th>
          <th style="width:150px">No. Transaksi</th>
          <th>Item</th>
          <th style="text-align:right;width:130px">Total</th>
          <th style="width:100px">Metode</th>
          <th style="width:110px">Kasir</th>
        </tr>
      </thead>
      <tbody>
        @foreach($transactions->take(200) as $trx)
        @php $pm = $paymentMeta[$trx->payment_method] ?? ['label'=>$trx->payment_method,'color'=>'#94a3b8']; @endphp
        <tr>
          <td>
            <div style="font-weight:600;font-size:13px;color:var(--text)">{{ $trx->date->translatedFormat('d M Y') }}</div>
            <div style="font-size:11px;color:var(--muted)">{{ $trx->created_at->format('H:i') }}</div>
          </td>
          <td style="font-size:12px;color:var(--sub);font-family:monospace">{{ $trx->transaction_number }}</td>
          <td style="font-size:12.5px;color:var(--sub)">
            {{ $trx->items->map(fn($i) => $i->product_name.' x'.$i->qty)->implode(', ') }}
          </td>
          <td style="text-align:right;font-weight:700;color:var(--text)">Rp {{ number_format($trx->total) }}</td>
          <td>
            <span style="display:inline-flex;align-items:center;gap:4px;padding:2px 9px;border-radius:99px;font-size:11px;font-weight:600;background:{{ $pm['color'] }}1a;color:{{ $pm['color'] }}">
              {{ $pm['label'] }}
            </span>
          </td>
          <td style="font-size:12px;color:var(--sub)">{{ $trx->user?->name ?? '-' }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @if($transactions->count() > 200)
  <div style="padding:12px 20px;border-top:1px solid var(--border);font-size:12.5px;color:var(--muted);text-align:center">
    Menampilkan 200 dari {{ number_format($transactions->count()) }} transaksi.
    <a href="{{ $outlet->route('reports.sales.export', request()->only(['from','to','payment_method','user_id'])) }}"
       style="color:var(--ac);font-weight:600">Export Excel</a> untuk data lengkap.
  </div>
  @endif
  @endif
</div>

</x-outlet-layout>
