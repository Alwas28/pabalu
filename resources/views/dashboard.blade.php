<x-app-layout title="Dashboard">

  {{-- Email verified flash --}}
  @if(request()->has('verified'))
  <div style="background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.25);border-radius:12px;
              padding:12px 18px;font-size:13px;color:#34d399;display:flex;align-items:center;gap:10px;margin-bottom:4px">
    <i class="fa-solid fa-circle-check" style="flex-shrink:0"></i>
    <span>Email Anda berhasil diverifikasi. Selamat datang di Pabalu!</span>
  </div>
  @endif

  {{-- CTA for new owners with no outlets --}}
  @if(auth()->user()->isOwner() && $noOutlets)
  <div style="background:linear-gradient(135deg,rgba(245,158,11,.12),rgba(239,68,68,.08));
              border:1px solid rgba(245,158,11,.3);border-radius:16px;padding:28px 32px;
              display:flex;align-items:center;gap:24px;flex-wrap:wrap;margin-bottom:8px">
    <div style="width:52px;height:52px;border-radius:14px;flex-shrink:0;
                background:linear-gradient(135deg,#f59e0b,#ef4444);
                display:grid;place-items:center;font-size:22px;color:#fff">
      <i class="fa-solid fa-store"></i>
    </div>
    <div style="flex:1;min-width:200px">
      <div style="font-family:'Clash Display',sans-serif;font-size:18px;font-weight:700;color:#e2e8f0;margin-bottom:4px">
        Selamat datang di Pabalu!
      </div>
      <div style="font-size:13.5px;color:#94a3b8">
        Anda belum memiliki outlet. Buat outlet pertama Anda untuk mulai menerima transaksi dan order online.
      </div>
    </div>
    @can('outlet.create')
    <a href="{{ route('outlets.create') }}"
       style="display:inline-flex;align-items:center;gap:8px;padding:10px 20px;border-radius:12px;
              background:linear-gradient(135deg,#f59e0b,#ef4444);color:#fff;font-weight:700;
              font-size:13.5px;text-decoration:none;white-space:nowrap;transition:opacity .15s"
       onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
      <i class="fa-solid fa-plus"></i> Buat Outlet Pertama
    </a>
    @endcan
  </div>
  @endif

  {{-- Outlet selector --}}
  <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
    @if ($assignedOutletId)
      @php $assignedOutlet = $outlets->firstWhere('id', $assignedOutletId); @endphp
      <div style="display:flex;align-items:center;gap:8px;padding:7px 14px;border-radius:10px;
                  background:var(--ac-lt);border:1px solid var(--ac);font-size:13px;font-weight:600;color:var(--ac)">
        <i class="fa-solid fa-store"></i>
        {{ $assignedOutlet?->nama ?? 'Outlet #'.$assignedOutletId }}
        <i class="fa-solid fa-lock" style="font-size:10px;opacity:.7"></i>
      </div>
    @else
      <form method="GET" action="{{ route('dashboard') }}" id="outlet-form" style="display:contents">
        <select name="outlet_id" class="f-input" style="width:auto;padding:7px 14px;font-size:13px"
          onchange="document.getElementById('outlet-form').submit()">
          <option value="">— Semua Outlet —</option>
          @foreach($outlets as $o)
          <option value="{{ $o->id }}" @selected($outletId == $o->id)>{{ $o->nama }}</option>
          @endforeach
        </select>
      </form>
    @endif
    <div style="font-size:12.5px;color:var(--muted)">
      <i class="fa-solid fa-calendar-day" style="margin-right:5px;color:var(--ac)"></i>
      {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}
    </div>
  </div>

  {{-- ── Stat Cards ── --}}
  @php
    $trendOmzet = $omzetKemarin > 0
      ? round((($omzetHariIni - $omzetKemarin) / $omzetKemarin) * 100, 1)
      : null;
  @endphp
  <div class="stat-grid animate-fadeUp">
    <div class="stat-card">
      <div class="stat-icon a-bg-lt" style="color:var(--ac)">
        <i class="fa-solid fa-sack-dollar"></i>
      </div>
      <div>
        <div class="stat-num" style="font-size:20px">Rp {{ number_format($omzetHariIni, 0, ',', '.') }}</div>
        <div class="stat-label">Omzet Hari Ini</div>
        @if($trendOmzet !== null)
        <div class="stat-trend {{ $trendOmzet >= 0 ? 'trend-up' : 'trend-down' }}">
          <i class="fa-solid fa-arrow-{{ $trendOmzet >= 0 ? 'up' : 'down' }}"></i>
          {{ abs($trendOmzet) }}% vs kemarin
        </div>
        @else
        <div class="stat-trend" style="color:var(--muted)"><i class="fa-solid fa-minus"></i> Pertama hari ini</div>
        @endif
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(99,102,241,.14);color:#818cf8">
        <i class="fa-solid fa-receipt"></i>
      </div>
      <div>
        <div class="stat-num">{{ $trxHariIni }}</div>
        <div class="stat-label">Transaksi Hari Ini</div>
        <div class="stat-trend" style="color:var(--muted)">
          <i class="fa-solid fa-cube" style="font-size:10px"></i> {{ number_format($itemTerjual) }} item terjual
        </div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(248,113,113,.14);color:#f87171">
        <i class="fa-solid fa-wallet"></i>
      </div>
      <div>
        <div class="stat-num" style="font-size:20px">Rp {{ number_format($expenseHariIni, 0, ',', '.') }}</div>
        <div class="stat-label">Pengeluaran Hari Ini</div>
        <div class="stat-trend" style="color:var(--muted)"><i class="fa-solid fa-minus"></i></div>
      </div>
    </div>
    <div class="stat-card" style="border-color:{{ $labaHariIni >= 0 ? 'rgba(52,211,153,.25)' : 'rgba(248,113,113,.25)' }}">
      <div class="stat-icon" style="background:rgba({{ $labaHariIni >= 0 ? '52,211,153' : '248,113,113' }},.15);color:{{ $labaHariIni >= 0 ? '#34d399' : '#f87171' }}">
        <i class="fa-solid fa-scale-balanced"></i>
      </div>
      <div>
        <div class="stat-num" style="font-size:20px;color:{{ $labaHariIni >= 0 ? '#34d399' : '#f87171' }}">
          {{ $labaHariIni < 0 ? '−' : '' }}Rp {{ number_format(abs($labaHariIni), 0, ',', '.') }}
        </div>
        <div class="stat-label">{{ $labaHariIni >= 0 ? 'Laba Kotor' : 'Rugi Kotor' }} Hari Ini</div>
        <div class="stat-trend" style="color:var(--muted)"><i class="fa-solid fa-minus"></i></div>
      </div>
    </div>
  </div>

  {{-- ── Quick Nav ── --}}
  <div class="qnav-grid animate-fadeUp d1">
    @can('transaction.create')
    <a class="qnav-item" href="{{ route('transactions.pos', $outletId ? ['outlet_id'=>$outletId] : []) }}"
      onmouseover="this.style.borderColor='#818cf8'" onmouseout="this.style.borderColor='var(--border)'">
      <div style="width:36px;height:36px;border-radius:10px;display:grid;place-items:center;flex-shrink:0;background:rgba(99,102,241,.14);color:#818cf8">
        <i class="fa-solid fa-cash-register" style="font-size:13px"></i>
      </div>
      <span style="font-size:13px;font-weight:600;color:var(--text)">POS / Kasir</span>
      <i class="fa-solid fa-chevron-right" style="margin-left:auto;font-size:10px;color:var(--muted)"></i>
    </a>
    @endcan
    @can('stock.opening')
    <a class="qnav-item" href="{{ route('opening.index', $outletId ? ['outlet_id'=>$outletId] : []) }}"
      onmouseover="this.style.borderColor='#34d399'" onmouseout="this.style.borderColor='var(--border)'">
      <div style="width:36px;height:36px;border-radius:10px;display:grid;place-items:center;flex-shrink:0;background:rgba(16,185,129,.14);color:#34d399">
        <i class="fa-solid fa-box-open" style="font-size:13px"></i>
      </div>
      <span style="font-size:13px;font-weight:600;color:var(--text)">Opening Stok</span>
      <i class="fa-solid fa-chevron-right" style="margin-left:auto;font-size:10px;color:var(--muted)"></i>
    </a>
    @endcan
    @canany(['report.outlet','report.all'])
    <a class="qnav-item" href="{{ route('reports.sales') }}"
      onmouseover="this.style.borderColor='var(--ac)'" onmouseout="this.style.borderColor='var(--border)'">
      <div class="a-bg-lt a-text" style="width:36px;height:36px;border-radius:10px;display:grid;place-items:center;flex-shrink:0">
        <i class="fa-solid fa-chart-line" style="font-size:13px"></i>
      </div>
      <span style="font-size:13px;font-weight:600;color:var(--text)">Laporan</span>
      <i class="fa-solid fa-chevron-right" style="margin-left:auto;font-size:10px;color:var(--muted)"></i>
    </a>
    @endcanany
    @can('closing.read')
    <a class="qnav-item" href="{{ route('closing.index', $outletId ? ['outlet_id'=>$outletId] : []) }}"
      onmouseover="this.style.borderColor='#f87171'" onmouseout="this.style.borderColor='var(--border)'">
      <div style="width:36px;height:36px;border-radius:10px;display:grid;place-items:center;flex-shrink:0;background:rgba(239,68,68,.14);color:#f87171">
        <i class="fa-solid fa-lock" style="font-size:13px"></i>
      </div>
      <span style="font-size:13px;font-weight:600;color:var(--text)">Closing Harian</span>
      <i class="fa-solid fa-chevron-right" style="margin-left:auto;font-size:10px;color:var(--muted)"></i>
    </a>
    @endcan
  </div>

  {{-- ── Chart + Stok Kritis ── --}}
  <div class="two-col animate-fadeUp d2">

    {{-- Grafik Penjualan --}}
    <div class="card">
      <div class="card-header">
        <div class="card-title"><i class="fa-solid fa-chart-line a-text" style="margin-right:8px"></i>Grafik Penjualan</div>
        <div style="display:flex;align-items:center;gap:8px">
          <button onclick="setChartPeriod('minggu',this)" id="btn-minggu" class="chart-period-btn"
            style="font-size:12px;font-weight:600;padding:5px 12px;border-radius:8px;border:none;cursor:pointer;background:var(--ac-lt);color:var(--ac);font-family:inherit">
            7 Hari
          </button>
          <button onclick="setChartPeriod('bulan',this)" id="btn-bulan" class="chart-period-btn"
            style="font-size:12px;font-weight:600;padding:5px 12px;border-radius:8px;border:none;cursor:pointer;background:var(--surface2);color:var(--muted);font-family:inherit">
            30 Hari
          </button>
        </div>
      </div>
      <div class="card-body" style="padding:16px;position:relative;height:260px">
        @if(!$outletId)
        <div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--muted);font-size:13px">
          <div style="text-align:center">
            <i class="fa-solid fa-chart-simple" style="font-size:32px;display:block;margin-bottom:10px;opacity:.3"></i>
            Pilih outlet untuk melihat grafik
          </div>
        </div>
        @else
        <canvas id="salesChart"></canvas>
        @endif
      </div>
    </div>

    {{-- Stok Kritis --}}
    <div class="card">
      <div class="card-header">
        <div class="card-title">
          <i class="fa-solid fa-triangle-exclamation" style="color:#f87171;margin-right:8px"></i>Stok Kritis
        </div>
        <span class="badge {{ $stokKritis->count() > 0 ? 'badge-red' : 'badge-green' }}">
          {{ $stokKritis->count() }} Produk
        </span>
      </div>
      @if($stokKritis->isEmpty())
      <div style="padding:36px 20px;text-align:center">
        <i class="fa-solid fa-circle-check" style="font-size:32px;color:#34d399;margin-bottom:10px;display:block"></i>
        <div style="font-size:13px;color:var(--muted)">
          {{ $outletId ? 'Semua stok aman' : 'Pilih outlet untuk cek stok' }}
        </div>
      </div>
      @else
      <div style="max-height:280px;overflow-y:auto">
        @foreach($stokKritis as $sk)
        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 20px;border-bottom:1px solid var(--border)">
          <div>
            <div style="font-size:13px;font-weight:600;color:var(--text)">{{ $sk['nama'] }}</div>
            <div style="font-size:11px;color:var(--muted)">{{ $sk['satuan'] }}</div>
          </div>
          <span style="font-family:'Clash Display',sans-serif;font-size:22px;font-weight:700;
            color:{{ $sk['stok'] == 0 ? '#f87171' : '#fbbf24' }}">
            {{ $sk['stok'] }}
          </span>
        </div>
        @endforeach
      </div>
      @endif
    </div>

  </div>

  {{-- ── Transaksi Terbaru ── --}}
  <div class="card animate-fadeUp d3">
    <div class="card-header">
      <div class="card-title"><i class="fa-solid fa-clock-rotate-left a-text" style="margin-right:8px"></i>Transaksi Terbaru</div>
      @if($outletId)
      <a href="{{ route('transactions.index', ['outlet_id'=>$outletId]) }}"
        style="font-size:12px;font-weight:600;padding:5px 12px;border-radius:8px;background:var(--surface2);color:var(--muted);text-decoration:none;transition:color .15s"
        onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--muted)'">
        Lihat Semua <i class="fa-solid fa-arrow-right" style="font-size:10px"></i>
      </a>
      @endif
    </div>
    <div style="overflow-x:auto">
      <table class="tbl">
        <thead>
          <tr>
            <th>No. Transaksi</th>
            <th>Kasir</th>
            <th style="text-align:center">Item</th>
            <th>Metode</th>
            <th style="text-align:right">Total</th>
            <th style="text-align:center">Status</th>
            <th>Waktu</th>
          </tr>
        </thead>
        <tbody>
          @forelse($recentTrx as $trx)
          @php
            $icons=['tunai'=>'money-bill-wave','qris'=>'qrcode','transfer'=>'building-columns'];
            $m = $trx->metode_bayar ?? 'tunai';
          @endphp
          <tr>
            <td>
              <a href="{{ route('transactions.show', $trx) }}"
                style="font-family:monospace;font-size:12px;color:var(--ac);text-decoration:none;font-weight:600">
                {{ $trx->nomor_transaksi }}
              </a>
            </td>
            <td style="font-size:12.5px">{{ $trx->kasir->name ?? '—' }}</td>
            <td style="text-align:center;color:var(--muted);font-size:12px">{{ $trx->items->count() }}</td>
            <td>
              <span class="badge badge-blue" style="font-size:10.5px">
                <i class="fa-solid fa-{{ $icons[$m] }}" style="font-size:9px"></i>
                {{ ucfirst($m) }}
              </span>
            </td>
            <td style="text-align:right;font-weight:600;color:var(--text)">
              Rp {{ number_format($trx->total, 0, ',', '.') }}
            </td>
            <td style="text-align:center">
              <span class="badge {{ $trx->status === 'paid' ? 'badge-green' : 'badge-red' }}">
                {{ $trx->status === 'paid' ? 'Lunas' : 'Void' }}
              </span>
            </td>
            <td style="font-size:12px;color:var(--muted)">{{ $trx->created_at->format('H:i') }}</td>
          </tr>
          @empty
          <tr>
            <td colspan="7" style="text-align:center;padding:40px;color:var(--muted)">
              <i class="fa-solid fa-inbox" style="font-size:28px;display:block;margin-bottom:10px;opacity:.4"></i>
              {{ $outletId ? 'Belum ada transaksi hari ini' : 'Pilih outlet terlebih dahulu' }}
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- ── Ringkasan 7 Hari ── --}}
  @if($outletId && count($chartMinggu))
  <div class="card animate-fadeUp d4">
    <div class="card-header">
      <div class="card-title"><i class="fa-solid fa-calendar-week a-text" style="margin-right:8px"></i>Penjualan 7 Hari Terakhir</div>
    </div>
    @php $maxOmzet = collect($chartMinggu)->max('value') ?: 1; @endphp
    <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:10px;padding:20px">
      @foreach($chartMinggu as $day)
      @php $pct = $maxOmzet > 0 ? min(100, ($day['value'] / $maxOmzet) * 100) : 0; @endphp
      <div style="display:flex;flex-direction:column;align-items:center;gap:5px">
        <div style="font-size:10.5px;font-weight:600;color:var(--muted);text-align:center">
          {{ $day['value'] > 0 ? 'Rp '.number_format($day['value']/1000,0,'.',',').'K' : '—' }}
        </div>
        <div style="flex:1;width:100%;min-height:60px;display:flex;align-items:flex-end">
          <div style="width:100%;height:{{ max(6, $pct) }}%;background:{{ $day['date'] === $tanggal ? 'var(--ac)' : 'var(--ac-lt)' }};
                      border-radius:6px 6px 4px 4px;min-height:6px;transition:height .3s"></div>
        </div>
        <div style="font-size:11.5px;font-weight:600;color:{{ $day['date'] === $tanggal ? 'var(--ac)' : 'var(--sub)' }}">
          {{ $day['label'] }}
        </div>
      </div>
      @endforeach
    </div>
  </div>
  @endif

@push('scripts')
@php
  $mingguJson = json_encode(array_column($chartMinggu, 'value'));
  $mingguLabels = json_encode(array_column($chartMinggu, 'label'));
  $bulanJson = json_encode(array_column($chartBulan, 'value'));
  $bulanLabels = json_encode(array_column($chartBulan, 'label'));
@endphp
<script>
var chartData = {
  minggu: { labels: {!! $mingguLabels !!}, data: {!! $mingguJson !!} },
  bulan:  { labels: {!! $bulanLabels !!},  data: {!! $bulanJson !!}  },
};
var salesChart;
var activePeriod = 'minggu';

window.rebuildChart = function() {
  var canvas = document.getElementById('salesChart');
  if (!canvas) return;
  var d        = chartData[activePeriod];
  var isDark   = !document.body.classList.contains('light');
  var gridColor = isDark ? 'rgba(255,255,255,.06)' : 'rgba(0,0,0,.05)';
  var tickColor = isDark ? '#64748b' : '#94a3b8';
  var acColor   = getComputedStyle(document.documentElement).getPropertyValue('--ac').trim() || '#f59e0b';

  if (salesChart) salesChart.destroy();
  salesChart = new Chart(canvas, {
    type: 'line',
    data: {
      labels: d.labels,
      datasets: [{
        label: 'Penjualan',
        data: d.data,
        borderColor: acColor,
        backgroundColor: acColor + '22',
        pointBackgroundColor: acColor,
        pointBorderColor: isDark ? '#161b27' : '#fff',
        pointBorderWidth: 2, pointRadius: 5, pointHoverRadius: 7,
        tension: 0.4, fill: true, borderWidth: 2.5,
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: isDark ? '#1c2336' : '#fff',
          borderColor: isDark ? '#252d42' : '#e2e8f0',
          borderWidth: 1, titleColor: tickColor,
          bodyColor: isDark ? '#e2e8f0' : '#1e293b',
          padding: 12, cornerRadius: 12, displayColors: false,
          callbacks: {
            label: function(ctx) {
              return 'Rp ' + Math.round(ctx.raw).toLocaleString('id-ID');
            }
          }
        }
      },
      scales: {
        x: { grid: { color: gridColor }, ticks: { color: tickColor, font: { size: 11, family: 'Plus Jakarta Sans' } } },
        y: {
          grid: { color: gridColor },
          ticks: {
            color: tickColor, font: { size: 11, family: 'Plus Jakarta Sans' },
            callback: function(v) {
              return v >= 1000000 ? 'Rp '+(v/1000000).toFixed(1)+'Jt' : 'Rp '+(v/1000).toFixed(0)+'K';
            }
          }
        }
      }
    }
  });
};

function setChartPeriod(period, btn) {
  activePeriod = period;
  document.querySelectorAll('.chart-period-btn').forEach(function(b) {
    b.style.background = 'var(--surface2)'; b.style.color = 'var(--muted)';
  });
  btn.style.background = 'var(--ac-lt)'; btn.style.color = 'var(--ac)';
  window.rebuildChart();
}

window.rebuildChart();
</script>
@endpush

</x-app-layout>
