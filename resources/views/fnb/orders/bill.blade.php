<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Struk Pesanan — {{ $order->order_number }}</title>
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<style>
  :root { --paper: 80mm; }
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'Courier New', Courier, monospace; font-size: 12px; background: #f5f5f5; }

  @media screen {
    body { display: flex; flex-direction: column; align-items: center; min-height: 100vh; padding: 20px; background: #f0f0f0; }
    .screen-bar {
      width: var(--paper); max-width: 100%;
      background: #fff; border-radius: 8px 8px 0 0;
      padding: 10px 12px;
      display: flex; gap: 8px; align-items: center;
      border: 1px solid #ddd; border-bottom: none;
      font-family: -apple-system, sans-serif; font-size: 13px;
    }
    .screen-btn {
      padding: 6px 14px; border-radius: 6px; border: 1px solid #ccc;
      cursor: pointer; font-size: 12px; font-weight: 600;
    }
    .screen-btn.primary { background: #f59e0b; color: #fff; border-color: #f59e0b; }
    .screen-btn.secondary { background: transparent; color: #666; }
    .ticket { background: #fff; border: 1px solid #ddd; border-radius: 0 0 8px 8px; }
  }

  @media print {
    body { background: #fff; display: block; padding: 0; }
    .screen-bar { display: none !important; }
    .ticket { border: none; border-radius: 0; }
    @page { size: var(--paper) auto; margin: 0; }
  }

  .ticket { width: var(--paper); max-width: 100%; padding: 8mm 6mm; }

  .center { text-align: center; }
  .bold   { font-weight: bold; }
  .lg     { font-size: 18px; font-weight: bold; }
  .sm     { font-size: 10px; }
  .muted  { color: #666; }
  .divider { border: none; border-top: 1px dashed #999; margin: 6px 0; }
  .divider-solid { border: none; border-top: 2px solid #000; margin: 6px 0; }

  .item-line { display: flex; justify-content: space-between; font-size: 12px; margin: 3px 0; }
  .item-line .name { flex: 1; padding-right: 6px; }
  .item-line .amt  { white-space: nowrap; font-weight: bold; }
  .item-sub { font-size: 10.5px; color: #666; margin-left: 0; }

  .total-row { display: flex; justify-content: space-between; font-size: 14px; font-weight: bold; margin-top: 4px; }

  .badge {
    display: inline-block;
    border: 2px solid #000;
    padding: 2px 10px;
    font-size: 13px;
    font-weight: bold;
    letter-spacing: 1px;
    border-radius: 4px;
  }

  #qr-box { display: flex; justify-content: center; margin: 10px 0; }
</style>
</head>
<body>

<div class="screen-bar" id="screen-bar">
  <button class="screen-btn primary" onclick="window.print()">&#128438; Cetak Struk Pesanan</button>
  <button class="screen-btn secondary" onclick="window.close()">Tutup</button>
  <span style="flex:1;text-align:right;color:#999;font-size:11px">{{ $order->order_number }}</span>
</div>

<div class="ticket">

  {{-- Header --}}
  <div class="center" style="margin-bottom:6px">
    <div class="lg">{{ $outlet->name }}</div>
    <div style="font-size:11px;letter-spacing:2px;text-transform:uppercase;color:#555;margin-top:2px">Struk Pesanan</div>
  </div>

  <hr class="divider-solid">

  {{-- Order info --}}
  <table style="width:100%;border-collapse:collapse;font-size:12px;margin-bottom:6px">
    <tr>
      <td>No. Order</td>
      <td style="text-align:right" class="bold">{{ $order->order_number }}</td>
    </tr>
    <tr>
      <td>Tanggal</td>
      <td style="text-align:right">{{ $order->created_at->format('d/m/Y H:i') }}</td>
    </tr>
    @if($order->customer_name)
    <tr>
      <td>Pemesan</td>
      <td style="text-align:right" class="bold">{{ $order->customer_name }}</td>
    </tr>
    @endif
    @if($order->table_number)
    <tr>
      <td>Meja</td>
      <td style="text-align:right">{{ $order->table_number }}</td>
    </tr>
    @endif
  </table>

  <hr class="divider">

  {{-- Items --}}
  @foreach($order->items as $item)
  <div class="item-line">
    <span class="name">{{ $item->qty }}x {{ $item->product_name }}</span>
    <span class="amt">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
  </div>
  <div class="item-sub">@ Rp {{ number_format($item->product_price, 0, ',', '.') }}</div>
  @endforeach

  <hr class="divider">

  {{-- Totals --}}
  <div class="item-line">
    <span>Subtotal</span>
    <span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
  </div>
  @if($order->discount_amount > 0)
  <div class="item-line">
    <span>Diskon{{ $order->discount_percent > 0 ? " ({$order->discount_percent}%)" : '' }}</span>
    <span>−Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
  </div>
  @endif
  <hr class="divider">
  <div class="total-row">
    <span>TOTAL</span>
    <span>Rp {{ number_format($order->total, 0, ',', '.') }}</span>
  </div>

  <hr class="divider-solid">

  {{-- QR code --}}
  <div id="qr-box"></div>
  <div class="center sm muted">Scan untuk cek status pesanan</div>

  {{-- Notes --}}
  @if($order->notes)
  <hr class="divider">
  <div style="margin:6px 0;font-size:12px">
    <span class="bold">CATATAN:</span><br>
    <span>{{ $order->notes }}</span>
  </div>
  @endif

  <hr class="divider">

  {{-- Footer --}}
  <div class="center sm muted" style="margin-top:4px">
    Kasir: {{ $order->user?->name ?? '—' }}
  </div>
  <div class="center sm muted" style="margin-top:2px">Terima kasih!</div>

</div>

<script>
new QRCode(document.getElementById('qr-box'), {
  text: @json($trackUrl),
  width: 110,
  height: 110,
  correctLevel: QRCode.CorrectLevel.M,
});

if (window.opener || new URLSearchParams(location.search).get('autoprint') === '1') {
  window.addEventListener('load', () => setTimeout(() => window.print(), 400));
}
</script>
</body>
</html>
