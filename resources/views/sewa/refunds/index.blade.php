<x-outlet-layout :outlet="$outlet" pageTitle="Refund">

{{-- ── HEADER ── --}}
<div class="animate-fadeUp">
  <h2 class="font-display" style="font-size:20px;font-weight:700;color:var(--text)">Refund</h2>
  <p style="font-size:13px;color:var(--muted);margin-top:2px">Pengembalian deposit ke pelanggan pada outlet <strong style="color:var(--sub)">{{ $outlet->name }}</strong></p>
</div>

{{-- ── STATS ── --}}
<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px" class="animate-fadeUp d1">
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(251,191,36,.15);color:#fbbf24"><i class="fa-solid fa-hourglass-half"></i></div>
    <div><div class="stat-num">Rp {{ number_format($totalPending, 0, ',', '.') }}</div><div class="stat-label">Menunggu Refund ({{ $pending->count() }})</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(52,211,153,.15);color:#34d399"><i class="fa-solid fa-circle-check"></i></div>
    <div><div class="stat-num">Rp {{ number_format($totalDone, 0, ',', '.') }}</div><div class="stat-label">Sudah Direfund ({{ $done->count() }})</div></div>
  </div>
</div>

{{-- ── TABS ── --}}
<div style="display:flex;gap:8px" class="animate-fadeUp d2">
  <a href="{{ $outlet->route('refunds.index', ['status' => 'pending']) }}"
    style="padding:9px 16px;border-radius:10px;font-size:13px;font-weight:700;text-decoration:none;{{ $status === 'pending' ? 'background:var(--ac-lt);color:var(--ac)' : 'background:var(--surface2);color:var(--sub)' }}">
    Menunggu Refund
  </a>
  <a href="{{ $outlet->route('refunds.index', ['status' => 'done']) }}"
    style="padding:9px 16px;border-radius:10px;font-size:13px;font-weight:700;text-decoration:none;{{ $status === 'done' ? 'background:var(--ac-lt);color:var(--ac)' : 'background:var(--surface2);color:var(--sub)' }}">
    Sudah Direfund
  </a>
</div>

{{-- ── TABLE ── --}}
<div class="card animate-fadeUp d3">
  <div class="card-header">
    <span class="card-title"><i class="fa-solid fa-rotate-left" style="color:var(--ac);margin-right:8px;font-size:14px"></i>
      {{ $status === 'done' ? 'Riwayat Refund' : 'Menunggu Diproses' }}
    </span>
  </div>

  @if($rentals->isEmpty())
  <div style="padding:56px 24px;text-align:center">
    <i class="fa-solid fa-rotate-left" style="font-size:26px;opacity:.4;display:block;margin-bottom:10px;color:var(--muted)"></i>
    <p style="font-size:13.5px;color:var(--muted)">{{ $status === 'done' ? 'Belum ada refund yang diproses.' : 'Tidak ada refund yang menunggu diproses.' }}</p>
  </div>
  @else
  <div style="overflow-x:auto">
  <table class="tbl">
    <thead>
      @if($status === 'done')
      <tr>
        <th>Tanggal Refund</th>
        <th>No. Transaksi</th>
        <th>Pelanggan</th>
        <th>Barang / Unit</th>
        <th style="text-align:right">Jumlah Direfund</th>
      </tr>
      @else
      <tr>
        <th>Tanggal Selesai</th>
        <th>No. Transaksi</th>
        <th>Pelanggan</th>
        <th>Barang / Unit</th>
        <th style="text-align:right">Deposit</th>
        <th style="text-align:right">Denda Belum Dibayar</th>
        <th style="text-align:right">Refund</th>
        <th style="text-align:right">Aksi</th>
      </tr>
      @endif
    </thead>
    <tbody>
      @foreach($rentals as $r)
      @if($status === 'done')
      <tr>
        <td style="font-size:12.5px;color:var(--sub)">{{ $r->refunded_at->translatedFormat('d M Y, H:i') }}</td>
        <td class="td-main" style="font-family:monospace;font-size:12px">{{ $r->order_number }}</td>
        <td>{{ $r->customer->name }}</td>
        <td>
          {{ $r->rentalUnit->rentalItem->name }}
          <div style="font-size:11px;color:var(--muted)">{{ $r->rentalUnit->code }}</div>
        </td>
        <td style="text-align:right;font-weight:700;color:#34d399">Rp {{ number_format($r->refund_amount, 0, ',', '.') }}</td>
      </tr>
      @else
      <tr>
        <td style="font-size:12.5px;color:var(--sub)">{{ $r->returned_at?->translatedFormat('d M Y, H:i') ?? '—' }}</td>
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
        <td style="text-align:right">Rp {{ number_format($r->deposit_amount, 0, ',', '.') }}</td>
        <td style="text-align:right;color:{{ $r->fineRemaining() > 0 ? '#f87171' : 'var(--muted)' }}">Rp {{ number_format($r->fineRemaining(), 0, ',', '.') }}</td>
        <td style="text-align:right;font-weight:700;color:var(--text)">Rp {{ number_format($r->depositBalance(), 0, ',', '.') }}</td>
        <td style="text-align:right">
          <button type="button" class="btn-save" style="padding:7px 14px;font-size:12px"
            onclick="openRefundConfirm({{ $r->id }}, {{ json_encode($r->order_number) }}, {{ json_encode($r->customer->name) }}, {{ $r->depositBalance() }})">
            <i class="fa-solid fa-check" style="margin-right:5px"></i>Tandai Sudah Refund
          </button>
        </td>
      </tr>
      @endif
      @endforeach
    </tbody>
  </table>
  </div>
  @endif
</div>

{{-- ══ MODAL KONFIRMASI REFUND ══ --}}
<div class="modal-backdrop" id="modal-refund" onclick="if(event.target===this)closeModal('modal-refund')">
  <div class="modal-box" style="max-width:400px">
    <div style="padding:24px 24px 0;text-align:center">
      <div style="width:56px;height:56px;border-radius:50%;background:rgba(52,211,153,.12);display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
        <i class="fa-solid fa-hand-holding-dollar" style="font-size:22px;color:#34d399"></i>
      </div>
      <h3 class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">Tandai Refund Selesai?</h3>
      <p style="font-size:13px;color:var(--muted);margin-top:6px;line-height:1.6">
        Pastikan Anda sudah benar-benar menyerahkan dana ini ke pelanggan sebelum menandainya di sini.
      </p>
    </div>

    <div style="margin:18px 24px 0;padding:14px 16px;border-radius:12px;background:var(--surface2);border:1px solid var(--border)">
      <div style="display:flex;justify-content:space-between;font-size:12.5px;color:var(--sub);margin-bottom:6px">
        <span>Transaksi</span>
        <strong id="rc-order" style="color:var(--text);font-family:monospace"></strong>
      </div>
      <div style="display:flex;justify-content:space-between;font-size:12.5px;color:var(--sub);margin-bottom:10px">
        <span>Pelanggan</span>
        <strong id="rc-customer" style="color:var(--text)"></strong>
      </div>
      <div style="border-top:1px solid var(--border);padding-top:10px;text-align:center">
        <div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.6px">Jumlah Refund</div>
        <div class="font-display" id="rc-amount" style="font-size:26px;font-weight:800;color:#34d399;margin-top:4px">Rp 0</div>
      </div>
    </div>

    <form id="form-refund-confirm" method="POST" action="">
      @csrf
      <div style="padding:20px 24px 24px;display:flex;gap:10px">
        <button type="button" onclick="closeModal('modal-refund')"
          style="flex:1;padding:11px;border-radius:12px;border:1px solid var(--border);background:transparent;color:var(--sub);font-size:13.5px;font-weight:600;cursor:pointer;font-family:inherit">
          Batal
        </button>
        <button type="submit"
          style="flex:1;display:flex;align-items:center;justify-content:center;gap:8px;padding:11px;background:linear-gradient(135deg,#34d399,#10b981);color:#fff;border:none;border-radius:12px;font-size:13.5px;font-weight:800;cursor:pointer;font-family:'Clash Display',sans-serif">
          <i class="fa-solid fa-circle-check"></i> Konfirmasi
        </button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
const outletId = {{ $outlet->id }};
const outletRp = '{{ $outlet->rp() }}';

function openRefundConfirm(id, orderNumber, customerName, amount) {
  document.getElementById('rc-order').textContent = orderNumber;
  document.getElementById('rc-customer').textContent = customerName;
  document.getElementById('rc-amount').textContent = 'Rp ' + Number(amount).toLocaleString('id-ID');
  document.getElementById('form-refund-confirm').action = `/${outletRp}/${outletId}/refunds/${id}/mark-refunded`;
  openModal('modal-refund');
}
</script>
@endpush

</x-outlet-layout>
