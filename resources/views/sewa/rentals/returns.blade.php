<x-outlet-layout :outlet="$outlet" pageTitle="Pengembalian">

{{-- ── HEADER ── --}}
<div class="animate-fadeUp">
  <h2 class="font-display" style="font-size:20px;font-weight:700;color:var(--text)">Pengembalian</h2>
  <p style="font-size:13px;color:var(--muted);margin-top:2px">Proses pengembalian barang, pemeriksaan kondisi, dan penyelesaian sewa</p>
</div>

{{-- ── TABLE ── --}}
<div class="card animate-fadeUp d1">
  <div class="card-header">
    <span class="card-title"><i class="fa-solid fa-right-left" style="color:var(--ac);margin-right:8px;font-size:14px"></i>Sewa Menunggu Pengembalian</span>
  </div>

  @if($rentals->isEmpty())
  <div style="padding:56px 24px;text-align:center">
    <i class="fa-solid fa-key" style="font-size:26px;opacity:.4;display:block;margin-bottom:10px;color:var(--muted)"></i>
    <p style="font-size:13.5px;color:var(--muted)">Tidak ada sewa yang sedang aktif.</p>
  </div>
  @else
  <div style="overflow-x:auto">
  <table class="tbl">
    <thead>
      <tr>
        <th>No. Transaksi</th>
        <th>Pelanggan</th>
        <th>Barang / Unit</th>
        <th>Jatuh Tempo</th>
        <th style="text-align:right">Deposit</th>
        <th style="text-align:center">Status</th>
        <th style="text-align:right">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @foreach($rentals as $r)
      <tr>
        <td class="td-main" style="font-family:monospace;font-size:12px">{{ $r->order_number }}</td>
        <td>{{ $r->customer->name }}</td>
        <td>
          {{ $r->rentalUnit->rentalItem->name }}
          <div style="font-size:11px;color:var(--muted)">{{ $r->rentalUnit->code }}</div>
        </td>
        <td>{{ $r->end_at->translatedFormat('d M Y, H:i') }}</td>
        <td style="text-align:right">{{ $r->depositAvailable() > 0 ? 'Rp '.number_format($r->depositAvailable(), 0, ',', '.') : '—' }}</td>
        <td style="text-align:center">
          @if($r->isOverdue())
          <span class="badge badge-red">Terlambat</span>
          @elseif($r->end_at->isToday())
          <span class="badge badge-amber">Jatuh Tempo</span>
          @else
          <span class="badge badge-blue">Aktif</span>
          @endif
        </td>
        <td style="text-align:right">
          <button type="button"
            onclick="openReturn({{ $r->id }}, {{ json_encode($r->order_number) }}, {{ $r->depositAvailable() }}, {{ $r->remainingAmount() }})"
            style="padding:7px 14px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);cursor:pointer;color:var(--sub);font-size:12px;font-weight:600">
            <i class="fa-solid fa-right-left" style="margin-right:5px"></i>Proses Pengembalian
          </button>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
  </div>
  @endif
</div>

@include('sewa.rentals._return-modal')

@push('scripts')
<script>
const outletId = {{ $outlet->id }};
const outletRp = '{{ $outlet->rp() }}';

function fmtRp(n) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(n); }

@if($errors->any())
  openModal('modal-return');
@endif
</script>
@endpush

</x-outlet-layout>
