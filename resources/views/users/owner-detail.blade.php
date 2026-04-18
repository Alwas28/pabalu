<x-app-layout title="Detail Owner — {{ $user->name }}">

  {{-- Breadcrumb --}}
  <div style="display:flex;align-items:center;gap:8px;font-size:12.5px;color:var(--muted)">
    <a href="{{ route('users.index') }}" style="color:var(--muted);text-decoration:none"
      onmouseover="this.style.color='var(--ac)'" onmouseout="this.style.color='var(--muted)'">
      <i class="fa-solid fa-users"></i> Kelola User
    </a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px"></i>
    <span style="color:var(--text)">Detail Owner: {{ $user->name }}</span>
  </div>

  {{-- Filter outlet --}}
  <form method="GET" action="{{ route('users.owner-detail', $user) }}"
    style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
    <select name="outlet_id" class="f-input" style="width:auto;padding:8px 14px;font-size:13px"
      onchange="this.form.submit()">
      <option value="">— Semua Outlet —</option>
      @foreach($outlets as $o)
      <option value="{{ $o->id }}" @selected($filterOutletId == $o->id)>{{ $o->nama }}</option>
      @endforeach
    </select>
    @if($filterOutletId)
    <a href="{{ route('users.owner-detail', $user) }}" class="btn" style="padding:8px 12px;font-size:12.5px;text-decoration:none">
      <i class="fa-solid fa-xmark"></i> Reset
    </a>
    @endif
  </form>

  <div style="display:grid;grid-template-columns:300px 1fr;gap:20px;align-items:start">

    {{-- ── Kolom Kiri: Profil + Outlet ── --}}
    <div style="display:flex;flex-direction:column;gap:16px">

      {{-- Profil Owner --}}
      <div class="card animate-fadeUp">
        <div class="card-body" style="padding:24px;text-align:center">
          <div style="width:64px;height:64px;border-radius:18px;margin:0 auto 14px;
                      background:linear-gradient(135deg,#818cf8,#6366f1);
                      display:grid;place-items:center;font-size:26px;color:#fff;font-weight:700">
            {{ strtoupper(mb_substr($user->name,0,1)) }}
          </div>
          <div style="font-family:'Clash Display',sans-serif;font-size:16px;font-weight:700;color:var(--text)">
            {{ $user->name }}
          </div>
          <div style="font-size:12px;color:var(--muted);margin-top:3px">{{ $user->email }}</div>
          <div style="margin-top:10px">
            <span class="badge" style="background:rgba(129,140,248,.15);color:#818cf8;font-size:11.5px">
              <i class="fa-solid fa-user-tie"></i> Owner
            </span>
            @if($user->hasVerifiedEmail())
            <span class="badge badge-green" style="font-size:11px;margin-left:4px">
              <i class="fa-solid fa-circle-check"></i> Terverifikasi
            </span>
            @else
            <span class="badge badge-yellow" style="font-size:11px;margin-left:4px">
              <i class="fa-solid fa-clock"></i> Belum Verifikasi
            </span>
            @endif
          </div>
          @if($user->profile?->no_hp)
          <div style="font-size:12.5px;color:var(--muted);margin-top:10px">
            <i class="fa-solid fa-phone" style="margin-right:4px;color:var(--ac)"></i>{{ $user->profile->no_hp }}
          </div>
          @endif
          <div style="font-size:12px;color:var(--muted);margin-top:6px">
            Bergabung {{ $user->created_at->translatedFormat('d F Y') }}
          </div>
        </div>
      </div>

      {{-- Outlet List --}}
      <div class="card animate-fadeUp d1">
        <div class="card-header">
          <div class="card-title">
            <i class="fa-solid fa-store" style="color:var(--ac);margin-right:8px"></i>Outlet ({{ $outlets->count() }})
          </div>
        </div>
        <div class="card-body" style="padding:0">
          @forelse($outletStats as $o)
          @php $isActive = $filterOutletId == $o['id']; @endphp
          <a href="{{ route('users.owner-detail', [$user, 'outlet_id' => $o['id']]) }}"
            style="display:flex;align-items:center;gap:12px;padding:12px 16px;
                   text-decoration:none;border-bottom:1px solid var(--border);
                   background:{{ $isActive ? 'var(--ac-lt)' : 'transparent' }};
                   transition:background .15s"
            onmouseover="this.style.background='var(--ac-lt)'"
            onmouseout="this.style.background='{{ $isActive ? 'var(--ac-lt)' : 'transparent' }}'">
            <div style="width:34px;height:34px;border-radius:9px;flex-shrink:0;
                        background:{{ $o['is_active'] ? 'var(--ac-lt)' : 'rgba(100,116,139,.15)' }};
                        color:{{ $o['is_active'] ? 'var(--ac)' : 'var(--muted)' }};
                        display:grid;place-items:center;font-size:14px">
              <i class="fa-solid fa-store"></i>
            </div>
            <div style="flex:1;min-width:0">
              <div style="font-size:13px;font-weight:600;color:var(--text);
                          white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                {{ $o['nama'] }}
              </div>
              <div style="font-size:11.5px;color:var(--muted)">
                {{ $o['trx'] }} trx · Rp {{ number_format($o['omzet'],0,',','.') }}
              </div>
            </div>
            @if($isActive)
            <i class="fa-solid fa-circle-dot" style="color:var(--ac);font-size:10px;flex-shrink:0"></i>
            @endif
          </a>
          @empty
          <div style="padding:20px;text-align:center;color:var(--muted);font-size:13px">
            Belum ada outlet
          </div>
          @endforelse
        </div>
      </div>

    </div>

    {{-- ── Kolom Kanan: Stats + Grafik ── --}}
    <div style="display:flex;flex-direction:column;gap:20px">

      {{-- Stat Cards --}}
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px">
        <div class="card animate-fadeUp">
          <div class="card-body" style="padding:18px">
            <div style="font-size:11.5px;font-weight:600;color:var(--muted);letter-spacing:.4px;margin-bottom:8px">TOTAL OMZET</div>
            <div style="font-family:'Clash Display',sans-serif;font-size:20px;font-weight:700;color:var(--ac)">
              Rp {{ number_format($totalOmzet,0,',','.') }}
            </div>
          </div>
        </div>
        <div class="card animate-fadeUp d1">
          <div class="card-body" style="padding:18px">
            <div style="font-size:11.5px;font-weight:600;color:var(--muted);letter-spacing:.4px;margin-bottom:8px">TOTAL TRANSAKSI</div>
            <div style="font-family:'Clash Display',sans-serif;font-size:20px;font-weight:700;color:#818cf8">
              {{ number_format($totalTrx,0,',','.') }}
            </div>
          </div>
        </div>
        <div class="card animate-fadeUp d2">
          <div class="card-body" style="padding:18px">
            <div style="font-size:11.5px;font-weight:600;color:var(--muted);letter-spacing:.4px;margin-bottom:8px">RATA-RATA / HARI</div>
            <div style="font-family:'Clash Display',sans-serif;font-size:20px;font-weight:700;color:#34d399">
              Rp {{ number_format($rataPerHari,0,',','.') }}
            </div>
            <div style="font-size:11px;color:var(--muted);margin-top:3px">dari {{ $hariAktif }} hari aktif</div>
          </div>
        </div>
      </div>

      {{-- Grafik 7 Hari --}}
      <div class="card animate-fadeUp d1">
        <div class="card-header">
          <div class="card-title">
            <i class="fa-solid fa-chart-bar" style="color:var(--ac);margin-right:8px"></i>Penjualan 7 Hari Terakhir
          </div>
          <div id="tooltip-7" style="font-size:12px;color:var(--muted)"></div>
        </div>
        <div class="card-body" style="padding:16px 20px 20px">
          <canvas id="chart7" height="100"></canvas>
        </div>
      </div>

      {{-- Grafik 12 Bulan --}}
      <div class="card animate-fadeUp d2">
        <div class="card-header">
          <div class="card-title">
            <i class="fa-solid fa-chart-line" style="color:var(--ac);margin-right:8px"></i>Penjualan 12 Bulan Terakhir
          </div>
          <div id="tooltip-12" style="font-size:12px;color:var(--muted)"></div>
        </div>
        <div class="card-body" style="padding:16px 20px 20px">
          <canvas id="chart12" height="90"></canvas>
        </div>
      </div>

    </div>
  </div>

  @push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
  <script>
  var acColor   = '#6366f1';
  var acFade    = 'rgba(99,102,241,.15)';
  var purColor  = '#818cf8';
  var purFade   = 'rgba(129,140,248,.12)';
  var gridColor = 'rgba(255,255,255,.05)';
  var textColor = '#64748b';

  function fmtRp(n) {
    if (n >= 1e9)  return 'Rp ' + (n/1e9).toFixed(1)  + ' M';
    if (n >= 1e6)  return 'Rp ' + (n/1e6).toFixed(1)  + ' jt';
    if (n >= 1e3)  return 'Rp ' + (n/1e3).toFixed(0)  + ' rb';
    return 'Rp ' + n.toLocaleString('id-ID');
  }
  function fmtFull(n) {
    return 'Rp ' + Math.round(n).toLocaleString('id-ID');
  }

  var baseOpts = {
    responsive: true,
    interaction: { mode: 'index', intersect: false },
    plugins: {
      legend: { display: false },
      tooltip: {
        backgroundColor: '#1a1d27',
        borderColor: '#2e3349',
        borderWidth: 1,
        titleColor: '#e8eaf0',
        bodyColor: '#94a3b8',
        padding: 10,
        callbacks: {
          label: function(ctx) {
            if (ctx.dataset.label === 'Omzet') return ' Omzet: ' + fmtFull(ctx.raw);
            return ' Transaksi: ' + ctx.raw + ' trx';
          }
        }
      }
    },
    scales: {
      x: {
        ticks: { color: textColor, font: { size: 11 } },
        grid:  { color: gridColor },
      },
      y: {
        ticks: { color: textColor, font: { size: 11 }, callback: fmtRp },
        grid:  { color: gridColor },
      },
      y2: {
        position: 'right',
        ticks: { color: purColor, font: { size: 11 } },
        grid:  { drawOnChartArea: false },
      },
    },
  };

  // ── Chart 7 Hari ──────────────────────────────────
  var d7   = @json($chart7);
  var ctx7 = document.getElementById('chart7').getContext('2d');
  new Chart(ctx7, {
    type: 'bar',
    data: {
      labels: d7.map(function(r){ return r.label; }),
      datasets: [
        {
          label: 'Omzet',
          data: d7.map(function(r){ return r.omzet; }),
          backgroundColor: acFade,
          borderColor: acColor,
          borderWidth: 2,
          borderRadius: 6,
          yAxisID: 'y',
        },
        {
          label: 'Transaksi',
          data: d7.map(function(r){ return r.trx; }),
          type: 'line',
          borderColor: purColor,
          backgroundColor: purFade,
          pointBackgroundColor: purColor,
          pointRadius: 4,
          tension: 0.35,
          fill: true,
          yAxisID: 'y2',
        },
      ],
    },
    options: baseOpts,
  });

  // ── Chart 12 Bulan ────────────────────────────────
  var d12   = @json($chart12);
  var ctx12 = document.getElementById('chart12').getContext('2d');
  new Chart(ctx12, {
    type: 'bar',
    data: {
      labels: d12.map(function(r){ return r.label; }),
      datasets: [
        {
          label: 'Omzet',
          data: d12.map(function(r){ return r.omzet; }),
          backgroundColor: acFade,
          borderColor: acColor,
          borderWidth: 2,
          borderRadius: 6,
          yAxisID: 'y',
        },
        {
          label: 'Transaksi',
          data: d12.map(function(r){ return r.trx; }),
          type: 'line',
          borderColor: purColor,
          backgroundColor: purFade,
          pointBackgroundColor: purColor,
          pointRadius: 4,
          tension: 0.35,
          fill: true,
          yAxisID: 'y2',
        },
      ],
    },
    options: baseOpts,
  });
  </script>
  @endpush

</x-app-layout>
