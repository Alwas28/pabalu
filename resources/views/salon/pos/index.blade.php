<x-outlet-layout :outlet="$outlet" pageTitle="POS / Kasir">

@php
  $canDiscount  = $user->hasPermission('pos.discount');
  $canSell      = $openingDone;
  $isKitchen    = $orderMode === 'kitchen';
@endphp

{{-- Override layout content area for full-height POS --}}
<style>
#content { padding:0 !important; max-width:none !important; gap:0 !important; }
#pos-wrap { display:grid; grid-template-columns:1fr 380px; height:calc(100vh - 65px); overflow:hidden; position:relative; }
#prod-panel { overflow-y:auto; padding:16px 20px; display:flex; flex-direction:column; gap:14px; }
#cart-panel { border-left:1px solid var(--border); display:flex; flex-direction:column; background:var(--surface); }
.prod-grid  { display:grid; grid-template-columns:repeat(auto-fill,minmax(150px,1fr)); gap:12px; }
.prod-card  { background:var(--surface); border:2px solid var(--border); border-radius:14px; padding:12px; cursor:pointer; transition:border-color .15s,transform .1s,box-shadow .15s; position:relative; user-select:none; }
.prod-card:hover:not(.out-of-stock) { border-color:var(--ac); box-shadow:0 4px 16px rgba(var(--ac-rgb),.2); transform:translateY(-1px); }
.prod-card.in-cart { border-color:var(--ac); background:var(--ac-lt2); }
.prod-card.out-of-stock { opacity:.45; cursor:not-allowed; }
.prod-img   { width:100%; aspect-ratio:1; border-radius:10px; margin-bottom:8px; background:var(--surface2); display:flex; align-items:center; justify-content:center; background-size:cover; background-position:center; }
.cart-item  { display:flex; align-items:center; gap:10px; padding:10px 16px; border-bottom:1px solid var(--border); }
.qty-ctrl   { display:flex; align-items:center; gap:0; }
.qty-btn    { width:26px; height:26px; border-radius:6px; border:1px solid var(--border); background:var(--surface2); color:var(--text); font-size:13px; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background .12s; }
.qty-btn:hover { background:var(--border); }
.cat-pill   { padding:6px 14px; border-radius:99px; border:1px solid var(--border); background:transparent; font-size:12.5px; font-weight:600; color:var(--sub); cursor:pointer; transition:all .15s; white-space:nowrap; }
.cat-pill.active { background:var(--ac-lt); color:var(--ac); border-color:var(--ac); }
#mobile-cart-btn  { display:none; }
.cart-close-mobile { display:none; }
@media (max-width:900px) {
  #pos-wrap { grid-template-columns:1fr; }
  #cart-panel { position:fixed !important; top:65px; right:0; bottom:0; width:100%; max-width:400px; z-index:150; transform:translateX(100%); transition:transform .28s ease; box-shadow:-6px 0 30px rgba(0,0,0,.2); border-left:1px solid var(--border); }
  #cart-panel.mobile-open { transform:translateX(0); }
  #cart-mob-overlay { position:fixed;inset:0;z-index:149;background:rgba(0,0,0,.4);display:none; }
  #cart-mob-overlay.open { display:block; }
  #mobile-cart-btn { display:flex !important; }
  .cart-close-mobile { display:flex !important; }
}
@media print { body { display:none !important; } }
</style>

<div id="pos-wrap">

{{-- ════ OPENING STOK BELUM DILAKUKAN ════ --}}
@if(!$canSell)
<div style="position:absolute;inset:0;z-index:100;background:rgba(0,0,0,.72);backdrop-filter:blur(6px);display:flex;align-items:center;justify-content:center;padding:24px">
  <div style="background:var(--surface);border:1px solid var(--border);border-radius:24px;padding:40px 36px;max-width:460px;width:100%;text-align:center;box-shadow:0 24px 64px rgba(0,0,0,.6)">
    <div style="width:72px;height:72px;border-radius:20px;background:rgba(251,191,36,.15);display:grid;place-items:center;margin:0 auto 20px;font-size:30px;color:#fbbf24">
      <i class="fa-solid fa-sun"></i>
    </div>
    <h2 class="font-display" style="font-size:22px;font-weight:700;color:var(--text);margin-bottom:10px">
      Opening Stok Belum Dilakukan
    </h2>
    <p style="font-size:14px;color:var(--muted);line-height:1.7;margin-bottom:8px">
      Outlet ini wajib melakukan <strong style="color:var(--text)">opening stok</strong> setiap pagi sebelum mulai berjualan.
    </p>
    <p style="font-size:13px;color:var(--muted);line-height:1.6;margin-bottom:28px">
      Input jumlah porsi/stok yang disiapkan hari ini, lalu kasir bisa langsung melayani pelanggan.
    </p>
    @if($user->hasPermission('stock.opening'))
    <a href="{{ $outlet->route('opening.index') }}"
      style="display:inline-flex;align-items:center;gap:10px;padding:13px 28px;border-radius:14px;background:linear-gradient(135deg,#f59e0b,#f97316);color:#fff;font-size:15px;font-weight:700;text-decoration:none;transition:opacity .15s;font-family:'Clash Display',sans-serif"
      onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
      <i class="fa-solid fa-sun"></i> Lakukan Opening Stok Sekarang
    </a>
    @else
    <div style="padding:14px 18px;border-radius:12px;background:rgba(251,191,36,.1);border:1px solid rgba(251,191,36,.25);font-size:13.5px;color:#fbbf24;font-weight:600">
      <i class="fa-solid fa-clock" style="margin-right:8px"></i>
      Hubungi admin/owner untuk melakukan opening stok hari ini.
    </div>
    @endif
  </div>
</div>
@endif

{{-- ════════════════════════════════════════════
     LEFT — PRODUK
════════════════════════════════════════════ --}}
<div id="prod-panel">

  {{-- Barcode scanner bar --}}
  @if($outlet->enable_barcode_scanner)
  <x-barcode-scanner />
  @endif

  {{-- Search + Category filter --}}
  <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:center">
    <div style="flex:1;min-width:200px;position:relative">
      <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:12px"></i>
      <input id="pos-search" type="text" placeholder="Cari produk..."
        style="width:100%;background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:9px 10px 9px 32px;font-size:13.5px;color:var(--text);outline:none;font-family:inherit;transition:border-color .15s"
        onfocus="this.style.borderColor='var(--ac)'" onblur="this.style.borderColor='var(--border)'"
        oninput="filterProducts()">
    </div>
    <div id="cat-pills" style="display:flex;gap:8px;overflow-x:auto;padding-bottom:2px;scrollbar-width:none">
      <button class="cat-pill active" data-cat="all" onclick="filterByCat(this,'all')">Semua</button>
      @foreach($categories as $cat)
      <button class="cat-pill" data-cat="{{ $cat->id }}" onclick="filterByCat(this,{{ $cat->id }})">{{ $cat->name }}</button>
      @endforeach
    </div>
  </div>

  {{-- Product grid --}}
  <div id="prod-grid" class="prod-grid">
    @forelse($products as $p)
    <div class="prod-card {{ ($trackCogs && $p->stock <= 0) ? 'out-of-stock' : '' }}"
      id="pcard-{{ $p->id }}"
      data-id="{{ $p->id }}"
      data-name="{{ $p->name }}"
      data-sku="{{ $p->sku ?? '' }}"
      data-price="{{ $p->price }}"
      data-stock="{{ $p->stock }}"
      data-min-stock="{{ $p->min_stock }}"
      data-unit="{{ $p->unit }}"
      data-cat="{{ $p->category_id ?? 'none' }}"
      data-image="{{ $p->image ? Storage::url($p->image) : '' }}"
      onclick="cardClick({{ $p->id }})">

      {{-- Image / icon --}}
      <div class="prod-img" @if($p->image) style="background-image:url('{{ Storage::url($p->image) }}')" @endif>
        @unless($p->image)
          <i class="fa-solid fa-cube" style="font-size:28px;color:var(--muted)"></i>
        @endunless
      </div>

      {{-- Stock badge (hanya jika track stok) --}}
      <span id="stock-badge-{{ $p->id }}">
      @if($trackCogs)
        @if($p->stock <= 0)
          <span style="position:absolute;top:8px;left:8px;background:#ef4444;color:#fff;font-size:10px;font-weight:700;padding:2px 7px;border-radius:99px">HABIS</span>
        @elseif($p->isLowStock())
          <span style="position:absolute;top:8px;left:8px;background:#f97316;color:#fff;font-size:10px;font-weight:700;padding:2px 7px;border-radius:99px">{{ $p->stock }} tersisa</span>
        @endif
      @endif
      </span>

      {{-- Cart qty badge --}}
      <span id="qbadge-{{ $p->id }}" style="display:none;position:absolute;top:8px;right:8px;background:var(--ac);color:#fff;font-size:11px;font-weight:800;min-width:22px;height:22px;border-radius:99px;display:none;align-items:center;justify-content:center;padding:0 5px"></span>

      <div style="font-size:13px;font-weight:700;color:var(--text);margin-bottom:4px;line-height:1.3">{{ $p->name }}</div>
      <div style="font-size:13.5px;font-weight:800;color:var(--ac)">Rp {{ number_format($p->price, 0, ',', '.') }}</div>
      @if($trackCogs)
      <div id="stock-txt-{{ $p->id }}" style="font-size:11px;color:var(--muted);margin-top:2px">Stok: {{ $p->stock }} {{ $p->unit }}</div>
      @endif
    </div>
    @empty
    <div style="grid-column:1/-1;padding:48px;text-align:center">
      <i class="fa-solid fa-box-open" style="font-size:36px;color:var(--muted);display:block;margin-bottom:12px"></i>
      <p style="color:var(--muted);font-size:14px">Belum ada produk aktif</p>
    </div>
    @endforelse
  </div>

  <div id="no-results" style="display:none;padding:48px;text-align:center">
    <i class="fa-solid fa-magnifying-glass" style="font-size:32px;color:var(--muted);display:block;margin-bottom:10px"></i>
    <p style="color:var(--muted)">Tidak ada produk yang cocok</p>
  </div>
</div>

{{-- ════════════════════════════════════════════
     RIGHT — CART
════════════════════════════════════════════ --}}
<div id="cart-panel">

  {{-- Cart Header --}}
  <div style="padding:14px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-shrink:0">
    <div style="display:flex;align-items:center;gap:8px">
      <i class="fa-solid fa-cart-shopping" style="color:var(--ac);font-size:15px"></i>
      <span class="font-display" style="font-size:15px;font-weight:700;color:var(--text)">Pesanan</span>
      <span id="cart-count" style="background:var(--ac);color:#fff;font-size:11px;font-weight:800;min-width:20px;height:20px;border-radius:99px;display:inline-flex;align-items:center;justify-content:center;padding:0 5px">0</span>
    </div>
    <div style="display:flex;align-items:center;gap:6px">
      <button onclick="clearCart()" id="clear-btn"
        style="display:none;align-items:center;gap:5px;padding:5px 10px;border-radius:8px;border:1px solid rgba(239,68,68,.3);background:transparent;color:#f87171;font-size:12px;font-weight:600;cursor:pointer;transition:all .15s"
        onmouseover="this.style.background='rgba(239,68,68,.1)'" onmouseout="this.style.background='transparent'">
        <i class="fa-solid fa-trash-can" style="font-size:10px"></i> Kosongkan
      </button>
      <button class="cart-close-mobile" onclick="closeMobileCart()"
        style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:14px;cursor:pointer;align-items:center;justify-content:center">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
  </div>

  {{-- Cart Items (scrollable) --}}
  <div id="cart-items" style="flex:1;overflow-y:auto;scrollbar-width:thin">
    {{-- Empty state --}}
    <div id="cart-empty" style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;gap:12px;color:var(--muted);padding:24px">
      <i class="fa-solid fa-cart-shopping" style="font-size:40px;opacity:.3"></i>
      <div style="text-align:center">
        <p style="font-size:14px;font-weight:600;margin-bottom:4px">Keranjang Kosong</p>
        <p style="font-size:12.5px">Tap produk untuk menambahkan ke pesanan</p>
      </div>
    </div>
  </div>

  {{-- Catatan order --}}
  <div id="notes-area" style="display:none;padding:10px 14px;border-top:1px solid var(--border);flex-shrink:0">
    <input id="order-notes" type="text" placeholder="Catatan pesanan (opsional)..."
      style="width:100%;background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:7px 10px;font-size:12.5px;color:var(--text);outline:none;font-family:inherit">
  </div>

  @if($isKitchen)
  {{-- Info pemesan (kitchen mode only) --}}
  <div id="table-area" style="display:none;padding:10px 14px;border-top:1px solid var(--border);flex-shrink:0">
    <div style="display:flex;flex-direction:column;gap:7px">
      <div style="display:flex;align-items:center;gap:8px">
        <i class="fa-solid fa-user" style="color:var(--ac);font-size:12px;flex-shrink:0;width:14px;text-align:center"></i>
        <input id="customer-name" type="text" placeholder="Nama pemesan (opsional)..."
          style="flex:1;background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:7px 10px;font-size:12.5px;color:var(--text);outline:none;font-family:inherit">
      </div>
      <div style="display:flex;align-items:center;gap:8px">
        <i class="fa-solid fa-utensils" style="color:var(--ac);font-size:11px;flex-shrink:0;width:14px;text-align:center"></i>
        <input id="table-number" type="text" placeholder="No. meja (opsional)..."
          style="flex:1;background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:7px 10px;font-size:12.5px;color:var(--text);outline:none;font-family:inherit">
      </div>
    </div>
  </div>
  @endif

  {{-- Discount --}}
  @if($canDiscount)
  <div id="discount-area" style="display:none;padding:10px 14px;border-top:1px solid var(--border);flex-shrink:0">
    <div style="display:flex;align-items:center;gap:8px">
      <div style="display:flex;border:1px solid var(--border);border-radius:8px;overflow:hidden;flex-shrink:0">
        <button id="disc-pct-btn" onclick="setDiscountMode('percent')"
          style="padding:5px 10px;border:none;background:var(--ac);color:#fff;font-size:12px;font-weight:700;cursor:pointer">%</button>
        <button id="disc-amt-btn" onclick="setDiscountMode('amount')"
          style="padding:5px 10px;border:none;background:transparent;color:var(--sub);font-size:12px;font-weight:700;cursor:pointer">Rp</button>
      </div>
      <input id="discount-input" type="number" min="0" placeholder="0"
        style="flex:1;background:var(--surface2);border:1px solid var(--border);border-radius:8px;padding:6px 10px;font-size:13px;color:var(--text);outline:none;font-family:inherit"
        oninput="updateTotals()">
      <span id="discount-preview" style="font-size:12px;color:#34d399;font-weight:700;white-space:nowrap"></span>
    </div>
  </div>
  @endif

  {{-- Totals --}}
  <div id="totals-area" style="display:none;padding:12px 16px;border-top:1px solid var(--border);flex-shrink:0;background:var(--surface2)">
    <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--sub);margin-bottom:6px">
      <span>Subtotal</span>
      <span id="subtotal-display">Rp 0</span>
    </div>
    @if($canDiscount)
    <div id="discount-row" style="display:none;justify-content:space-between;font-size:13px;color:#34d399;margin-bottom:6px">
      <span id="discount-label">Diskon</span>
      <span id="discount-display">−Rp 0</span>
    </div>
    @endif
    <div style="display:flex;justify-content:space-between;font-size:17px;font-weight:800;color:var(--text);padding-top:8px;border-top:1px solid var(--border)">
      <span class="font-display">TOTAL</span>
      <span id="total-display">Rp 0</span>
    </div>
  </div>

  {{-- Action buttons --}}
  <div style="padding:12px 14px;border-top:1px solid var(--border);flex-shrink:0;display:flex;flex-direction:column;gap:8px">
    @if($canDiscount)
    <button id="toggle-discount" onclick="toggleDiscount()"
      style="display:none;width:100%;padding:7px;border-radius:10px;border:1px solid var(--border);background:transparent;color:var(--sub);font-size:12.5px;font-weight:600;cursor:pointer;transition:all .15s"
      onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
      <i class="fa-solid fa-tag" style="margin-right:5px;font-size:11px"></i>Tambah Diskon
    </button>
    @endif
    @if($isKitchen)
    <button id="pay-btn" onclick="sendToKitchen()" disabled
      style="width:100%;padding:14px;border-radius:12px;border:none;background:linear-gradient(135deg,#f59e0b,#f97316);color:#fff;font-size:15px;font-weight:800;cursor:pointer;transition:opacity .15s;opacity:.5;font-family:'Clash Display',sans-serif;letter-spacing:.3px"
      onmouseover="if(!this.disabled)this.style.opacity='.88'" onmouseout="this.style.opacity=this.disabled?'.5':'1'">
      <i class="fa-solid fa-fire-burner" style="margin-right:8px;font-size:13px"></i>
      KIRIM KE DAPUR
    </button>
    @else
    <button id="pay-btn" onclick="openPayment()" disabled
      style="width:100%;padding:14px;border-radius:12px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:15px;font-weight:800;cursor:pointer;transition:opacity .15s;opacity:.5;font-family:'Clash Display',sans-serif;letter-spacing:.3px"
      onmouseover="if(!this.disabled)this.style.opacity='.88'" onmouseout="this.style.opacity=this.disabled?'.5':'1'">
      <i class="fa-solid fa-cash-register" style="margin-right:8px;font-size:13px"></i>
      BAYAR  <span id="pay-total-label"></span>
    </button>
    @endif
  </div>

</div>
</div>{{-- end #pos-wrap --}}

{{-- Mobile: cart overlay backdrop --}}
<div id="cart-mob-overlay" onclick="closeMobileCart()"></div>

{{-- Mobile: floating cart button --}}
<button id="mobile-cart-btn" onclick="openMobileCart()"
  style="position:fixed;bottom:24px;right:24px;z-index:140;width:62px;height:62px;border-radius:50%;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;cursor:pointer;box-shadow:0 4px 20px rgba(0,0,0,.25);flex-direction:column;align-items:center;justify-content:center;gap:2px">
  <i class="fa-solid fa-cart-shopping" style="font-size:18px"></i>
  <span id="mobile-cart-count" style="font-size:9px;font-weight:800;line-height:1">0 item</span>
</button>

{{-- ════════════════════════════════════════════
     MODAL PEMBAYARAN
════════════════════════════════════════════ --}}
<div id="modal-payment" class="modal-backdrop" onclick="if(event.target===this)closePayment()">
  <div class="modal-box" style="max-width:440px">

    {{-- Header --}}
    <div style="padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
      <h3 class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">
        <i class="fa-solid fa-cash-register" style="color:var(--ac);margin-right:8px;font-size:13px"></i>Pembayaran
      </h3>
      <button onclick="closePayment()"
        style="width:28px;height:28px;border-radius:8px;border:none;background:var(--surface2);color:var(--sub);font-size:13px;cursor:pointer">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <div style="padding:18px 22px;display:flex;flex-direction:column;gap:16px">

      {{-- Item list --}}
      <div id="pm-items" style="border:1px solid var(--border);border-radius:10px;overflow:hidden;max-height:140px;overflow-y:auto;scrollbar-width:thin"></div>

      {{-- Total --}}
      <div style="text-align:center;padding:14px;background:var(--ac-lt);border-radius:12px">
        <div style="font-size:12px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.8px;margin-bottom:4px">Total Pembayaran</div>
        <div id="pm-total" class="font-display" style="font-size:28px;font-weight:800;color:var(--ac)">Rp 0</div>
      </div>

      {{-- Metode --}}
      <div>
        <div style="font-size:12px;font-weight:600;color:var(--muted);margin-bottom:8px;text-transform:uppercase;letter-spacing:.5px">Metode Pembayaran</div>
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px">
          @foreach(['cash'=>['Tunai','fa-money-bill-wave'],'qris'=>['QRIS','fa-qrcode'],'transfer'=>['Transfer','fa-building-columns'],'card'=>['Kartu','fa-credit-card']] as $m=>[$ml,$mi])
          <button class="pay-method-btn" data-method="{{ $m }}" onclick="selectMethod('{{ $m }}')"
            style="padding:10px 6px;border-radius:10px;border:2px solid var(--border);background:var(--surface2);cursor:pointer;transition:all .15s;display:flex;flex-direction:column;align-items:center;gap:5px">
            <i class="fa-solid {{ $mi }}" style="font-size:16px;color:var(--muted)"></i>
            <span style="font-size:11px;font-weight:700;color:var(--sub)">{{ $ml }}</span>
          </button>
          @endforeach
        </div>
      </div>

      {{-- Cash input section --}}
      <div id="cash-section" style="display:flex;flex-direction:column;gap:10px">
        <div>
          <div style="font-size:12px;font-weight:600;color:var(--muted);margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px">Uang Diterima</div>
          <div style="position:relative">
            <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:13px;color:var(--muted);font-weight:600">Rp</span>
            <input id="cash-input" type="text" inputmode="numeric" placeholder="0"
              style="width:100%;background:var(--surface2);border:2px solid var(--ac);border-radius:12px;padding:10px 10px 10px 34px;font-size:18px;font-weight:700;color:var(--text);outline:none;font-family:inherit"
              oninput="syncCash(this)" onblur="formatCashDisplay(this)">
          </div>
        </div>
        {{-- Quick amounts --}}
        <div id="quick-amounts" style="display:flex;gap:6px;flex-wrap:wrap"></div>
        {{-- Kembalian --}}
        <div id="change-row" style="display:flex;justify-content:space-between;align-items:center;padding:10px 14px;border-radius:10px;background:var(--surface2)">
          <span style="font-size:13px;color:var(--sub);font-weight:600">Kembalian</span>
          <span id="change-display" style="font-size:16px;font-weight:800;color:#34d399">Rp 0</span>
        </div>
      </div>

      {{-- Non-cash section --}}
      <div id="noncash-section" style="display:none">
        <div style="display:flex;flex-direction:column;gap:12px">
          {{-- Konfirmasi --}}
          <div style="text-align:center;padding:14px;border-radius:12px;background:var(--surface2);border:1px solid var(--border)">
            <i class="fa-solid fa-circle-check" style="font-size:28px;color:#34d399;margin-bottom:8px;display:block"></i>
            <p style="font-size:13.5px;color:var(--sub)">Konfirmasi pembayaran <span id="pm-method-label" style="font-weight:700;color:var(--text)"></span></p>
            <p style="font-size:18px;font-weight:800;color:var(--text);margin-top:6px" id="pm-noncash-total">Rp 0</p>
          </div>
          {{-- Nomor Referensi --}}
          <div>
            <div style="font-size:11.5px;font-weight:600;color:var(--muted);margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px">
              No. Referensi <span style="font-weight:400;text-transform:none">(opsional)</span>
            </div>
            <input id="pm-reference" type="text" maxlength="100" placeholder="Contoh: TRF12345 / ID transaksi QRIS"
              style="width:100%;background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:8px 12px;font-size:13px;color:var(--text);outline:none;font-family:inherit;transition:border-color .15s"
              onfocus="this.style.borderColor='var(--ac)'" onblur="this.style.borderColor='var(--border)'">
          </div>
          {{-- Bukti Foto --}}
          <div>
            <div style="font-size:11.5px;font-weight:600;color:var(--muted);margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px">
              Bukti Foto <span style="font-weight:400;text-transform:none">(opsional)</span>
            </div>
            <label id="proof-label" for="pm-proof"
              style="display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:10px;border:1.5px dashed var(--border);background:var(--surface2);cursor:pointer;transition:border-color .15s"
              onmouseenter="this.style.borderColor='var(--ac)'" onmouseleave="this.style.borderColor='var(--border)'">
              <i class="fa-solid fa-camera" style="font-size:15px;color:var(--muted)"></i>
              <span id="proof-filename" style="font-size:13px;color:var(--muted)">Pilih foto bukti pembayaran</span>
            </label>
            <input type="file" id="pm-proof" accept="image/*" style="display:none" onchange="previewProof(this)">
            <div id="proof-preview" style="display:none;margin-top:8px;position:relative">
              <img id="proof-img" src="" alt="bukti"
                style="max-width:100%;width:100%;border-radius:10px;max-height:140px;object-fit:cover">
              <button onclick="clearProof()" type="button"
                style="position:absolute;top:6px;right:6px;width:26px;height:26px;border-radius:7px;border:none;background:rgba(0,0,0,.6);color:#fff;cursor:pointer;font-size:11px;display:grid;place-items:center">
                <i class="fa-solid fa-xmark"></i>
              </button>
            </div>
          </div>
        </div>
      </div>

      {{-- Catatan --}}
      <div>
        <div style="font-size:12px;font-weight:600;color:var(--muted);margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px">Catatan <span style="font-weight:400;text-transform:none">(opsional)</span></div>
        <input id="pm-notes" type="text" placeholder="Catatan untuk transaksi ini..."
          style="width:100%;background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:8px 12px;font-size:13px;color:var(--text);outline:none;font-family:inherit">
      </div>

    </div>

    {{-- Footer --}}
    <div style="padding:14px 22px;border-top:1px solid var(--border);display:flex;gap:10px">
      <button onclick="closePayment()"
        style="flex:0 0 auto;padding:11px 18px;border-radius:12px;border:1px solid var(--border);background:transparent;color:var(--sub);font-size:13.5px;font-weight:600;cursor:pointer">
        Batal
      </button>
      <button id="confirm-btn" onclick="confirmPayment()"
        style="flex:1;padding:11px;border-radius:12px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:14px;font-weight:800;cursor:pointer;font-family:'Clash Display',sans-serif;transition:opacity .15s"
        onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
        <i class="fa-solid fa-check" style="margin-right:6px"></i>Konfirmasi Pembayaran
      </button>
    </div>
  </div>
</div>

{{-- ════════════════════════════════════════════
     MODAL STRUK / RECEIPT
════════════════════════════════════════════ --}}
<div id="modal-receipt" class="modal-backdrop">
  <div class="modal-box" style="max-width:420px">

    {{-- Header success --}}
    <div style="padding:22px 24px 14px;text-align:center;border-bottom:1px solid var(--border)">
      <div style="width:54px;height:54px;border-radius:16px;background:rgba(16,185,129,.15);display:grid;place-items:center;margin:0 auto 12px;font-size:22px;color:#34d399">
        <i class="fa-solid fa-circle-check"></i>
      </div>
      <h3 class="font-display" style="font-size:18px;font-weight:700;color:var(--text)">Transaksi Berhasil!</h3>
      <p id="rc-number" style="font-size:12.5px;color:var(--muted);margin-top:4px;font-family:monospace"></p>
    </div>

    {{-- Receipt body --}}
    <div style="padding:16px 22px;max-height:360px;overflow-y:auto">

      {{-- Outlet & waktu --}}
      <div style="text-align:center;margin-bottom:14px">
        <div style="font-weight:700;font-size:14px;color:var(--text)">{{ $outlet->name }}</div>
        <div id="rc-time" style="font-size:12px;color:var(--muted)"></div>
        <div id="rc-cashier" style="font-size:12px;color:var(--muted)"></div>
      </div>

      {{-- Items --}}
      <div id="rc-items" style="border-top:1px dashed var(--border);padding-top:12px;margin-bottom:12px"></div>

      {{-- Summary --}}
      <div id="rc-summary" style="border-top:1px dashed var(--border);padding-top:12px;display:flex;flex-direction:column;gap:6px"></div>
    </div>

    {{-- Buttons --}}
    <div style="padding:14px 22px;border-top:1px solid var(--border);display:flex;gap:10px">
      <button onclick="printReceipt()"
        style="flex:0 0 auto;padding:10px 16px;border-radius:12px;border:1px solid var(--border);background:transparent;color:var(--sub);font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px"
        onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
        <i class="fa-solid fa-print" style="font-size:11px"></i> Cetak
      </button>
      <button onclick="newTransaction()"
        style="flex:1;padding:10px;border-radius:12px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:14px;font-weight:800;cursor:pointer;font-family:'Clash Display',sans-serif">
        <i class="fa-solid fa-plus" style="margin-right:6px;font-size:12px"></i>Transaksi Baru
      </button>
    </div>
  </div>
</div>

@if($isKitchen)
{{-- ════════════════════════════════════════════
     MODAL ORDER BERHASIL DIKIRIM (kitchen mode)
════════════════════════════════════════════ --}}
<div id="modal-order-success" class="modal-backdrop">
  <div class="modal-box" style="max-width:400px">
    <div style="padding:24px 24px 16px;text-align:center;border-bottom:1px solid var(--border)">
      <div style="width:60px;height:60px;border-radius:18px;background:rgba(245,158,11,.15);display:grid;place-items:center;margin:0 auto 14px;font-size:26px;color:#f59e0b">
        <i class="fa-solid fa-fire-burner"></i>
      </div>
      <h3 class="font-display" style="font-size:18px;font-weight:700;color:var(--text)">Order Dikirim ke Dapur!</h3>
      <p id="ok-order-number" style="font-size:12.5px;color:var(--muted);margin-top:5px;font-family:monospace"></p>
    </div>
    <div style="padding:16px 22px">
      <div style="background:var(--surface2);border-radius:12px;padding:12px 16px;display:flex;flex-direction:column;gap:8px">
        <div style="display:flex;justify-content:space-between;font-size:13px">
          <span style="color:var(--muted)">Nomor Order</span>
          <span id="ok-order-num2" style="font-weight:700;color:var(--text);font-family:monospace"></span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:13px">
          <span style="color:var(--muted)">Pemesan</span>
          <span id="ok-customer" style="font-weight:700;color:var(--text)"></span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:13px">
          <span style="color:var(--muted)">No. Meja</span>
          <span id="ok-table" style="font-weight:700;color:var(--text)"></span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:13px">
          <span style="color:var(--muted)">Status</span>
          <span style="font-weight:700;color:#fbbf24">Menunggu Diproses</span>
        </div>
      </div>
      <p style="font-size:12px;color:var(--muted);text-align:center;margin-top:12px;line-height:1.6">
        Struk dapur otomatis dibuka. Pantau di <strong style="color:var(--text)">Antrian Order</strong>.
      </p>
    </div>
    <div style="padding:12px 22px 18px;display:flex;gap:10px">
      <a id="ok-order-link" href="#" target="_blank"
        style="flex:0 0 auto;padding:10px 16px;border-radius:12px;border:1px solid var(--border);background:transparent;color:var(--sub);font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;text-decoration:none"
        onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
        <i class="fa-solid fa-list-check" style="font-size:11px"></i> Detail
      </a>
      <button onclick="newTransaction()"
        style="flex:1;padding:10px;border-radius:12px;border:none;background:linear-gradient(135deg,#f59e0b,#f97316);color:#fff;font-size:14px;font-weight:800;cursor:pointer;font-family:'Clash Display',sans-serif">
        <i class="fa-solid fa-plus" style="margin-right:6px;font-size:12px"></i>Order Baru
      </button>
    </div>
  </div>
</div>
@endif

{{-- Loading overlay --}}
<div id="loading-overlay"
  style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);backdrop-filter:blur(4px);z-index:200;display:none;align-items:center;justify-content:center">
  <div style="background:var(--surface);border-radius:16px;padding:28px 36px;text-align:center;border:1px solid var(--border)">
    <i class="fa-solid fa-spinner fa-spin" style="font-size:28px;color:var(--ac);display:block;margin-bottom:12px"></i>
    <div id="loading-text" style="font-size:14px;color:var(--text);font-weight:600">{{ $isKitchen ? 'Mengirim ke Dapur...' : 'Memproses Transaksi...' }}</div>
  </div>
</div>


@push('scripts')
<script>
// ── State ──────────────────────────────────────────────
const TRACK_COGS = {{ $trackCogs ? 'true' : 'false' }};
let cart        = [];  // [{id,name,price,qty,stock,unit,image}]
let discMode    = 'percent';
let currentMethod = 'cash';
let lastReceipt = null;

// ── Helpers ────────────────────────────────────────────
const fmt = n => 'Rp ' + Number(n).toLocaleString('id-ID');

function getSubtotal() { return cart.reduce((s, i) => s + i.price * i.qty, 0); }

function getDiscount() {
  @if($canDiscount)
  const v = parseInt(document.getElementById('discount-input')?.value) || 0;
  if (v <= 0) return 0;
  const sub = getSubtotal();
  if (discMode === 'percent') return Math.round(sub * Math.min(v, 100) / 100);
  return Math.min(v, sub);
  @else
  return 0;
  @endif
}

function getTotal() { return Math.max(0, getSubtotal() - getDiscount()); }

// ── Cart operations ────────────────────────────────────
function cardClick(id) {
  const card = document.getElementById('pcard-' + id);
  if (TRACK_COGS && card.classList.contains('out-of-stock')) return;
  const existing = cart.find(i => i.id === id);
  if (existing) {
    if (TRACK_COGS && existing.qty >= existing.stock) {
      showToast('error', `Stok ${existing.name} hanya ${existing.stock}`);
      return;
    }
    existing.qty++;
  } else {
    const d = card.dataset;
    cart.push({ id, name: d.name, price: parseInt(d.price), qty: 1,
      stock: parseInt(d.stock), unit: d.unit, image: d.image });
  }
  renderCart();
  // pulse animation on card
  card.style.transform = 'scale(1.04)';
  setTimeout(() => card.style.transform = '', 120);
}

function setQty(id, val) {
  const item = cart.find(i => i.id === id);
  if (!item) return;
  const qty = TRACK_COGS ? Math.max(1, Math.min(item.stock, parseInt(val) || 1)) : Math.max(1, parseInt(val) || 1);
  item.qty = qty;
  renderCart();
}

function stepQty(id, delta) {
  const item = cart.find(i => i.id === id);
  if (!item) return;
  const next = item.qty + delta;
  if (next < 1) { removeItem(id); return; }
  if (TRACK_COGS && next > item.stock) { showToast('error', `Stok ${item.name} hanya ${item.stock}`); return; }
  item.qty = next;
  renderCart();
}

function removeItem(id) {
  cart = cart.filter(i => i.id !== id);
  renderCart();
}

function clearCart() {
  cart = [];
  @if($canDiscount)
  const di = document.getElementById('discount-input');
  if (di) di.value = '';
  @endif
  @if($isKitchen)
  const cn = document.getElementById('customer-name');
  if (cn) cn.value = '';
  const tn = document.getElementById('table-number');
  if (tn) tn.value = '';
  @endif
  renderCart();
}

// ── Render ─────────────────────────────────────────────
function renderCart() {
  const container  = document.getElementById('cart-items');
  const empty      = document.getElementById('cart-empty');
  const payBtn     = document.getElementById('pay-btn');
  const clearBtn   = document.getElementById('clear-btn');
  const notesArea  = document.getElementById('notes-area');
  const totals     = document.getElementById('totals-area');
  @if($canDiscount)
  const discArea   = document.getElementById('discount-area');
  const togDisc    = document.getElementById('toggle-discount');
  @endif

  // Update product card badges
  document.querySelectorAll('[id^="pcard-"]').forEach(card => {
    const id = parseInt(card.dataset.id);
    const item = cart.find(i => i.id === id);
    const badge = document.getElementById('qbadge-' + id);
    if (item) {
      card.classList.add('in-cart');
      badge.style.display = 'inline-flex';
      badge.textContent = item.qty;
    } else {
      card.classList.remove('in-cart');
      badge.style.display = 'none';
    }
  });

  if (cart.length === 0) {
    empty.style.display = 'flex';
    container.querySelectorAll('.cart-item').forEach(el => el.remove());
    payBtn.disabled = true;
    payBtn.style.opacity = '.5';
    clearBtn.style.display = 'none';
    if (notesArea)  notesArea.style.display  = 'none';
    if (totals)     totals.style.display     = 'none';
    @if($canDiscount)
    if (discArea)   discArea.style.display   = 'none';
    if (togDisc)    togDisc.style.display    = 'none';
    @endif
    @if($isKitchen)
    const tableAreaEmpty = document.getElementById('table-area');
    if (tableAreaEmpty) tableAreaEmpty.style.display = 'none';
    @endif
    @if(!$isKitchen)
    document.getElementById('pay-total-label').textContent = '';
    @endif
    document.getElementById('cart-count').textContent = '0';
    const mcc0 = document.getElementById('mobile-cart-count');
    if (mcc0) mcc0.textContent = '0 item';
    return;
  }

  empty.style.display = 'none';
  if (notesArea) notesArea.style.display = 'block';
  if (totals)    totals.style.display    = 'block';
  @if($isKitchen)
  const tableArea = document.getElementById('table-area');
  if (tableArea) tableArea.style.display = 'block';
  @endif
  @if($canDiscount)
  if (togDisc) togDisc.style.display = 'flex';
  @endif

  // Re-render items
  const existing = new Set(cart.map(i => i.id));
  container.querySelectorAll('.cart-item').forEach(el => {
    if (!existing.has(parseInt(el.dataset.id))) el.remove();
  });

  cart.forEach(item => {
    let row = container.querySelector(`.cart-item[data-id="${item.id}"]`);
    if (!row) {
      row = document.createElement('div');
      row.className = 'cart-item';
      row.dataset.id = item.id;
      container.appendChild(row);
    }
    row.innerHTML = `
      <div style="width:36px;height:36px;border-radius:8px;background:var(--surface2);flex-shrink:0;overflow:hidden;display:flex;align-items:center;justify-content:center;color:var(--muted)">
        ${item.image ? `<img src="${item.image}" style="width:100%;height:100%;object-fit:cover">` : '<i class="fa-solid fa-cube" style="font-size:14px"></i>'}
      </div>
      <div style="flex:1;min-width:0">
        <div style="font-size:13px;font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${item.name}</div>
        <div style="font-size:12px;color:var(--muted)">${fmt(item.price)}</div>
      </div>
      <div class="qty-ctrl" style="gap:0;flex-shrink:0">
        <button class="qty-btn" onclick="stepQty(${item.id},-1)"><i class="fa-solid fa-minus" style="font-size:9px"></i></button>
        <input type="number" value="${item.qty}" min="1" max="${TRACK_COGS ? item.stock : 9999}"
          style="width:36px;height:26px;border-top:1px solid var(--border);border-bottom:1px solid var(--border);border-left:none;border-right:none;background:var(--surface);color:var(--text);text-align:center;font-size:13px;font-weight:700;outline:none;-moz-appearance:textfield"
          onchange="setQty(${item.id},this.value)">
        <button class="qty-btn" onclick="stepQty(${item.id},1)"><i class="fa-solid fa-plus" style="font-size:9px"></i></button>
      </div>
      <div style="font-size:13px;font-weight:700;color:var(--text);min-width:70px;text-align:right;flex-shrink:0">
        ${fmt(item.price * item.qty)}
      </div>
      <button onclick="removeItem(${item.id})"
        style="width:24px;height:24px;border-radius:6px;border:none;background:transparent;color:#f87171;cursor:pointer;font-size:11px;display:flex;align-items:center;justify-content:center;flex-shrink:0"
        onmouseover="this.style.background='rgba(239,68,68,.1)'" onmouseout="this.style.background='transparent'">
        <i class="fa-solid fa-xmark"></i>
      </button>`;
  });

  updateTotals();

  const count = cart.reduce((s, i) => s + i.qty, 0);
  document.getElementById('cart-count').textContent = count;
  const mcc = document.getElementById('mobile-cart-count');
  if (mcc) mcc.textContent = count + ' item';
  clearBtn.style.display = 'flex';
  payBtn.disabled = false;
  payBtn.style.opacity = '1';
  const ptl = document.getElementById('pay-total-label');
  if (ptl) ptl.textContent = '· ' + fmt(getTotal());
}

// ── Totals ─────────────────────────────────────────────
function updateTotals() {
  const sub  = getSubtotal();
  const disc = getDiscount();
  const tot  = getTotal();

  document.getElementById('subtotal-display').textContent = fmt(sub);
  document.getElementById('total-display').textContent = fmt(tot);

  @if($canDiscount)
  const discRow = document.getElementById('discount-row');
  const discLbl = document.getElementById('discount-label');
  const discDsp = document.getElementById('discount-display');
  const discPrv = document.getElementById('discount-preview');

  if (disc > 0) {
    if (discRow) { discRow.style.display = 'flex'; }
    const pct = discMode === 'percent' ? (parseInt(document.getElementById('discount-input').value)||0) : 0;
    if (discLbl) discLbl.textContent = pct > 0 ? `Diskon (${pct}%)` : 'Diskon';
    if (discDsp) discDsp.textContent = '−' + fmt(disc);
    if (discPrv) discPrv.textContent = '−' + fmt(disc);
  } else {
    if (discRow) discRow.style.display = 'none';
    if (discPrv) discPrv.textContent = '';
  }
  @endif

  const ptl2 = document.getElementById('pay-total-label');
  if (ptl2) ptl2.textContent = cart.length ? '· ' + fmt(tot) : '';
}

// ── Discount ───────────────────────────────────────────
@if($canDiscount)
let discountOpen = false;
function toggleDiscount() {
  discountOpen = !discountOpen;
  const area = document.getElementById('discount-area');
  if (area) area.style.display = discountOpen ? 'block' : 'none';
  document.getElementById('toggle-discount').innerHTML =
    discountOpen
      ? '<i class="fa-solid fa-xmark" style="margin-right:5px;font-size:11px"></i>Hapus Diskon'
      : '<i class="fa-solid fa-tag" style="margin-right:5px;font-size:11px"></i>Tambah Diskon';
  if (!discountOpen) {
    document.getElementById('discount-input').value = '';
    updateTotals();
  }
}

function setDiscountMode(mode) {
  discMode = mode;
  document.getElementById('disc-pct-btn').style.background = mode === 'percent' ? 'var(--ac)' : 'transparent';
  document.getElementById('disc-pct-btn').style.color      = mode === 'percent' ? '#fff' : 'var(--sub)';
  document.getElementById('disc-amt-btn').style.background = mode === 'amount'  ? 'var(--ac)' : 'transparent';
  document.getElementById('disc-amt-btn').style.color      = mode === 'amount'  ? '#fff' : 'var(--sub)';
  document.getElementById('discount-input').value = '';
  updateTotals();
}
@endif

// ── Stock update (after purchase) ─────────────────────
function updateCardStock(id, soldQty, unit) {
  if (!TRACK_COGS) return;
  const card = document.getElementById('pcard-' + id);
  if (!card) return;
  const newStock  = Math.max(0, parseInt(card.dataset.stock) - soldQty);
  const minStock  = parseInt(card.dataset.minStock) || 0;
  card.dataset.stock = newStock;

  // Stock text
  const txt = document.getElementById('stock-txt-' + id);
  if (txt) txt.textContent = 'Stok: ' + newStock + ' ' + unit;

  // Status badge
  const badgeWrap = document.getElementById('stock-badge-' + id);
  if (badgeWrap) {
    if (newStock <= 0) {
      badgeWrap.innerHTML = `<span style="position:absolute;top:8px;left:8px;background:#ef4444;color:#fff;font-size:10px;font-weight:700;padding:2px 7px;border-radius:99px">HABIS</span>`;
    } else if (newStock <= minStock) {
      badgeWrap.innerHTML = `<span style="position:absolute;top:8px;left:8px;background:#f97316;color:#fff;font-size:10px;font-weight:700;padding:2px 7px;border-radius:99px">${newStock} tersisa</span>`;
    } else {
      badgeWrap.innerHTML = '';
    }
  }

  // Out-of-stock class
  if (newStock <= 0) {
    card.classList.add('out-of-stock');
  } else {
    card.classList.remove('out-of-stock');
  }
}

// ── Mobile cart ────────────────────────────────────────
function openMobileCart() {
  document.getElementById('cart-panel').classList.add('mobile-open');
  document.getElementById('cart-mob-overlay').classList.add('open');
}
function closeMobileCart() {
  document.getElementById('cart-panel').classList.remove('mobile-open');
  document.getElementById('cart-mob-overlay').classList.remove('open');
}

// ── Filter ─────────────────────────────────────────────
let activeCat = 'all';
function filterByCat(btn, cat) {
  document.querySelectorAll('.cat-pill').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  activeCat = cat;
  filterProducts();
}

function filterProducts() {
  const q = document.getElementById('pos-search').value.toLowerCase();
  let visible = 0;
  document.querySelectorAll('[id^="pcard-"]').forEach(card => {
    const catMatch = activeCat === 'all' || card.dataset.cat == activeCat;
    const nameMatch = !q || card.dataset.name.toLowerCase().includes(q);
    const show = catMatch && nameMatch;
    card.style.display = show ? '' : 'none';
    if (show) visible++;
  });
  document.getElementById('no-results').style.display = visible === 0 ? 'flex' : 'none';
}

// ── Payment modal ──────────────────────────────────────
function openPayment() {
  if (cart.length === 0) return;
  const total = getTotal();
  document.getElementById('pm-total').textContent = fmt(total);
  document.getElementById('pm-noncash-total').textContent = fmt(total);
  document.getElementById('pm-notes').value = document.getElementById('order-notes')?.value || '';

  // Populate item list
  const pmItems = document.getElementById('pm-items');
  pmItems.innerHTML = cart.map((i, idx) => `
    <div style="display:flex;justify-content:space-between;align-items:center;padding:7px 12px;${idx < cart.length - 1 ? 'border-bottom:1px solid var(--border);' : ''}font-size:13px">
      <div><span style="font-weight:600;color:var(--text)">${i.name}</span><span style="color:var(--muted);margin-left:5px">×${i.qty}</span></div>
      <span style="font-weight:600;color:var(--text);flex-shrink:0;margin-left:8px">${fmt(i.price * i.qty)}</span>
    </div>`).join('');

  selectMethod('cash');
  document.getElementById('pm-reference').value = '';
  clearProof();
  document.getElementById('modal-payment').classList.add('open');
}

function closePayment() {
  document.getElementById('modal-payment').classList.remove('open');
}

function selectMethod(m) {
  currentMethod = m;
  const total = getTotal();

  document.querySelectorAll('.pay-method-btn').forEach(btn => {
    const sel = btn.dataset.method === m;
    btn.style.borderColor  = sel ? 'var(--ac)' : 'var(--border)';
    btn.style.background   = sel ? 'var(--ac-lt)' : 'var(--surface2)';
    btn.querySelector('i').style.color  = sel ? 'var(--ac)' : 'var(--muted)';
    btn.querySelector('span').style.color = sel ? 'var(--ac)' : 'var(--sub)';
  });

  const cashSec    = document.getElementById('cash-section');
  const noncashSec = document.getElementById('noncash-section');
  const pmLabel    = document.getElementById('pm-method-label');

  if (m === 'cash') {
    cashSec.style.display    = 'flex';
    noncashSec.style.display = 'none';
    document.getElementById('cash-input').value = '';
    document.getElementById('change-display').textContent = 'Rp 0';
    renderQuickAmounts(total);
  } else {
    cashSec.style.display    = 'none';
    noncashSec.style.display = 'block';
    if (pmLabel) {
      const labels = {qris:'QRIS', transfer:'Transfer Bank', card:'Kartu Debit/Kredit'};
      pmLabel.textContent = labels[m] || m;
    }
  }
}

function renderQuickAmounts(total) {
  const container = document.getElementById('quick-amounts');
  const amounts = [
    { label: 'Pas', value: total },
    ...[ 50000, 100000, 200000, 500000 ]
      .filter(a => a > total)
      .slice(0, 3)
      .map(a => ({ label: fmt(a).replace('Rp ',''), value: a }))
  ];
  container.innerHTML = amounts.map(a =>
    `<button type="button" onclick="setCashAmount(${a.value})"
      style="padding:5px 12px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);font-size:12px;font-weight:600;color:var(--sub);cursor:pointer;transition:all .15s"
      onmouseover="this.style.background='var(--ac-lt)';this.style.color='var(--ac)';this.style.borderColor='var(--ac)'"
      onmouseout="this.style.background='var(--surface2)';this.style.color='var(--sub)';this.style.borderColor='var(--border)'">
      ${a.label}
    </button>`
  ).join('');
}

function setCashAmount(amount) {
  const input = document.getElementById('cash-input');
  input.value = Number(amount).toLocaleString('id-ID');
  calcChange();
}

function syncCash(input) {
  const raw = input.value.replace(/\D/g,'');
  // don't reformat while typing, just strip non-digits
  calcChange();
}

function formatCashDisplay(input) {
  const raw = input.value.replace(/\D/g,'');
  if (raw) input.value = Number(raw).toLocaleString('id-ID');
  calcChange();
}

function calcChange() {
  const raw    = document.getElementById('cash-input').value.replace(/\D/g,'');
  const paid   = parseInt(raw) || 0;
  const total  = getTotal();
  const change = Math.max(0, paid - total);
  document.getElementById('change-display').textContent = fmt(change);
  document.getElementById('change-display').style.color = paid >= total ? '#34d399' : '#f87171';
}

function previewProof(input) {
  const file = input.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = (e) => {
    document.getElementById('proof-img').src = e.target.result;
    document.getElementById('proof-preview').style.display = 'block';
    document.getElementById('proof-filename').textContent = file.name;
    document.getElementById('proof-label').style.display = 'none';
  };
  reader.readAsDataURL(file);
}

function clearProof() {
  const proofInput = document.getElementById('pm-proof');
  if (proofInput) proofInput.value = '';
  const preview = document.getElementById('proof-preview');
  if (preview) preview.style.display = 'none';
  const img = document.getElementById('proof-img');
  if (img) img.src = '';
  const fname = document.getElementById('proof-filename');
  if (fname) fname.textContent = 'Pilih foto bukti pembayaran';
  const label = document.getElementById('proof-label');
  if (label) label.style.display = 'flex';
}

async function confirmPayment() {
  const total  = getTotal();
  const method = currentMethod;

  let payAmt = total;
  if (method === 'cash') {
    const raw = document.getElementById('cash-input').value.replace(/\D/g,'');
    payAmt = parseInt(raw) || 0;
    if (payAmt < total) {
      showToast('error', 'Uang kurang dari total. Masukkan jumlah yang cukup.');
      document.getElementById('cash-input').style.borderColor = '#f87171';
      return;
    }
    document.getElementById('cash-input').style.borderColor = 'var(--ac)';
  }

  const fd = new FormData();
  cart.forEach((item, idx) => {
    fd.append(`items[${idx}][id]`, item.id);
    fd.append(`items[${idx}][qty]`, item.qty);
  });
  fd.append('payment_method', method);
  fd.append('payment_amount', payAmt);
  fd.append('discount_type', discMode);
  fd.append('discount_value', @if($canDiscount) parseInt(document.getElementById('discount-input')?.value)||0 @else 0 @endif);
  fd.append('notes', document.getElementById('pm-notes').value);
  if (method !== 'cash') {
    const ref = (document.getElementById('pm-reference')?.value || '').trim();
    const proofFile = document.getElementById('pm-proof')?.files[0];
    if (ref) fd.append('reference_number', ref);
    if (proofFile) fd.append('proof_image', proofFile);
  }

  // Show loading
  const overlay = document.getElementById('loading-overlay');
  overlay.style.display = 'flex';
  document.getElementById('confirm-btn').disabled = true;

  try {
    const resp = await fetch('{{ $storeUrl }}', {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': '{{ $csrfToken }}',
      },
      body: fd,
    });

    const data = await resp.json();

    if (resp.ok && data.success) {
      closePayment();
      // Update product card stocks locally so page refresh isn't needed
      cart.forEach(item => updateCardStock(item.id, item.qty, item.unit));
      lastReceipt = { ...data.receipt, receipt_url: data.receipt_url };
      showReceipt(data.receipt);
    } else {
      const msg = data.message || (data.errors ? Object.values(data.errors).flat()[0] : 'Terjadi kesalahan.');
      showToast('error', msg);
    }
  } catch (err) {
    showToast('error', 'Gagal terhubung ke server. Coba lagi.');
  } finally {
    overlay.style.display = 'none';
    document.getElementById('confirm-btn').disabled = false;
  }
}

@if($isKitchen)
// ── Kitchen: Kirim ke Dapur ────────────────────────────
async function sendToKitchen() {
  if (cart.length === 0) return;

  const payload = {
    items:          cart.map(i => ({ id: i.id, qty: i.qty })),
    customer_name:  document.getElementById('customer-name')?.value?.trim() || null,
    table_number:   document.getElementById('table-number')?.value?.trim() || null,
    discount_type:  discMode,
    discount_value: @if($canDiscount) parseInt(document.getElementById('discount-input')?.value)||0 @else 0 @endif,
    notes:          document.getElementById('order-notes')?.value?.trim() || null,
  };

  const overlay = document.getElementById('loading-overlay');
  overlay.style.display = 'flex';
  document.getElementById('pay-btn').disabled = true;

  try {
    const resp = await fetch('{{ $storeUrl }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': '{{ $csrfToken }}',
      },
      body: JSON.stringify(payload),
    });

    const data = await resp.json();

    if (resp.ok && data.success) {
      cart.forEach(item => updateCardStock(item.id, item.qty, item.unit));
      // Auto-open kitchen ticket in popup
      window.open(data.kitchen_url, '_blank', 'width=420,height=650,menubar=no,toolbar=no,location=no,status=no');
      showOrderSuccess(data);
    } else {
      const msg = data.message || (data.errors ? Object.values(data.errors).flat()[0] : 'Terjadi kesalahan.');
      showToast('error', msg);
    }
  } catch (err) {
    showToast('error', 'Gagal terhubung ke server. Coba lagi.');
  } finally {
    overlay.style.display = 'none';
    document.getElementById('pay-btn').disabled = false;
  }
}

function showOrderSuccess(data) {
  document.getElementById('ok-order-number').textContent = data.order_number;
  document.getElementById('ok-order-num2').textContent   = data.order_number;
  document.getElementById('ok-customer').textContent     = data.customer_name || '—';
  document.getElementById('ok-table').textContent        = data.table || '—';
  const link = document.getElementById('ok-order-link');
  if (link) link.href = data.order_url;
  // clear cart state but don't call newTransaction (that closes the modal)
  cart = [];
  @if($canDiscount)
  const di = document.getElementById('discount-input');
  if (di) di.value = '';
  discountOpen = false;
  const da = document.getElementById('discount-area');
  if (da) da.style.display = 'none';
  const td = document.getElementById('toggle-discount');
  if (td) td.innerHTML = '<i class="fa-solid fa-tag" style="margin-right:5px;font-size:11px"></i>Tambah Diskon';
  @endif
  const cn = document.getElementById('customer-name');
  if (cn) cn.value = '';
  const tn = document.getElementById('table-number');
  if (tn) tn.value = '';
  const on = document.getElementById('order-notes');
  if (on) on.value = '';
  renderCart();
  document.getElementById('modal-order-success').classList.add('open');
}
@endif

// ── Receipt ────────────────────────────────────────────
function showReceipt(r) {
  document.getElementById('rc-number').textContent  = r.transaction_number;
  document.getElementById('rc-time').textContent    = r.created_at;
  document.getElementById('rc-cashier').textContent = 'Kasir: ' + r.cashier;

  // Items
  const itemsEl = document.getElementById('rc-items');
  itemsEl.innerHTML = r.items.map(i => `
    <div style="display:flex;justify-content:space-between;margin-bottom:7px;font-size:13px">
      <div style="color:var(--text)">
        <span style="font-weight:600">${i.name}</span>
        <span style="color:var(--muted);margin-left:6px">×${i.qty}</span>
      </div>
      <div style="font-weight:600;color:var(--text)">${fmt(i.subtotal)}</div>
    </div>`).join('');

  // Summary
  const sumEl = document.getElementById('rc-summary');
  let rows = `<div style="display:flex;justify-content:space-between;font-size:13px;color:var(--sub)">
    <span>Subtotal</span><span>${fmt(r.subtotal)}</span></div>`;
  if (r.discount_amount > 0) {
    const label = r.discount_percent > 0 ? `Diskon (${r.discount_percent}%)` : 'Diskon';
    rows += `<div style="display:flex;justify-content:space-between;font-size:13px;color:#34d399">
      <span>${label}</span><span>−${fmt(r.discount_amount)}</span></div>`;
  }
  rows += `<div style="display:flex;justify-content:space-between;font-size:16px;font-weight:800;color:var(--text);border-top:1px dashed var(--border);padding-top:8px;margin-top:2px">
    <span class="font-display">TOTAL</span><span>${fmt(r.total)}</span></div>
  <div style="display:flex;justify-content:space-between;font-size:12.5px;color:var(--sub);margin-top:8px">
    <span>${r.payment_label}</span><span>${fmt(r.payment_amount)}</span></div>`;
  if (r.payment_method === 'cash' && r.change_amount > 0) {
    rows += `<div style="display:flex;justify-content:space-between;font-size:12.5px;color:#34d399;font-weight:600">
      <span>Kembalian</span><span>${fmt(r.change_amount)}</span></div>`;
  }
  sumEl.innerHTML = rows;

  document.getElementById('modal-receipt').classList.add('open');
}

function newTransaction() {
  clearCart();
  @if($canDiscount)
  const di = document.getElementById('discount-input');
  if (di) { di.value=''; }
  discountOpen = false;
  const da = document.getElementById('discount-area');
  if (da) da.style.display = 'none';
  const td = document.getElementById('toggle-discount');
  if (td) td.innerHTML='<i class="fa-solid fa-tag" style="margin-right:5px;font-size:11px"></i>Tambah Diskon';
  @endif
  @if($isKitchen)
  document.getElementById('modal-order-success').classList.remove('open');
  @else
  document.getElementById('modal-receipt').classList.remove('open');
  lastReceipt = null;
  @endif
  document.getElementById('pos-search').value = '';
  filterProducts();
}

function printReceipt() {
  if (!lastReceipt || !lastReceipt.receipt_url) return;
  window.open(lastReceipt.receipt_url, '_blank', 'width=420,height=700,menubar=no,toolbar=no,location=no,status=no');
}

// ── Init ───────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  renderCart();
  // fix number spinner style
  const style = document.createElement('style');
  style.textContent = `input[type=number]::-webkit-inner-spin-button,input[type=number]::-webkit-outer-spin-button{-webkit-appearance:none;margin:0}`;
  document.head.appendChild(style);
});

// ── Barcode scan handler ────────────────────────────────
@if($outlet->enable_barcode_scanner)
document.addEventListener('bscan', e => {
  const sku = (e.detail || '').trim().toLowerCase();
  if (!sku) return;

  // Cari product card dengan data-sku yang cocok
  let matched = null;
  document.querySelectorAll('[id^="pcard-"]').forEach(card => {
    if ((card.dataset.sku || '').toLowerCase() === sku) matched = card;
  });

  if (!matched) {
    // Tidak ditemukan — tampilkan toast singkat
    bsNotFound(sku);
    return;
  }

  const id = parseInt(matched.dataset.id);

  // Jika produk habis, beri feedback
  if (matched.classList.contains('out-of-stock')) {
    bsToast('Stok ' + matched.dataset.name + ' habis.', '#f87171');
    return;
  }

  // Reset filter agar produk terlihat, lalu tambah ke cart
  document.getElementById('pos-search').value = '';
  filterProducts();

  cardClick(id);

  // Flash animasi pada card
  matched.style.outline = '2px solid var(--ac)';
  matched.style.boxShadow = '0 0 0 4px var(--ac-lt)';
  setTimeout(() => { matched.style.outline = ''; matched.style.boxShadow = ''; }, 700);
});

function bsToast(msg, color) {
  color = color || 'var(--ac)';
  let el = document.getElementById('bs-toast');
  if (!el) {
    el = document.createElement('div');
    el.id = 'bs-toast';
    el.style.cssText = 'position:fixed;bottom:80px;left:50%;transform:translateX(-50%);z-index:600;padding:10px 20px;border-radius:12px;font-size:13.5px;font-weight:600;color:#fff;pointer-events:none;transition:opacity .3s;white-space:nowrap;max-width:90vw;text-align:center;box-shadow:0 8px 24px rgba(0,0,0,.4)';
    document.body.appendChild(el);
  }
  el.style.background = color;
  el.style.opacity = '1';
  el.textContent = msg;
  clearTimeout(el._t);
  el._t = setTimeout(() => { el.style.opacity = '0'; }, 2200);
}

function bsNotFound(sku) {
  bsToast('Produk tidak ditemukan: ' + sku, '#64748b');
}
@endif
</script>
@endpush

</x-outlet-layout>
