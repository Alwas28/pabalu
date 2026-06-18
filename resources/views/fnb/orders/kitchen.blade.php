<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Struk Dapur — {{ $order->order_number }}</title>
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

  .item-block { margin: 6px 0; }
  .item-qty { font-size: 22px; font-weight: bold; display: inline; }
  .item-name { font-size: 14px; font-weight: bold; }
  .item-note { font-size: 11px; color: #555; margin-left: 4px; }

  .badge {
    display: inline-block;
    border: 2px solid #000;
    padding: 2px 10px;
    font-size: 13px;
    font-weight: bold;
    letter-spacing: 1px;
    border-radius: 4px;
  }
</style>
</head>
<body>

<div class="screen-bar" id="screen-bar">
  <button class="screen-btn primary" onclick="window.print()">&#128438; Cetak Struk Dapur</button>
  <button class="screen-btn secondary" onclick="window.close()">Tutup</button>
  <span style="flex:1;text-align:right;color:#999;font-size:11px">{{ $order->order_number }}</span>
</div>

<div class="ticket">

  {{-- Header --}}
  <div class="center" style="margin-bottom:6px">
    <div style="font-size:11px;letter-spacing:2px;text-transform:uppercase;color:#555">STRUK DAPUR</div>
    <div style="font-size:10px;color:#666">{{ $outlet->name }}</div>
  </div>

  <hr class="divider-solid">

  {{-- Order info --}}
  <div style="margin-bottom:6px">
    <table style="width:100%;border-collapse:collapse;font-size:12px">
      <tr>
        <td style="width:50%"><span class="bold">ORDER</span></td>
        <td style="text-align:right"><span class="bold">{{ now()->format('H:i') }}</span></td>
      </tr>
      <tr>
        <td colspan="2">
          <div class="sm muted" style="font-size:10px">{{ $order->order_number }}</div>
        </td>
      </tr>
    </table>
  </div>

  {{-- Nama pemesan + meja --}}
  <div class="center" style="margin:8px 0">
    @if($order->customer_name)
    <div class="badge" style="font-size:15px;padding:3px 12px">{{ strtoupper($order->customer_name) }}</div>
    @endif
    @if($order->table_number)
    <div style="margin-top:4px;font-size:11px;font-weight:bold;color:#555">MEJA: {{ $order->table_number }}</div>
    @elseif(!$order->customer_name)
    <div class="badge">WALK-IN</div>
    @endif
  </div>

  <hr class="divider-solid">

  {{-- Items — big & bold, no prices --}}
  @foreach($order->items as $item)
  <div class="item-block">
    <div>
      <span class="item-qty">{{ $item->qty }}x</span>
      <span class="item-name"> {{ strtoupper($item->product_name) }}</span>
    </div>
  </div>
  @endforeach

  <hr class="divider">

  {{-- Notes --}}
  @if($order->notes)
  <div style="margin:6px 0;font-size:12px">
    <span class="bold">CATATAN:</span><br>
    <span>{{ $order->notes }}</span>
  </div>
  <hr class="divider">
  @endif

  {{-- Footer --}}
  <div class="center sm muted" style="margin-top:4px">
    Kasir: {{ $order->user?->name ?? '—' }}
    &bull; {{ $order->created_at->format('d/m/Y H:i') }}
  </div>
  <div class="center" style="margin-top:6px;font-size:11px;color:#999">— Segera Diproses —</div>

</div>

<script>
if (window.opener || new URLSearchParams(location.search).get('autoprint') === '1') {
  window.addEventListener('load', () => setTimeout(() => window.print(), 300));
}
</script>
</body>
</html>
