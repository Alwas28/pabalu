<x-outlet-layout :outlet="$outlet" pageTitle="Deposit">

{{-- ── HEADER ── --}}
<div class="animate-fadeUp">
  <h2 class="font-display" style="font-size:20px;font-weight:700;color:var(--text)">Deposit</h2>
  <p style="font-size:13px;color:var(--muted);margin-top:2px">Riwayat deposit/jaminan pelanggan pada outlet <strong style="color:var(--sub)">{{ $outlet->name }}</strong></p>
</div>

{{-- ── STATS ── --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px" class="animate-fadeUp d1">
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(96,165,250,.15);color:#60a5fa"><i class="fa-solid fa-vault"></i></div>
    <div><div class="stat-num">Rp {{ number_format($totalHeld, 0, ',', '.') }}</div><div class="stat-label">Sedang Ditahan (Sewa Aktif)</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(251,191,36,.15);color:#fbbf24"><i class="fa-solid fa-hourglass-half"></i></div>
    <div><div class="stat-num">Rp {{ number_format($totalPending, 0, ',', '.') }}</div><div class="stat-label">Menunggu Refund</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(52,211,153,.15);color:#34d399"><i class="fa-solid fa-circle-check"></i></div>
    <div><div class="stat-num">Rp {{ number_format($totalRefunded, 0, ',', '.') }}</div><div class="stat-label">Sudah Direfund</div></div>
  </div>
</div>

{{-- ── FILTER TANGGAL ── --}}
<div class="card animate-fadeUp d2" style="padding:14px 18px">
  <form method="GET" action="{{ $outlet->route('deposits.index') }}" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
    <div>
      <label class="f-label">Dari Tanggal Mulai Sewa</label>
      <input type="date" name="from" class="f-input" value="{{ $from }}" max="{{ now()->toDateString() }}">
    </div>
    <div>
      <label class="f-label">Sampai Tanggal Mulai Sewa</label>
      <input type="date" name="to" class="f-input" value="{{ $to }}" max="{{ now()->toDateString() }}">
    </div>
    <button type="submit" class="btn-save" style="padding:9px 18px">
      <i class="fa-solid fa-filter" style="margin-right:6px"></i>Terapkan
    </button>
    @if($from || $to)
    <a href="{{ $outlet->route('deposits.index') }}"
      style="padding:9px 16px;border-radius:10px;border:1px solid var(--border);background:transparent;color:var(--sub);font-size:13px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center">
      Reset
    </a>
    @endif
  </form>
</div>

{{-- ── TABLE ── --}}
<div class="card animate-fadeUp d3">
  <div class="card-header">
    <span class="card-title"><i class="fa-solid fa-piggy-bank" style="color:var(--ac);margin-right:8px;font-size:14px"></i>Daftar Deposit</span>
  </div>

  @if($rentals->isEmpty())
  <div style="padding:56px 24px;text-align:center">
    <i class="fa-solid fa-piggy-bank" style="font-size:26px;opacity:.4;display:block;margin-bottom:10px;color:var(--muted)"></i>
    <p style="font-size:13.5px;color:var(--muted)">Belum ada sewa dengan deposit.</p>
  </div>
  @else
  <div style="overflow-x:auto">
  <table class="tbl">
    <thead>
      <tr>
        <th>Mulai Sewa</th>
        <th>No. Transaksi</th>
        <th>Pelanggan</th>
        <th>Barang / Unit</th>
        <th style="text-align:right">Deposit Awal</th>
        <th style="text-align:right">Terpakai</th>
        <th style="text-align:right">Tersedia / Direfund</th>
        <th style="text-align:center">Status</th>
        <th style="text-align:right">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @foreach($rentals as $r)
      @php
        $status = \App\Http\Controllers\Sewa\DepositController::depositStatus($r);
        $applied = $r->depositApplied();
      @endphp
      <tr>
        <td style="font-size:12.5px;color:var(--sub)">{{ $r->start_at->translatedFormat('d M Y') }}</td>
        <td class="td-main" style="font-family:monospace;font-size:12px">{{ $r->order_number }}</td>
        <td>
          <a href="{{ $outlet->route('customers.show', [$r->customer]) }}" style="color:var(--ac);font-weight:600;text-decoration:none">
            {{ $r->customer->name }}
          </a>
        </td>
        <td>
          {{ $r->rentalUnit->rentalItem->name }}
          <div style="font-size:11px;color:var(--muted)">{{ $r->rentalUnit->code }}</div>
        </td>
        <td style="text-align:right;font-weight:700;color:var(--text)">Rp {{ number_format($r->deposit_amount, 0, ',', '.') }}</td>
        <td style="text-align:right;color:var(--muted)">{{ $applied > 0 ? 'Rp '.number_format($applied, 0, ',', '.') : '—' }}</td>
        <td style="text-align:right;font-weight:700;color:{{ $status === 'menunggu' ? '#fbbf24' : ($status === 'direfund' ? '#34d399' : 'var(--text)') }}">
          @if($status === 'direfund')
            Rp {{ number_format($r->refund_amount, 0, ',', '.') }}
          @else
            Rp {{ number_format($r->depositAvailable(), 0, ',', '.') }}
          @endif
        </td>
        <td style="text-align:center">
          @if($status === 'aktif')
          <span class="badge badge-blue">Sedang Ditahan</span>
          @elseif($status === 'menunggu')
          <span class="badge badge-amber">Menunggu Refund</span>
          @elseif($status === 'direfund')
          <span class="badge badge-green">Sudah Direfund</span>
          @else
          <span class="badge badge-gray">Habis Terpakai</span>
          @endif
        </td>
        <td style="text-align:right;white-space:nowrap">
          <a href="{{ $outlet->route('rentals.show', [$r]) }}"
            style="padding:7px 12px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:12px;font-weight:600;text-decoration:none;display:inline-block">
            Detail
          </a>
          @if($status === 'menunggu')
          <a href="{{ $outlet->route('refunds.index') }}"
            style="padding:7px 12px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:12px;font-weight:600;text-decoration:none;display:inline-block;margin-left:4px">
            Proses Refund
          </a>
          @endif
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
  </div>
  <div style="padding:16px 20px">{{ $rentals->links() }}</div>
  @endif
</div>

</x-outlet-layout>
