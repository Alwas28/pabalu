<x-outlet-layout :outlet="$outlet" pageTitle="Riwayat Transaksi">

@php
  $methods = ['cash'=>['Tunai','fa-money-bill-wave','#34d399','rgba(16,185,129,.15)'],'qris'=>['QRIS','fa-qrcode','#60a5fa','rgba(99,162,241,.15)'],'transfer'=>['Transfer','fa-building-columns','#a78bfa','rgba(139,92,246,.15)'],'card'=>['Kartu','fa-credit-card','#fbbf24','rgba(245,158,11,.15)']];
  $hasFilter = request()->hasAny(['method','status','cashier','search','date_from','date_to']);
@endphp

{{-- ── HEADER ── --}}
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px" class="animate-fadeUp">
  <div>
    <h2 class="font-display" style="font-size:20px;font-weight:700;color:var(--text)">Riwayat Transaksi</h2>
    <p style="font-size:13px;color:var(--muted);margin-top:2px">
      {{ $isKasir ? 'Transaksi saya' : 'Semua transaksi penjualan' }} · {{ now()->translatedFormat('F Y') }}
    </p>
  </div>
  <a href="{{ $outlet->route('pos.index') }}"
    style="display:inline-flex;align-items:center;gap:8px;padding:10px 18px;border-radius:12px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13.5px;font-weight:700;cursor:pointer;text-decoration:none;transition:opacity .15s"
    onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
    <i class="fa-solid fa-cash-register" style="font-size:12px"></i> Buka Kasir
  </a>
</div>

{{-- ── STAT CARDS ── --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px" class="animate-fadeUp d1">
  <div class="stat-card">
    <div class="stat-icon" style="background:var(--ac-lt);color:var(--ac)"><i class="fa-solid fa-receipt"></i></div>
    <div>
      <div class="stat-num">{{ number_format($statCount) }}</div>
      <div class="stat-label">Transaksi Bulan Ini</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(16,185,129,.15);color:#34d399"><i class="fa-solid fa-coins"></i></div>
    <div>
      <div class="stat-num" style="font-size:20px">Rp {{ number_format($statRevenue, 0, ',', '.') }}</div>
      <div class="stat-label">Pendapatan Bulan Ini</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(79,110,247,.15);color:#818cf8"><i class="fa-solid fa-chart-bar"></i></div>
    <div>
      <div class="stat-num" style="font-size:20px">Rp {{ number_format($statAvg, 0, ',', '.') }}</div>
      <div class="stat-label">Rata-rata per Transaksi</div>
    </div>
  </div>
</div>

{{-- ── TWO-COL: TABLE + SIDEBAR ── --}}
<div class="two-col animate-fadeUp d2">

{{-- LEFT: Filter + Table --}}
<div style="display:flex;flex-direction:column;gap:16px">

  {{-- Filter bar --}}
  <form method="GET" action="{{ $outlet->route('transactions.index') }}"
    style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:16px 20px;display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end">

    <div style="flex:1;min-width:180px">
      <label class="f-label">Cari No. Transaksi</label>
      <div style="position:relative">
        <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:11px"></i>
        <input name="search" type="text" value="{{ request('search') }}" placeholder="TRX-..."
          class="f-input" style="padding-left:32px">
      </div>
    </div>

    <div style="min-width:140px">
      <label class="f-label">Metode</label>
      <select name="method" class="f-input">
        <option value="">Semua Metode</option>
        @foreach(['cash'=>'Tunai','qris'=>'QRIS','transfer'=>'Transfer','card'=>'Kartu'] as $k=>$v)
        <option value="{{ $k }}" @selected(request('method')===$k)>{{ $v }}</option>
        @endforeach
      </select>
    </div>

    <div style="min-width:130px">
      <label class="f-label">Status</label>
      <select name="status" class="f-input">
        <option value="">Semua Status</option>
        <option value="completed" @selected(request('status')==='completed')>Selesai</option>
        <option value="voided"    @selected(request('status')==='voided')>Void</option>
      </select>
    </div>

    @if($cashiers->count() > 1)
    <div style="min-width:140px">
      <label class="f-label">Kasir</label>
      <select name="cashier" class="f-input">
        <option value="">Semua Kasir</option>
        @foreach($cashiers as $c)
        <option value="{{ $c->id }}" @selected(request('cashier')==(string)$c->id)>{{ $c->name }}</option>
        @endforeach
      </select>
    </div>
    @endif

    <div style="min-width:130px">
      <label class="f-label">Dari Tanggal</label>
      <input name="date_from" type="date" value="{{ request('date_from') }}" class="f-input">
    </div>

    <div style="min-width:130px">
      <label class="f-label">Sampai Tanggal</label>
      <input name="date_to" type="date" value="{{ request('date_to') }}" class="f-input">
    </div>

    <div style="display:flex;gap:8px;align-items:flex-end">
      <button type="submit"
        style="padding:9px 18px;border-radius:12px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:700;cursor:pointer;white-space:nowrap">
        <i class="fa-solid fa-filter" style="font-size:11px;margin-right:5px"></i>Filter
      </button>
      @if($hasFilter)
      <a href="{{ $outlet->route('transactions.index') }}"
        style="padding:9px 14px;border-radius:12px;border:1px solid var(--border);background:transparent;color:var(--sub);font-size:13px;font-weight:600;text-decoration:none;white-space:nowrap">
        Reset
      </a>
      @endif
    </div>
  </form>

  {{-- Table --}}
  <div class="card">
    <div class="card-header">
      <span class="card-title">
        <i class="fa-solid fa-clock-rotate-left" style="color:var(--ac);margin-right:8px;font-size:14px"></i>
        Daftar Transaksi
      </span>
      <span style="font-size:12px;color:var(--muted)">
        {{ $transactions->total() }} transaksi
        @if($hasFilter)<span style="color:var(--ac);font-weight:600"> (difilter)</span>@endif
      </span>
    </div>

    @if($transactions->isEmpty())
    <div style="padding:60px 24px;text-align:center">
      <div style="width:60px;height:60px;border-radius:16px;background:var(--ac-lt);display:grid;place-items:center;margin:0 auto 16px;font-size:22px;color:var(--ac)">
        <i class="fa-solid fa-receipt"></i>
      </div>
      <h3 class="font-display" style="font-size:17px;font-weight:700;color:var(--text);margin-bottom:6px">
        {{ $hasFilter ? 'Tidak Ada Hasil' : 'Belum Ada Transaksi' }}
      </h3>
      <p style="font-size:13.5px;color:var(--muted);max-width:300px;margin:0 auto">
        {{ $hasFilter ? 'Coba ubah atau hapus filter pencarian.' : 'Transaksi dari POS akan muncul di sini.' }}
      </p>
    </div>
    @else
    <div style="overflow-x:auto">
      <table class="tbl">
        <thead>
          <tr>
            <th>No. Transaksi</th>
            <th>Tanggal &amp; Waktu</th>
            <th style="text-align:center;width:70px">Item</th>
            <th style="text-align:right">Total</th>
            <th style="width:100px">Metode</th>
            <th style="width:90px">Status</th>
            <th>Kasir</th>
            <th style="width:60px"></th>
          </tr>
        </thead>
        <tbody>
          @foreach($transactions as $trx)
          @php [$ml,$mi,$mc,$mb] = $methods[$trx->payment_method] ?? ['Lainnya','fa-circle-question','#94a3b8','rgba(148,163,184,.12)']; @endphp
          <tr style="{{ $trx->status==='voided' ? 'opacity:.55' : '' }}">
            <td class="td-main" style="font-family:monospace;font-size:12.5px">
              {{ $trx->transaction_number }}
            </td>
            <td>
              <div style="font-size:13px;font-weight:500;color:var(--text)">{{ $trx->date->translatedFormat('d M Y') }}</div>
              <div style="font-size:11.5px;color:var(--muted)">{{ $trx->created_at->format('H:i') }}</div>
            </td>
            <td style="text-align:center">
              <span style="font-size:13px;font-weight:600;color:var(--text)">{{ $trx->items_count }}</span>
            </td>
            <td style="text-align:right">
              <div style="font-size:14px;font-weight:700;color:var(--text)">Rp {{ number_format($trx->total, 0, ',', '.') }}</div>
              @if($trx->discount_amount > 0)
              <div style="font-size:11px;color:#34d399">−Rp {{ number_format($trx->discount_amount, 0, ',', '.') }}</div>
              @endif
            </td>
            <td>
              <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:99px;font-size:11px;font-weight:600;background:{{ $mb }};color:{{ $mc }}">
                <i class="fa-solid {{ $mi }}" style="font-size:9px"></i>{{ $ml }}
              </span>
            </td>
            <td>
              @if($trx->status === 'completed')
                <span class="badge badge-green"><i class="fa-solid fa-circle-check" style="font-size:9px"></i> Selesai</span>
              @else
                <span class="badge badge-red"><i class="fa-solid fa-ban" style="font-size:9px"></i> Void</span>
              @endif
            </td>
            <td style="font-size:12.5px;color:var(--sub)">{{ $trx->user->name ?? '—' }}</td>
            <td>
              <a href="{{ $outlet->route('transactions.show', [$trx]) }}"
                style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);text-decoration:none;font-size:12px;transition:all .15s"
                title="Lihat detail"
                onmouseover="this.style.borderColor='var(--ac)';this.style.color='var(--ac)'"
                onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--sub)'">
                <i class="fa-solid fa-eye"></i>
              </a>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    @if($transactions->hasPages())
    <div style="padding:14px 20px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
      <span style="font-size:12.5px;color:var(--muted)">
        Menampilkan {{ $transactions->firstItem() }}–{{ $transactions->lastItem() }} dari {{ $transactions->total() }}
      </span>
      <div style="display:flex;gap:6px">
        @if($transactions->onFirstPage())
          <span style="padding:6px 12px;border-radius:8px;border:1px solid var(--border);font-size:13px;color:var(--muted);cursor:default">← Prev</span>
        @else
          <a href="{{ $transactions->previousPageUrl() }}" style="padding:6px 12px;border-radius:8px;border:1px solid var(--border);font-size:13px;color:var(--sub);text-decoration:none;transition:all .15s" onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">← Prev</a>
        @endif
        @if($transactions->hasMorePages())
          <a href="{{ $transactions->nextPageUrl() }}" style="padding:6px 12px;border-radius:8px;border:1px solid var(--border);font-size:13px;color:var(--sub);text-decoration:none;transition:all .15s" onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">Next →</a>
        @else
          <span style="padding:6px 12px;border-radius:8px;border:1px solid var(--border);font-size:13px;color:var(--muted);cursor:default">Next →</span>
        @endif
      </div>
    </div>
    @endif
    @endif
  </div>

</div>{{-- end left --}}

{{-- RIGHT: Sidebar metode pembayaran --}}
<div style="display:flex;flex-direction:column;gap:16px">

  {{-- Breakdown metode --}}
  <div class="card">
    <div class="card-header">
      <span class="card-title"><i class="fa-solid fa-wallet" style="color:var(--ac);margin-right:8px;font-size:13px"></i>Metode Pembayaran</span>
      <span style="font-size:11px;color:var(--muted)">Bulan ini</span>
    </div>
    <div class="card-body" style="display:flex;flex-direction:column;gap:14px">
      @forelse($byMethod as $method => $row)
      @php [$ml,$mi,$mc,$mb] = $methods[$method] ?? ['Lainnya','fa-circle-question','#94a3b8','rgba(148,163,184,.12)']; @endphp
      <div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
          <div style="display:flex;align-items:center;gap:8px">
            <div style="width:30px;height:30px;border-radius:8px;background:{{ $mb }};display:grid;place-items:center;font-size:12px;color:{{ $mc }}">
              <i class="fa-solid {{ $mi }}"></i>
            </div>
            <div>
              <div style="font-size:13px;font-weight:600;color:var(--text)">{{ $ml }}</div>
              <div style="font-size:11px;color:var(--muted)">{{ $row->trx_count }} transaksi</div>
            </div>
          </div>
          <div style="text-align:right">
            <div style="font-size:13px;font-weight:700;color:var(--text)">Rp {{ number_format($row->total_sum, 0, ',', '.') }}</div>
            <div style="font-size:11px;color:var(--muted)">{{ $statRevenue > 0 ? number_format($row->total_sum / $statRevenue * 100, 1) : 0 }}%</div>
          </div>
        </div>
        <div class="prog-bar">
          <div class="prog-fill" style="width:{{ $statRevenue > 0 ? min(100, $row->total_sum / $statRevenue * 100) : 0 }}%;background:{{ $mc }}"></div>
        </div>
      </div>
      @empty
      <p style="font-size:13px;color:var(--muted);text-align:center;padding:16px 0">Belum ada transaksi bulan ini</p>
      @endforelse
    </div>
  </div>

  {{-- Ringkasan hari ini --}}
  <div class="card">
    <div class="card-header">
      <span class="card-title"><i class="fa-solid fa-calendar-day" style="color:var(--ac);margin-right:8px;font-size:13px"></i>Hari Ini</span>
      <span style="font-size:11px;color:var(--muted)">{{ now()->translatedFormat('d M Y') }}</span>
    </div>
    @php
      $todayTrx = $outlet->transactions()->where('status','completed')->whereDate('date', today())->get();
      $todayCount   = $todayTrx->count();
      $todayRevenue = $todayTrx->sum('total');
    @endphp
    <div class="card-body" style="display:flex;flex-direction:column;gap:12px">
      <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 14px;background:var(--surface2);border-radius:12px">
        <div style="font-size:13px;color:var(--sub)">Jumlah Transaksi</div>
        <div style="font-size:18px;font-weight:800;color:var(--text);font-family:'Clash Display',sans-serif">{{ $todayCount }}</div>
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 14px;background:var(--ac-lt);border-radius:12px">
        <div style="font-size:13px;color:var(--ac);font-weight:600">Total Pendapatan</div>
        <div style="font-size:16px;font-weight:800;color:var(--ac);font-family:'Clash Display',sans-serif">Rp {{ number_format($todayRevenue, 0, ',', '.') }}</div>
      </div>
    </div>
  </div>

</div>{{-- end right --}}
</div>{{-- end two-col --}}

@if(session('success'))
@push('scripts')
<script>document.addEventListener('DOMContentLoaded',()=>showToast('success','{{ session('success') }}'));</script>
@endpush
@endif

</x-outlet-layout>
