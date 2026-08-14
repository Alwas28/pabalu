<x-outlet-layout :outlet="$outlet" pageTitle="Laporan Denda">

@php
  $chartLabels = $chartData->pluck('label')->values()->toArray();
  $chartAdded  = $chartData->pluck('added')->values()->toArray();
  $chartPaid   = $chartData->pluck('paid')->values()->toArray();
@endphp

@push('scripts')
<script>
const fLabels = @json($chartLabels);
const fAdded  = @json($chartAdded);
const fPaid   = @json($chartPaid);
let fChart = null;

function rebuildFineChart() {
  const el = document.getElementById('fine-chart');
  if (!el) return;
  const dark      = !document.body.classList.contains('light');
  const textColor = dark ? '#94a3b8' : '#64748b';
  const gridColor = dark ? 'rgba(255,255,255,.06)' : 'rgba(0,0,0,.07)';

  if (fChart) fChart.destroy();

  fChart = new Chart(el, {
    type: 'line',
    data: {
      labels: fLabels,
      datasets: [
        { label: 'Denda Ditambahkan', data: fAdded, borderColor: '#f87171', backgroundColor: 'rgba(248,113,113,.1)', borderWidth: 2.5, pointRadius: fLabels.length > 30 ? 0 : 3, pointBackgroundColor: '#f87171', tension: 0.35, fill: true },
        { label: 'Denda Dibayar', data: fPaid, borderColor: '#34d399', backgroundColor: 'rgba(52,211,153,.1)', borderWidth: 2.5, pointRadius: fLabels.length > 30 ? 0 : 3, pointBackgroundColor: '#34d399', tension: 0.35, fill: true },
      ]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: { display: true, labels: { color: textColor, boxWidth: 12, font: { size: 12 } } },
        tooltip: {
          backgroundColor: dark ? '#1c2336' : '#fff', titleColor: dark ? '#e2e8f0' : '#1e293b', bodyColor: textColor,
          borderColor: dark ? '#252d42' : '#e2e8f0', borderWidth: 1, padding: 12, cornerRadius: 10,
          callbacks: { label: ctx => '  Rp ' + ctx.raw.toLocaleString('id-ID') }
        }
      },
      scales: {
        x: { grid: { color: gridColor }, ticks: { color: textColor, font: { size: 11 } }, border: { color: gridColor } },
        y: { grid: { color: gridColor }, border: { color: gridColor }, ticks: { color: textColor, font: { size: 11 },
          callback: v => v >= 1_000_000 ? (v/1_000_000).toFixed(1)+'jt' : v >= 1_000 ? (v/1_000).toFixed(0)+'rb' : v } }
      }
    }
  });
}
document.addEventListener('DOMContentLoaded', rebuildFineChart);
</script>
@endpush

{{-- ── HEADER ── --}}
<div class="animate-fadeUp">
  <h2 class="font-display" style="font-size:20px;font-weight:700;color:var(--text)">Laporan Denda</h2>
  <p style="font-size:13px;color:var(--muted);margin-top:2px">
    Ringkasan denda keterlambatan &amp; kerusakan · {{ \Carbon\Carbon::parse($from)->translatedFormat('d M Y') }} – {{ \Carbon\Carbon::parse($to)->translatedFormat('d M Y') }}
  </p>
</div>

{{-- ── FILTER ── --}}
<div class="card animate-fadeUp d1" style="padding:16px 20px">
  <form method="GET" action="{{ $outlet->route('reports.fines') }}" style="display:flex;align-items:flex-end;gap:12px;flex-wrap:wrap">
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
      <a href="{{ $outlet->route('reports.fines', ['from' => $f, 'to' => $t]) }}"
         style="padding:7px 12px;border-radius:9px;font-size:12px;font-weight:600;text-decoration:none;border:1px solid {{ ($from===$f && $to===$t) ? 'var(--ac)' : 'var(--border)' }};color:{{ ($from===$f && $to===$t) ? 'var(--ac)' : 'var(--muted)' }};background:{{ ($from===$f && $to===$t) ? 'var(--ac-lt)' : 'transparent' }};white-space:nowrap">
        {{ $label }}
      </a>
      @endforeach
    </div>
  </form>
</div>

{{-- ── STATS ── --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px" class="animate-fadeUp d2">
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(251,191,36,.15);color:#fbbf24"><i class="fa-solid fa-triangle-exclamation"></i></div>
    <div><div class="stat-num" style="font-size:17px">Rp {{ number_format($totalFines) }}</div><div class="stat-label">Total Denda Ditambahkan ({{ $totalCount }})</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(52,211,153,.15);color:#34d399"><i class="fa-solid fa-circle-check"></i></div>
    <div><div class="stat-num" style="font-size:17px">Rp {{ number_format($totalPaid) }}</div><div class="stat-label">Sudah Dibayar</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(248,113,113,.15);color:#f87171"><i class="fa-solid fa-hourglass-half"></i></div>
    <div><div class="stat-num" style="font-size:17px">Rp {{ number_format($totalRemaining) }}</div><div class="stat-label">Belum Dibayar</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(96,165,250,.15);color:#60a5fa"><i class="fa-solid fa-money-bill-wave"></i></div>
    <div><div class="stat-num" style="font-size:17px">Rp {{ number_format($totalFinePaidInPeriod) }}</div><div class="stat-label">Denda Dibayar (periode ini)</div></div>
  </div>
</div>

{{-- ── CHART ── --}}
<div class="card animate-fadeUp d3">
  <div class="card-header">
    <span class="card-title"><i class="fa-solid fa-chart-line" style="color:var(--ac);margin-right:8px;font-size:13px"></i>Tren Denda</span>
    <span style="font-size:12px;color:var(--muted)">per {{ $groupBy === 'day' ? 'hari' : 'bulan' }}</span>
  </div>
  @if($chartData->isEmpty())
  <div style="padding:40px;text-align:center;color:var(--muted);font-size:13px">
    <i class="fa-solid fa-chart-line" style="font-size:28px;display:block;margin-bottom:10px;opacity:.35"></i>
    Tidak ada data pada periode ini
  </div>
  @else
  <div style="padding:20px;position:relative;height:260px"><canvas id="fine-chart"></canvas></div>
  @endif
</div>

{{-- ── BAWAH: TOP PELANGGAN + DAFTAR ── --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px" class="animate-fadeUp d3">

  <div class="card">
    <div class="card-header">
      <span class="card-title"><i class="fa-solid fa-ranking-star" style="color:var(--ac);margin-right:8px;font-size:13px"></i>Pelanggan dengan Denda Terbanyak</span>
    </div>
    <div style="overflow-x:auto">
      <table class="tbl">
        <thead><tr><th style="width:28px">#</th><th>Pelanggan</th><th style="text-align:right;width:150px">Total Denda</th></tr></thead>
        <tbody>
          @forelse($topCustomers as $name => $total)
          <tr>
            <td style="font-size:11px;font-weight:700;color:{{ $loop->iteration <= 3 ? 'var(--ac)' : 'var(--muted)' }}">{{ $loop->iteration }}</td>
            <td class="td-main">{{ $name }}</td>
            <td style="text-align:right;font-weight:700;color:var(--text)">Rp {{ number_format($total) }}</td>
          </tr>
          @empty
          <tr><td colspan="3" style="text-align:center;padding:32px;color:var(--muted)">Tidak ada data</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <span class="card-title"><i class="fa-solid fa-list-ul" style="color:var(--ac);margin-right:8px;font-size:13px"></i>Daftar Denda</span>
      <span style="font-size:12px;color:var(--muted)">{{ $rentals->count() }} sewa</span>
    </div>
    <div style="overflow-x:auto;max-height:360px;overflow-y:auto">
      <table class="tbl">
        <thead><tr><th>No. Transaksi</th><th style="text-align:right;width:110px">Denda</th><th style="text-align:center;width:90px">Status</th></tr></thead>
        <tbody>
          @forelse($rentals as $r)
          @php $remaining = $r->fineRemaining(); @endphp
          <tr>
            <td style="font-size:12px;font-family:monospace;color:var(--sub)">{{ $r->order_number }}</td>
            <td style="text-align:right;font-weight:700;color:var(--text)">Rp {{ number_format($r->fine_amount) }}</td>
            <td style="text-align:center">
              @if($remaining <= 0)
              <span class="badge badge-green">Lunas</span>
              @else
              <span class="badge badge-red">Belum Lunas</span>
              @endif
            </td>
          </tr>
          @empty
          <tr><td colspan="3" style="text-align:center;padding:32px;color:var(--muted)">Tidak ada data</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>

</x-outlet-layout>
