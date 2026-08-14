<x-outlet-layout :outlet="$outlet" pageTitle="Detail Pembayaran">

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:18px" class="animate-fadeUp">
  <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--muted)">
    <a href="{{ $outlet->route('payments.index') }}" style="color:var(--muted);text-decoration:none">
      <i class="fa-solid fa-money-bill-wave"></i> Pembayaran
    </a>
    <i class="fa-solid fa-chevron-right" style="font-size:10px"></i>
    <span style="color:var(--text);font-weight:500">Detail Pembayaran</span>
  </div>
  <a href="{{ $outlet->route('rentals.receipt', [$payment->rentalTransaction]) }}?autoprint=1" target="_blank"
    style="display:inline-flex;align-items:center;gap:8px;padding:9px 16px;border-radius:12px;border:1px solid var(--border);background:transparent;color:var(--sub);font-size:13px;font-weight:600;cursor:pointer;text-decoration:none;transition:all .15s"
    onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
    <i class="fa-solid fa-print" style="font-size:11px"></i>
    {{ $outlet->rental_receipt_type === 'invoice' ? 'Cetak Invoice' : 'Cetak Struk' }}
  </a>
</div>

{{-- ── RINGKASAN PEMBAYARAN ── --}}
<div class="card animate-fadeUp">
  <div class="card-header">
    <span class="card-title">Pembayaran</span>
    @if($payment->is_fine)
    <span class="badge badge-red">Denda</span>
    @endif
  </div>
  <div class="card-body" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px">
    <div>
      <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Jumlah</div>
      <div style="font-size:20px;font-weight:800;color:var(--text)">Rp {{ number_format($payment->amount, 0, ',', '.') }}</div>
    </div>
    <div>
      <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Tanggal</div>
      <div style="font-size:13.5px;font-weight:600;color:var(--text)">{{ $payment->paid_at->translatedFormat('d M Y, H:i') }}</div>
    </div>
    <div>
      <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Metode</div>
      <div><span class="badge badge-gray">{{ $payment->methodLabel() }}</span></div>
    </div>
    <div>
      <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">No. Referensi</div>
      <div style="font-size:13.5px;font-weight:600;color:var(--text);font-family:monospace">{{ $payment->reference_number ?: '—' }}</div>
    </div>
  </div>

  @if($payment->photo)
  <div style="padding:0 20px 20px">
    <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px">Foto Bukti</div>
    <a href="{{ Storage::url($payment->photo) }}" target="_blank" rel="noopener">
      <img src="{{ Storage::url($payment->photo) }}" alt="Bukti pembayaran" style="max-width:280px;max-height:280px;border-radius:12px;border:1px solid var(--border);object-fit:contain">
    </a>
  </div>
  @endif

  @if($payment->note)
  <div style="padding:0 20px 20px">
    <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Catatan</div>
    <p style="font-size:13px;color:var(--sub);line-height:1.6">{{ $payment->note }}</p>
  </div>
  @endif
</div>

{{-- ── TRANSAKSI TERKAIT ── --}}
<div class="card animate-fadeUp d1">
  <div class="card-header">
    <span class="card-title">Transaksi Sewa</span>
  </div>
  <div class="card-body" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px">
    <div>
      <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">No. Transaksi</div>
      <a href="{{ $outlet->route('rentals.show', [$payment->rentalTransaction]) }}" style="font-size:13.5px;font-weight:700;color:var(--ac);text-decoration:none;font-family:monospace">
        {{ $payment->rentalTransaction->order_number }}
      </a>
    </div>
    <div>
      <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Pelanggan</div>
      <a href="{{ $outlet->route('customers.show', [$payment->rentalTransaction->customer]) }}" style="font-size:13.5px;font-weight:700;color:var(--ac);text-decoration:none">
        {{ $payment->rentalTransaction->customer->name }}
      </a>
    </div>
    <div>
      <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Barang / Unit</div>
      <div style="font-size:13.5px;font-weight:700;color:var(--text)">{{ $payment->rentalTransaction->rentalUnit->rentalItem->name }}</div>
      <div style="font-size:11.5px;color:var(--muted)">{{ $payment->rentalTransaction->rentalUnit->code }}</div>
    </div>
  </div>
  <div style="padding:0 20px 20px">
    <a href="{{ $outlet->route('rentals.show', [$payment->rentalTransaction]) }}"
      style="padding:9px 16px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13px;font-weight:600;text-decoration:none;display:inline-block">
      <i class="fa-solid fa-arrow-up-right-from-square" style="margin-right:6px;font-size:11px"></i>Lihat Detail Sewa
    </a>
  </div>
</div>

</x-outlet-layout>
