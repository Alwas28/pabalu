<x-outlet-layout :outlet="$outlet" pageTitle="Tagihan">

@php
  $bulanLabel = ['1'=>'Januari','2'=>'Februari','3'=>'Maret','4'=>'April','5'=>'Mei','6'=>'Juni','7'=>'Juli','8'=>'Agustus','9'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];
@endphp

@if(session('success'))
<div style="padding:12px 16px;border-radius:12px;background:rgba(52,211,153,.12);border:1px solid rgba(52,211,153,.3);
     display:flex;align-items:center;gap:10px;font-size:12.5px;color:#34d399;font-weight:600">
  <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div style="padding:12px 16px;border-radius:12px;background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.3);
     display:flex;align-items:center;gap:10px;font-size:12.5px;color:#f87171;font-weight:600">
  <i class="fa-solid fa-circle-xmark"></i> {{ session('error') }}
</div>
@endif

<div class="card" style="margin-top:16px">
  <div class="card-header">
    <div class="card-title"><i class="fa-solid fa-file-invoice-dollar a-text" style="margin-right:8px"></i>Tagihan Outlet</div>
  </div>

  @if($invoices->isEmpty())
  <div style="padding:56px 24px;text-align:center">
    <div style="width:60px;height:60px;border-radius:16px;background:var(--surface2);display:grid;place-items:center;margin:0 auto 16px;font-size:22px;color:var(--muted)">
      <i class="fa-solid fa-file-invoice-dollar"></i>
    </div>
    <div style="font-size:15px;font-weight:600;color:var(--text);margin-bottom:6px">Belum Ada Tagihan</div>
    <p style="font-size:13px;color:var(--muted)">Tagihan yang diinput admin untuk outlet ini akan muncul di sini.</p>
  </div>
  @else
  <div style="overflow-x:auto">
    <table class="tbl">
      <thead>
        <tr>
          <th>Periode</th>
          <th style="text-align:right">Nominal Tagihan</th>
          <th>Catatan</th>
          <th style="text-align:center;width:100px">Status</th>
          <th style="text-align:center;width:150px">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @foreach($invoices as $inv)
        <tr>
          <td class="td-main">
            {{ $inv->period_type === 'bulan' ? $bulanLabel[(string) $inv->period_start->month] . ' ' . $inv->period_start->year : 'Tahun ' . $inv->period_start->year }}
          </td>
          <td style="text-align:right;font-size:13px;font-weight:700;color:var(--text)">Rp {{ number_format($inv->amount, 0, ',', '.') }}</td>
          <td style="font-size:12px;color:var(--muted);max-width:220px">{{ $inv->note ?? '—' }}</td>
          <td style="text-align:center">
            @if($inv->status === 'lunas')
              <span class="badge badge-green"><i class="fa-solid fa-circle" style="font-size:6px"></i>Lunas</span>
            @else
              <span class="badge badge-gray"><i class="fa-solid fa-circle" style="font-size:6px"></i>Belum Lunas</span>
            @endif
          </td>
          <td style="text-align:center">
            @if($inv->status !== 'lunas')
            <button type="button" onclick="openPayInvoice({{ $inv->id }})"
              style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:9px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:12px;font-weight:600;cursor:pointer;font-family:inherit">
              <i class="fa-solid fa-key"></i> Bayar dengan Kode
            </button>
            @else
              <span style="font-size:11.5px;color:var(--muted)">
                {{ $inv->paid_at?->format('d/m/Y') }}
              </span>
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>

<p style="font-size:11px;color:var(--muted);line-height:1.6;max-width:700px">
  Tagihan dilunaskan lewat 2 cara: admin menandai lunas secara langsung setelah menerima pembayaran, atau Anda
  masukkan kode pelunasan yang diberikan admin lewat tombol di atas.
</p>

{{-- Modal Bayar dengan Kode --}}
<div class="modal-backdrop" id="modal-pay-invoice" onclick="if(event.target===this)closeModal('modal-pay-invoice')">
  <div class="modal-box" style="max-width:400px">
    <form id="form-pay-invoice" method="POST" action="">
      @csrf
      <div style="padding:20px 24px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <h3 class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">Bayar dengan Kode</h3>
        <button type="button" onclick="closeModal('modal-pay-invoice')"
          style="width:30px;height:30px;border-radius:8px;border:none;background:var(--surface2);cursor:pointer;color:var(--sub);font-size:14px">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <div style="padding:20px 24px;display:flex;flex-direction:column;gap:14px">
        <div>
          <label class="f-label">Kode Pelunasan dari Admin</label>
          <input name="code" class="f-input" placeholder="cth: LUNAS-XXXX-XXXX" style="text-transform:uppercase;letter-spacing:1px" maxlength="40" required>
        </div>
      </div>
      <div style="padding:16px 24px 20px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
        <button type="button" onclick="closeModal('modal-pay-invoice')"
          style="padding:9px 18px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
          Batal
        </button>
        <button type="submit"
          style="padding:9px 20px;border-radius:10px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
          Lunaskan
        </button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
const payUrlTemplate = "{{ route('outlet.tagihan.redeem', [$outlet, '__INVOICE__']) }}";

function openPayInvoice(invoiceId) {
  document.getElementById('form-pay-invoice').action = payUrlTemplate.replace('__INVOICE__', invoiceId);
  openModal('modal-pay-invoice');
}
</script>
@endpush

</x-outlet-layout>
