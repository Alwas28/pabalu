<x-outlet-layout :outlet="$outlet" pageTitle="Laporan Deposit">

@php
  $chartLabels   = $chartData->pluck('label')->values()->toArray();
  $chartCollected = $chartData->pluck('collected')->values()->toArray();
  $chartRefunded  = $chartData->pluck('refunded')->values()->toArray();
@endphp

@push('scripts')
<script>
const dLabels    = @json($chartLabels);
const dCollected = @json($chartCollected);
const dRefunded  = @json($chartRefunded);
let dChart = null;

function rebuildDepositChart() {
  const el = document.getElementById('deposit-chart');
  if (!el) return;
  const dark      = !document.body.classList.contains('light');
  const textColor = dark ? '#94a3b8' : '#64748b';
  const gridColor = dark ? 'rgba(255,255,255,.06)' : 'rgba(0,0,0,.07)';

  if (dChart) dChart.destroy();

  dChart = new Chart(el, {
    type: 'bar',
    data: {
      labels: dLabels,
      datasets: [
        { label: 'Deposit Dikumpulkan', data: dCollected, backgroundColor: 'rgba(96,165,250,.35)', borderColor: '#60a5fa', borderWidth: 1.5, borderRadius: 5 },
        { label: 'Deposit Direfund', data: dRefunded, backgroundColor: 'rgba(52,211,153,.35)', borderColor: '#34d399', borderWidth: 1.5, borderRadius: 5 },
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
document.addEventListener('DOMContentLoaded', rebuildDepositChart);
</script>
@endpush

{{-- ── HEADER ── --}}
<div class="animate-fadeUp">
  <h2 class="font-display" style="font-size:20px;font-weight:700;color:var(--text)">Laporan Deposit</h2>
  <p style="font-size:13px;color:var(--muted);margin-top:2px">
    Ringkasan deposit yang sedang ditahan &amp; sudah dikembalikan · {{ \Carbon\Carbon::parse($from)->translatedFormat('d M Y') }} – {{ \Carbon\Carbon::parse($to)->translatedFormat('d M Y') }}
  </p>
</div>

{{-- ── FILTER ── --}}
<div class="card animate-fadeUp d1" style="padding:16px 20px">
  <form method="GET" action="{{ $outlet->route('reports.deposits') }}" style="display:flex;align-items:flex-end;gap:12px;flex-wrap:wrap">
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
      <a href="{{ $outlet->route('reports.deposits', ['from' => $f, 'to' => $t]) }}"
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
    <div class="stat-icon" style="background:rgba(96,165,250,.15);color:#60a5fa"><i class="fa-solid fa-vault"></i></div>
    <div><div class="stat-num" style="font-size:17px">Rp {{ number_format($totalCollected) }}</div><div class="stat-label">Deposit Dikumpulkan (periode ini)</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(129,140,248,.15);color:#818cf8"><i class="fa-solid fa-lock"></i></div>
    <div><div class="stat-num" style="font-size:17px">Rp {{ number_format($totalHeld) }}</div><div class="stat-label">Sedang Ditahan ({{ $heldCount }} sewa aktif)</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(251,191,36,.15);color:#fbbf24"><i class="fa-solid fa-hourglass-half"></i></div>
    <div><div class="stat-num" style="font-size:17px">Rp {{ number_format($totalPending) }}</div><div class="stat-label">Menunggu Refund ({{ $pendingCount }})</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(52,211,153,.15);color:#34d399"><i class="fa-solid fa-circle-check"></i></div>
    <div><div class="stat-num" style="font-size:17px">Rp {{ number_format($totalRefundedInPeriod) }}</div><div class="stat-label">Direfund (periode ini, {{ $refundedCount }})</div></div>
  </div>
</div>

{{-- ── CHART ── --}}
<div class="card animate-fadeUp d3">
  <div class="card-header">
    <span class="card-title"><i class="fa-solid fa-chart-column" style="color:var(--ac);margin-right:8px;font-size:13px"></i>Tren Deposit Dikumpulkan vs Direfund</span>
    <span style="font-size:12px;color:var(--muted)">per {{ $groupBy === 'day' ? 'hari' : 'bulan' }}</span>
  </div>
  @if($chartData->isEmpty())
  <div style="padding:40px;text-align:center;color:var(--muted);font-size:13px">
    <i class="fa-solid fa-chart-column" style="font-size:28px;display:block;margin-bottom:10px;opacity:.35"></i>
    Tidak ada data pada periode ini
  </div>
  @else
  <div style="padding:20px;position:relative;height:260px"><canvas id="deposit-chart"></canvas></div>
  @endif
</div>

<div class="card animate-fadeUp d3" style="padding:16px 20px;display:flex;align-items:center;gap:12px">
  <i class="fa-solid fa-circle-info" style="color:var(--muted);font-size:14px"></i>
  <p style="font-size:12.5px;color:var(--muted);line-height:1.6">
    Untuk daftar per transaksi beserta tombol aksi (proses refund), lihat menu
    <a href="{{ $outlet->route('deposits.index') }}" style="color:var(--ac);font-weight:600">Transaksi &gt; Deposit</a>.
  </p>
</div>

</x-outlet-layout>
