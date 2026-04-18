<x-app-layout title="Laporan Penjualan">

  {{-- Filter --}}
  <form method="GET" action="{{ route('reports.sales') }}" id="filter-form">
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr auto auto;gap:10px;align-items:end;flex-wrap:wrap">
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
        <label class="f-label">Dari Tanggal</label>
        <input type="date" name="date_from" class="f-input" value="{{ $dateFrom }}">
      </div>
      <div class="f-group" style="margin-bottom:0">
        <label class="f-label">Sampai Tanggal</label>
        <input type="date" name="date_to" class="f-input" value="{{ $dateTo }}">
      </div>
      <button type="submit" class="btn btn-primary" style="padding:9px 16px">
        <i class="fa-solid fa-magnifying-glass"></i> Filter
      </button>
      <button type="button" onclick="window.print()" class="btn no-print" style="padding:9px 14px">
        <i class="fa-solid fa-print"></i>
      </button>
    </div>
    {{-- Shortcut rentang --}}
    <div style="display:flex;gap:6px;margin-top:10px;flex-wrap:wrap">
      @php
        $shortcuts = [
          ['label'=>'Hari Ini',    'from'=>today(),                  'to'=>today()],
          ['label'=>'Kemarin',     'from'=>today()->subDay(),         'to'=>today()->subDay()],
          ['label'=>'7 Hari',      'from'=>today()->subDays(6),       'to'=>today()],
          ['label'=>'Bulan Ini',   'from'=>today()->startOfMonth(),   'to'=>today()],
          ['label'=>'Bulan Lalu',  'from'=>today()->subMonth()->startOfMonth(), 'to'=>today()->subMonth()->endOfMonth()],
        ];
      @endphp
      @foreach($shortcuts as $sc)
      <button type="button"
        onclick="setRange('{{ $sc['from']->toDateString() }}','{{ $sc['to']->toDateString() }}')"
        style="padding:4px 11px;border-radius:8px;font-size:11.5px;font-weight:600;
               border:1px solid var(--border);background:var(--surface2);color:var(--sub);
               cursor:pointer;font-family:inherit;transition:background .15s,color .15s"
        onmouseover="this.style.background='var(--border)';this.style.color='var(--text)'"
        onmouseout="this.style.background='var(--surface2)';this.style.color='var(--sub)'">
        {{ $sc['label'] }}
      </button>
      @endforeach
    </div>
  </form>

  {{-- Stats --}}
  <div class="stat-grid animate-fadeUp" style="grid-template-columns:repeat(3,1fr)">
    <div class="stat-card">
      <div class="stat-icon a-bg-lt" style="color:var(--ac)"><i class="fa-solid fa-receipt"></i></div>
      <div>
        <div class="stat-num">{{ number_format($totalTrx) }}</div>
        <div class="stat-label">Total Transaksi</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(52,211,153,.15);color:#34d399"><i class="fa-solid fa-sack-dollar"></i></div>
      <div>
        <div class="stat-num" style="font-size:18px">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</div>
        <div class="stat-label">Total Omzet</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(129,140,248,.15);color:#818cf8"><i class="fa-solid fa-chart-bar"></i></div>
      <div>
        <div class="stat-num" style="font-size:18px">
          Rp {{ $totalTrx > 0 ? number_format($totalOmzet / $totalTrx, 0, ',', '.') : '0' }}
        </div>
        <div class="stat-label">Rata-rata per Transaksi</div>
      </div>
    </div>
  </div>

  @if($outletId && $perHari->isNotEmpty())
  <div style="display:flex;flex-direction:column;gap:20px">

    {{-- Grafik per hari --}}
    <div class="card animate-fadeUp d1">
      <div class="card-header">
        <div class="card-title"><i class="fa-solid fa-chart-line a-text" style="margin-right:8px"></i>Omzet Per Hari</div>
      </div>
      <div class="card-body" style="height:260px;padding:16px">
        <canvas id="salesChart"></canvas>
      </div>
    </div>

    <div class="two-col animate-fadeUp d2">

      {{-- Per hari table --}}
      <div class="card">
        <div class="card-header">
          <div class="card-title"><i class="fa-solid fa-calendar-days a-text" style="margin-right:8px"></i>Ringkasan Per Hari</div>
        </div>
        <div style="overflow-x:auto;max-height:380px;overflow-y:auto">
          <table class="tbl">
            <thead style="position:sticky;top:0;z-index:1">
              <tr>
                <th>Tanggal</th>
                <th style="text-align:center">Transaksi</th>
                <th style="text-align:right">Omzet</th>
              </tr>
            </thead>
            <tbody>
              @foreach($perHari as $row)
              <tr>
                <td style="white-space:nowrap">{{ \Carbon\Carbon::parse($row['tanggal'])->translatedFormat('d M Y') }}</td>
                <td style="text-align:center">{{ $row['jumlah'] }}</td>
                <td style="text-align:right;font-weight:600;color:var(--text)">Rp {{ number_format($row['omzet'], 0, ',', '.') }}</td>
              </tr>
              @endforeach
            </tbody>
            <tfoot>
              <tr>
                <td colspan="2" style="text-align:right;font-weight:700;padding:10px 14px;color:var(--text)">Total</td>
                <td style="text-align:right;font-weight:700;padding:10px 14px;color:#34d399">Rp {{ number_format($totalOmzet,0,',','.') }}</td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      {{-- Per produk --}}
      <div class="card">
        <div class="card-header">
          <div class="card-title"><i class="fa-solid fa-cubes a-text" style="margin-right:8px"></i>Produk Terlaris</div>
        </div>
        @if($perProduk->isEmpty())
        <div class="card-body" style="text-align:center;padding:40px;color:var(--muted)">Belum ada data</div>
        @else
        <div style="overflow-x:auto;max-height:380px;overflow-y:auto">
          <table class="tbl">
            <thead style="position:sticky;top:0;z-index:1">
              <tr>
                <th>#</th>
                <th>Produk</th>
                <th style="text-align:center">Qty</th>
                <th style="text-align:right">Omzet</th>
              </tr>
            </thead>
            <tbody>
              @foreach($perProduk as $i => $row)
              <tr>
                <td style="color:var(--muted);font-size:12px">{{ $i+1 }}</td>
                <td class="td-main">{{ $row->nama_produk }}</td>
                <td style="text-align:center;font-weight:600">{{ number_format($row->total_qty) }}</td>
                <td style="text-align:right;font-weight:600;color:var(--text)">Rp {{ number_format($row->total_subtotal,0,',','.') }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @endif
      </div>

    </div>

  </div>
  @elseif(!$outletId)
  <div class="card"><div class="card-body" style="text-align:center;padding:56px;color:var(--muted)">
    <i class="fa-solid fa-chart-line" style="font-size:40px;display:block;margin-bottom:14px;opacity:.3"></i>
    Pilih outlet untuk melihat laporan penjualan.
  </div></div>
  @else
  <div class="card"><div class="card-body" style="text-align:center;padding:56px;color:var(--muted)">
    <i class="fa-solid fa-inbox" style="font-size:40px;display:block;margin-bottom:14px;opacity:.3"></i>
    Tidak ada transaksi pada periode yang dipilih.
  </div></div>
  @endif

@push('scripts')
@php
  $chartLabels = json_encode($perHari->pluck('tanggal')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m'))->toArray());
  $chartValues = json_encode($perHari->pluck('omzet')->toArray());
@endphp
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
}
</style>
<script>
(function(){
  var canvas = document.getElementById('salesChart');
  if (!canvas) return;
  var labels = {!! $chartLabels !!};
  var values = {!! $chartValues !!};
  var isDark = !document.body.classList.contains('light');
  var acColor = getComputedStyle(document.documentElement).getPropertyValue('--ac').trim() || '#f59e0b';
  var gridColor = isDark ? 'rgba(255,255,255,.06)' : 'rgba(0,0,0,.05)';
  var tickColor = isDark ? '#64748b' : '#94a3b8';

  window.rebuildChart = function() {
    if (window._salesChart) window._salesChart.destroy();
    window._salesChart = new Chart(canvas, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
          label: 'Omzet',
          data: values,
          backgroundColor: acColor + 'bb',
          borderColor: acColor,
          borderWidth: 1,
          borderRadius: 6,
        }]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: function(ctx) { return 'Rp ' + Math.round(ctx.raw).toLocaleString('id-ID'); }
            }
          }
        },
        scales: {
          x: { grid: { color: gridColor }, ticks: { color: tickColor, font: { size: 11 } } },
          y: { grid: { color: gridColor }, ticks: {
            color: tickColor, font: { size: 11 },
            callback: function(v) { return v >= 1000000 ? 'Rp '+(v/1000000).toFixed(1)+'Jt' : 'Rp '+(v/1000)+'K'; }
          }}
        }
      }
    });
  };
  window.rebuildChart();
})();
</script>
<script>
function setRange(from, to) {
  document.querySelector('[name="date_from"]').value = from;
  document.querySelector('[name="date_to"]').value   = to;
  document.getElementById('filter-form').submit();
}
</script>
@endpush

</x-app-layout>
