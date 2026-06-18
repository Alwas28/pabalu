@php
  $statusMeta = [
    'pending'   => ['label'=>'Menunggu Konfirmasi', 'color'=>'#7c3aed', 'bg'=>'rgba(124,58,237,.1)',  'icon'=>'fa-hourglass-half'],
    'open'      => ['label'=>'Menunggu',             'color'=>'#f59e0b', 'bg'=>'rgba(245,158,11,.1)',  'icon'=>'fa-clock'],
    'preparing' => ['label'=>'Sedang Diproses',      'color'=>'#4f6ef7', 'bg'=>'rgba(79,110,247,.1)',  'icon'=>'fa-fire'],
    'served'    => ['label'=>'Sudah Diantarkan',     'color'=>'#10b981', 'bg'=>'rgba(16,185,129,.1)',  'icon'=>'fa-bell'],
    'paid'      => ['label'=>'Selesai',              'color'=>'#64748b', 'bg'=>'rgba(100,116,139,.1)', 'icon'=>'fa-circle-check'],
    'cancelled' => ['label'=>'Dibatalkan',           'color'=>'#ef4444', 'bg'=>'rgba(239,68,68,.1)',   'icon'=>'fa-ban'],
  ];
  $meta = $statusMeta[$order->status] ?? $statusMeta['open'];
@endphp

<x-public-menu-layout :title="'Pesanan ' . $order->order_number . ' — ' . $outlet->name">

<x-slot name="styles">
<style>
body{background:#f4f4f6}
.wrap{max-width:420px;margin:0 auto;padding:20px 16px 40px}
.outlet-badge{display:flex;align-items:center;gap:10px;margin-bottom:20px}
.outlet-badge .back{width:36px;height:36px;border-radius:10px;background:#fff;border:1.5px solid #e5e7eb;display:grid;place-items:center;color:#6b7280;text-decoration:none;font-size:14px}
.outlet-name{font-size:14px;font-weight:700;color:#111}
.outlet-type{font-size:12px;color:#9ca3af}
.status-card{background:#fff;border-radius:20px;padding:28px 24px;text-align:center;margin-bottom:16px;border:1.5px solid #e5e7eb}
.status-icon{width:72px;height:72px;border-radius:50%;display:grid;place-items:center;font-size:28px;margin:0 auto 16px}
.status-label{font-size:22px;font-weight:800;margin-bottom:6px}
.order-num{font-size:13px;color:#9ca3af;margin-bottom:4px}
.order-num strong{color:#374151;font-weight:700}
.last-refresh{font-size:11px;color:#d1d5db;margin-top:8px}
.items-card{background:#fff;border-radius:16px;padding:16px;margin-bottom:16px;border:1.5px solid #e5e7eb}
.items-card h3{font-size:13px;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;margin-bottom:12px;display:flex;align-items:center;gap:6px}
.item-row{display:flex;justify-content:space-between;align-items:flex-start;padding:8px 0;border-bottom:1px solid #f3f4f6;font-size:13.5px}
.item-row:last-child{border-bottom:none}
.item-name{font-weight:600;color:#111;flex:1}
.item-qty{color:#9ca3af;font-size:12px;margin-top:2px}
.item-sub{font-weight:700;color:#374151;flex-shrink:0;margin-left:12px}
.total-row{display:flex;justify-content:space-between;padding-top:10px;margin-top:4px;border-top:1.5px solid #e5e7eb}
.total-row .label{font-size:14px;font-weight:700;color:#374151}
.total-row .amount{font-size:18px;font-weight:800;color:#111}
.btn-back{display:block;width:100%;padding:13px;border-radius:12px;background:linear-gradient(135deg,#e8192c,#c41020);color:#fff;font-size:14px;font-weight:800;text-align:center;text-decoration:none;margin-top:8px}
.note-card{background:#fffbeb;border:1.5px solid #fde68a;border-radius:12px;padding:12px 14px;font-size:13px;color:#92400e;margin-bottom:12px}
</style>
</x-slot>

<div class="wrap">

  {{-- Back to menu --}}
  <div class="outlet-badge">
    <a href="{{ route('public.menu', $outlet->code) }}" class="back">
      <i class="fa-solid fa-arrow-left"></i>
    </a>
    <div>
      <div class="outlet-name">{{ $outlet->name }}</div>
      @if($outlet->outletType)
      <div class="outlet-type">{{ $outlet->outletType->name }}</div>
      @endif
    </div>
  </div>

  {{-- Status card --}}
  <div class="status-card">
    <div class="status-icon" style="background:{{ $meta['bg'] }};color:{{ $meta['color'] }}">
      <i class="fa-solid {{ $meta['icon'] }}"></i>
    </div>
    <div class="status-label" style="color:{{ $meta['color'] }}">{{ $meta['label'] }}</div>
    <div class="order-num">No. Pesanan: <strong>{{ $order->order_number }}</strong></div>
    @if($order->customer_name)
    <div class="order-num">Pemesan: <strong>{{ $order->customer_name }}</strong></div>
    @endif
    @if($order->table_number)
    <div class="order-num">Meja: <strong>{{ $order->table_number }}</strong></div>
    @endif
    <div class="last-refresh" id="refresh-label">Memuat status...</div>
  </div>

  @if($order->notes)
  <div class="note-card">
    <i class="fa-solid fa-note-sticky" style="margin-right:6px"></i>{{ $order->notes }}
  </div>
  @endif

  {{-- Items --}}
  <div class="items-card">
    <h3><i class="fa-solid fa-list-ul"></i>Item Pesanan</h3>
    @foreach($order->items as $item)
    <div class="item-row">
      <div>
        <div class="item-name">{{ $item->product_name }}</div>
        <div class="item-qty">{{ $item->qty }} × Rp {{ number_format($item->product_price, 0, ',', '.') }}</div>
      </div>
      <div class="item-sub">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</div>
    </div>
    @endforeach
    <div class="total-row">
      <span class="label">Total</span>
      <span class="amount">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
    </div>
  </div>

  @if($order->status === 'pending')
  <div style="background:#f5f3ff;border:1.5px solid #ddd6fe;border-radius:12px;padding:12px 14px;margin-bottom:12px;font-size:13px;color:#5b21b6;line-height:1.6">
    <i class="fa-solid fa-hourglass-half" style="margin-right:6px"></i>
    Pesanan kamu sedang ditinjau oleh kasir. Mohon tunggu sebentar.
  </div>
  @elseif($order->status === 'served')
  <div style="background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:12px;padding:12px 14px;margin-bottom:12px;font-size:13px;color:#166534;line-height:1.6">
    <i class="fa-solid fa-bell" style="margin-right:6px"></i>
    Pesanan sudah diantarkan. Mau nambah? Pesan lagi di bawah.
  </div>
  @elseif(in_array($order->status, ['paid','cancelled']))
  <div style="background:#f9fafb;border:1.5px solid #e5e7eb;border-radius:12px;padding:12px 14px;margin-bottom:12px;font-size:13px;color:#6b7280;line-height:1.6">
    <i class="fa-solid fa-utensils" style="margin-right:6px"></i>
    Pesanan selesai. Mau memesan lagi? Ketuk tombol di bawah.
  </div>
  @endif

  @if(!in_array($order->status, ['paid','cancelled']))
  <a href="{{ route('public.menu', $outlet->code) }}" class="btn-back"
    style="background:transparent;border:1.5px solid #e5e7eb;color:#374151;font-weight:600;margin-bottom:8px;display:block;text-align:center;padding:13px;border-radius:12px;text-decoration:none;font-size:14px">
    <i class="fa-solid fa-plus" style="margin-right:6px"></i>Tambah Pesanan
  </a>
  @else
  <a href="{{ route('public.menu', $outlet->code) }}" class="btn-back">
    <i class="fa-solid fa-utensils" style="margin-right:6px"></i>Pesan Lagi
  </a>
  @endif

</div>

<x-slot name="scripts">
<script>
const STATUS_DONE = ['paid','cancelled','served'];
const CURRENT_STATUS = '{{ $order->status }}';

function updateRefreshLabel() {
  const el = document.getElementById('refresh-label');
  if (el) el.textContent = 'Diperbarui ' + new Date().toLocaleTimeString('id-ID');
}

updateRefreshLabel();

if (!STATUS_DONE.includes(CURRENT_STATUS)) {
  setInterval(() => location.reload(), 10000);
}
</script>
</x-slot>

</x-public-menu-layout>
