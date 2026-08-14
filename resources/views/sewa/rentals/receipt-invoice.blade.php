<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Invoice {{ $rental->order_number }}</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }

  body {
    width: 210mm;
    min-height: 297mm;
    margin: 0 auto;
    font-family: 'Segoe UI', Arial, sans-serif;
    font-size: 13px;
    color: #1a1a1a;
    background: #fff;
    padding: 16mm 18mm;
    line-height: 1.5;
  }

  @media screen {
    html { background: #e5e7eb; padding: 20px 0; }
    body { box-shadow: 0 4px 24px rgba(0,0,0,.18); }
    .screen-bar {
      position: fixed; top: 0; left: 0; right: 0;
      background: #1e293b; padding: 10px 20px;
      display: flex; align-items: center; gap: 12px;
      font-family: sans-serif; font-size: 13px; color: #94a3b8;
      z-index: 100;
    }
    .screen-bar strong { color: #f1f5f9; }
    .btn-print {
      margin-left: auto; padding: 7px 18px; border-radius: 8px; border: none;
      background: #e8192c; color: #fff; font-size: 13px; font-weight: 700; cursor: pointer;
      font-family: sans-serif;
    }
    .btn-close {
      padding: 7px 14px; border-radius: 8px; border: 1px solid #475569;
      background: transparent; color: #94a3b8; font-size: 13px; font-weight: 600; cursor: pointer;
      font-family: sans-serif;
    }
  }

  @media print {
    html, body { background: #fff !important; box-shadow: none !important; padding: 0 !important; }
    .screen-bar { display: none !important; }
    @page { size: A4; margin: 0; }
    body { padding: 16mm 18mm; }
  }

  .invoice-header { display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 16px; border-bottom: 2px solid #1a1a1a; }
  .outlet-name { font-size: 20px; font-weight: 800; text-transform: uppercase; letter-spacing: .02em; }
  .dimmed { color: #666; }
  .small { font-size: 11.5px; }
  .invoice-title { font-size: 26px; font-weight: 800; text-align: right; color: #333; letter-spacing: .05em; }
  .invoice-meta { text-align: right; font-size: 12px; color: #555; margin-top: 6px; }
  .invoice-meta .num { font-weight: 700; color: #1a1a1a; font-family: 'Courier New', monospace; }

  .status-badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; margin-top: 6px; }
  .status-lunas { background: #dcfce7; color: #15803d; }
  .status-dp { background: #fef3c7; color: #92400e; }
  .status-belum { background: #fee2e2; color: #b91c1c; }

  .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin: 24px 0; }
  .info-label { font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #888; margin-bottom: 5px; }
  .info-value { font-size: 13.5px; font-weight: 600; }

  table.items { width: 100%; border-collapse: collapse; margin-top: 8px; }
  table.items th {
    text-align: left; font-size: 10.5px; text-transform: uppercase; letter-spacing: .04em;
    color: #666; padding: 9px 8px; border-bottom: 2px solid #1a1a1a; background: #f8f8f8;
  }
  table.items td { padding: 10px 8px; border-bottom: 1px solid #e5e5e5; font-size: 12.5px; vertical-align: top; }
  table.items .right { text-align: right; }
  table.items .center { text-align: center; }

  .summary { display: flex; justify-content: flex-end; margin-top: 14px; }
  .summary-box { width: 280px; }
  .summary-row { display: flex; justify-content: space-between; padding: 5px 0; font-size: 12.5px; }
  .summary-row.total { border-top: 2px solid #1a1a1a; margin-top: 4px; padding-top: 8px; font-size: 15px; font-weight: 800; }
  .summary-row.paid { color: #15803d; }
  .summary-row.sisa { color: #b91c1c; font-weight: 700; }

  .pay-history { margin-top: 24px; }
  .section-title { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #888; margin-bottom: 8px; border-bottom: 1px solid #ddd; padding-bottom: 5px; }

  .notes-box { margin-top: 20px; padding: 12px 14px; background: #f8f8f8; border-radius: 6px; font-size: 12px; color: #444; }

  .invoice-footer { margin-top: 40px; padding-top: 16px; border-top: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; font-size: 10.5px; color: #999; }
  .brand-footer { display: flex; align-items: center; gap: 10px; }
  .brand-logo { height: 20px; object-fit: contain; opacity: .8; }
</style>
</head>
<body>

<div class="screen-bar">
  <strong>Invoice A4</strong>
  <span class="dimmed">{{ $rental->order_number }}</span>
  <button class="btn-close" onclick="window.close()">&#10005; Tutup</button>
  <button class="btn-print" onclick="window.print()">&#128438; Cetak Invoice</button>
</div>

@php
  $type      = $outlet->outletType;
  $paid      = $rental->paidAmount();
  $sisa      = $rental->remainingAmount();
  $statusLbl = $rental->paymentStatusLabel();
  $statusCls = ['Lunas' => 'status-lunas', 'DP' => 'status-dp', 'Belum Bayar' => 'status-belum'][$statusLbl] ?? 'status-belum';
@endphp

{{-- ══ HEADER ══ --}}
<div class="invoice-header">
  <div>
    <div class="outlet-name">{{ $outlet->name }}</div>
    @if($type)<div class="small dimmed" style="margin-top:2px">{{ $type->name }}</div>@endif
    @if($outlet->address)<div class="small dimmed" style="margin-top:4px;max-width:260px">{{ $outlet->address }}</div>@endif
    @if($outlet->phone)<div class="small dimmed">Telp: {{ $outlet->phone }}</div>@endif
  </div>
  <div>
    <div class="invoice-title">INVOICE</div>
    <div class="invoice-meta">
      No. <span class="num">{{ $rental->order_number }}</span><br>
      Tanggal: {{ $rental->created_at->translatedFormat('d F Y') }}
    </div>
    <div style="text-align:right"><span class="status-badge {{ $statusCls }}">{{ $statusLbl }}</span></div>
  </div>
</div>

{{-- ══ INFO PELANGGAN & SEWA ══ --}}
<div class="info-grid">
  <div>
    <div class="info-label">Pelanggan</div>
    <div class="info-value">{{ $rental->customer->name }}</div>
    @if($rental->customer->phone)<div class="small dimmed" style="margin-top:2px">{{ $rental->customer->phone }}</div>@endif
  </div>
  <div>
    <div class="info-label">Periode Sewa</div>
    <div class="info-value">{{ $rental->start_at->translatedFormat('d M Y, H:i') }} &ndash; {{ $rental->end_at->translatedFormat('d M Y, H:i') }}</div>
  </div>
</div>

{{-- ══ RINCIAN ══ --}}
<table class="items">
  <thead>
    <tr>
      <th>Barang / Unit</th>
      <th class="center">Periode</th>
      <th class="right">Biaya Sewa</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>
        <div style="font-weight:700">{{ $rental->rentalUnit->rentalItem->name }}</div>
        <div class="small dimmed">Unit {{ $rental->rentalUnit->code }}</div>
      </td>
      <td class="center small">
        {{ $rental->start_at->translatedFormat('d/m/Y') }} &ndash; {{ $rental->end_at->translatedFormat('d/m/Y') }}
      </td>
      <td class="right">Rp {{ number_format($rental->total_amount, 0, ',', '.') }}</td>
    </tr>
    @foreach($rental->extensions as $ext)
    <tr>
      <td colspan="2" class="small dimmed">Perpanjangan s/d {{ $ext->new_end_at->translatedFormat('d M Y, H:i') }}</td>
      <td class="right">{{ $ext->additional_amount > 0 ? 'Rp '.number_format($ext->additional_amount, 0, ',', '.') : '—' }}</td>
    </tr>
    @endforeach
    @if($rental->fine_amount > 0)
    <tr>
      <td colspan="2" class="small" style="color:#b91c1c">Denda</td>
      <td class="right" style="color:#b91c1c">Rp {{ number_format($rental->fine_amount, 0, ',', '.') }}</td>
    </tr>
    @endif
  </tbody>
</table>

{{-- ══ RINGKASAN ══ --}}
<div class="summary">
  <div class="summary-box">
    <div class="summary-row total"><span>TOTAL</span><span>Rp {{ number_format($rental->total_amount, 0, ',', '.') }}</span></div>
    <div class="summary-row paid"><span>Terbayar</span><span>Rp {{ number_format($paid, 0, ',', '.') }}</span></div>
    <div class="summary-row sisa"><span>Sisa Tagihan</span><span>Rp {{ number_format($sisa, 0, ',', '.') }}</span></div>
  </div>
</div>

{{-- ══ RIWAYAT PEMBAYARAN ══ --}}
@if($rental->payments->isNotEmpty())
<div class="pay-history">
  <div class="section-title">Riwayat Pembayaran</div>
  <table class="items">
    <thead>
      <tr>
        <th>Tanggal</th>
        <th>Metode</th>
        <th class="right">Jumlah</th>
      </tr>
    </thead>
    <tbody>
      @foreach($rental->payments as $p)
      <tr>
        <td class="small">{{ $p->paid_at->translatedFormat('d M Y, H:i') }}</td>
        <td class="small">{{ $p->methodLabel() }}{{ $p->is_fine ? ' (Denda)' : '' }}</td>
        <td class="right">Rp {{ number_format($p->amount, 0, ',', '.') }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>
@endif

@if($rental->notes)
<div class="notes-box"><strong>Catatan:</strong> {{ $rental->notes }}</div>
@endif

{{-- ══ FOOTER ══ --}}
<div class="invoice-footer">
  <span>Terima kasih atas kepercayaan Anda &mdash; {{ $outlet->name }}</span>
  <div class="brand-footer">
    <img src="/img/Logo%20Viteks%20Hitam.png" alt="Viteks" class="brand-logo">
    <img src="/img/Logo%20Pabalu%20-%20Hitam.png" alt="Pabalu" class="brand-logo">
  </div>
</div>

<script>
  if (window.opener || new URLSearchParams(location.search).get('autoprint') === '1') {
    window.addEventListener('load', () => {
      setTimeout(() => { window.print(); }, 400);
    });
  }
</script>
</body>
</html>
