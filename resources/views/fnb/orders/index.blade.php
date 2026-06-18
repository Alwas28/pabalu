<x-outlet-layout :outlet="$outlet" pageTitle="Antrian Order">

<style>
.order-card { background:var(--surface); border:2px solid var(--border); border-radius:16px; padding:16px; cursor:pointer; transition:border-color .15s,box-shadow .15s; display:block; }
.order-card:hover { border-color:var(--ac); box-shadow:0 4px 16px rgba(var(--ac-rgb),.15); }
.status-pill { display:inline-flex; align-items:center; gap:5px; padding:4px 10px; border-radius:99px; font-size:11.5px; font-weight:700; }
.column-header { font-size:11px; font-weight:700; letter-spacing:.8px; text-transform:uppercase; color:var(--muted); margin-bottom:10px; display:flex; align-items:center; gap:6px; }

.scan-modal { position:fixed; inset:0; background:rgba(0,0,0,.78); z-index:400; display:none; align-items:center; justify-content:center; padding:20px; backdrop-filter:blur(4px); }
.scan-modal.open { display:flex; }
.scan-box { background:var(--surface); border-radius:18px; width:100%; max-width:380px; overflow:hidden; }
.scan-head { display:flex; align-items:center; justify-content:space-between; padding:14px 18px; border-bottom:1px solid var(--border); }
.scan-head span { font-size:14px; font-weight:700; color:var(--text); }
.scan-head button { background:none; border:none; font-size:20px; color:var(--muted); cursor:pointer; line-height:1; }
#qr-reader { width:100%; background:#000; }
.scan-hint { padding:12px 18px 16px; font-size:12.5px; color:var(--muted); text-align:center; }
.scan-status { padding:10px 18px; font-size:12.5px; text-align:center; }

/* Pesanan Masuk card */
.pending-card{background:var(--surface);border:2px solid #fde68a;border-radius:16px;padding:16px;margin-bottom:10px}
.pending-actions{display:flex;gap:8px;margin-top:12px}
.btn-confirm{flex:1;padding:9px;border-radius:10px;border:none;background:#dcfce7;color:#15803d;font-size:12.5px;font-weight:700;cursor:pointer;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:6px}
.btn-confirm:hover{background:#bbf7d0}
.btn-reject{flex:1;padding:9px;border-radius:10px;border:none;background:#fee2e2;color:#dc2626;font-size:12.5px;font-weight:700;cursor:pointer;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:6px}
.btn-reject:hover{background:#fecaca}

/* Responsive */
@media(max-width:900px){
  .kanban-grid{grid-template-columns:1fr 1fr!important}
}
@media(max-width:640px){
  .kanban-grid{grid-template-columns:1fr!important}
  .header-actions{flex-wrap:wrap}
}
</style>

<div style="max-width:1200px;margin:0 auto;display:flex;flex-direction:column;gap:20px">

  {{-- Header --}}
  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
    <div>
      <h1 class="font-display" style="font-size:22px;font-weight:700;color:var(--text)">Antrian Order</h1>
      <p style="font-size:13px;color:var(--muted);margin-top:2px">{{ $outlet->name }} &mdash; real-time kitchen queue</p>
    </div>
    <div style="display:flex;align-items:center;gap:10px">
      <span style="font-size:13px;color:var(--muted)">
        <i class="fa-solid fa-circle-check" style="color:#34d399;margin-right:4px"></i>
        {{ $todayPaid }} lunas hari ini
      </span>
      <button onclick="openScanner()"
        style="display:inline-flex;align-items:center;gap:7px;padding:9px 14px;border-radius:12px;border:1px solid var(--border);background:var(--surface);color:var(--sub);font-size:13px;font-weight:600;cursor:pointer"
        onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='var(--surface)'">
        <i class="fa-solid fa-qrcode" style="font-size:11px"></i> Scan Struk
      </button>
      <button onclick="location.reload()"
        style="display:inline-flex;align-items:center;gap:7px;padding:9px 14px;border-radius:12px;border:1px solid var(--border);background:var(--surface);color:var(--sub);font-size:13px;font-weight:600;cursor:pointer"
        onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='var(--surface)'">
        <i class="fa-solid fa-rotate" style="font-size:11px"></i> Refresh
      </button>
    </div>
  </div>

  {{-- Columns: Pesanan Masuk / Open / Preparing / Served --}}
  @php
    $pendingOrders = $activeOrders['pending'] ?? collect();
    $cols = [
      'open'      => ['label'=>'Menunggu',    'color'=>'#fbbf24', 'bg'=>'rgba(251,191,36,.12)',  'icon'=>'fa-clock'],
      'preparing' => ['label'=>'Diproses',    'color'=>'#60a5fa', 'bg'=>'rgba(96,165,250,.12)',  'icon'=>'fa-fire-burner'],
      'served'    => ['label'=>'Diantarkan',  'color'=>'#34d399', 'bg'=>'rgba(52,211,153,.12)',  'icon'=>'fa-bell-concierge'],
    ];
    $colCount = $selfOrderEnabled ? 4 : 3;
  @endphp

  <div class="kanban-grid" style="display:grid;grid-template-columns:repeat({{ $colCount }},1fr);gap:16px;align-items:start">
    {{-- Kolom Pesanan Masuk (self-order, hanya tampil jika self order diaktifkan) --}}
    @if($selfOrderEnabled)
    <div style="background:rgba(251,191,36,.07);border:1.5px solid #fde68a;border-radius:16px;padding:14px;min-height:200px">
      <div class="column-header">
        <span style="width:28px;height:28px;border-radius:8px;background:rgba(251,191,36,.2);display:inline-flex;align-items:center;justify-content:center;font-size:12px;color:#f59e0b">
          <i class="fa-solid fa-mobile-screen-button"></i>
        </span>
        Pesanan Masuk
        <span style="margin-left:auto;background:rgba(251,191,36,.2);color:#d97706;padding:2px 8px;border-radius:99px;font-size:11px;font-weight:800">
          {{ $pendingOrders->count() }}
        </span>
      </div>

      @forelse($pendingOrders as $order)
      <div class="pending-card">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:8px">
          <div>
            <div style="font-size:11.5px;color:var(--muted);font-family:monospace">{{ $order->order_number }}</div>
            <div style="font-size:14px;font-weight:700;color:var(--text);margin-top:2px">
              {{ $order->customer_name ?: 'Pelanggan' }}
            </div>
            @if($order->table_number)
            <div style="font-size:11px;color:var(--muted);margin-top:1px">
              <i class="fa-solid fa-utensils" style="font-size:9px;margin-right:3px"></i>{{ $order->table_number }}
            </div>
            @endif
          </div>
          <div style="font-size:11px;color:var(--muted);text-align:right;white-space:nowrap">
            {{ $order->created_at->format('H:i') }}<br>
            <span style="font-size:10px">{{ $order->created_at->diffForHumans() }}</span>
          </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:3px;margin-bottom:8px">
          @foreach($order->items->take(4) as $item)
          <div style="font-size:12px;color:var(--text)">{{ $item->qty }}× {{ $item->product_name }}</div>
          @endforeach
          @if($order->items->count() > 4)
          <div style="font-size:11px;color:var(--muted)">+{{ $order->items->count() - 4 }} item lainnya</div>
          @endif
        </div>

        <div style="font-size:12px;color:var(--muted);border-top:1px solid #fde68a;padding-top:8px;margin-bottom:8px">
          {{ $order->items->count() }} item &middot; Rp {{ number_format($order->total,0,',','.') }}
          @if($order->notes)
          <div style="margin-top:4px;font-style:italic;font-size:11.5px;color:#92400e">
            <i class="fa-solid fa-note-sticky" style="font-size:10px;margin-right:3px"></i>{{ $order->notes }}
          </div>
          @endif
        </div>

        <div class="pending-actions">
          <form method="POST" action="{{ $outlet->route('orders.status', [$order]) }}" style="flex:1">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="open">
            <button type="submit" class="btn-confirm" style="width:100%">
              <i class="fa-solid fa-check"></i>Konfirmasi
            </button>
          </form>
          <form method="POST" action="{{ $outlet->route('orders.status', [$order]) }}" style="flex:1"
            onsubmit="return confirm('Tolak pesanan dari {{ addslashes($order->customer_name) }}?')">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="cancelled">
            <button type="submit" class="btn-reject" style="width:100%">
              <i class="fa-solid fa-xmark"></i>Tolak
            </button>
          </form>
        </div>
      </div>
      @empty
      <div style="padding:32px 16px;text-align:center;color:var(--muted)">
        <i class="fa-solid fa-mobile-screen-button" style="font-size:24px;opacity:.3;display:block;margin-bottom:8px"></i>
        <p style="font-size:12.5px">Tidak ada pesanan masuk</p>
      </div>
      @endforelse
    </div>
    @endif

    @foreach($cols as $status => $col)
    @php $orders = $activeOrders[$status] ?? collect(); @endphp
    <div style="background:var(--surface2);border-radius:16px;padding:14px;min-height:200px">
      <div class="column-header">
        <span style="width:28px;height:28px;border-radius:8px;background:{{ $col['bg'] }};display:inline-flex;align-items:center;justify-content:center;font-size:12px;color:{{ $col['color'] }}">
          <i class="fa-solid {{ $col['icon'] }}"></i>
        </span>
        {{ $col['label'] }}
        <span style="margin-left:auto;background:{{ $col['bg'] }};color:{{ $col['color'] }};padding:2px 8px;border-radius:99px;font-size:11px;font-weight:800">
          {{ $orders->count() }}
        </span>
      </div>

      @forelse($orders as $order)
      <div onclick="location.href='{{ $outlet->route('orders.show', [$order]) }}'"
        class="order-card" style="margin-bottom:10px;position:relative">
        <a href="{{ $outlet->route('orders.bill', [$order]) }}" target="_blank" onclick="event.stopPropagation()"
          title="Cetak Struk Pesanan"
          style="position:absolute;top:12px;right:12px;width:26px;height:26px;border-radius:8px;background:var(--surface2);display:flex;align-items:center;justify-content:center;color:var(--muted);text-decoration:none;z-index:1">
          <i class="fa-solid fa-receipt" style="font-size:11px"></i>
        </a>
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:10px">
          <div>
            <div style="font-size:11.5px;color:var(--muted);font-family:monospace">{{ $order->order_number }}</div>
            <div style="font-size:14px;font-weight:700;color:var(--text);margin-top:2px">
              {{ $order->customer_name ?: ($order->table_number ? 'Meja '.$order->table_number : 'Walk-in') }}
            </div>
            @if($order->table_number)
            <div style="font-size:11px;color:var(--muted);margin-top:1px">
              <i class="fa-solid fa-utensils" style="font-size:9px;margin-right:3px"></i>{{ $order->table_number }}
            </div>
            @endif
          </div>
          <div style="font-size:11px;color:var(--muted);text-align:right;white-space:nowrap;margin-right:30px">
            {{ $order->created_at->format('H:i') }}<br>
            <span style="font-size:10px">{{ $order->created_at->diffForHumans() }}</span>
          </div>
        </div>

        {{-- Items --}}
        <div style="display:flex;flex-direction:column;gap:4px;margin-bottom:10px">
          @foreach($order->items->take(4) as $item)
          <div style="display:flex;justify-content:space-between;font-size:12px;color:{{ $item->is_served ? 'var(--muted)' : 'var(--text)' }}">
            <span @if($item->is_served) style="text-decoration:line-through" @endif>
              {{ $item->qty }}× {{ $item->product_name }}
            </span>
            @if($item->is_served)
            <i class="fa-solid fa-check" style="color:#34d399;font-size:10px"></i>
            @endif
          </div>
          @endforeach
          @if($order->items->count() > 4)
          <div style="font-size:11px;color:var(--muted)">+{{ $order->items->count() - 4 }} item lainnya</div>
          @endif
        </div>

        {{-- Footer --}}
        <div style="display:flex;align-items:center;justify-content:space-between;border-top:1px solid var(--border);padding-top:8px">
          <span style="font-size:12px;color:var(--muted)">
            {{ $order->items->count() }} item &middot; Rp {{ number_format($order->total,0,',','.') }}
          </span>
          @php
            $served = $order->items->where('is_served', true)->count();
            $total  = $order->items->count();
          @endphp
          @if($total > 0)
          <span style="font-size:11px;color:{{ $served === $total ? '#34d399' : 'var(--muted)' }};font-weight:600">
            {{ $served }}/{{ $total }} diantarkan
          </span>
          @endif
        </div>
      </div>
      @empty
      <div style="padding:32px 16px;text-align:center;color:var(--muted)">
        <i class="fa-solid fa-inbox" style="font-size:24px;opacity:.3;display:block;margin-bottom:8px"></i>
        <p style="font-size:12.5px">Tidak ada order</p>
      </div>
      @endforelse
    </div>
    @endforeach
  </div>

  @php $hasAnyOrder = $activeOrders->filter->isNotEmpty()->isNotEmpty(); @endphp
  @if(!$hasAnyOrder)
  <div style="padding:60px;text-align:center;background:var(--surface2);border-radius:16px">
    <i class="fa-solid fa-fire-burner" style="font-size:40px;color:var(--muted);opacity:.3;display:block;margin-bottom:14px"></i>
    <h3 class="font-display" style="font-size:16px;color:var(--text);margin-bottom:6px">Antrian Kosong</h3>
    <p style="font-size:13px;color:var(--muted)">Belum ada order aktif. Order baru akan muncul di sini secara otomatis.</p>
  </div>
  @endif

</div>

{{-- Modal scan QR struk --}}
<div id="scan-modal" class="scan-modal">
  <div class="scan-box">
    <div class="scan-head">
      <span><i class="fa-solid fa-qrcode" style="margin-right:7px;color:var(--ac)"></i>Scan QR Struk</span>
      <button onclick="closeScanner()">&times;</button>
    </div>
    <div id="qr-reader"></div>
    <div id="scan-status" class="scan-status"></div>
    <p class="scan-hint">Arahkan kamera ke QR code pada struk pesanan untuk membuka detail order</p>
  </div>
</div>

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
const lookupUrl = '{{ $outlet->route('orders.lookup') }}';
let html5QrCode = null;
let scanBusy = false;

function setScanStatus(msg, isError) {
  const el = document.getElementById('scan-status');
  el.textContent = msg || '';
  el.style.color = isError ? '#ef4444' : 'var(--muted)';
}

function openScanner() {
  document.getElementById('scan-modal').classList.add('open');
  setScanStatus('');
  scanBusy = false;
  html5QrCode = new Html5Qrcode('qr-reader');
  html5QrCode.start(
    { facingMode: 'environment' },
    { fps: 10, qrbox: 220 },
    onScanSuccess
  ).catch(err => {
    setScanStatus('Tidak bisa mengakses kamera: ' + err, true);
  });
}

function closeScanner() {
  document.getElementById('scan-modal').classList.remove('open');
  if (html5QrCode) {
    html5QrCode.stop().then(() => html5QrCode.clear()).catch(() => {});
    html5QrCode = null;
  }
}

async function onScanSuccess(decodedText) {
  if (scanBusy) return;
  scanBusy = true;
  setScanStatus('Memproses...', false);

  try {
    const url = new URL(decodedText);
    const parts = url.pathname.split('/').filter(Boolean);
    const idx = parts.indexOf('pesanan');
    const orderNumber = idx >= 0 ? parts[idx + 1] : null;
    if (!orderNumber) throw new Error('QR tidak dikenali sebagai struk pesanan.');

    const res = await fetch(lookupUrl + '?order_number=' + encodeURIComponent(orderNumber), {
      headers: { 'Accept': 'application/json' },
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.message || 'Order tidak ditemukan.');

    setScanStatus('Order ditemukan, membuka...', false);
    location.href = data.redirect_url;

  } catch (e) {
    setScanStatus(e.message, true);
    scanBusy = false;
  }
}

// Auto-refresh setiap 30 detik
let refreshTimer = setInterval(() => location.reload(), 30000);
let countdown = 30;
const refreshBtn = document.querySelector('[onclick="location.reload()"]');

setInterval(() => {
  countdown--;
  if (countdown <= 0) countdown = 30;
  if (refreshBtn) refreshBtn.title = `Auto-refresh dalam ${countdown}s`;
}, 1000);
</script>
@endpush

</x-outlet-layout>
