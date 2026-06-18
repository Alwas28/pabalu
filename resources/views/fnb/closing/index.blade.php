<x-outlet-layout :outlet="$outlet" pageTitle="Closing Harian">

@php
  $sudahClosing = !is_null($todayClosing);
  $hasOpening   = !is_null($openingOpname);
@endphp

{{-- ── HEADER ── --}}
<div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px" class="animate-fadeUp">
  <div>
    <h2 class="font-display" style="font-size:20px;font-weight:700;color:var(--text)">Closing Harian</h2>
    <p style="font-size:13px;color:var(--muted);margin-top:2px">
      {{ $today->translatedFormat('l, d F Y') }}
    </p>
  </div>
  <div style="display:flex;align-items:center;gap:10px">
    @if($sudahClosing)
      <span style="display:inline-flex;align-items:center;gap:7px;padding:8px 16px;border-radius:99px;background:rgba(52,211,153,.15);border:1px solid rgba(52,211,153,.3);font-size:13px;font-weight:700;color:#34d399">
        <i class="fa-solid fa-circle-check"></i> Closing Selesai · {{ $todayClosing->closed_at->format('H:i') }}
      </span>
      <button onclick="document.getElementById('modal-batal-closing').classList.add('open')"
        style="display:inline-flex;align-items:center;gap:7px;padding:8px 14px;border-radius:99px;background:transparent;border:1px solid rgba(248,113,113,.4);font-size:12.5px;font-weight:700;color:#f87171;cursor:pointer;transition:all .15s"
        onmouseover="this.style.background='rgba(248,113,113,.08)'" onmouseout="this.style.background='transparent'">
        <i class="fa-solid fa-rotate-left" style="font-size:11px"></i> Batalkan Closing
      </button>
    @else
      <span style="display:inline-flex;align-items:center;gap:7px;padding:8px 16px;border-radius:99px;background:rgba(251,191,36,.12);border:1px solid rgba(251,191,36,.3);font-size:13px;font-weight:700;color:#fbbf24">
        <i class="fa-solid fa-clock"></i> Belum Closing
      </span>
    @endif
  </div>
</div>

{{-- ── STAT CARDS ── --}}
<div style="display:grid;grid-template-columns:repeat(5,1fr);gap:16px" class="animate-fadeUp d1">
  <div class="stat-card">
    <div class="stat-icon" style="background:var(--ac-lt);color:var(--ac)">
      <i class="fa-solid fa-receipt"></i>
    </div>
    <div>
      <div class="stat-num">{{ number_format($totalTransactions) }}</div>
      <div class="stat-label">Total Transaksi</div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(52,211,153,.15);color:#34d399">
      <i class="fa-solid fa-money-bill-trend-up"></i>
    </div>
    <div>
      <div class="stat-num" style="font-size:16px">Rp {{ number_format($totalRevenue) }}</div>
      <div class="stat-label">Total Pendapatan</div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(245,158,11,.12);color:#f59e0b">
      <i class="fa-solid fa-trash-can"></i>
    </div>
    <div>
      <div class="stat-num" style="font-size:20px;color:#f59e0b">{{ $totalWasteQty }} <span style="font-size:13px">pcs</span></div>
      <div class="stat-label">Total Waste</div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(248,113,113,.15);color:#f87171">
      <i class="fa-solid fa-arrow-trend-down"></i>
    </div>
    <div>
      <div class="stat-num" style="font-size:16px;color:#f87171">Rp {{ number_format($totalExpense) }}</div>
      <div class="stat-label">Pengeluaran</div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon" style="background:{{ $kasBersih >= 0 ? 'rgba(52,211,153,.15)' : 'rgba(248,113,113,.15)' }};color:{{ $kasBersih >= 0 ? '#34d399' : '#f87171' }}">
      <i class="fa-solid fa-scale-balanced"></i>
    </div>
    <div>
      <div class="stat-num" style="font-size:16px;color:{{ $kasBersih >= 0 ? '#34d399' : '#f87171' }}">
        Rp {{ number_format(abs($kasBersih)) }}
      </div>
      <div class="stat-label">Kas Tunai Bersih</div>
    </div>
  </div>
</div>

{{-- ── MAIN TWO-COL ── --}}
<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start" class="animate-fadeUp d2">

{{-- ══ LEFT ══ --}}
<div style="display:flex;flex-direction:column;gap:20px">

  {{-- Penjualan per Metode Bayar --}}
  <div class="card">
    <div class="card-header">
      <span class="card-title">
        <i class="fa-solid fa-credit-card" style="color:var(--ac);margin-right:8px;font-size:13px"></i>
        Penjualan per Metode Bayar
      </span>
    </div>
    <div style="overflow-x:auto">
      <table class="tbl">
        <thead>
          <tr>
            <th>Metode</th>
            <th style="text-align:center;width:100px">Transaksi</th>
            <th style="text-align:right;width:160px">Total</th>
            <th style="text-align:right;width:100px">%</th>
          </tr>
        </thead>
        <tbody>
          @foreach($byMethod as $key => $m)
          <tr>
            <td>
              <div style="display:flex;align-items:center;gap:10px">
                <div style="width:32px;height:32px;border-radius:8px;background:{{ $m['color'] }}1a;display:grid;place-items:center;font-size:13px;color:{{ $m['color'] }}">
                  <i class="fa-solid fa-{{ $m['icon'] }}"></i>
                </div>
                <span style="font-weight:600;color:var(--text)">{{ $m['label'] }}</span>
              </div>
            </td>
            <td style="text-align:center;font-weight:600;color:var(--sub)">
              {{ $m['count'] > 0 ? $m['count'] : '—' }}
            </td>
            <td style="text-align:right;font-weight:700;color:{{ $m['total'] > 0 ? 'var(--text)' : 'var(--muted)' }}">
              {{ $m['total'] > 0 ? 'Rp '.number_format($m['total']) : '—' }}
            </td>
            <td style="text-align:right">
              @php $pct = $totalRevenue > 0 ? round($m['total'] / $totalRevenue * 100) : 0; @endphp
              @if($pct > 0)
                <span style="font-size:12px;font-weight:700;color:{{ $m['color'] }}">{{ $pct }}%</span>
              @else
                <span style="color:var(--muted);font-size:12px">—</span>
              @endif
            </td>
          </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr style="background:var(--surface2);border-top:2px solid var(--border)">
            <td style="font-weight:700;color:var(--text);padding-top:12px;padding-bottom:12px">
              <span style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--muted);font-weight:700">TOTAL</span>
            </td>
            <td style="text-align:center;padding-top:12px;padding-bottom:12px">
              <span style="font-size:16px;font-weight:800;color:var(--text)">{{ $totalTransactions }}</span>
              <div style="font-size:11px;color:var(--muted)">transaksi</div>
            </td>
            <td style="text-align:right;padding-top:12px;padding-bottom:12px">
              <span style="font-size:17px;font-weight:800;color:var(--text)">Rp {{ number_format($totalRevenue) }}</span>
              @if($totalDiscount > 0)
              <div style="font-size:11px;color:var(--muted)">diskon Rp {{ number_format($totalDiscount) }}</div>
              @endif
            </td>
            <td style="padding-top:12px;padding-bottom:12px">
              <span style="font-size:12px;font-weight:700;color:var(--muted)">100%</span>
            </td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

  {{-- Ringkasan Stok Produk --}}
  <div class="card">
    <div class="card-header">
      <span class="card-title">
        <i class="fa-solid fa-boxes-stacking" style="color:var(--ac);margin-right:8px;font-size:13px"></i>
        Ringkasan Stok Produk
      </span>
      @if(!$hasOpening)
      <span style="font-size:12px;color:#f59e0b;font-weight:600">
        <i class="fa-solid fa-triangle-exclamation" style="margin-right:4px"></i>Opening stok tidak ditemukan
      </span>
      @endif
    </div>
    <div style="overflow-x:auto">
      <table class="tbl">
        <thead>
          <tr>
            <th>Produk</th>
            <th style="text-align:center;width:90px">Disiapkan</th>
            <th style="text-align:center;width:80px">Terjual</th>
            <th style="text-align:center;width:80px">Waste</th>
            <th style="text-align:center;width:80px">Sisa</th>
          </tr>
        </thead>
        <tbody>
          @forelse($products as $product)
          @php
            $openingQty = $hasOpening
              ? ($openingOpname->items->firstWhere('product_id', $product->id)?->actual_qty ?? 0)
              : null;
            $soldQty    = $salesByProduct->get($product->id)?->sold_qty ?? 0;
            $wasteQty   = $wasteByProduct->get($product->id)?->waste_qty ?? 0;
            $sisaQty    = $product->stock;
          @endphp
          <tr>
            <td>
              <div style="display:flex;align-items:center;gap:9px">
                @if($product->image)
                  <img src="{{ Storage::url($product->image) }}"
                    style="width:30px;height:30px;border-radius:8px;object-fit:cover;flex-shrink:0;border:1px solid var(--border)">
                @else
                  <div style="width:30px;height:30px;border-radius:8px;background:var(--ac-lt);color:var(--ac);display:grid;place-items:center;flex-shrink:0;font-size:12px">
                    <i class="fa-solid fa-bowl-food"></i>
                  </div>
                @endif
                <div>
                  <div style="font-weight:600;font-size:13px;color:var(--text)">{{ $product->name }}</div>
                  <div style="font-size:11px;color:var(--muted)">{{ $product->category?->name ?? '—' }}</div>
                </div>
              </div>
            </td>
            <td style="text-align:center">
              @if($openingQty !== null)
                <span style="font-weight:700;color:var(--sub)">{{ $openingQty }}</span>
                <span style="font-size:11px;color:var(--muted)"> {{ $product->unit }}</span>
              @else
                <span style="color:var(--muted);font-size:12px">—</span>
              @endif
            </td>
            <td style="text-align:center">
              @if($soldQty > 0)
                <span style="font-weight:700;color:#f87171">-{{ $soldQty }}</span>
                <span style="font-size:11px;color:var(--muted)"> {{ $product->unit }}</span>
              @else
                <span style="color:var(--muted);font-size:12px">—</span>
              @endif
            </td>
            <td style="text-align:center">
              @if($wasteQty > 0)
                <span style="font-weight:700;color:#f59e0b">-{{ $wasteQty }}</span>
                <span style="font-size:11px;color:var(--muted)"> {{ $product->unit }}</span>
              @else
                <span style="color:var(--muted);font-size:12px">—</span>
              @endif
            </td>
            <td style="text-align:center">
              <span style="font-weight:800;font-size:15px;color:{{ $sisaQty <= 0 ? '#f87171' : ($product->isLowStock() ? '#f59e0b' : '#34d399') }}">
                {{ $sisaQty }}
              </span>
              <span style="font-size:11px;color:var(--muted)"> {{ $product->unit }}</span>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="5" style="text-align:center;padding:32px;color:var(--muted);font-size:13px">
              Belum ada produk aktif
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Waste / Rusak Hari Ini --}}
  <div class="card">
    <div class="card-header">
      <span class="card-title">
        <i class="fa-solid fa-trash-can" style="color:#f59e0b;margin-right:8px;font-size:13px"></i>
        Waste / Rusak Hari Ini
      </span>
      <div style="display:flex;align-items:center;gap:10px">
        @if($totalWasteQty > 0)
        <span style="font-size:13px;font-weight:700;color:#f59e0b">{{ $totalWasteQty }} pcs</span>
        @endif
        @if($user->hasPermission('stock.waste'))
        <a href="{{ $outlet->route('waste.create') }}"
           style="display:inline-flex;align-items:center;gap:5px;padding:5px 12px;border-radius:8px;background:rgba(245,158,11,.12);color:#f59e0b;font-size:12px;font-weight:600;text-decoration:none;border:1px solid rgba(245,158,11,.25)">
          <i class="fa-solid fa-plus" style="font-size:10px"></i> Catat Waste
        </a>
        @endif
      </div>
    </div>

    @if($wastes->isEmpty())
    <div style="padding:28px;text-align:center;color:var(--muted);font-size:13px">
      <i class="fa-solid fa-check-circle" style="display:block;font-size:22px;margin-bottom:8px;color:#34d399;opacity:.7"></i>
      Tidak ada waste hari ini
    </div>
    @else
    <div style="overflow-x:auto">
      <table class="tbl">
        <thead>
          <tr>
            <th>Produk</th>
            <th style="text-align:center;width:70px">Qty</th>
            <th style="width:130px">Alasan</th>
            <th style="width:100px">Dicatat oleh</th>
          </tr>
        </thead>
        <tbody>
          @foreach($wastes as $waste)
            @foreach($waste->items as $item)
            @php
              $reasonColors = ['sisa'=>'#fbbf24','rusak'=>'#f87171','expired'=>'#f43f5e','lainnya'=>'#94a3b8'];
              $reasonLabels = \App\Models\Waste::$reasons;
              $rc = $reasonColors[$item->reason] ?? '#94a3b8';
            @endphp
            <tr>
              <td class="td-main">{{ $item->product_name }}</td>
              <td style="text-align:center;font-weight:700;color:#f59e0b">{{ $item->qty }}</td>
              <td>
                <span style="display:inline-flex;padding:2px 9px;border-radius:99px;font-size:11px;font-weight:600;background:{{ $rc }}1a;color:{{ $rc }}">
                  {{ $reasonLabels[$item->reason] ?? $item->reason }}
                </span>
              </td>
              <td style="font-size:12px;color:var(--muted)">{{ $waste->user?->name ?? '—' }}</td>
            </tr>
            @endforeach
          @endforeach
        </tbody>
        <tfoot>
          <tr style="border-top:2px solid var(--border)">
            <td style="padding:10px 14px;font-weight:600;color:var(--text)">Total</td>
            <td style="text-align:center;padding:10px 14px;font-weight:800;font-size:15px;color:#f59e0b">{{ $totalWasteQty }}</td>
            <td colspan="2" style="font-size:12px;color:var(--muted);padding:10px 14px">pcs terbuang</td>
          </tr>
        </tfoot>
      </table>
    </div>
    @endif
  </div>

</div>{{-- end left --}}

{{-- ══ RIGHT ══ --}}
<div style="display:flex;flex-direction:column;gap:16px">

  {{-- Form Closing / Sudah Closing --}}
  @if($sudahClosing)
  <div class="card">
    <div style="padding:24px;text-align:center">
      <div style="width:52px;height:52px;border-radius:14px;background:rgba(52,211,153,.15);display:grid;place-items:center;margin:0 auto 12px;font-size:20px;color:#34d399">
        <i class="fa-solid fa-circle-check"></i>
      </div>
      <div class="font-display" style="font-size:15px;font-weight:700;color:var(--text);margin-bottom:4px">Closing Sudah Dilakukan</div>
      <div style="font-size:12px;color:var(--muted);margin-bottom:14px">
        {{ $todayClosing->closed_at->translatedFormat('d F Y, H:i') }}<br>
        oleh {{ $todayClosing->user->name }}
      </div>
      @if($todayClosing->notes)
      <div style="text-align:left;padding:10px 12px;background:var(--surface2);border-radius:10px;font-size:13px;color:var(--sub);line-height:1.5">
        <div style="font-size:11px;color:var(--muted);margin-bottom:4px;font-weight:700;text-transform:uppercase;letter-spacing:.5px">Catatan</div>
        {{ $todayClosing->notes }}
      </div>
      @endif
    </div>
  </div>
  @else
  <div class="card">
    <div class="card-header">
      <span class="card-title">
        <i class="fa-solid fa-flag-checkered" style="color:var(--ac);margin-right:8px;font-size:13px"></i>
        Lakukan Closing
      </span>
    </div>
    <form action="{{ $outlet->route('closing.store') }}" method="POST">
    @csrf
    <div style="padding:16px 20px;display:flex;flex-direction:column;gap:12px">
      <div style="padding:10px 14px;border-radius:10px;background:rgba(251,191,36,.1);border:1px solid rgba(251,191,36,.2);font-size:12.5px;color:#fbbf24;line-height:1.7">
        <div style="font-weight:700;margin-bottom:4px"><i class="fa-solid fa-circle-info" style="margin-right:6px"></i>Perhatian sebelum closing:</div>
        <ul style="margin:0;padding-left:18px">
          <li>Data penjualan hari ini dikunci sebagai laporan harian</li>
          <li>Seluruh stok produk akan direset ke <strong>0</strong> (sisa porsi dianggap habis)</li>
        </ul>
      </div>

      <div>
        <label class="f-label">Catatan Penutupan <span style="color:var(--muted);font-weight:400;font-size:11px">(opsional)</span></label>
        <textarea name="notes" rows="3" placeholder="Kendala hari ini, catatan untuk shift berikutnya..."
          class="f-input" style="resize:vertical">{{ old('notes') }}</textarea>
      </div>

      @if($totalTransactions === 0)
      <div style="padding:10px 14px;border-radius:10px;background:rgba(248,113,113,.08);border:1px solid rgba(248,113,113,.2);font-size:12.5px;color:#f87171;display:flex;align-items:center;gap:8px">
        <i class="fa-solid fa-ban"></i>
        Closing tidak tersedia — belum ada transaksi hari ini.
      </div>
      <button type="button" disabled
        style="width:100%;padding:12px;border-radius:14px;border:none;background:var(--surface2);color:var(--muted);font-size:14px;font-weight:700;cursor:not-allowed;display:flex;align-items:center;justify-content:center;gap:9px;opacity:.5">
        <i class="fa-solid fa-flag-checkered" style="font-size:13px"></i>
        Simpan &amp; Tutup Hari Ini
      </button>
      @else
      <button type="button"
        onclick="bukaModalClosing()"
        style="width:100%;padding:12px;border-radius:14px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:14px;font-weight:700;cursor:pointer;transition:opacity .15s;display:flex;align-items:center;justify-content:center;gap:9px"
        onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
        <i class="fa-solid fa-flag-checkered" style="font-size:13px"></i>
        Simpan &amp; Tutup Hari Ini
      </button>
      @endif
    </div>
    </form>
  </div>
  @endif

  {{-- Kasir Bertugas --}}
  <div class="card">
    <div class="card-header">
      <span class="card-title">
        <i class="fa-solid fa-user-tie" style="color:var(--ac);margin-right:8px;font-size:13px"></i>
        Kasir Bertugas
      </span>
    </div>
    <div style="padding:12px 16px;display:flex;flex-direction:column;gap:8px">
      @forelse($cashiers as $kasir)
      @php
        $kasirTrx   = $transactions->where('user_id', $kasir->id);
        $kasirTotal = $kasirTrx->sum('total');
      @endphp
      <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;background:var(--surface2);border-radius:10px">
        <div style="display:flex;align-items:center;gap:9px">
          <div style="width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,var(--ac),var(--ac2));display:grid;place-items:center;color:#fff;font-size:11px;font-weight:800;flex-shrink:0">
            {{ strtoupper(substr($kasir->name, 0, 1)) }}
          </div>
          <div>
            <div style="font-weight:600;font-size:13px;color:var(--text)">{{ $kasir->name }}</div>
            <div style="font-size:11px;color:var(--muted)">{{ $kasirTrx->count() }} transaksi</div>
          </div>
        </div>
        <div style="font-size:13px;font-weight:700;color:var(--text)">Rp {{ number_format($kasirTotal) }}</div>
      </div>
      @empty
      <div style="text-align:center;padding:20px;color:var(--muted);font-size:13px">
        <i class="fa-solid fa-user-slash" style="display:block;font-size:20px;margin-bottom:8px"></i>
        Belum ada transaksi hari ini
      </div>
      @endforelse
    </div>
  </div>

  {{-- Pengeluaran --}}
  <div class="card">
    <div class="card-header">
      <span class="card-title">
        <i class="fa-solid fa-arrow-trend-down" style="color:#f87171;margin-right:8px;font-size:13px"></i>
        Pengeluaran Hari Ini
      </span>
      <span style="font-size:13px;font-weight:700;color:#f87171">
        Rp {{ number_format($totalExpense) }}
      </span>
    </div>
    <div style="padding:12px 16px;display:flex;flex-direction:column;gap:6px">
      @forelse($expenses as $exp)
      <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 10px;border-radius:8px;background:var(--surface2)">
        <div>
          <div style="font-size:13px;color:var(--text);font-weight:500">{{ $exp->description }}</div>
          <div style="font-size:11px;color:var(--muted)">{{ $exp->category }} · {{ \Carbon\Carbon::parse($exp->created_at)->format('H:i') }}</div>
        </div>
        <span style="font-size:13px;font-weight:700;color:#f87171;white-space:nowrap">
          Rp {{ number_format($exp->amount) }}
        </span>
      </div>
      @empty
      <div style="text-align:center;padding:16px;color:var(--muted);font-size:12.5px">
        Tidak ada pengeluaran hari ini
      </div>
      @endforelse
    </div>
  </div>

</div>{{-- end right --}}
</div>{{-- end two-col --}}

{{-- ── RIWAYAT CLOSING ── --}}
<div class="card animate-fadeUp d3">
  <div class="card-header">
    <span class="card-title">
      <i class="fa-solid fa-clock-rotate-left" style="color:var(--ac);margin-right:8px;font-size:14px"></i>
      Riwayat Closing Harian
    </span>
    <span style="font-size:12px;color:var(--muted)">{{ $history->total() }} catatan</span>
  </div>

  @if($history->isEmpty())
  <div style="padding:48px 24px;text-align:center">
    <div style="width:52px;height:52px;border-radius:14px;background:var(--ac-lt);display:grid;place-items:center;margin:0 auto 12px;font-size:20px;color:var(--ac)">
      <i class="fa-solid fa-flag-checkered"></i>
    </div>
    <p style="font-size:13.5px;color:var(--muted)">Belum ada riwayat closing</p>
  </div>
  @else
  <div style="overflow-x:auto">
    <table class="tbl">
      <thead>
        <tr>
          <th style="width:120px">Tanggal</th>
          <th style="text-align:center;width:90px">Transaksi</th>
          <th style="text-align:right;width:150px">Pendapatan</th>
          <th style="text-align:right;width:130px">Pengeluaran</th>
          <th style="text-align:right;width:150px">Kas Tunai Bersih</th>
          <th>Dicatat oleh</th>
          <th style="width:70px;text-align:center">Waktu</th>
        </tr>
      </thead>
      <tbody>
        @foreach($history as $cl)
        @php $bersih = $cl->cash_in - $cl->total_expense; @endphp
        <tr>
          <td class="td-main">
            <div style="font-weight:600;color:var(--text)">{{ $cl->date->translatedFormat('d M Y') }}</div>
            @if($cl->date->isToday())
              <span class="badge" style="font-size:10px;background:var(--ac-lt2);color:var(--ac)">Hari Ini</span>
            @endif
          </td>
          <td style="text-align:center;font-weight:700;color:var(--text)">{{ number_format($cl->total_transactions) }}</td>
          <td style="text-align:right;font-weight:700;color:var(--text)">Rp {{ number_format($cl->total_revenue) }}</td>
          <td style="text-align:right;font-weight:600;color:#f87171">
            {{ $cl->total_expense > 0 ? 'Rp '.number_format($cl->total_expense) : '—' }}
          </td>
          <td style="text-align:right;font-weight:800;color:{{ $bersih >= 0 ? '#34d399' : '#f87171' }}">
            Rp {{ number_format(abs($bersih)) }}
          </td>
          <td style="font-size:12.5px;color:var(--sub)">{{ $cl->user->name }}</td>
          <td style="text-align:center;font-size:12px;color:var(--muted)">{{ $cl->closed_at->format('H:i') }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  @if($history->hasPages())
  <div style="padding:14px 20px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
    <span style="font-size:12.5px;color:var(--muted)">
      Menampilkan {{ $history->firstItem() }}–{{ $history->lastItem() }} dari {{ $history->total() }}
    </span>
    <div style="display:flex;gap:6px">
      @if($history->onFirstPage())
        <span style="padding:6px 12px;border-radius:8px;border:1px solid var(--border);color:var(--muted);font-size:12.5px">← Sebelumnya</span>
      @else
        <a href="{{ $history->previousPageUrl() }}"
          style="padding:6px 12px;border-radius:8px;border:1px solid var(--border);color:var(--sub);font-size:12.5px;text-decoration:none;transition:all .15s"
          onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">← Sebelumnya</a>
      @endif
      @if($history->hasMorePages())
        <a href="{{ $history->nextPageUrl() }}"
          style="padding:6px 12px;border-radius:8px;border:1px solid var(--border);color:var(--sub);font-size:12.5px;text-decoration:none;transition:all .15s"
          onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">Berikutnya →</a>
      @else
        <span style="padding:6px 12px;border-radius:8px;border:1px solid var(--border);color:var(--muted);font-size:12.5px">Berikutnya →</span>
      @endif
    </div>
  </div>
  @endif
  @endif
</div>

{{-- ── MODAL KONFIRMASI CLOSING ── --}}
@if(!$sudahClosing)
<div id="modal-konfirmasi-closing" class="modal-backdrop" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-box" style="max-width:440px">
    <div style="padding:28px 24px 20px;text-align:center">
      <div style="width:56px;height:56px;border-radius:16px;background:linear-gradient(135deg,var(--ac),var(--ac2));display:grid;place-items:center;margin:0 auto 14px;font-size:22px;color:#fff">
        <i class="fa-solid fa-flag-checkered"></i>
      </div>
      <h3 class="font-display" style="font-size:18px;font-weight:700;color:var(--text);margin-bottom:8px">Konfirmasi Closing Harian</h3>
      <p style="font-size:13.5px;color:var(--muted);max-width:340px;margin:0 auto;line-height:1.65">
        Yakin menutup hari ini? Laporan akan dikunci dan
        <strong style="color:var(--text)">semua stok produk direset ke 0</strong>.
      </p>
      <div style="margin-top:14px;padding:10px 14px;border-radius:10px;background:var(--surface2);text-align:left;font-size:12.5px;color:var(--sub);display:flex;flex-direction:column;gap:5px">
        <div><i class="fa-solid fa-receipt" style="color:var(--ac);width:16px"></i> <strong>{{ $totalTransactions }}</strong> transaksi · Rp {{ number_format($totalRevenue) }}</div>
        @if($totalWasteQty > 0)
        <div><i class="fa-solid fa-trash-can" style="color:#f59e0b;width:16px"></i> Waste <strong>{{ $totalWasteQty }} pcs</strong> dari {{ $wastes->count() }} laporan</div>
        @endif
        <div><i class="fa-solid fa-arrow-trend-down" style="color:#f87171;width:16px"></i> Pengeluaran Rp {{ number_format($totalExpense) }}</div>
      </div>
    </div>
    <form id="form-closing" action="{{ $outlet->route('closing.store') }}" method="POST">
    @csrf
    <input type="hidden" name="notes" id="hidden-notes">
    <div style="padding:0 20px 20px;display:flex;gap:10px">
      <button type="button" onclick="document.getElementById('modal-konfirmasi-closing').classList.remove('open')"
        style="flex:1;padding:11px;border-radius:12px;border:1px solid var(--border);background:transparent;color:var(--sub);font-size:13.5px;font-weight:600;cursor:pointer">
        Batal
      </button>
      <button type="submit"
        style="flex:1;padding:11px;border-radius:12px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13.5px;font-weight:700;cursor:pointer">
        <i class="fa-solid fa-flag-checkered" style="margin-right:6px;font-size:12px"></i>Ya, Closing Sekarang
      </button>
    </div>
    </form>
  </div>
</div>
@endif

{{-- ── MODAL BATALKAN CLOSING ── --}}
@if($sudahClosing)
<div id="modal-batal-closing" class="modal-backdrop" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-box" style="max-width:420px">
    <div style="padding:28px 24px 20px;text-align:center">
      <div style="width:52px;height:52px;border-radius:14px;background:rgba(248,113,113,.15);display:grid;place-items:center;margin:0 auto 14px;font-size:20px;color:#f87171">
        <i class="fa-solid fa-rotate-left"></i>
      </div>
      <h3 class="font-display" style="font-size:17px;font-weight:700;color:var(--text);margin-bottom:8px">Batalkan Closing?</h3>
      <p style="font-size:13.5px;color:var(--muted);max-width:320px;margin:0 auto;line-height:1.65">
        Closing akan dibatalkan dan <strong style="color:var(--text)">stok produk dikembalikan</strong> ke kondisi sebelum closing dilakukan.
      </p>
    </div>
    <form action="{{ $outlet->route('closing.destroy') }}" method="POST">
    @csrf @method('DELETE')
    <div style="padding:0 20px 20px;display:flex;gap:10px">
      <button type="button" onclick="document.getElementById('modal-batal-closing').classList.remove('open')"
        style="flex:1;padding:11px;border-radius:12px;border:1px solid var(--border);background:transparent;color:var(--sub);font-size:13.5px;font-weight:600;cursor:pointer">
        Tidak
      </button>
      <button type="submit"
        style="flex:1;padding:11px;border-radius:12px;border:none;background:#ef4444;color:#fff;font-size:13.5px;font-weight:700;cursor:pointer">
        <i class="fa-solid fa-rotate-left" style="margin-right:6px;font-size:12px"></i>Ya, Batalkan Closing
      </button>
    </div>
    </form>
  </div>
</div>
@endif

@if(!$sudahClosing)
<script>
function bukaModalClosing() {
  const ta = document.querySelector('textarea[name="notes"]');
  if (ta) document.getElementById('hidden-notes').value = ta.value;
  document.getElementById('modal-konfirmasi-closing').classList.add('open');
}
</script>
@endif

@if(session('success'))
<div id="toast-ok" style="position:fixed;bottom:24px;right:24px;z-index:999;background:#34d399;color:#fff;padding:12px 20px;border-radius:14px;font-size:13.5px;font-weight:700;box-shadow:0 8px 24px rgba(52,211,153,.4);display:flex;align-items:center;gap:9px;animation:slideUp .3s ease">
  <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
</div>
<script>setTimeout(() => { const t = document.getElementById('toast-ok'); if(t) t.remove(); }, 3500);</script>
@endif

@if(session('error'))
<div id="toast-err" style="position:fixed;bottom:24px;right:24px;z-index:999;background:#f87171;color:#fff;padding:12px 20px;border-radius:14px;font-size:13.5px;font-weight:700;box-shadow:0 8px 24px rgba(248,113,113,.4);display:flex;align-items:center;gap:9px">
  <i class="fa-solid fa-triangle-exclamation"></i> {{ session('error') }}
</div>
<script>setTimeout(() => { const t = document.getElementById('toast-err'); if(t) t.remove(); }, 4000);</script>
@endif

</x-outlet-layout>
