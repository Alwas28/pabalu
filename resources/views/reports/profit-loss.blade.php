<x-app-layout title="Laporan Laba & Rugi">

  {{-- Filter --}}
  <form method="GET" action="{{ route('reports.profit-loss') }}" id="filter-form">
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
            <option value="">— Pilih Outlet —</option>
            @foreach($outlets as $o)
            <option value="{{ $o->id }}" @selected($outletId == $o->id)>{{ $o->nama }}</option>
            @endforeach
          </select>
        @endif
      </div>
      <div class="f-group" style="margin-bottom:0">
        <label class="f-label">Dari</label>
        <input type="date" name="date_from" class="f-input" value="{{ $dateFrom }}">
      </div>
      <div class="f-group" style="margin-bottom:0">
        <label class="f-label">Sampai</label>
        <input type="date" name="date_to" class="f-input" value="{{ $dateTo }}">
      </div>
      <button type="submit" class="btn btn-primary" style="padding:9px 16px">
        <i class="fa-solid fa-magnifying-glass"></i> Hitung
      </button>
      <button type="button" onclick="window.print()" class="btn no-print" style="padding:9px 14px">
        <i class="fa-solid fa-print"></i>
      </button>
    </div>
    <div style="display:flex;gap:6px;margin-top:10px;flex-wrap:wrap">
      @php
        $shortcuts = [
          ['label'=>'Hari Ini',    'from'=>today(),                          'to'=>today()],
          ['label'=>'7 Hari',      'from'=>today()->subDays(6),               'to'=>today()],
          ['label'=>'Bulan Ini',   'from'=>today()->startOfMonth(),           'to'=>today()],
          ['label'=>'Bulan Lalu',  'from'=>today()->subMonth()->startOfMonth(),'to'=>today()->subMonth()->endOfMonth()],
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

  @if(!$outletId)
  <div class="card"><div class="card-body" style="text-align:center;padding:56px;color:var(--muted)">
    <i class="fa-solid fa-scale-balanced" style="font-size:40px;display:block;margin-bottom:14px;opacity:.3"></i>
    Pilih outlet untuk melihat laporan laba & rugi.
  </div></div>

  @elseif($perHari->isEmpty())
  <div class="card"><div class="card-body" style="text-align:center;padding:56px;color:var(--muted)">
    <i class="fa-solid fa-inbox" style="font-size:40px;display:block;margin-bottom:14px;opacity:.3"></i>
    Tidak ada data pada periode yang dipilih.
  </div></div>

  @else
  {{-- Summary stats --}}
  <div class="stat-grid animate-fadeUp">
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(52,211,153,.15);color:#34d399"><i class="fa-solid fa-sack-dollar"></i></div>
      <div>
        <div class="stat-num" style="font-size:18px">Rp {{ number_format($totalOmzet,0,',','.') }}</div>
        <div class="stat-label">Total Omzet</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(248,113,113,.15);color:#f87171"><i class="fa-solid fa-wallet"></i></div>
      <div>
        <div class="stat-num" style="font-size:18px">Rp {{ number_format($totalExpense,0,',','.') }}</div>
        <div class="stat-label">Total Pengeluaran</div>
      </div>
    </div>
    <div class="stat-card" style="border-color:{{ $totalLaba>=0?'rgba(52,211,153,.3)':'rgba(248,113,113,.3)' }}">
      <div class="stat-icon" style="background:rgba({{ $totalLaba>=0?'52,211,153':'248,113,113' }},.15);color:{{ $totalLaba>=0?'#34d399':'#f87171' }}">
        <i class="fa-solid fa-scale-balanced"></i>
      </div>
      <div>
        <div class="stat-num" style="font-size:18px;color:{{ $totalLaba>=0?'#34d399':'#f87171' }}">
          {{ $totalLaba<0?'−':'' }}Rp {{ number_format(abs($totalLaba),0,',','.') }}
        </div>
        <div class="stat-label">{{ $totalLaba>=0?'Laba Kotor':'Rugi Kotor' }}</div>
      </div>
    </div>
    @if($totalOmzet > 0)
    <div class="stat-card">
      <div class="stat-icon a-bg-lt" style="color:var(--ac)"><i class="fa-solid fa-percent"></i></div>
      <div>
        <div class="stat-num" style="font-size:22px;color:{{ $totalLaba>=0?'#34d399':'#f87171' }}">
          {{ round(($totalLaba/$totalOmzet)*100,1) }}%
        </div>
        <div class="stat-label">Margin Laba</div>
      </div>
    </div>
    @endif
  </div>

  <div class="two-col animate-fadeUp d1">

    {{-- Grafik Laba/Rugi --}}
    <div class="card">
      <div class="card-header">
        <div class="card-title"><i class="fa-solid fa-chart-line a-text" style="margin-right:8px"></i>Tren Laba / Rugi</div>
      </div>
      <div class="card-body" style="height:280px;padding:16px">
        <canvas id="plChart"></canvas>
      </div>
    </div>

    {{-- Breakdown pengeluaran --}}
    <div class="card">
      <div class="card-header">
        <div class="card-title"><i class="fa-solid fa-pie-chart a-text" style="margin-right:8px"></i>Komposisi Pengeluaran</div>
      </div>
      @if($expenseByKat->isEmpty())
      <div class="card-body" style="text-align:center;padding:40px;color:var(--muted);font-size:13px">
        Tidak ada pengeluaran pada periode ini.
      </div>
      @else
      <div style="padding:16px">
        @foreach($expenseByKat as $ek)
        @php
          $pct = $totalExpense > 0 ? ($ek->total / $totalExpense) * 100 : 0;
          $label = \App\Models\Expense::KATEGORI[$ek->kategori] ?? $ek->kategori;
        @endphp
        <div style="margin-bottom:14px">
          <div style="display:flex;justify-content:space-between;margin-bottom:5px;font-size:13px">
            <span style="color:var(--sub)">{{ $label }}</span>
            <span style="font-weight:600;color:var(--text)">Rp {{ number_format($ek->total,0,',','.') }}</span>
          </div>
          <div class="prog-bar">
            <div class="prog-fill" style="width:{{ $pct }}%"></div>
          </div>
          <div style="font-size:11px;color:var(--muted);margin-top:3px;text-align:right">{{ round($pct,1) }}%</div>
        </div>
        @endforeach
      </div>
      @endif
    </div>

  </div>

  {{-- Detail per hari --}}
  <div class="card animate-fadeUp d2">
    <div class="card-header">
      <div class="card-title"><i class="fa-solid fa-table-list a-text" style="margin-right:8px"></i>Detail Per Hari</div>
    </div>
    <div style="overflow-x:auto">
      <table class="tbl">
        <thead>
          <tr>
            <th>Tanggal</th>
            <th style="text-align:right">Omzet</th>
            <th style="text-align:right">Pengeluaran</th>
            <th style="text-align:right">Laba / Rugi</th>
            <th style="text-align:center">Margin</th>
          </tr>
        </thead>
        <tbody>
          @foreach($perHari as $row)
          @php $margin = $row['omzet'] > 0 ? round(($row['laba']/$row['omzet'])*100,1) : null; @endphp
          <tr>
            <td style="white-space:nowrap">{{ \Carbon\Carbon::parse($row['tanggal'])->translatedFormat('d M Y') }}</td>
            <td style="text-align:right;color:#34d399;font-weight:600">
              {{ $row['omzet'] > 0 ? 'Rp '.number_format($row['omzet'],0,',','.') : '—' }}
            </td>
            <td style="text-align:right;color:#f87171;font-weight:600">
              {{ $row['expense'] > 0 ? 'Rp '.number_format($row['expense'],0,',','.') : '—' }}
            </td>
            <td style="text-align:right;font-weight:700;color:{{ $row['laba']>=0?'#34d399':'#f87171' }}">
              {{ $row['laba'] != 0 ? ($row['laba']<0?'−':'').'Rp '.number_format(abs($row['laba']),0,',','.') : '—' }}
            </td>
            <td style="text-align:center;font-size:12px;color:{{ $margin !== null && $margin < 0 ? '#f87171' : 'var(--muted)' }}">
              {{ $margin !== null ? $margin.'%' : '—' }}
            </td>
          </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr>
            <td style="font-weight:700;padding:10px 14px;color:var(--text)">Total</td>
            <td style="text-align:right;font-weight:700;color:#34d399;padding:10px 14px">Rp {{ number_format($totalOmzet,0,',','.') }}</td>
            <td style="text-align:right;font-weight:700;color:#f87171;padding:10px 14px">Rp {{ number_format($totalExpense,0,',','.') }}</td>
            <td style="text-align:right;font-weight:700;color:{{ $totalLaba>=0?'#34d399':'#f87171' }};padding:10px 14px">
              {{ $totalLaba<0?'−':'' }}Rp {{ number_format(abs($totalLaba),0,',','.') }}
            </td>
            <td style="text-align:center;font-weight:700;padding:10px 14px;color:{{ $totalLaba>=0?'#34d399':'#f87171' }}">
              @if($totalOmzet > 0){{ round(($totalLaba/$totalOmzet)*100,1) }}%@endif
            </td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
  @endif

@push('scripts')
@php
  $plLabels  = json_encode($perHari->pluck('tanggal')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m'))->toArray());
  $plOmzet   = json_encode($perHari->pluck('omzet')->toArray());
  $plExpense = json_encode($perHari->pluck('expense')->toArray());
  $plLaba    = json_encode($perHari->pluck('laba')->toArray());
@endphp
<style>
@media print {
  #sb,#header,#ov,#toast-container,.no-print{display:none!important}
  #main{margin-left:0!important}
  #content{padding:0!important}
  body{background:white!important;color:black!important}
  .card{border:1px solid #ddd!important;border-radius:0!important;page-break-inside:avoid}
  .tbl thead th{background:#f5f5f5!important;color:#555!important}
  .tbl tbody td,.tbl tfoot td{color:#333!important}
  .animate-fadeUp{animation:none!important}
}
</style>
<script>
(function(){
  var canvas = document.getElementById('plChart');
  if (!canvas) return;
  var labels  = {!! $plLabels !!};
  var omzet   = {!! $plOmzet !!};
  var expense = {!! $plExpense !!};
  var laba    = {!! $plLaba !!};
  var isDark  = !document.body.classList.contains('light');
  var grid    = isDark ? 'rgba(255,255,255,.06)' : 'rgba(0,0,0,.05)';
  var tick    = isDark ? '#64748b' : '#94a3b8';
  var ac      = getComputedStyle(document.documentElement).getPropertyValue('--ac').trim() || '#f59e0b';

  window.rebuildChart = function() {
    if (window._plChart) window._plChart.destroy();
    window._plChart = new Chart(canvas, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [
          { label:'Omzet',      data:omzet,   backgroundColor:'rgba(52,211,153,.5)', borderColor:'#34d399', borderWidth:1, borderRadius:4 },
          { label:'Pengeluaran',data:expense, backgroundColor:'rgba(248,113,113,.5)',borderColor:'#f87171', borderWidth:1, borderRadius:4 },
          { label:'Laba/Rugi',  data:laba,    type:'line', borderColor:ac, backgroundColor:ac+'22',
            pointBackgroundColor:ac, pointRadius:4, tension:0.3, fill:false, borderWidth:2 },
        ]
      },
      options: {
        responsive:true, maintainAspectRatio:false,
        plugins: {
          legend: { labels:{ color:tick, font:{size:11,family:'Plus Jakarta Sans'} } },
          tooltip: {
            callbacks: {
              label: function(ctx) { return ctx.dataset.label+': Rp '+Math.round(ctx.raw).toLocaleString('id-ID'); }
            }
          }
        },
        scales: {
          x: { grid:{color:grid}, ticks:{color:tick,font:{size:11}} },
          y: { grid:{color:grid}, ticks:{
            color:tick, font:{size:11},
            callback:function(v){ return v>=1000000?'Rp '+(v/1000000).toFixed(1)+'Jt':'Rp '+(v/1000)+'K'; }
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
