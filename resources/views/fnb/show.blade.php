@php
  $type       = $outlet->outletType;
  $hasOpening = $type?->requires_opening_stock ?? false;
  $trackCogs  = $type?->track_cogs ?? false;
  /** @var \App\Models\User $user */
  $p = fn(string $s) => $user->hasPermission($s);

  $paymentMeta = [
    'cash'     => ['label'=>'Tunai',    'color'=>'#34d399'],
    'qris'     => ['label'=>'QRIS',     'color'=>'#818cf8'],
    'transfer' => ['label'=>'Transfer', 'color'=>'#60a5fa'],
    'card'     => ['label'=>'Kartu',    'color'=>'#f59e0b'],
  ];
@endphp

<x-outlet-layout :outlet="$outlet" pageTitle="Dashboard">

@push('scripts')
<script>
const chart7Labels  = @json($chart7->keys()->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m'))->values());
const chart7Data    = @json($chart7->values());
const chart30Labels = @json($chart30->keys()->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m'))->values());
const chart30Data   = @json($chart30->values());
let salesChart = null;
let activeRange = 'week';

function buildChart(range) {
  const ctx    = document.getElementById('salesChart');
  if (!ctx) return;
  const isDark = !document.body.classList.contains('light');
  const labels = range === 'week' ? chart7Labels  : chart30Labels;
  const data   = range === 'week' ? chart7Data    : chart30Data;
  const ac     = getComputedStyle(document.documentElement).getPropertyValue('--ac').trim();

  if (salesChart) salesChart.destroy();

  salesChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: 'Penjualan',
        data,
        backgroundColor: ac + '55',
        borderColor: ac,
        borderWidth: 1.5,
        borderRadius: 6,
        borderSkipped: false,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: isDark ? '#1c2336' : '#fff',
          borderColor: isDark ? '#252d42' : '#e2e8f0',
          borderWidth: 1,
          titleColor: isDark ? '#e2e8f0' : '#1e293b',
          bodyColor: isDark ? '#94a3b8' : '#64748b',
          padding: 10,
          cornerRadius: 10,
          callbacks: { label: ctx => 'Rp ' + ctx.raw.toLocaleString('id-ID') }
        }
      },
      scales: {
        x: {
          grid: { color: isDark ? 'rgba(255,255,255,.05)' : 'rgba(0,0,0,.05)' },
          ticks: { color: isDark ? '#64748b' : '#94a3b8', font: { size: 11 }, maxTicksLimit: range === 'month' ? 10 : 7 },
          border: { color: isDark ? 'rgba(255,255,255,.05)' : 'rgba(0,0,0,.05)' },
        },
        y: {
          grid: { color: isDark ? 'rgba(255,255,255,.05)' : 'rgba(0,0,0,.05)' },
          ticks: {
            color: isDark ? '#64748b' : '#94a3b8',
            font: { size: 11 },
            callback: v => v === 0 ? '0' : (v >= 1000000 ? (v/1000000).toFixed(1)+'jt' : (v/1000).toFixed(0)+'rb')
          },
          border: { color: isDark ? 'rgba(255,255,255,.05)' : 'rgba(0,0,0,.05)' },
          beginAtZero: true
        }
      }
    }
  });
}

function setChartRange(range) {
  activeRange = range;
  ['week','month'].forEach(r => {
    const btn = document.getElementById('btn-'+r);
    if (!btn) return;
    btn.style.background = r === range ? 'var(--ac)' : 'transparent';
    btn.style.color      = r === range ? '#fff'      : 'var(--sub)';
    btn.style.border     = r === range ? 'none'      : '1px solid var(--border)';
  });
  buildChart(range);
}

function rebuildChart() { buildChart(activeRange); }

document.addEventListener('DOMContentLoaded', () => buildChart('week'));
</script>
@endpush

{{-- ── STAT CARDS ── --}}
<div class="stat-grid animate-fadeUp">

  <div class="stat-card d1">
    <div class="stat-icon" style="background:var(--ac-lt);color:var(--ac)">
      <i class="fa-solid fa-sack-dollar"></i>
    </div>
    <div>
      <div class="stat-num">Rp {{ number_format($todayRevenue) }}</div>
      <div class="stat-label">Pendapatan Hari Ini</div>
      <div class="stat-trend {{ $revenueChangePct === null ? '' : ($revenueChangePct >= 0 ? 'trend-up' : 'trend-down') }}"
           style="{{ $revenueChangePct === null ? 'color:var(--muted)' : '' }}">
        @if($revenueChangePct === null)
          <i class="fa-solid fa-minus"></i>Belum ada data kemarin
        @elseif($revenueChangePct >= 0)
          <i class="fa-solid fa-arrow-trend-up"></i>+{{ $revenueChangePct }}% vs kemarin
        @else
          <i class="fa-solid fa-arrow-trend-down"></i>{{ $revenueChangePct }}% vs kemarin
        @endif
      </div>
    </div>
  </div>

  <div class="stat-card d2">
    <div class="stat-icon" style="background:rgba(79,110,247,.15);color:#818cf8">
      <i class="fa-solid fa-receipt"></i>
    </div>
    <div>
      <div class="stat-num">{{ $todayTrxCount }}</div>
      <div class="stat-label">Transaksi Hari Ini</div>
      <div class="stat-trend" style="color:{{ $todayTrxCount > 0 ? '#34d399' : 'var(--muted)' }}">
        <i class="fa-solid {{ $todayTrxCount > 0 ? 'fa-circle-check' : 'fa-minus' }}"></i>
        {{ $todayTrxCount > 0 ? 'Ada '.$todayTrxCount.' transaksi' : 'Belum ada transaksi' }}
      </div>
    </div>
  </div>

  <div class="stat-card d3">
    <div class="stat-icon" style="background:rgba(248,113,113,.15);color:#f87171">
      <i class="fa-solid fa-file-invoice-dollar"></i>
    </div>
    <div>
      <div class="stat-num">Rp {{ number_format($todayExpenses) }}</div>
      <div class="stat-label">Pengeluaran Hari Ini</div>
      <div class="stat-trend" style="color:var(--muted)">
        <i class="fa-solid fa-minus"></i>{{ $todayExpenses > 0 ? 'Tercatat hari ini' : 'Belum ada pengeluaran' }}
      </div>
    </div>
  </div>

  <div class="stat-card d4">
    <div class="stat-icon" style="background:rgba(251,191,36,.15);color:#fbbf24">
      <i class="fa-solid fa-triangle-exclamation"></i>
    </div>
    <div>
      <div class="stat-num">{{ $criticalProducts->count() }}</div>
      <div class="stat-label">Stok Kritis</div>
      <div class="stat-trend" style="color:{{ $criticalProducts->isEmpty() ? '#34d399' : '#f87171' }}">
        <i class="fa-solid {{ $criticalProducts->isEmpty() ? 'fa-circle-check' : 'fa-circle-exclamation' }}"></i>
        {{ $criticalProducts->isEmpty() ? 'Semua stok aman' : $criticalProducts->count().' produk perlu restock' }}
      </div>
    </div>
  </div>

</div>

{{-- ── GRAFIK ── --}}
<div class="animate-fadeUp d2">
  <div class="card">
    <div class="card-header">
      <span class="card-title">
        <i class="fa-solid fa-chart-area" style="color:var(--ac);margin-right:8px;font-size:14px"></i>Penjualan Harian
      </span>
      <div style="display:flex;gap:6px">
        <button onclick="setChartRange('week')" id="btn-week"
          style="padding:5px 12px;border-radius:8px;font-size:12px;font-weight:600;border:none;cursor:pointer;transition:all .15s;background:var(--ac);color:#fff">7 Hari</button>
        <button onclick="setChartRange('month')" id="btn-month"
          style="padding:5px 12px;border-radius:8px;font-size:12px;font-weight:600;border:1px solid var(--border);cursor:pointer;transition:all .15s;background:transparent;color:var(--sub)">30 Hari</button>
      </div>
    </div>
    <div class="card-body" style="padding:20px 16px 14px;height:220px">
      <canvas id="salesChart"></canvas>
    </div>
    <div style="padding:0 20px 16px;display:flex;align-items:center;gap:20px">
      <div>
        <div style="font-size:11px;color:var(--muted)">Total 7 Hari</div>
        <div class="font-display" style="font-size:18px;font-weight:700;color:var(--text)">
          Rp {{ number_format($chart7->sum()) }}
        </div>
      </div>
      <div style="width:1px;height:30px;background:var(--border)"></div>
      <div>
        <div style="font-size:11px;color:var(--muted)">Rata-rata/Hari</div>
        <div class="font-display" style="font-size:18px;font-weight:700;color:var(--text)">
          Rp {{ number_format((int)($chart7->sum() / 7)) }}
        </div>
      </div>
      <div style="width:1px;height:30px;background:var(--border)"></div>
      <div>
        <div style="font-size:11px;color:var(--muted)">Transaksi Hari Ini</div>
        <div class="font-display" style="font-size:18px;font-weight:700;color:var(--text)">{{ $todayTrxCount }}</div>
      </div>
    </div>
  </div>
</div>

{{-- ── TRANSAKSI TERBARU + KOLOM KANAN ── --}}
<div class="two-col animate-fadeUp d3">

  {{-- Transaksi terbaru --}}
  @if($p('transaction.view'))
  <div class="card">
    <div class="card-header">
      <span class="card-title">
        <i class="fa-solid fa-clock-rotate-left" style="color:var(--ac);margin-right:8px;font-size:14px"></i>Transaksi Terbaru
      </span>
      <a href="{{ $outlet->route('transactions.index') }}"
         style="font-size:12.5px;font-weight:600;color:var(--ac);text-decoration:none;opacity:.85"
         onmouseover="this.style.opacity=1" onmouseout="this.style.opacity='.85'">
        Lihat semua <i class="fa-solid fa-arrow-right" style="font-size:10px"></i>
      </a>
    </div>
    <div style="overflow-x:auto">
      <table class="tbl">
        <thead>
          <tr>
            <th>No. Struk</th>
            <th>Waktu</th>
            <th>Kasir</th>
            <th style="text-align:right">Total</th>
            <th style="text-align:center">Metode</th>
          </tr>
        </thead>
        <tbody>
          @forelse($recentTransactions as $trx)
          @php $pm = $paymentMeta[$trx->payment_method] ?? ['label'=>$trx->payment_method,'color'=>'#94a3b8']; @endphp
          <tr onclick="window.location='{{ $outlet->route('transactions.show', [$trx]) }}'" style="cursor:pointer">
            <td style="font-size:12px;font-family:monospace;color:var(--sub)">{{ $trx->transaction_number }}</td>
            <td style="font-size:12px;color:var(--muted)">{{ $trx->created_at->format('d/m H:i') }}</td>
            <td class="td-main">{{ $trx->user?->name ?? '—' }}</td>
            <td style="text-align:right;font-weight:700;color:var(--text)">Rp {{ number_format($trx->total) }}</td>
            <td style="text-align:center">
              <span style="padding:2px 9px;border-radius:99px;font-size:11px;font-weight:600;background:{{ $pm['color'] }}1a;color:{{ $pm['color'] }}">
                {{ $pm['label'] }}
              </span>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="5" style="text-align:center;padding:40px;color:var(--muted)">
              <div style="display:flex;flex-direction:column;align-items:center;gap:8px">
                <i class="fa-solid fa-receipt" style="font-size:28px;opacity:.25"></i>
                <div style="font-size:13px">Belum ada transaksi</div>
                <div style="font-size:11.5px;opacity:.7">Transaksi akan muncul setelah POS digunakan</div>
              </div>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  @endif

  {{-- Kolom kanan --}}
  <div style="display:flex;flex-direction:column;gap:20px">

    {{-- Stok Kritis --}}
    @if($p('stock.view'))
    <div class="card">
      <div class="card-header">
        <span class="card-title">
          <i class="fa-solid fa-triangle-exclamation" style="color:#f87171;margin-right:8px;font-size:13px"></i>Stok Kritis
        </span>
        <a href="{{ $outlet->route('stock.index') }}"
           style="font-size:12.5px;font-weight:600;color:var(--ac);text-decoration:none;opacity:.85"
           onmouseover="this.style.opacity=1" onmouseout="this.style.opacity='.85'">Detail</a>
      </div>
      @if($criticalProducts->isEmpty())
      <div class="card-body" style="padding:20px;text-align:center;color:var(--muted)">
        <i class="fa-solid fa-circle-check" style="font-size:24px;color:#34d399;margin-bottom:8px;display:block"></i>
        <div style="font-size:13px">Semua stok dalam kondisi baik</div>
      </div>
      @else
      <div style="overflow-x:auto">
        <table class="tbl">
          <thead>
            <tr>
              <th>Produk</th>
              <th style="text-align:center;width:65px">Stok</th>
              <th style="text-align:center;width:65px">Min.</th>
            </tr>
          </thead>
          <tbody>
            @foreach($criticalProducts as $prod)
            <tr>
              <td class="td-main">{{ $prod->name }}</td>
              <td style="text-align:center;font-weight:700;color:{{ $prod->stock == 0 ? '#f87171' : '#fbbf24' }}">
                {{ $prod->stock }}
              </td>
              <td style="text-align:center;font-size:12px;color:var(--muted)">{{ $prod->min_stock }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @endif
    </div>
    @endif

    {{-- Top Produk --}}
    @if($p('report.sales'))
    <div class="card">
      <div class="card-header">
        <span class="card-title">
          <i class="fa-solid fa-fire" style="color:#fb923c;margin-right:8px;font-size:13px"></i>Produk Terlaris
        </span>
        <span style="font-size:11px;color:var(--muted)">7 hari terakhir</span>
      </div>
      @if($topProducts->isEmpty())
      <div class="card-body" style="padding:20px;text-align:center;color:var(--muted)">
        <i class="fa-solid fa-chart-bar" style="font-size:24px;opacity:.25;margin-bottom:8px;display:block"></i>
        <div style="font-size:13px">Belum ada data penjualan</div>
      </div>
      @else
      <div style="padding:8px 0">
        @foreach($topProducts as $i => $prod)
        <div style="display:flex;align-items:center;gap:12px;padding:8px 18px">
          <div style="width:22px;height:22px;border-radius:6px;display:grid;place-items:center;font-size:11px;font-weight:800;background:{{ $i === 0 ? 'rgba(251,146,60,.2)' : 'var(--surface2)' }};color:{{ $i === 0 ? '#fb923c' : 'var(--muted)' }};flex-shrink:0">
            {{ $i + 1 }}
          </div>
          <div style="flex:1;min-width:0">
            <div style="font-size:13px;font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
              {{ $prod->product_name }}
            </div>
            <div style="font-size:11.5px;color:var(--muted)">{{ number_format($prod->total_qty) }} terjual</div>
          </div>
          <div style="font-size:13px;font-weight:700;color:var(--text);white-space:nowrap">
            Rp {{ number_format($prod->total_revenue) }}
          </div>
        </div>
        @endforeach
      </div>
      @endif
    </div>
    @endif

    {{-- Info Outlet --}}
    <div class="card">
      <div class="card-header">
        <span class="card-title">
          <i class="fa-solid fa-circle-info" style="color:var(--ac);margin-right:8px;font-size:14px"></i>Info Outlet
        </span>
      </div>
      <div class="card-body" style="padding:14px 20px;display:flex;flex-direction:column;gap:12px">

        <div style="display:flex;align-items:flex-start;gap:10px">
          <i class="fa-solid fa-barcode" style="color:var(--muted);font-size:12px;margin-top:2px;width:14px;text-align:center"></i>
          <div>
            <div style="font-size:11.5px;color:var(--muted)">Kode Outlet</div>
            <div style="font-size:14px;font-weight:800;font-family:monospace;letter-spacing:.06em;color:var(--ac)">
              {{ ($type?->type_code ?? '??') . ($outlet->code ?? '????') }}
            </div>
          </div>
        </div>

        <div style="display:flex;align-items:flex-start;gap:10px">
          <i class="fa-solid fa-shop" style="color:var(--muted);font-size:12px;margin-top:2px;width:14px;text-align:center"></i>
          <div>
            <div style="font-size:11.5px;color:var(--muted)">Jenis Outlet</div>
            <div style="font-size:13.5px;font-weight:600;color:var(--text)">{{ $type?->name ?? '—' }}</div>
          </div>
        </div>

        @if($outlet->phone)
        <div style="display:flex;align-items:flex-start;gap:10px">
          <i class="fa-brands fa-whatsapp" style="color:#25d366;font-size:12px;margin-top:2px;width:14px;text-align:center"></i>
          <div>
            <div style="font-size:11.5px;color:var(--muted)">Nomor WhatsApp</div>
            <div style="font-size:13.5px;font-weight:600;color:var(--text)">{{ $outlet->phone }}</div>
          </div>
        </div>
        @endif

        @if($outlet->address)
        <div style="display:flex;align-items:flex-start;gap:10px">
          <i class="fa-solid fa-location-dot" style="color:var(--muted);font-size:12px;margin-top:2px;width:14px;text-align:center"></i>
          <div>
            <div style="font-size:11.5px;color:var(--muted)">Alamat</div>
            <div style="font-size:13.5px;font-weight:600;color:var(--text)">{{ $outlet->address }}</div>
          </div>
        </div>
        @endif

        <div style="display:flex;align-items:flex-start;gap:10px">
          <i class="fa-solid fa-circle" style="font-size:8px;margin-top:4px;width:14px;text-align:center;color:{{ $outlet->is_active ? '#34d399' : '#f87171' }}"></i>
          <div>
            <div style="font-size:11.5px;color:var(--muted)">Status</div>
            <div style="font-size:13.5px;font-weight:600;color:{{ $outlet->is_active ? '#34d399' : '#f87171' }}">
              {{ $outlet->is_active ? 'Aktif' : 'Nonaktif' }}
            </div>
          </div>
        </div>

        <div style="border-top:1px solid var(--border);padding-top:12px;display:flex;flex-direction:column;gap:7px">
          <div style="font-size:11px;font-weight:600;letter-spacing:.8px;text-transform:uppercase;color:var(--muted)">Fitur Aktif</div>
          @if($hasOpening)
          <div style="display:flex;align-items:center;gap:7px;font-size:12.5px">
            <i class="fa-solid fa-check-circle" style="color:#34d399;font-size:12px"></i>
            <span style="color:var(--sub)">Opening &amp; Closing Stok</span>
          </div>
          @endif
          @if($trackCogs)
          <div style="display:flex;align-items:center;gap:7px;font-size:12.5px">
            <i class="fa-solid fa-check-circle" style="color:#34d399;font-size:12px"></i>
            <span style="color:var(--sub)">Tracking HPP &amp; Laba Rugi</span>
          </div>
          @endif
          <div style="display:flex;align-items:center;gap:7px;font-size:12.5px">
            <i class="fa-solid fa-check-circle" style="color:#34d399;font-size:12px"></i>
            <span style="color:var(--sub)">Point of Sale (POS)</span>
          </div>
        </div>

      </div>
    </div>

  </div>
</div>

</x-outlet-layout>
