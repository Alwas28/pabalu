<x-outlet-layout :outlet="$outlet" pageTitle="Laporan Rental">

@php
  $chartLabels = $chartData->pluck('label')->values()->toArray();
  $chartCounts = $chartData->pluck('count')->values()->toArray();
  $chartTotals = $chartData->pluck('total')->values()->toArray();
@endphp

@push('scripts')
<script>
const rLabels = @json($chartLabels);
const rCounts = @json($chartCounts);
const rTotals = @json($chartTotals);
let rChart = null;

function rebuildRentalChart() {
  const el = document.getElementById('rental-chart');
  if (!el) return;
  const dark      = !document.body.classList.contains('light');
  const textColor = dark ? '#94a3b8' : '#64748b';
  const gridColor = dark ? 'rgba(255,255,255,.06)' : 'rgba(0,0,0,.07)';
  const ac        = getComputedStyle(document.documentElement).getPropertyValue('--ac').trim() || '#e8192c';

  if (rChart) rChart.destroy();

  rChart = new Chart(el, {
    data: {
      labels: rLabels,
      datasets: [
        { type: 'bar', label: 'Jumlah Sewa', data: rCounts, backgroundColor: ac + '33', borderColor: ac, borderWidth: 1.5, borderRadius: 5, yAxisID: 'yCount' },
        { type: 'line', label: 'Nilai Sewa', data: rTotals, borderColor: '#34d399', backgroundColor: 'transparent', borderWidth: 2, pointRadius: 3, pointBackgroundColor: '#34d399', tension: 0.35, yAxisID: 'yValue' },
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
          callbacks: { label: ctx => ctx.dataset.label === 'Nilai Sewa' ? '  Rp ' + ctx.raw.toLocaleString('id-ID') : '  ' + ctx.raw + ' sewa' }
        }
      },
      scales: {
        x: { grid: { color: gridColor }, ticks: { color: textColor, font: { size: 11 } }, border: { color: gridColor } },
        yCount: { position: 'left', grid: { color: gridColor }, border: { color: gridColor }, ticks: { color: textColor, font: { size: 11 } } },
        yValue: { position: 'right', grid: { display: false }, border: { display: false }, ticks: { color: '#34d399', font: { size: 11 },
          callback: v => v >= 1_000_000 ? (v/1_000_000).toFixed(1)+'jt' : v >= 1_000 ? (v/1_000).toFixed(0)+'rb' : v } }
      }
    }
  });
}
document.addEventListener('DOMContentLoaded', rebuildRentalChart);
</script>
@endpush

{{-- ── HEADER ── --}}
<div class="animate-fadeUp">
  <h2 class="font-display" style="font-size:20px;font-weight:700;color:var(--text)">Laporan Rental</h2>
  <p style="font-size:13px;color:var(--muted);margin-top:2px">
    Statistik jumlah dan performa transaksi sewa · {{ \Carbon\Carbon::parse($from)->translatedFormat('d M Y') }} – {{ \Carbon\Carbon::parse($to)->translatedFormat('d M Y') }}
  </p>
</div>

{{-- ── FILTER ── --}}
<div class="card animate-fadeUp d1" style="padding:16px 20px">
  <form method="GET" action="{{ $outlet->route('reports.rentals') }}" style="display:flex;align-items:flex-end;gap:12px;flex-wrap:wrap">
    <div>
      <label class="f-label">Dari Tanggal</label>
      <input type="date" name="from" value="{{ $from }}" class="f-input" style="width:155px">
    </div>
    <div>
      <label class="f-label">Sampai</label>
      <input type="date" name="to" value="{{ $to }}" class="f-input" style="width:155px">
    </div>
    <div>
      <label class="f-label">Jenis Sewa</label>
      <select name="rental_type" class="f-input" style="width:150px">
        <option value="">Semua</option>
        @foreach(\App\Models\RentalTransaction::TYPES as $typeCode => $typeLabel)
        <option value="{{ $typeCode }}" @selected($rentalType === $typeCode)>{{ $typeLabel }}</option>
        @endforeach
      </select>
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
          'Tahun Ini'  => [today()->startOfYear()->format('Y-m-d'), today()->format('Y-m-d')],
        ];
      @endphp
      @foreach($shortcuts as $label => [$f, $t])
      <a href="{{ $outlet->route('reports.rentals', ['from' => $f, 'to' => $t]) }}"
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
    <div class="stat-icon" style="background:var(--ac-lt);color:var(--ac)"><i class="fa-solid fa-key"></i></div>
    <div><div class="stat-num">{{ number_format($totalRentals) }}</div><div class="stat-label">Total Sewa ({{ $activeCount }} aktif, {{ $doneCount }} selesai)</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(52,211,153,.15);color:#34d399"><i class="fa-solid fa-sack-dollar"></i></div>
    <div><div class="stat-num" style="font-size:17px">Rp {{ number_format($avgValue) }}</div><div class="stat-label">Rata-rata Nilai Sewa</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(96,165,250,.15);color:#60a5fa"><i class="fa-solid fa-hourglass-half"></i></div>
    <div><div class="stat-num">{{ number_format($avgDurationHours) }} jam</div><div class="stat-label">Rata-rata Durasi Sewa</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(248,113,113,.15);color:#f87171"><i class="fa-solid fa-triangle-exclamation"></i></div>
    <div><div class="stat-num">{{ $latePct }}%</div><div class="stat-label">Tingkat Keterlambatan ({{ $lateCount }} sewa)</div></div>
  </div>
</div>

{{-- ── CHART ── --}}
<div class="card animate-fadeUp d3">
  <div class="card-header">
    <span class="card-title"><i class="fa-solid fa-chart-column" style="color:var(--ac);margin-right:8px;font-size:13px"></i>Tren Jumlah &amp; Nilai Sewa</span>
    <span style="font-size:12px;color:var(--muted)">per {{ $groupBy === 'day' ? 'hari' : 'bulan' }}</span>
  </div>
  @if($chartData->isEmpty())
  <div style="padding:40px;text-align:center;color:var(--muted);font-size:13px">
    <i class="fa-solid fa-chart-column" style="font-size:28px;display:block;margin-bottom:10px;opacity:.35"></i>
    Tidak ada data pada periode ini
  </div>
  @else
  <div style="padding:20px;position:relative;height:260px"><canvas id="rental-chart"></canvas></div>
  @endif
</div>

{{-- ── TINGKAT KETERLAMBATAN PER JENIS SEWA ── --}}
<div class="card animate-fadeUp d3">
  <div class="card-header">
    <span class="card-title"><i class="fa-solid fa-triangle-exclamation" style="color:var(--ac);margin-right:8px;font-size:13px"></i>Tingkat Keterlambatan per Jenis Sewa</span>
    <span style="font-size:11.5px;color:var(--muted)">Sewa Per Jam wajar lebih sering "mepet" — lihat terpisah agar tidak mencampur statistik</span>
  </div>
  <div style="overflow-x:auto">
    <table class="tbl">
      <thead><tr><th>Jenis Sewa</th><th style="text-align:center;width:100px">Jumlah</th><th style="text-align:center;width:100px">Terlambat</th><th style="text-align:right;width:160px">Tingkat Keterlambatan</th></tr></thead>
      <tbody>
        @foreach($lateByType as $code => $t)
        <tr>
          <td class="td-main">
            {{ $t['label'] }}
            @if($rentalType === $code)<span class="badge badge-blue" style="margin-left:6px">Filter Aktif</span>@endif
          </td>
          <td style="text-align:center;font-weight:700;color:var(--text)">{{ $t['count'] }}</td>
          <td style="text-align:center;font-weight:700;color:{{ $t['late'] > 0 ? '#f87171' : 'var(--muted)' }}">{{ $t['late'] }}</td>
          <td style="text-align:right">
            <div style="display:flex;align-items:center;gap:8px;justify-content:flex-end">
              <div style="height:6px;width:80px;border-radius:99px;background:var(--border);overflow:hidden">
                <div style="height:100%;width:{{ $t['late_pct'] }}%;border-radius:99px;background:{{ $t['late_pct'] >= 50 ? '#f87171' : ($t['late_pct'] >= 20 ? '#fbbf24' : '#34d399') }}"></div>
              </div>
              <span style="font-size:12.5px;font-weight:700;color:var(--text);width:42px">{{ $t['late_pct'] }}%</span>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

{{-- ── BAWAH: STATUS PEMBAYARAN + TOP BARANG ── --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px" class="animate-fadeUp d3">

  <div class="card">
    <div class="card-header">
      <span class="card-title"><i class="fa-solid fa-money-check-dollar" style="color:var(--ac);margin-right:8px;font-size:13px"></i>Status Pembayaran</span>
    </div>
    <div style="padding:16px 20px">
      @foreach($paymentStatus as $label => $count)
      @php
        $pct = $totalRentals > 0 ? round($count / $totalRentals * 100) : 0;
        $color = $label === 'Lunas' ? '#34d399' : ($label === 'DP' ? '#fbbf24' : '#f87171');
      @endphp
      <div style="margin-bottom:14px">
        <div style="display:flex;justify-content:space-between;margin-bottom:5px">
          <span style="font-size:13px;color:var(--sub)">{{ $label }}</span>
          <span style="font-size:13px;font-weight:700;color:var(--text)">{{ $count }} ({{ $pct }}%)</span>
        </div>
        <div style="height:6px;border-radius:99px;background:var(--border);overflow:hidden">
          <div style="height:100%;width:{{ $pct }}%;border-radius:99px;background:{{ $color }}"></div>
        </div>
      </div>
      @endforeach
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <span class="card-title"><i class="fa-solid fa-ranking-star" style="color:var(--ac);margin-right:8px;font-size:13px"></i>Barang Paling Sering Disewa</span>
    </div>
    <div style="overflow-x:auto">
      <table class="tbl">
        <thead><tr><th style="width:28px">#</th><th>Barang</th><th style="text-align:center;width:80px">Sewa</th><th style="text-align:right;width:140px">Nilai</th></tr></thead>
        <tbody>
          @forelse($topItems as $name => $item)
          <tr>
            <td style="font-size:11px;font-weight:700;color:var(--ac)">{{ $loop->iteration }}</td>
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

</div>

</x-outlet-layout>
