<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Struk {{ $transaction->transaction_number }}</title>
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
    .brand-logo { filter: brightness(0) !important; }
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

  .item-row { margin-bottom: 4px; }
  .item-name { word-break: break-word; }
  .item-detail {
    display: flex; justify-content: space-between;
    padding-left: 2px;
    font-size: 11.5px;
  }
  .item-detail .qty-price { color: #444; }
  .item-detail .subtotal  { font-weight: bold; }

  .summary-row {
    display: flex; justify-content: space-between;
    margin-bottom: 3px; font-size: 12px;
  }
  .total-row {
    display: flex; justify-content: space-between;
    font-size: 14px; font-weight: bold;
    padding: 4px 0; margin-top: 2px;
  }
  .pay-row {
    display: flex; justify-content: space-between;
    font-size: 12px; margin-bottom: 2px;
  }
  .change-row {
    display: flex; justify-content: space-between;
    font-size: 12.5px; font-weight: bold;
  }

  .footer { text-align: center; font-size: 11px; color: #333; line-height: 1.7; margin-top: 6px; }
  .footer .thank-you { font-size: 12.5px; font-weight: bold; }
  .trx-number { font-size: 9.5px; letter-spacing: .03em; color: #555; }
  .brand-footer { display: flex; justify-content: center; align-items: center; gap: 10px; margin-top: 6px; }
  .brand-logo { height: 26px; object-fit: contain; }

  /* QR placeholder (opsional) */
  .qr-box {
    width: 60px; height: 60px; border: 1px solid #aaa;
    margin: 6px auto;
    display: flex; align-items: center; justify-content: center;
    font-size: 8px; color: #aaa; text-align: center;
  }
</style>
</head>
<body>

{{-- Preview bar (hanya layar) --}}
<div class="screen-bar">
  <strong>Struk Thermal</strong>
  <span class="dimmed">{{ $transaction->transaction_number }}</span>
  <button class="btn-close" onclick="window.close()">&#10005; Tutup</button>
  <button class="btn-print" onclick="window.print()">&#128438; Cetak Struk</button>
</div>

@php
  $type       = $outlet->outletType;
  $isVoided   = $transaction->status === 'voided';
  $payLabels  = ['cash'=>'Tunai','qris'=>'QRIS','transfer'=>'Transfer Bank','card'=>'Kartu Debit/Kredit'];
  $payLabel   = $payLabels[$transaction->payment_method] ?? $transaction->payment_method;
  $hasDisc    = $transaction->discount_amount > 0;
  $hasCash    = $transaction->payment_method === 'cash';
  $typeCode   = $type?->type_code ?? '??';
  $outCode    = $outlet->code ?? '????';
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

{{-- VOID banner --}}
@if($isVoided)
<div class="center bold" style="font-size:13px;letter-spacing:.1em;padding:3px 0">
  *** TRANSAKSI VOID ***
</div>
<hr class="sep-dashed">
@endif

{{-- ══ INFO TRANSAKSI ══ --}}
<div class="small">
  <div style="display:flex;justify-content:space-between">
    <span class="dimmed">No. Struk</span>
    <span class="trx-number">{{ $transaction->transaction_number }}</span>
  </div>
  <div style="display:flex;justify-content:space-between;margin-top:2px">
    <span class="dimmed">Tanggal</span>
    <span>{{ $transaction->date->format('d/m/Y') }}</span>
  </div>
  <div style="display:flex;justify-content:space-between;margin-top:2px">
    <span class="dimmed">Jam</span>
    <span>{{ $transaction->created_at->format('H:i:s') }}</span>
  </div>
  <div style="display:flex;justify-content:space-between;margin-top:2px">
    <span class="dimmed">Kasir</span>
    <span>{{ $transaction->user?->name ?? '—' }}</span>
  </div>
</div>

<hr class="sep-dashed">

{{-- ══ ITEM BELANJA ══ --}}
@foreach($transaction->items as $item)
<div class="item-row">
  <div class="item-name bold">{{ $item->product_name }}</div>
  <div class="item-detail">
    <span class="qty-price">{{ $item->qty }} x Rp {{ number_format($item->product_price, 0, ',', '.') }}</span>
    <span class="subtotal">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
  </div>
</div>
@endforeach

<hr class="sep-dashed">

{{-- ══ RINGKASAN ══ --}}
<div class="summary-row">
  <span>Subtotal ({{ $transaction->items->count() }} item)</span>
  <span>Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</span>
</div>

@if($hasDisc)
<div class="summary-row" style="color:#333">
  <span>Diskon{{ $transaction->discount_percent > 0 ? ' ('.$transaction->discount_percent.'%)' : '' }}</span>
  <span>-Rp {{ number_format($transaction->discount_amount, 0, ',', '.') }}</span>
</div>
@endif

<hr class="sep-solid">

<div class="total-row">
  <span>TOTAL</span>
  <span>Rp {{ number_format($transaction->total, 0, ',', '.') }}</span>
</div>

<hr class="sep-solid">

{{-- ══ PEMBAYARAN ══ --}}
<div class="pay-row">
  <span>{{ $payLabel }}</span>
  <span>Rp {{ number_format($transaction->payment_amount, 0, ',', '.') }}</span>
</div>
@if($hasCash && $transaction->change_amount > 0)
<div class="change-row">
  <span>Kembali</span>
  <span>Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</span>
</div>
@endif

@if($transaction->notes)
<hr class="sep-dashed">
<div class="small"><span class="dimmed">Catatan:</span> {{ $transaction->notes }}</div>
@endif

<hr class="sep-dashed">

{{-- ══ FOOTER ══ --}}
<div class="footer">
  <div class="thank-you">Terima kasih!</div>
  <div>Sampai jumpa kembali di</div>
  <div class="bold">{{ $outlet->name }}</div>
  <div style="margin-top:5px;font-size:9.5px;color:#888">
    {{ $typeCode }}{{ $outCode }}
  </div>
  <div class="brand-footer">
    <img src="/img/stempel.png" alt="Viteks" class="brand-logo">
    <img src="/img/logo-pabalu.png" alt="Pabalu" class="brand-logo">
  </div>
  <div style="font-size:8px;color:#888;margin-top:3px">
    <a href="https://pabalu.id/" style="color:#888;text-decoration:none">pabalu.id</a> didukung oleh viteks.id
  </div>
</div>

<script>
  // Auto-print jika dibuka sebagai popup dari POS/struk tombol
  if (window.opener || new URLSearchParams(location.search).get('autoprint') === '1') {
    window.addEventListener('load', () => {
      setTimeout(() => { window.print(); }, 400);
    });
  }
</script>
</body>
</html>
