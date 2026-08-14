<x-outlet-layout :outlet="$outlet" pageTitle="Detail Sewa">

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:18px" class="animate-fadeUp">
  <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--muted)">
    <a href="{{ $outlet->route('rentals.active') }}" style="color:var(--muted);text-decoration:none">
      <i class="fa-solid fa-hourglass-half"></i> Sewa Aktif
    </a>
    <i class="fa-solid fa-chevron-right" style="font-size:10px"></i>
    <span style="color:var(--text);font-weight:500">{{ $rental->order_number }}</span>
  </div>
  <a href="{{ $outlet->route('rentals.receipt', [$rental]) }}?autoprint=1" target="_blank"
    style="display:inline-flex;align-items:center;gap:8px;padding:9px 16px;border-radius:12px;border:1px solid var(--border);background:transparent;color:var(--sub);font-size:13px;font-weight:600;cursor:pointer;text-decoration:none;transition:all .15s"
    onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
    <i class="fa-solid fa-print" style="font-size:11px"></i>
    {{ $outlet->rental_receipt_type === 'invoice' ? 'Cetak Invoice' : 'Cetak Struk' }}
  </a>
</div>

{{-- ── INFO SEWA ── --}}
<div class="card animate-fadeUp">
  <div class="card-header">
    <div>
      <span class="card-title" style="font-family:monospace">{{ $rental->order_number }}</span>
    </div>
    @if($rental->status === 'aktif')
      @if($rental->isOverdue())
      <span class="badge badge-red">Terlambat</span>
      @else
      <span class="badge badge-blue">Aktif</span>
      @endif
    @else
      <span class="badge badge-green">Selesai</span>
    @endif
  </div>
  <div class="card-body" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px">
    <div>
      <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Pelanggan</div>
      <a href="{{ $outlet->route('customers.show', [$rental->customer]) }}" style="font-size:13.5px;font-weight:700;color:var(--ac);text-decoration:none">
        {{ $rental->customer->name }}
      </a>
    </div>
    <div>
      <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Barang / Unit</div>
      <div style="font-size:13.5px;font-weight:700;color:var(--text)">{{ $rental->rentalUnit->rentalItem->name }}</div>
      <div style="font-size:11.5px;color:var(--muted)">{{ $rental->rentalUnit->code }}</div>
    </div>
    <div>
      <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Jenis Sewa</div>
      <div><span class="badge badge-gray">{{ $rental->rentalTypeLabel() }}</span></div>
    </div>
    <div>
      <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Mulai</div>
      <div style="font-size:13.5px;font-weight:700;color:var(--text)">{{ $rental->start_at->translatedFormat('d M Y, H:i') }}</div>
    </div>
    <div>
      <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Selesai (Jatuh Tempo)</div>
      <div style="font-size:13.5px;font-weight:700;color:var(--text)">{{ $rental->end_at->translatedFormat('d M Y, H:i') }}</div>
    </div>
    @if($rental->returned_at)
    <div>
      <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Dikembalikan</div>
      <div style="font-size:13.5px;font-weight:700;color:var(--text)">{{ $rental->returned_at->translatedFormat('d M Y, H:i') }}</div>
    </div>
    @endif
  </div>
  @if($rental->notes)
  <div class="card-body" style="border-top:1px solid var(--border);padding-top:14px">
    <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Catatan</div>
    <div style="font-size:13px;color:var(--sub)">{{ $rental->notes }}</div>
  </div>
  @endif
</div>

{{-- ── PEMBAYARAN ── --}}
<div class="card animate-fadeUp d1">
  <div class="card-header">
    <span class="card-title"><i class="fa-solid fa-money-bill-wave" style="color:var(--ac);margin-right:8px;font-size:14px"></i>Pembayaran</span>
    @php $payStatus = $rental->paymentStatusLabel(); @endphp
    @if($payStatus === 'Lunas')
    <span class="badge badge-green">Lunas</span>
    @elseif($payStatus === 'DP')
    <span class="badge badge-amber">DP</span>
    @else
    <span class="badge badge-red">Belum Bayar</span>
    @endif
  </div>
  <div class="card-body" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:16px">
    <div>
      <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Biaya Sewa</div>
      <div style="font-size:15px;font-weight:800;color:var(--text)">Rp {{ number_format($rental->total_amount, 0, ',', '.') }}</div>
    </div>
    <div>
      <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Terbayar</div>
      <div style="font-size:15px;font-weight:800;color:#34d399">Rp {{ number_format($rental->paidAmount(), 0, ',', '.') }}</div>
    </div>
    <div>
      <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Sisa Tagihan</div>
      <div style="font-size:15px;font-weight:800;color:{{ $rental->remainingAmount() > 0 ? '#f87171' : 'var(--text)' }}">Rp {{ number_format($rental->remainingAmount(), 0, ',', '.') }}</div>
    </div>
    <div>
      <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Deposit</div>
      <div style="font-size:15px;font-weight:800;color:var(--text)">Rp {{ number_format($rental->deposit_amount, 0, ',', '.') }}</div>
    </div>
    @if($rental->fine_amount > 0)
    <div>
      <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Denda</div>
      <div style="font-size:15px;font-weight:800;color:#f87171">Rp {{ number_format($rental->fine_amount, 0, ',', '.') }}</div>
      <div style="font-size:11px;color:var(--muted);margin-top:2px">
        Terbayar Rp {{ number_format($rental->finePaid(), 0, ',', '.') }}
        @if($rental->fineRemaining() > 0)
          &middot; Sisa Rp {{ number_format($rental->fineRemaining(), 0, ',', '.') }}
        @endif
      </div>
    </div>
    @endif
  </div>

  @if($rental->payments->isNotEmpty())
  <div style="overflow-x:auto;border-top:1px solid var(--border)">
    <table class="tbl">
      <thead>
        <tr>
          <th>Tanggal</th>
          <th>Metode</th>
          <th style="text-align:right">Jumlah</th>
          <th>Referensi</th>
          <th style="text-align:center">Bukti</th>
          <th>Catatan</th>
        </tr>
      </thead>
      <tbody>
        @foreach($rental->payments as $p)
        <tr>
          <td style="font-size:12.5px;color:var(--sub)">{{ $p->paid_at->translatedFormat('d M Y, H:i') }}</td>
          <td>
            <span class="badge badge-gray">{{ $p->methodLabel() }}</span>
            @if($p->is_fine)
            <span class="badge badge-red" style="margin-left:4px">Denda</span>
            @endif
          </td>
          <td style="text-align:right;font-weight:700;color:var(--text)">Rp {{ number_format($p->amount, 0, ',', '.') }}</td>
          <td style="font-size:12px;color:var(--muted);font-family:monospace">{{ $p->reference_number ?: '—' }}</td>
          <td style="text-align:center">
            @if($p->photo)
            <a href="{{ Storage::url($p->photo) }}" target="_blank" rel="noopener" style="color:var(--ac)">
              <i class="fa-solid fa-image"></i>
            </a>
            @else
            —
            @endif
          </td>
          <td style="font-size:12px;color:var(--muted)">{{ $p->note ?: '—' }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>

{{-- ── PERPANJANGAN ── --}}
@if($rental->extensions->isNotEmpty())
<div class="card animate-fadeUp d2">
  <div class="card-header">
    <span class="card-title"><i class="fa-solid fa-rotate" style="color:var(--ac);margin-right:8px;font-size:14px"></i>Riwayat Perpanjangan</span>
  </div>
  <div style="overflow-x:auto">
    <table class="tbl">
      <thead>
        <tr>
          <th>Dari</th>
          <th>Menjadi</th>
          <th style="text-align:right">Biaya Tambahan</th>
          <th>Catatan</th>
        </tr>
      </thead>
      <tbody>
        @foreach($rental->extensions as $ext)
        <tr>
          <td>{{ $ext->previous_end_at->translatedFormat('d M Y, H:i') }}</td>
          <td>{{ $ext->new_end_at->translatedFormat('d M Y, H:i') }}</td>
          <td style="text-align:right">{{ $ext->additional_amount > 0 ? 'Rp '.number_format($ext->additional_amount, 0, ',', '.') : '—' }}</td>
          <td style="font-size:12px;color:var(--muted)">{{ $ext->notes ?: '—' }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endif

{{-- ── KONDISI PENGEMBALIAN ── --}}
@if($rental->status === 'selesai' && $rental->condition_note)
<div class="card animate-fadeUp d3">
  <div class="card-header">
    <span class="card-title"><i class="fa-solid fa-clipboard-check" style="color:var(--ac);margin-right:8px;font-size:14px"></i>Kondisi Saat Pengembalian</span>
  </div>
  <div class="card-body">
    <p style="font-size:13px;color:var(--sub);line-height:1.6">{{ $rental->condition_note }}</p>
  </div>
</div>
@endif

</x-outlet-layout>
