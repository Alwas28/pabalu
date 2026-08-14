<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Struk {{ $rental->order_number }}</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }

  /* ── Ukuran kertas thermal ── */
  :root { --paper: 80mm; }   /* ganti 58mm untuk printer 58mm */

  body {
    width: var(--paper);
    margin: 0 auto;
    font-family: 'Courier New', Courier, monospace;
    font-size: 12px;
    color: #000;
    background: #fff;
    padding: 4mm 3mm 8mm;
    line-height: 1.45;
  }

  /* ── Preview di layar ── */
  @media screen {
    html { background: #e5e7eb; padding: 20px 0; }
    body {
      box-shadow: 0 4px 24px rgba(0,0,0,.18);
      border-radius: 4px;
      min-height: 80px;
    }
    .screen-bar {
      position: fixed; top: 0; left: 0; right: 0;
      background: #1e293b;
      padding: 10px 20px;
      display: flex; align-items: center; gap: 12px;
      font-family: sans-serif; font-size: 13px; color: #94a3b8;
      z-index: 100;
    }
    .screen-bar strong { color: #f1f5f9; }
    .btn-print {
      margin-left: auto;
      padding: 7px 18px; border-radius: 8px; border: none;
      background: #e8192c; color: #fff;
      font-size: 13px; font-weight: 700; cursor: pointer;
      font-family: sans-serif;
    }
    .btn-close {
      padding: 7px 14px; border-radius: 8px; border: 1px solid #475569;
      background: transparent; color: #94a3b8;
      font-size: 13px; font-weight: 600; cursor: pointer;
      font-family: sans-serif;
    }
  }

  /* ── Print ── */
  @media print {
    html, body { background: #fff !important; box-shadow: none !important; padding: 0 !important; }
    .screen-bar { display: none !important; }
    @page {
      size: var(--paper) auto;
      margin: 0;
    }
    body { padding: 2mm 2mm 6mm; }
  }

  /* ── Komponen struk ── */
  .center  { text-align: center; }
  .right   { text-align: right; }
  .bold    { font-weight: bold; }
  .big     { font-size: 14px; font-weight: bold; }
  .small   { font-size: 10.5px; }
  .dimmed  { color: #555; }

  .sep-solid  { border: none; border-top: 1px solid #000; margin: 5px 0; }
  .sep-dashed { border: none; border-top: 1px dashed #555; margin: 5px 0; }

  .header-outlet { font-size: 15px; font-weight: bold; text-transform: uppercase; letter-spacing: .04em; }

  .row { display: flex; justify-content: space-between; margin-bottom: 3px; font-size: 12px; }
  .total-row { display: flex; justify-content: space-between; font-size: 14px; font-weight: bold; padding: 4px 0; margin-top: 2px; }
  .pay-row { display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 2px; }

  .footer { text-align: center; font-size: 11px; color: #333; line-height: 1.7; margin-top: 6px; }
  .footer .thank-you { font-size: 12.5px; font-weight: bold; }
  .trx-number { font-size: 9.5px; letter-spacing: .03em; color: #555; }
  .brand-footer { display: flex; justify-content: center; align-items: center; gap: 10px; margin-top: 6px; }
  .brand-logo { height: 26px; object-fit: contain; }
</style>
</head>
<body>

{{-- Preview bar (hanya layar) --}}
<div class="screen-bar">
  <strong>Struk Thermal</strong>
  <span class="dimmed">{{ $rental->order_number }}</span>
  <button class="btn-close" onclick="window.close()">&#10005; Tutup</button>
  <button class="btn-print" onclick="window.print()">&#128438; Cetak Struk</button>
</div>

@php
  $type   = $outlet->outletType;
  $paid   = $rental->paidAmount();
  $sisa   = $rental->remainingAmount();
@endphp

{{-- ══ HEADER OUTLET ══ --}}
<div class="center">
  <div class="header-outlet">{{ $outlet->name }}</div>
  @if($type)
  <div class="small dimmed">{{ $type->name }}</div>
  @endif
  @if($outlet->address)
  <div class="small dimmed" style="margin-top:2px">{{ $outlet->address }}</div>
  @endif
  @if($outlet->phone)
  <div class="small dimmed">Telp: {{ $outlet->phone }}</div>
  @endif
</div>

<hr class="sep-solid">

{{-- ══ INFO SEWA ══ --}}
<div class="small">
  <div class="row"><span class="dimmed">No. Sewa</span><span class="trx-number">{{ $rental->order_number }}</span></div>
  <div class="row"><span class="dimmed">Pelanggan</span><span>{{ $rental->customer->name }}</span></div>
  <div class="row"><span class="dimmed">Barang</span><span>{{ $rental->rentalUnit->rentalItem->name }} ({{ $rental->rentalUnit->code }})</span></div>
  <div class="row"><span class="dimmed">Mulai</span><span>{{ $rental->start_at->translatedFormat('d/m/y H:i') }}</span></div>
  <div class="row"><span class="dimmed">Selesai</span><span>{{ $rental->end_at->translatedFormat('d/m/y H:i') }}</span></div>
</div>

<hr class="sep-dashed">

{{-- ══ RINGKASAN BIAYA ══ --}}
<div class="row"><span>Biaya Sewa</span><span>Rp {{ number_format($rental->total_amount, 0, ',', '.') }}</span></div>
@if($rental->fine_amount > 0)
<div class="row"><span>Denda</span><span>Rp {{ number_format($rental->fine_amount, 0, ',', '.') }}</span></div>
@endif

<hr class="sep-solid">

<div class="total-row"><span>TOTAL</span><span>Rp {{ number_format($rental->total_amount, 0, ',', '.') }}</span></div>

<hr class="sep-solid">

{{-- ══ PEMBAYARAN ══ --}}
@foreach($rental->payments as $p)
<div class="pay-row">
  <span>{{ $p->methodLabel() }}{{ $p->is_fine ? ' (Denda)' : '' }}</span>
  <span>Rp {{ number_format($p->amount, 0, ',', '.') }}</span>
</div>
@endforeach

<div class="row" style="margin-top:4px"><span class="bold">Terbayar</span><span class="bold">Rp {{ number_format($paid, 0, ',', '.') }}</span></div>
<div class="row"><span>Sisa Tagihan</span><span>Rp {{ number_format($sisa, 0, ',', '.') }}</span></div>

@if($rental->notes)
<hr class="sep-dashed">
<div class="small"><span class="dimmed">Catatan:</span> {{ $rental->notes }}</div>
@endif

<hr class="sep-dashed">

{{-- ══ FOOTER ══ --}}
<div class="footer">
  <div class="thank-you">Terima kasih!</div>
  <div>Sampai jumpa kembali di</div>
  <div class="bold">{{ $outlet->name }}</div>
  <div class="brand-footer">
    <img src="/img/Logo%20Viteks%20Hitam.png" alt="Viteks" class="brand-logo">
    <img src="/img/Logo%20Pabalu%20-%20Hitam.png" alt="Pabalu" class="brand-logo">
  </div>
  <div style="font-size:8px;color:#888;margin-top:3px">
    <a href="https://pabalu.id/" style="color:#888;text-decoration:none">pabalu.id</a> didukung oleh viteks.id
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
