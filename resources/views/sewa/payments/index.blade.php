<x-outlet-layout :outlet="$outlet" pageTitle="Pembayaran">

{{-- ── HEADER ── --}}
<div class="animate-fadeUp">
  <h2 class="font-display" style="font-size:20px;font-weight:700;color:var(--text)">Pembayaran</h2>
  <p style="font-size:13px;color:var(--muted);margin-top:2px">Riwayat pembayaran sewa outlet <strong style="color:var(--sub)">{{ $outlet->name }}</strong></p>
</div>

{{-- ── STATS ── --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px" class="animate-fadeUp d1">
  <div class="stat-card">
    <div class="stat-icon" style="background:var(--ac-lt);color:var(--ac)"><i class="fa-solid fa-money-bill-wave"></i></div>
    <div><div class="stat-num">Rp {{ number_format($totalToday, 0, ',', '.') }}</div><div class="stat-label">Diterima Hari Ini</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(96,165,250,.15);color:#60a5fa"><i class="fa-solid fa-receipt"></i></div>
    <div><div class="stat-num">{{ $payments->total() }}</div><div class="stat-label">Total Transaksi{{ ($from || $to) ? ' (Filter)' : '' }}</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(52,211,153,.15);color:#34d399"><i class="fa-solid fa-sack-dollar"></i></div>
    <div><div class="stat-num">Rp {{ number_format($totalFiltered, 0, ',', '.') }}</div><div class="stat-label">Total Nominal{{ ($from || $to) ? ' (Filter)' : '' }}</div></div>
  </div>
</div>

{{-- ── FILTER TANGGAL ── --}}
<div class="card animate-fadeUp d2" style="padding:14px 18px">
  <form method="GET" action="{{ $outlet->route('payments.index') }}" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
    <div>
      <label class="f-label">Dari Tanggal</label>
      <input type="date" name="from" class="f-input" value="{{ $from }}" max="{{ now()->toDateString() }}">
    </div>
    <div>
      <label class="f-label">Sampai Tanggal</label>
      <input type="date" name="to" class="f-input" value="{{ $to }}" max="{{ now()->toDateString() }}">
    </div>
    <button type="submit" class="btn-save" style="padding:9px 18px">
      <i class="fa-solid fa-filter" style="margin-right:6px"></i>Terapkan
    </button>
    @if($from || $to)
    <a href="{{ $outlet->route('payments.index') }}"
      style="padding:9px 16px;border-radius:10px;border:1px solid var(--border);background:transparent;color:var(--sub);font-size:13px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center">
      Reset
    </a>
    @endif
  </form>
</div>

{{-- ── TABLE ── --}}
<div class="card animate-fadeUp d3">
  <div class="card-header">
    <span class="card-title"><i class="fa-solid fa-money-bill-wave" style="color:var(--ac);margin-right:8px;font-size:14px"></i>Riwayat Pembayaran</span>
  </div>

  @if($payments->isEmpty())
  <div style="padding:56px 24px;text-align:center">
    <i class="fa-solid fa-money-bill-wave" style="font-size:26px;opacity:.4;display:block;margin-bottom:10px;color:var(--muted)"></i>
    <p style="font-size:13.5px;color:var(--muted)">Belum ada pembayaran tercatat.</p>
  </div>
  @else
  <div style="overflow-x:auto">
  <table class="tbl">
    <thead>
      <tr>
        <th>Tanggal</th>
        <th>No. Transaksi</th>
        <th>Pelanggan</th>
        <th style="text-align:right">Jumlah</th>
        <th style="text-align:right">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @foreach($payments as $p)
      <tr>
        <td style="font-size:12.5px;color:var(--sub)">{{ $p->paid_at->translatedFormat('d M Y, H:i') }}</td>
        <td class="td-main" style="font-family:monospace;font-size:12px">
          {{ $p->rentalTransaction->order_number }}
          @if($p->is_fine)
          <span class="badge badge-red" style="margin-left:4px">Denda</span>
          @endif
        </td>
        <td>{{ $p->rentalTransaction->customer->name }}</td>
        <td style="text-align:right;font-weight:700;color:var(--text)">Rp {{ number_format($p->amount, 0, ',', '.') }}</td>
        <td style="text-align:right;white-space:nowrap">
          <a href="{{ $outlet->route('payments.show', [$p]) }}"
            style="display:inline-flex;align-items:center;gap:6px;padding:7px 12px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:12px;font-weight:600;text-decoration:none">
            Detail
          </a>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
  </div>
  <div style="padding:16px 20px">{{ $payments->links() }}</div>
  @endif
</div>

</x-outlet-layout>