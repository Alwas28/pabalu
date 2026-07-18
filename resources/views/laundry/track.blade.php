<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Cek Status Laundry — {{ $order->order_number }}</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#f0f2f5;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:flex-start;padding:24px 16px}
.card{background:#fff;border-radius:20px;box-shadow:0 4px 24px rgba(0,0,0,.10);width:100%;max-width:440px;overflow:hidden}
.card-top{padding:24px;text-align:center;background:linear-gradient(135deg,#e8192c 0%,#b91c2c 100%);color:#fff}
.outlet-name{font-size:13px;font-weight:600;opacity:.85;letter-spacing:.5px;margin-bottom:4px}
.order-num{font-size:22px;font-weight:900;letter-spacing:1px;word-break:break-all}
.section{padding:20px 24px;border-bottom:1px solid #f0f0f0}
.section:last-child{border-bottom:none}
.label{font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.6px;margin-bottom:10px}
/* Stepper */
.stepper{display:flex;align-items:flex-start;gap:0}
.step{display:flex;flex-direction:column;align-items:center;gap:6px;flex:1}
.step-circle{width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;border:2px solid #e5e7eb;color:#9ca3af;background:#fff;flex-shrink:0}
.step-lbl{font-size:10px;font-weight:600;text-align:center;color:#9ca3af;white-space:nowrap}
.connector{flex:1;height:2px;background:#e5e7eb;margin-bottom:22px;margin-top:17px}
.step.done .step-circle{background:#e8192c;border-color:#e8192c;color:#fff}
.step.done .step-lbl{color:#e8192c}
.step.active .step-circle{background:#fff1f2;border-color:#e8192c;color:#e8192c}
.step.active .step-lbl{color:#e8192c;font-weight:800}
.connector.done{background:#e8192c}
/* Status banner */
.status-banner{display:flex;align-items:center;gap:12px;padding:14px 18px;border-radius:14px;font-size:14px;font-weight:700}
.status-masuk  {background:#eff6ff;color:#1d4ed8}
.status-proses {background:#fffbeb;color:#b45309}
.status-selesai{background:#f0fdf4;color:#15803d}
.status-diambil{background:#f3f4f6;color:#6b7280}
.status-icon{width:38px;height:38px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
.status-icon.masuk  {background:#dbeafe}
.status-icon.proses {background:#fef3c7}
.status-icon.selesai{background:#dcfce7}
.status-icon.diambil{background:#e5e7eb}
/* Info */
.info-row{display:flex;gap:10px;padding:8px 0;border-bottom:1px solid #f3f4f6}
.info-row:last-child{border-bottom:none}
.info-icon{width:28px;height:28px;border-radius:8px;background:#fff1f2;color:#e8192c;display:flex;align-items:center;justify-content:center;font-size:11px;flex-shrink:0;margin-top:1px}
.info-lbl{font-size:11px;color:#9ca3af;font-weight:600;margin-bottom:2px}
.info-val{font-size:13.5px;font-weight:600;color:#111}
/* Items */
.item-row{display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #f3f4f6}
.item-row:last-child{border-bottom:none}
.item-name{font-size:13px;font-weight:600;color:#111}
.item-meta{font-size:11px;color:#9ca3af;margin-top:1px}
/* Footer */
.footer-note{text-align:center;font-size:12px;color:#9ca3af;padding:16px;line-height:1.6}
@media(prefers-color-scheme:dark){
  body{background:#1a1a2e}
  .card{background:#1e1e2e;box-shadow:0 4px 24px rgba(0,0,0,.4)}
  .section{border-bottom-color:#2d2d3d}
  .label{color:#6b7280}
  .step-circle{border-color:#374151;background:#1e1e2e;color:#6b7280}
  .connector{background:#374151}
  .info-row{border-bottom-color:#2d2d3d}
  .info-lbl{color:#6b7280}
  .info-val{color:#f9fafb}
  .item-row{border-bottom-color:#2d2d3d}
  .item-name{color:#f9fafb}
  .status-masuk  {background:#1e3a5f;color:#60a5fa}
  .status-proses {background:#3d2c00;color:#fbbf24}
  .status-selesai{background:#052e16;color:#4ade80}
  .status-diambil{background:#1f2937;color:#9ca3af}
  .status-icon.masuk  {background:#1e3a5f}
  .status-icon.proses {background:#3d2c00}
  .status-icon.selesai{background:#052e16}
  .status-icon.diambil{background:#1f2937}
  .footer-note{color:#4b5563}
}
</style>
</head>
<body>

@php
  $steps = [
    'masuk'   => ['icon'=>'📥', 'label'=>'Masuk'],
    'proses'  => ['icon'=>'🔄', 'label'=>'Diproses'],
    'selesai' => ['icon'=>'✅', 'label'=>'Selesai'],
    'diambil' => ['icon'=>'🏠', 'label'=>'Diambil'],
  ];
  $statusOrder = array_keys($steps);
  $currentIdx  = array_search($order->status, $statusOrder);

  $statusBannerText = [
    'masuk'   => 'Pesanan diterima, menunggu diproses',
    'proses'  => 'Cucian sedang diproses',
    'selesai' => 'Cucian sudah selesai, siap diambil!',
    'diambil' => 'Cucian sudah diambil',
  ];
  $statusIcons = [
    'masuk'   => '📥',
    'proses'  => '🔄',
    'selesai' => '🎉',
    'diambil' => '✅',
  ];
@endphp

<div class="card">
  {{-- Header --}}
  <div class="card-top">
    <div class="outlet-name">{{ strtoupper($order->outlet->name ?? 'Laundry') }}</div>
    <div style="font-size:11px;opacity:.7;margin-bottom:12px">Status Cucian</div>
    <div class="order-num">{{ $order->order_number }}</div>
  </div>

  {{-- Status Banner --}}
  <div class="section">
    <div class="status-banner status-{{ $order->status }}">
      <div class="status-icon {{ $order->status }}" style="font-size:18px">
        {{ $statusIcons[$order->status] ?? '📦' }}
      </div>
      <div>
        <div>{{ \App\Models\LaundryOrder::$statusLabels[$order->status] ?? $order->status }}</div>
        <div style="font-size:12px;font-weight:400;opacity:.8;margin-top:2px">
          {{ $statusBannerText[$order->status] ?? '' }}
        </div>
      </div>
    </div>
  </div>

  {{-- Stepper --}}
  <div class="section">
    <div class="label">Alur Pesanan</div>
    <div class="stepper">
      @foreach($steps as $sKey => $sInfo)
        @php $idx = array_search($sKey, $statusOrder); @endphp
        @if(!$loop->first)
          <div class="connector {{ $idx <= $currentIdx ? 'done' : '' }}"></div>
        @endif
        <div class="step {{ $idx < $currentIdx ? 'done' : ($idx == $currentIdx ? 'active' : '') }}">
          <div class="step-circle">{{ $sInfo['icon'] }}</div>
          <div class="step-lbl">{{ $sInfo['label'] }}</div>
        </div>
      @endforeach
    </div>
  </div>

  {{-- Info --}}
  <div class="section">
    <div class="label">Informasi</div>
    <div class="info-row">
      <div class="info-icon">👤</div>
      <div><div class="info-lbl">Nama</div><div class="info-val">{{ $order->customer_name }}</div></div>
    </div>
    <div class="info-row">
      <div class="info-icon">📅</div>
      <div><div class="info-lbl">Tanggal Masuk</div><div class="info-val">{{ $order->created_at->translatedFormat('d F Y, H:i') }}</div></div>
    </div>
    @if($order->estimated_done_at)
    <div class="info-row">
      <div class="info-icon">⏰</div>
      <div>
        <div class="info-lbl">Estimasi Selesai</div>
        <div class="info-val" style="{{ $order->status !== 'diambil' && $order->estimated_done_at->isPast() ? 'color:#dc2626' : '' }}">
          {{ $order->estimated_done_at->translatedFormat('d F Y, H:i') }}
          @if($order->status !== 'diambil' && $order->estimated_done_at->isPast())
          <span style="font-size:11px;background:#fee2e2;color:#dc2626;padding:1px 8px;border-radius:99px;margin-left:4px">Terlambat</span>
          @endif
        </div>
      </div>
    </div>
    @endif
    @if($order->paid_at)
    <div class="info-row">
      <div class="info-icon">✅</div>
      <div><div class="info-lbl">Diambil & Dibayar</div><div class="info-val">{{ $order->paid_at->translatedFormat('d F Y, H:i') }}</div></div>
    </div>
    @endif
  </div>

  {{-- Item Cucian --}}
  <div class="section">
    <div class="label">Item Cucian ({{ $order->items->count() }} item)</div>
    @foreach($order->items as $item)
    <div class="item-row">
      <div>
        <div class="item-name">{{ $item->product_name }}</div>
        <div class="item-meta">
          {{ number_format($item->qty, $item->qty == (int)$item->qty ? 0 : 2) }}
          {{ $item->unit }}
        </div>
      </div>
    </div>
    @endforeach
  </div>

  <div class="footer-note">
    Tunjukkan struk ini saat mengambil cucian.<br>
    Hubungi kami jika ada pertanyaan.
    @if($order->outlet->phone ?? false)
    <br><a href="tel:{{ $order->outlet->phone }}" style="color:#e8192c;text-decoration:none;font-weight:600">
      📞 {{ $order->outlet->phone }}
    </a>
    @endif
  </div>
</div>

</body>
</html>
