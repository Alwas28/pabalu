<x-app-layout title="Closing Harian">

  {{-- Filter --}}
  <form method="GET" action="{{ route('closing.index') }}" id="filter-form">
    <div style="display:grid;grid-template-columns:1fr 1fr auto;gap:12px;align-items:end">
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
          <select name="outlet_id" class="f-input" onchange="document.getElementById('filter-form').submit()">
            <option value="">— Pilih Outlet —</option>
            @foreach($outlets as $o)
            <option value="{{ $o->id }}" @selected($outletId == $o->id)>{{ $o->nama }}</option>
            @endforeach
          </select>
        @endif
      </div>
      <div class="f-group" style="margin-bottom:0">
        <label class="f-label">Tanggal</label>
        <input type="date" name="tanggal" class="f-input" value="{{ $tanggal }}"
          onchange="document.getElementById('filter-form').submit()">
      </div>
      <div style="display:flex;gap:8px">
        <button type="submit" class="btn" style="padding:9px 16px">
          <i class="fa-solid fa-rotate"></i> Muat
        </button>
        <button type="button" onclick="window.print()" class="btn btn-primary no-print" style="padding:9px 16px">
          <i class="fa-solid fa-print"></i> Cetak
        </button>
      </div>
    </div>
  </form>

  @if(!$outletId)
  <div class="card">
    <div class="card-body" style="text-align:center;padding:56px">
      <i class="fa-solid fa-lock" style="font-size:40px;color:var(--muted);margin-bottom:14px;display:block"></i>
      <div style="font-size:15px;font-weight:600;color:var(--text);margin-bottom:6px">Pilih Outlet</div>
      <div style="font-size:13px;color:var(--sub)">Pilih outlet untuk melihat ringkasan closing harian.</div>
    </div>
  </div>

  @else

  {{-- ── Print Header (hidden on screen) ── --}}
  <div class="print-only" style="display:none;text-align:center;margin-bottom:20px;padding-bottom:12px;border-bottom:2px solid #ccc">
    <div style="font-size:20px;font-weight:700">{{ $outlet->nama ?? '' }}</div>
    <div style="font-size:13px;color:#555;margin-top:4px">
      Laporan Closing Harian — {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}
    </div>
  </div>

  {{-- ── Summary Stats ── --}}
  <div class="stat-grid animate-fadeUp">
    <div class="stat-card">
      <div class="stat-icon a-bg-lt" style="color:var(--ac)">
        <i class="fa-solid fa-cash-register"></i>
      </div>
      <div>
        <div class="stat-num">{{ $totalTrx }}</div>
        <div class="stat-label">Total Transaksi</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(52,211,153,.15);color:#34d399">
        <i class="fa-solid fa-sack-dollar"></i>
      </div>
      <div>
        <div class="stat-num" style="font-size:18px">Rp {{ number_format($omzet, 0, ',', '.') }}</div>
        <div class="stat-label">Total Omzet</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(248,113,113,.15);color:#f87171">
        <i class="fa-solid fa-wallet"></i>
      </div>
      <div>
        <div class="stat-num" style="font-size:18px">Rp {{ number_format($totalExpense, 0, ',', '.') }}</div>
        <div class="stat-label">Total Pengeluaran</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba({{ $labaKotor >= 0 ? '52,211,153' : '248,113,113' }},.15);color:{{ $labaKotor >= 0 ? '#34d399' : '#f87171' }}">
        <i class="fa-solid fa-scale-balanced"></i>
      </div>
      <div>
        <div class="stat-num" style="font-size:18px;color:{{ $labaKotor >= 0 ? '#34d399' : '#f87171' }}">
          Rp {{ number_format(abs($labaKotor), 0, ',', '.') }}
        </div>
        <div class="stat-label">{{ $labaKotor >= 0 ? 'Laba Kotor' : 'Rugi Kotor' }}</div>
      </div>
    </div>
  </div>

  <div style="display:flex;flex-direction:column;gap:20px">

    {{-- ── Ringkasan Stok ── --}}
    <div class="card animate-fadeUp d1">
      <div class="card-header">
        <div class="card-title">
          <i class="fa-solid fa-boxes-stacking" style="color:var(--ac);margin-right:8px"></i>
          Ringkasan Stok
        </div>
      </div>

      @if($stockSummary->isEmpty())
      <div class="card-body" style="text-align:center;padding:40px;color:var(--muted);font-size:13px">
        Belum ada pergerakan stok pada tanggal ini.
      </div>
      @else
      <div style="overflow-x:auto">
        <table class="tbl">
          <thead>
            <tr>
              <th>Produk</th>
              <th style="text-align:center">Opening</th>
              <th style="text-align:center">Masuk (+)</th>
              <th style="text-align:center">Terjual (−)</th>
              <th style="text-align:center">Waste (−)</th>
              <th style="text-align:center;color:var(--ac)">Akhir</th>
            </tr>
          </thead>
          <tbody>
            @foreach($stockSummary as $s)
            <tr>
              <td class="td-main">
                {{ $s->product->nama }}
                <div style="font-size:11px;color:var(--muted)">{{ $s->product->category?->nama ?? '—' }}</div>
              </td>
              <td style="text-align:center">{{ $s->opening }}</td>
              <td style="text-align:center;color:#34d399;font-weight:{{ $s->in ? '700' : '400' }}">
                {{ $s->in ?: '—' }}
              </td>
              <td style="text-align:center;color:var(--ac);font-weight:{{ $s->sold ? '700' : '400' }}">
                {{ $s->sold ?: '—' }}
              </td>
              <td style="text-align:center;color:#f87171;font-weight:{{ $s->waste ? '700' : '400' }}">
                {{ $s->waste ?: '—' }}
              </td>
              <td style="text-align:center;font-weight:700;color:{{ $s->akhir > 0 ? 'var(--text)' : '#f87171' }}">
                {{ $s->akhir }}
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @endif
    </div>

    {{-- ── Pengeluaran Hari Ini ── --}}
    <div class="card animate-fadeUp d2">
      <div class="card-header">
        <div class="card-title">
          <i class="fa-solid fa-wallet" style="color:#f87171;margin-right:8px"></i>
          Pengeluaran Hari Ini
        </div>
        <span style="font-family:'Clash Display',sans-serif;font-size:15px;font-weight:700;color:#f87171">
          Rp {{ number_format($totalExpense, 0, ',', '.') }}
        </span>
      </div>

      @if($expenses->isEmpty())
      <div class="card-body" style="text-align:center;padding:32px;color:var(--muted);font-size:13px">
        Tidak ada pengeluaran tercatat.
      </div>
      @else
      <div style="overflow-x:auto">
        <table class="tbl">
          <thead>
            <tr>
              <th>Kategori</th>
              <th>Keterangan</th>
              <th style="text-align:right">Jumlah</th>
              <th>Dicatat Oleh</th>
            </tr>
          </thead>
          <tbody>
            @foreach($expenses as $exp)
            <tr>
              <td><span class="badge badge-amber">{{ \App\Models\Expense::KATEGORI[$exp->kategori] ?? $exp->kategori }}</span></td>
              <td style="font-size:12.5px;color:var(--sub)">{{ $exp->keterangan ?: '—' }}</td>
              <td style="text-align:right;font-weight:600;color:#f87171">Rp {{ number_format($exp->jumlah, 0, ',', '.') }}</td>
              <td style="font-size:12px;color:var(--muted)">{{ $exp->user->name ?? '—' }}</td>
            </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr>
              <td colspan="2" style="text-align:right;font-weight:700;color:var(--text);padding:10px 14px">Total</td>
              <td style="text-align:right;font-weight:700;color:#f87171;padding:10px 14px">Rp {{ number_format($totalExpense, 0, ',', '.') }}</td>
              <td></td>
            </tr>
          </tfoot>
        </table>
      </div>
      @endif
    </div>

    {{-- ── Transaksi Hari Ini ── --}}
    <div class="card animate-fadeUp d3">
      <div class="card-header">
        <div class="card-title">
          <i class="fa-solid fa-receipt" style="color:#34d399;margin-right:8px"></i>
          Transaksi Hari Ini
        </div>
        <div style="display:flex;align-items:center;gap:10px">
          <span style="font-family:'Clash Display',sans-serif;font-size:15px;font-weight:700;color:#34d399">
            Rp {{ number_format($omzet, 0, ',', '.') }}
          </span>
          <span class="badge badge-green">{{ $totalTrx }} transaksi</span>
        </div>
      </div>

      @if($transactions->isEmpty())
      <div class="card-body" style="text-align:center;padding:32px;color:var(--muted);font-size:13px">
        Tidak ada transaksi pada hari ini.
      </div>
      @else
      <div style="overflow-x:auto">
        <table class="tbl">
          <thead>
            <tr>
              <th>No. Transaksi</th>
              <th>Waktu</th>
              <th>Item</th>
              <th>Metode</th>
              <th style="text-align:right">Total</th>
            </tr>
          </thead>
          <tbody>
            @foreach($transactions as $trx)
            <tr>
              <td>
                <a href="{{ route('transactions.show', $trx) }}"
                  style="font-family:monospace;color:var(--ac);text-decoration:none;font-size:12px">
                  {{ $trx->nomor_transaksi }}
                </a>
              </td>
              <td style="font-size:12px;color:var(--sub)">{{ $trx->created_at->format('H:i') }}</td>
              <td style="color:var(--muted);font-size:12px">{{ $trx->items->count() }} produk</td>
              <td>
                @php $m = $trx->metode_bayar ?? 'tunai'; $icons=['tunai'=>'money-bill-wave','qris'=>'qrcode','transfer'=>'building-columns']; @endphp
                <span class="badge badge-blue" style="font-size:10.5px">
                  <i class="fa-solid fa-{{ $icons[$m] }}" style="font-size:9px"></i>
                  {{ ucfirst($m) }}
                </span>
              </td>
              <td style="text-align:right;font-weight:600;color:var(--text)">
                Rp {{ number_format($trx->total, 0, ',', '.') }}
              </td>
            </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr>
              <td colspan="4" style="text-align:right;font-weight:700;color:var(--text);padding:10px 14px">Total Omzet</td>
              <td style="text-align:right;font-weight:700;color:#34d399;padding:10px 14px">
                Rp {{ number_format($omzet, 0, ',', '.') }}
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
      @endif
    </div>

    {{-- ── Laba / Rugi Kotor ── --}}
    <div class="card animate-fadeUp d4" style="border-color:{{ $labaKotor >= 0 ? 'rgba(52,211,153,.3)' : 'rgba(248,113,113,.3)' }}">
      <div class="card-body" style="padding:24px">
        <div style="font-size:13px;color:var(--sub);margin-bottom:16px;font-weight:600;text-transform:uppercase;letter-spacing:.5px">
          Ringkasan Finansial
        </div>
        <div style="display:flex;flex-direction:column;gap:10px">
          <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border)">
            <span style="color:var(--sub);font-size:13.5px">Total Omzet</span>
            <span style="font-weight:700;color:#34d399;font-size:15px">Rp {{ number_format($omzet, 0, ',', '.') }}</span>
          </div>
          <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border)">
            <span style="color:var(--sub);font-size:13.5px">Total Pengeluaran</span>
            <span style="font-weight:700;color:#f87171;font-size:15px">− Rp {{ number_format($totalExpense, 0, ',', '.') }}</span>
          </div>
          <div style="display:flex;justify-content:space-between;padding:14px 0 0">
            <span style="font-weight:700;font-size:15px;color:var(--text)">
              {{ $labaKotor >= 0 ? 'Laba Kotor' : 'Rugi Kotor' }}
            </span>
            <span style="font-family:'Clash Display',sans-serif;font-size:22px;font-weight:700;color:{{ $labaKotor >= 0 ? '#34d399' : '#f87171' }}">
              {{ $labaKotor < 0 ? '−' : '' }}Rp {{ number_format(abs($labaKotor), 0, ',', '.') }}
            </span>
          </div>
        </div>
      </div>
    </div>

  </div>
  @endif

  @push('scripts')
  <style>
  @media print {
    #sb, #header, #ov, #toast-container, .no-print { display: none !important; }
    #main { margin-left: 0 !important; }
    #content { padding: 0 !important; }
    body { background: white !important; color: black !important; }
    .card { border: 1px solid #ddd !important; border-radius: 0 !important; page-break-inside: avoid; }
    .card-header { background: white !important; }
    .print-only { display: block !important; }
    .no-print { display: none !important; }
    .stat-card { border: 1px solid #ddd; }
    .badge { border: 1px solid #ccc; }
    a { color: inherit !important; text-decoration: none !important; }
    .tbl thead th { background: #f5f5f5 !important; color: #555 !important; }
    .tbl tbody td { color: #333 !important; }
    .animate-fadeUp { animation: none !important; }
  }
  </style>
  @endpush

</x-app-layout>
