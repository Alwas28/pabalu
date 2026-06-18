<x-outlet-layout :outlet="$outlet" pageTitle="Pesanan Masuk">

<style>
.inbox-empty{text-align:center;padding:60px 24px;color:var(--muted)}
.inbox-empty i{font-size:48px;display:block;margin-bottom:14px;opacity:.3}
.inbox-empty p{font-size:14px;font-weight:600}
.inbox-empty small{font-size:12.5px}

.pending-list{display:flex;flex-direction:column;gap:10px}

.pending-card{background:var(--surface);border:1.5px solid var(--border);border-radius:14px;overflow:hidden;transition:border-color .15s,box-shadow .15s}
.pending-card:hover{border-color:rgba(var(--ac-rgb),.4);box-shadow:0 4px 16px rgba(0,0,0,.1)}

.pc-head{display:flex;align-items:center;gap:10px;padding:12px 14px 8px}
.pc-badge{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:99px;font-size:10.5px;font-weight:700;background:rgba(167,139,250,.15);color:#7c3aed}
.pc-num{font-size:12px;font-weight:700;font-family:monospace;color:var(--text)}
.pc-time{font-size:11px;color:var(--muted);margin-left:auto}

.pc-customer{padding:0 14px 6px;font-size:14px;font-weight:700;color:var(--text);display:flex;align-items:center;gap:8px}
.pc-table{font-size:12px;color:var(--muted);padding:0 14px 8px;display:flex;align-items:center;gap:5px}

.pc-items{padding:8px 14px;border-top:1px solid var(--border);display:flex;flex-direction:column;gap:3px}
.pc-item-row{display:flex;justify-content:space-between;font-size:12.5px;color:var(--sub)}

.pc-foot{display:flex;align-items:center;justify-content:space-between;padding:10px 14px;background:var(--surface2);border-top:1px solid var(--border)}
.pc-total{font-size:14px;font-weight:800;color:var(--text)}
.pc-actions{display:flex;gap:8px}

.btn-review{display:inline-flex;align-items:center;gap:6px;padding:7px 16px;border-radius:9px;background:var(--ac);color:#fff;font-size:13px;font-weight:700;text-decoration:none;border:none;cursor:pointer;font-family:inherit}
.btn-review:hover{opacity:.88}
.btn-reject{display:inline-flex;align-items:center;gap:6px;padding:7px 12px;border-radius:9px;background:rgba(248,113,113,.12);color:#ef4444;font-size:12.5px;font-weight:600;border:none;cursor:pointer;font-family:inherit}
.btn-reject:hover{background:rgba(248,113,113,.22)}

.refresh-bar{display:flex;align-items:center;gap:10px;padding:10px 16px;background:var(--surface2);border:1px solid var(--border);border-radius:12px;font-size:12.5px;color:var(--muted);margin-bottom:16px}
.refresh-bar .dot{width:8px;height:8px;border-radius:50%;background:#34d399;animation:pulse 2s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}

.section-recent{margin-top:28px}
.recent-head{font-size:11px;font-weight:700;color:var(--muted);letter-spacing:.08em;text-transform:uppercase;margin-bottom:10px}
.recent-row{display:flex;align-items:center;gap:10px;padding:10px 14px;background:var(--surface);border:1px solid var(--border);border-radius:10px;margin-bottom:6px;font-size:12.5px;flex-wrap:wrap}
.recent-badge{display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:99px;font-size:10.5px;font-weight:600}
.btn-delete{display:inline-flex;align-items:center;gap:5px;padding:5px 10px;border-radius:8px;background:rgba(248,113,113,.1);color:#ef4444;font-size:11.5px;font-weight:600;border:none;cursor:pointer;font-family:inherit}
.btn-delete:hover{background:rgba(248,113,113,.2)}
</style>

<div class="refresh-bar">
  <span class="dot"></span>
  <span>Auto-refresh <strong>15 detik</strong> — <span id="countdown">15</span>s lagi</span>
  <button onclick="location.reload()"
    style="margin-left:auto;background:var(--surface);border:1px solid var(--border);border-radius:8px;padding:4px 12px;font-size:12px;font-weight:600;color:var(--text);cursor:pointer;font-family:inherit">
    <i class="fa-solid fa-rotate-right" style="margin-right:4px"></i>Perbarui
  </button>
  <span style="font-size:12px;font-weight:600;color:var(--text)">{{ $pending->count() }} menunggu</span>
</div>

@if($pending->isEmpty())
<div class="inbox-empty">
  <i class="fa-solid fa-inbox"></i>
  <p>Tidak ada pesanan masuk</p>
  <small>Pesanan dari QR menu akan muncul di sini</small>
</div>
@else
<div class="pending-list">
  @foreach($pending as $order)
  @php
    $diffMin = $order->created_at->diffInMinutes(now());
    $diffStr = $diffMin < 1 ? 'Baru saja' : ($diffMin < 60 ? $diffMin.'m lalu' : $order->created_at->format('H:i'));
  @endphp
  <div class="pending-card">
    <div class="pc-head">
      <span class="pc-badge"><i class="fa-solid fa-qrcode" style="font-size:9px"></i>QR Menu</span>
      <span class="pc-num">{{ $order->order_number }}</span>
      <span class="pc-time"><i class="fa-regular fa-clock" style="margin-right:3px"></i>{{ $diffStr }}</span>
    </div>

    <div class="pc-customer">
      <i class="fa-solid fa-user" style="font-size:12px;color:var(--muted)"></i>
      {{ $order->customer_name ?: '—' }}
      @if($order->table_number && in_array($order->table_number, $activeTables))
      <span style="margin-left:6px;display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:99px;background:rgba(245,158,11,.15);color:#b45309;font-size:10.5px;font-weight:700">
        <i class="fa-solid fa-triangle-exclamation" style="font-size:9px"></i>Tambahan
      </span>
      @endif
    </div>
    @if($order->table_number)
    <div class="pc-table">
      <i class="fa-solid fa-chair" style="font-size:11px"></i>Meja {{ $order->table_number }}
    </div>
    @endif

    <div class="pc-items">
      @foreach($order->items as $item)
      <div class="pc-item-row">
        <span>{{ $item->product_name }} <span style="color:var(--muted)">×{{ $item->qty }}</span></span>
        <span>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
      </div>
      @endforeach
    </div>

    <div class="pc-foot">
      <div class="pc-total">Rp {{ number_format($order->total, 0, ',', '.') }}</div>
      <div class="pc-actions">
        <form method="POST" action="{{ $outlet->route('self-orders.reject', [$order]) }}"
          onsubmit="return confirm('Tolak pesanan {{ $order->order_number }}?')">
          @csrf @method('PATCH')
          <button type="submit" class="btn-reject"><i class="fa-solid fa-xmark"></i>Tolak</button>
        </form>
        <a href="{{ $outlet->route('self-orders.show', [$order]) }}" class="btn-review">
          <i class="fa-solid fa-pen-to-square"></i>Tinjau & Edit
        </a>
      </div>
    </div>
  </div>
  @endforeach
</div>
@endif

{{-- Recent --}}
@if($recent->isNotEmpty())
<div class="section-recent">
  <p class="recent-head">Selesai / Ditolak (10 terakhir)</p>
  @foreach($recent as $order)
  @php $m = $order->statusMeta(); @endphp
  <div class="recent-row">
    <span class="recent-badge" style="background:{{ $m['bg'] }};color:{{ $m['color'] }}">
      <i class="fa-solid {{ $m['icon'] }}" style="font-size:10px"></i>{{ $m['label'] }}
    </span>
    <span style="font-family:monospace;font-size:11.5px;color:var(--sub)">{{ $order->order_number }}</span>
    <span style="font-weight:600;color:var(--text)">{{ $order->customer_name ?: '—' }}</span>
    @if($order->table_number)
    <span style="color:var(--muted)">Meja {{ $order->table_number }}</span>
    @endif
    <span style="margin-left:auto;font-weight:700;color:var(--text)">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
    <span style="color:var(--muted);font-size:11px">{{ $order->updated_at->format('d/m H:i') }}</span>
    @if($order->status === 'cancelled' && auth()->user()->role === 'owner')
    <form method="POST" action="{{ $outlet->route('self-orders.destroy', [$order]) }}"
      onsubmit="return confirm('Hapus permanen pesanan {{ $order->order_number }}?')">
      @csrf @method('DELETE')
      <button type="submit" class="btn-delete"><i class="fa-solid fa-trash-can"></i>Hapus</button>
    </form>
    @endif
  </div>
  @endforeach
</div>
@endif

@push('scripts')
<script>
let secs = 15;
const cd = document.getElementById('countdown');
setInterval(() => { secs--; if (cd) cd.textContent = secs; if (secs <= 0) location.reload(); }, 1000);
</script>
@endpush

</x-outlet-layout>
