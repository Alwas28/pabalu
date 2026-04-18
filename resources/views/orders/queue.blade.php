<x-app-layout title="Antrian Order">

<style>
.order-card{
  background:var(--surface);border:1px solid var(--border);border-radius:16px;
  padding:18px;transition:border-color .2s,box-shadow .2s;
  display:flex;flex-direction:column;gap:12px;
}
.order-card:hover{border-color:rgba(var(--ac-rgb),.4);box-shadow:0 4px 20px rgba(0,0,0,.15)}
.order-card.status-pending  {border-left:3px solid #fbbf24}
.order-card.status-processing{border-left:3px solid #818cf8}
.order-card.status-ready    {border-left:3px solid #34d399}
.order-card.status-completed{border-left:3px solid #64748b;opacity:.65}
.order-card.status-cancelled{border-left:3px solid #f87171;opacity:.55}

.order-grid{
  display:grid;
  grid-template-columns:repeat(auto-fill,minmax(300px,1fr));
  gap:16px;
  align-items:start;
}

.item-row{display:flex;justify-content:space-between;font-size:12.5px;padding:3px 0;border-bottom:1px solid var(--border)}
.item-row:last-child{border-bottom:none}

.timer{font-size:11px;color:var(--muted);font-variant-numeric:tabular-nums}
.timer.urgent{color:#f87171;font-weight:600}

.tab-bar{display:flex;gap:4px;background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:4px}
.tab-btn{
  padding:7px 16px;border-radius:9px;border:none;cursor:pointer;font-family:inherit;
  font-size:12.5px;font-weight:600;transition:background .15s,color .15s;
  background:transparent;color:var(--muted);
}
.tab-btn.active{background:var(--ac-lt);color:var(--ac)}
.tab-btn:hover:not(.active){background:var(--surface2);color:var(--text)}

.pulse-dot{
  display:inline-block;width:8px;height:8px;border-radius:50%;margin-right:5px;
  background:var(--ac);animation:pulse-anim 1.5s ease infinite;
}
@keyframes pulse-anim{
  0%,100%{transform:scale(1);opacity:1}
  50%{transform:scale(1.5);opacity:.6}
}

#new-order-banner{
  display:none;position:fixed;top:80px;left:50%;transform:translateX(-50%);
  background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;
  padding:12px 24px;border-radius:40px;font-size:13px;font-weight:700;
  box-shadow:0 8px 32px rgba(0,0,0,.4);z-index:200;cursor:pointer;
  animation:slideDown .4s cubic-bezier(.34,1.56,.64,1);
}
@keyframes slideDown{from{top:60px;opacity:0}to{top:80px;opacity:1}}
</style>

{{-- ── Top bar: outlet + tabs ── --}}
<div style="display:flex;flex-wrap:wrap;align-items:center;gap:12px;justify-content:space-between">
  {{-- Outlet selector / locked --}}
  <div style="display:flex;align-items:center;gap:10px">
    <form method="GET" id="outlet-form" style="display:flex;align-items:center;gap:8px">
      @if ($assignedOutletId)
        @php $assignedOutlet = $outlets->firstWhere('id', $assignedOutletId); @endphp
        <div class="f-input" style="width:auto;padding:8px 14px;display:flex;align-items:center;gap:8px;color:var(--ac);font-weight:600;pointer-events:none">
          <i class="fa-solid fa-store"></i>{{ $assignedOutlet?->nama ?? 'Outlet #'.$assignedOutletId }}
          <i class="fa-solid fa-lock" style="font-size:10px;opacity:.7;margin-left:4px"></i>
        </div>
        <input type="hidden" name="outlet_id" value="{{ $assignedOutletId }}">
      @else
        <select name="outlet_id" class="f-input" style="width:auto" onchange="document.getElementById('outlet-form').submit()">
          @foreach($outlets as $o)
            <option value="{{ $o->id }}" {{ $o->id == $outletId ? 'selected' : '' }}>{{ $o->nama }}</option>
          @endforeach
        </select>
      @endif
    </form>
    {{-- Live indicator --}}
    <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--muted)">
      <span class="pulse-dot" id="live-dot"></span>
      <span id="live-label">Live</span>
    </div>
  </div>

  {{-- Status tabs --}}
  <div class="tab-bar">
    <button type="button" class="tab-btn {{ $statusFilter === 'active' ? 'active' : '' }}"
      onclick="setFilter('active')">Aktif
      @if(($stats['pending']+$stats['processing']+$stats['ready']) > 0)
        <span style="background:var(--ac);color:#fff;border-radius:99px;padding:1px 7px;font-size:10px;margin-left:4px">
          {{ $stats['pending']+$stats['processing']+$stats['ready'] }}
        </span>
      @endif
    </button>
    <button type="button" class="tab-btn {{ $statusFilter === 'completed' ? 'active' : '' }}"
      onclick="setFilter('completed')">Selesai</button>
    <button type="button" class="tab-btn {{ $statusFilter === 'cancelled' ? 'active' : '' }}"
      onclick="setFilter('cancelled')">Dibatalkan</button>
    <button type="button" class="tab-btn {{ $statusFilter === 'all' ? 'active' : '' }}"
      onclick="setFilter('all')">Semua</button>
  </div>
</div>

{{-- ── Stat cards ── --}}
<div class="stat-grid" style="grid-template-columns:repeat(3,1fr)">
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(251,191,36,.15)"><i class="fa-solid fa-hourglass-start" style="color:#fbbf24"></i></div>
    <div>
      <div class="stat-num" id="stat-pending">{{ $stats['pending'] }}</div>
      <div class="stat-label">Menunggu</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(129,140,248,.15)"><i class="fa-solid fa-kitchen-set" style="color:#818cf8"></i></div>
    <div>
      <div class="stat-num" id="stat-processing">{{ $stats['processing'] }}</div>
      <div class="stat-label">Diproses</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(52,211,153,.15)"><i class="fa-solid fa-bell-concierge" style="color:#34d399"></i></div>
    <div>
      <div class="stat-num" id="stat-ready">{{ $stats['ready'] }}</div>
      <div class="stat-label">Siap Diambil</div>
    </div>
  </div>
</div>

{{-- ── New order banner (shown by polling) ── --}}
<div id="new-order-banner" onclick="reloadPage()">
  <i class="fa-solid fa-bell"></i> Ada order baru! Klik untuk refresh
</div>

{{-- ── Order grid ── --}}
@if($orders->isEmpty())
  <div style="text-align:center;padding:64px 24px;color:var(--muted)">
    <i class="fa-solid fa-clipboard-list" style="font-size:40px;opacity:.25;display:block;margin-bottom:12px"></i>
    <p style="font-size:14px">Tidak ada order untuk filter ini.</p>
  </div>
@else
  <div class="order-grid" id="order-grid">
    @foreach($orders as $order)
    <div class="order-card status-{{ $order->order_status }} animate-fadeUp" data-id="{{ $order->id }}" data-created="{{ $order->created_at->toISOString() }}">

      {{-- Header --}}
      <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px">
        <div>
          <div style="font-family:'Clash Display',sans-serif;font-size:15px;font-weight:700;color:var(--text)">
            {{ $order->order_number }}
          </div>
          <div style="font-size:12px;color:var(--muted);margin-top:2px">
            <i class="fa-solid fa-user" style="font-size:10px"></i>
            {{ $order->customer_name }}
            @if($order->customer_phone)
              · <a href="tel:{{ $order->customer_phone }}" style="color:var(--ac);text-decoration:none">{{ $order->customer_phone }}</a>
            @endif
          </div>
        </div>
        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px">
          <span class="badge badge-{{ \App\Models\Order::statusColor($order->order_status) }}">
            {{ \App\Models\Order::statusLabel($order->order_status) }}
          </span>
          <span class="timer" data-created="{{ $order->created_at->toISOString() }}" id="timer-{{ $order->id }}">
            {{ $order->created_at->diffForHumans() }}
          </span>
        </div>
      </div>

      {{-- Items --}}
      <div style="background:var(--surface2);border-radius:10px;padding:10px 12px">
        @foreach($order->items as $item)
        <div class="item-row">
          <span style="color:var(--text);font-weight:500">{{ $item->nama_produk }}</span>
          <span style="color:var(--muted)">{{ $item->qty }}x <span style="color:var(--ac)">Rp{{ number_format($item->harga_satuan,0,',','.') }}</span></span>
        </div>
        @endforeach
        <div style="display:flex;justify-content:space-between;margin-top:8px;padding-top:6px;border-top:1px dashed var(--border)">
          <span style="font-size:12px;color:var(--muted)">Total</span>
          <span style="font-size:13px;font-weight:700;color:var(--ac)">Rp{{ number_format($order->subtotal,0,',','.') }}</span>
        </div>
      </div>

      {{-- Catatan --}}
      @if($order->catatan)
      <div style="font-size:12px;color:var(--muted);background:var(--surface2);border-radius:8px;padding:8px 10px;border-left:2px solid var(--ac)">
        <i class="fa-solid fa-note-sticky" style="margin-right:5px;font-size:10px"></i>{{ $order->catatan }}
      </div>
      @endif

      {{-- Actions --}}
      @if(!in_array($order->order_status, ['completed','cancelled']))
      @can('order.manage')
      <div style="display:flex;gap:8px;flex-wrap:wrap">
        @if($order->nextStatus())
        <form method="POST" action="{{ route('orders.advance', $order) }}" style="flex:1">
          @csrf
          <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">
            <i class="fa-solid fa-circle-arrow-right"></i>
            {{ $order->nextLabel() }}
          </button>
        </form>
        @endif
        <form method="POST" action="{{ route('orders.cancel', $order) }}" onsubmit="return confirm('Batalkan order ini?')">
          @csrf
          <button type="submit" class="btn btn-danger" style="padding:9px 14px">
            <i class="fa-solid fa-ban"></i>
          </button>
        </form>
      </div>
      @endcan
      @endif

    </div>
    @endforeach
  </div>
@endif

@push('scripts')
<script>
/* ── State ── */
const outletId   = {{ $outletId ? $outletId : 'null' }};
let   statusFilter = '{{ $statusFilter }}';
let   lastPoll   = new Date().toISOString();
let   pollTimer  = null;
let   timerTick  = null;

/* ── Navigate with filter / outlet ── */
function setFilter(f) {
  const url = new URL(window.location.href);
  url.searchParams.set('status', f);
  if (outletId) url.searchParams.set('outlet_id', outletId);
  window.location.href = url.toString();
}

function reloadPage() {
  const url = new URL(window.location.href);
  if (outletId) url.searchParams.set('outlet_id', outletId);
  window.location.href = url.toString();
}

/* ── Elapsed timers ── */
function updateTimers() {
  document.querySelectorAll('[data-created]').forEach(function(el) {
    if (!el.id || !el.id.startsWith('timer-')) return;
    const created = new Date(el.dataset.created);
    const mins = Math.floor((Date.now() - created) / 60000);
    const secs = Math.floor(((Date.now() - created) % 60000) / 1000);
    let label;
    if (mins < 1)        label = secs + 'd';
    else if (mins < 60)  label = mins + 'm ' + secs + 'd';
    else                 label = Math.floor(mins/60) + 'j ' + (mins%60) + 'm';
    el.textContent = label;
    el.classList.toggle('urgent', mins >= 15);
  });
}

/* ── Polling ── */
function doPoll() {
  const pollUrl = '{{ route("orders.poll") }}?since=' + encodeURIComponent(lastPoll)
    + (outletId ? '&outlet_id=' + outletId : '');

  fetch(pollUrl, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } })
    .then(function(r){ return r.json(); })
    .then(function(data) {
      lastPoll = data.now;

      /* Update stat badges */
      /* We keep stats rough — will update on reload; full accuracy via page reload */

      /* Show banner if new orders arrived */
      if (data.new_count > 0 && statusFilter === 'active') {
        var banner = document.getElementById('new-order-banner');
        banner.innerHTML = '<i class="fa-solid fa-bell"></i> ' + data.new_count + ' order baru! Klik untuk refresh';
        banner.style.display = 'block';
        playBeep();
      }

      /* Pulse live dot */
      var dot = document.getElementById('live-dot');
      dot.style.background = '#34d399';
      setTimeout(function(){ dot.style.background = 'var(--ac)'; }, 800);
      document.getElementById('live-label').textContent = 'Live · ' + new Date().toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit',second:'2-digit'});
    })
    .catch(function(){ /* silent fail */ });
}

/* ── Beep notification ── */
function playBeep() {
  try {
    var ctx = new (window.AudioContext || window.webkitAudioContext)();
    var osc = ctx.createOscillator();
    var gain = ctx.createGain();
    osc.connect(gain); gain.connect(ctx.destination);
    osc.frequency.value = 880;
    osc.type = 'sine';
    gain.gain.setValueAtTime(0.3, ctx.currentTime);
    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.4);
    osc.start(ctx.currentTime);
    osc.stop(ctx.currentTime + 0.4);
  } catch(e) { /* no audio ctx */ }
}

/* ── Auto-reload for active tab every 30s ── */
function startAutoReload() {
  if (statusFilter === 'active') {
    setTimeout(function(){
      reloadPage();
    }, 30000);
  }
}

/* ── Init ── */
updateTimers();
timerTick = setInterval(updateTimers, 1000);
doPoll();
pollTimer  = setInterval(doPoll, 10000);
startAutoReload();
</script>
@endpush

</x-app-layout>
