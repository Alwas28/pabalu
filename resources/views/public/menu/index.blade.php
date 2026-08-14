<x-public-menu-layout :title="$outlet->name . ' — Menu'">

<x-slot name="styles">
<style>
/* ─── Reset & Base ─────────────────────────────────── */
*{-webkit-tap-highlight-color:transparent}
#app{display:flex;flex-direction:column;min-height:100vh;max-width:430px;margin:0 auto;background:#f5f5f7;position:relative;overflow-x:hidden;padding-bottom:64px}

/* ─── Hero Header ─────────────────────────────────── */
.hero{background:linear-gradient(160deg,var(--red) 0%,var(--red-dark) 75%);color:#fff;padding:18px 20px 24px;position:relative;overflow:hidden}
.hero::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E")}
.hero-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;position:relative}
.hero-brand{display:flex;align-items:center;gap:7px;font-size:12.5px;font-weight:800;color:#fff}
.hero-brand img{width:20px;height:20px;border-radius:6px;background:#fff;padding:2.5px;flex-shrink:0}
.hero-brand-tag{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.7);background:rgba(255,255,255,.16);padding:3px 9px;border-radius:99px}
.hero-type{display:inline-flex;align-items:center;gap:6px;padding:4px 12px;border-radius:99px;background:rgba(255,255,255,.16);font-size:11px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;margin-bottom:12px}
.hero-name{font-size:26px;font-weight:800;line-height:1.15;letter-spacing:-.5px;margin-bottom:6px}
.hero-addr{display:flex;align-items:center;gap:6px;font-size:12.5px;color:rgba(255,255,255,.75)}
.hero-addr i{font-size:10px;color:rgba(255,255,255,.55)}
.hero-wave{position:absolute;bottom:-1px;left:0;right:0;line-height:0}

/* ─── Bottom nav ──────────────────────────────────── */
.bottom-nav{position:fixed;bottom:0;left:50%;transform:translateX(-50%);width:100%;max-width:430px;display:flex;background:#fff;border-top:1px solid #ececec;box-shadow:0 -4px 18px rgba(0,0,0,.06);z-index:45}
.bn-item{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:3px;padding:9px 0 8px;border:none;background:none;font-family:inherit;color:#9ca3af;text-decoration:none;cursor:pointer}
.bn-item i{font-size:18px}
.bn-item span{font-size:10.5px;font-weight:700}
.bn-item.active{color:var(--ac)}

/* ─── Info sheet ──────────────────────────────────── */
#info-overlay{position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:60;opacity:0;pointer-events:none;transition:opacity .25s;backdrop-filter:blur(2px)}
#info-overlay.open{opacity:1;pointer-events:all}
#info-sheet{position:fixed;bottom:0;left:50%;transform:translateX(-50%) translateY(100%);width:100%;max-width:430px;background:#fff;border-radius:24px 24px 0 0;z-index:61;max-height:85vh;overflow-y:auto;transition:transform .32s cubic-bezier(.32,.72,0,1)}
#info-sheet.open{transform:translateX(-50%) translateY(0)}
.info-icon{width:64px;height:64px;border-radius:16px;background:var(--red-soft);color:var(--red);display:grid;place-items:center;font-size:24px;margin:4px 20px 12px}
.info-row{display:flex;align-items:flex-start;gap:12px;padding:13px 20px;border-top:1px solid #f5f5f7}
.info-row i{width:18px;text-align:center;color:var(--ac);font-size:14px;margin-top:2px}
.info-row .lbl{font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#aaa;margin-bottom:2px}
.info-row .val{font-size:13.5px;font-weight:600;color:#111;line-height:1.5}
.info-actions{display:flex;gap:10px;padding:14px 20px 4px}
.info-actions a{flex:1;text-align:center;padding:11px;border-radius:12px;font-size:13px;font-weight:700;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:6px}
.info-actions .wa{background:#e7f9ee;color:#15a35a}
.info-actions .tel{background:var(--red-soft);color:var(--red)}
.info-foot{text-align:center;padding:18px 20px 24px;font-size:11.5px;color:#bbb}
.info-foot a{color:var(--ac);font-weight:700;text-decoration:none}

/* ─── Category bar ────────────────────────────────── */
.cat-wrap{position:sticky;top:0;z-index:20;background:#f5f5f7;padding:14px 16px 10px}
.cat-scroll{display:flex;gap:8px;overflow-x:auto;scrollbar-width:none;padding-bottom:2px}
.cat-scroll::-webkit-scrollbar{display:none}
.cat-chip{flex-shrink:0;padding:7px 16px;border-radius:99px;font-size:13px;font-weight:700;border:none;background:#fff;color:#555;cursor:pointer;box-shadow:0 1px 3px rgba(0,0,0,.08);transition:all .18s;white-space:nowrap}
.cat-chip.active{background:var(--ac);color:#fff;box-shadow:0 3px 10px rgba(232,25,44,.35)}

/* ─── Section header ──────────────────────────────── */
.sec-head{padding:18px 16px 10px;display:flex;align-items:center;gap:8px}
.sec-head-line{flex:1;height:1px;background:#e5e7eb}
.sec-head-label{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:#aaa;white-space:nowrap}

/* ─── Product grid ────────────────────────────────── */
.prod-grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;padding:0 12px 6px}

/* ─── Product card ────────────────────────────────── */
.prod-card{background:#fff;border-radius:16px;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 1px 4px rgba(0,0,0,.07);transition:box-shadow .2s,transform .15s;position:relative}
.prod-card.in-cart{box-shadow:0 0 0 2px var(--ac),0 4px 12px rgba(232,25,44,.15)}
.prod-card.unavailable{opacity:.5;pointer-events:none}
.prod-card:active{transform:scale(.97)}

.prod-img{height:88px;background:#f0f0f0;position:relative;overflow:hidden}
.prod-img .no-img-wrap{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;color:#ccc}
.prod-img .no-img-wrap i{font-size:20px}
.prod-img .no-img-wrap span{font-size:9px;font-weight:600;letter-spacing:.05em}

.prod-body{padding:8px 8px 0;flex:1;display:flex;flex-direction:column;gap:2px}
.prod-name{font-size:12px;font-weight:700;color:#111;line-height:1.3}
.prod-desc{font-size:10px;color:#aaa;line-height:1.35;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.prod-price{font-size:13px;font-weight:800;color:var(--ac)}
.prod-badge-sold{font-size:9px;font-weight:700;padding:2px 6px;border-radius:99px;background:#fef2f2;color:#ef4444;border:1px solid #fecaca;display:inline-block;margin-top:1px}

.prod-foot{padding:5px 8px 8px;display:flex;align-items:center;justify-content:flex-end;margin-top:auto}
.btn-add{width:28px;height:28px;border-radius:8px;border:none;background:var(--ac);color:#fff;font-size:14px;display:grid;place-items:center;cursor:pointer;flex-shrink:0}
.btn-add:active{opacity:.75}
.qty-ctrl{display:flex;align-items:center;gap:0;background:#f5f5f7;border-radius:8px;overflow:hidden}
.qty-ctrl button{width:28px;height:28px;border:none;background:transparent;color:#333;font-size:12px;display:grid;place-items:center;cursor:pointer}
.qty-ctrl button:active{background:#e5e5e5}
.qty-ctrl .n{font-size:13px;font-weight:800;min-width:22px;text-align:center;color:var(--ac)}

/* ─── Search ──────────────────────────────────────── */
.search-bar{padding:0 0 10px}
.search-box{position:relative;display:flex;align-items:center}
.search-ico{position:absolute;left:13px;color:#bbb;font-size:14px;pointer-events:none}
.search-inp{width:100%;padding:11px 40px 11px 40px;border:none;border-radius:12px;font-size:14px;font-family:inherit;outline:none;color:#111;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.08)}
.search-inp::placeholder{color:#bbb}
.search-inp:focus{box-shadow:0 0 0 2px var(--ac)}
.search-clr{position:absolute;right:10px;width:26px;height:26px;border-radius:8px;border:none;background:#f0f0f0;color:#888;cursor:pointer;font-size:12px;display:grid;place-items:center}
#search-empty{display:none;text-align:center;padding:48px 24px;color:#ccc}
#search-empty i{font-size:36px;display:block;margin-bottom:10px}
#search-empty p{font-size:13.5px;font-weight:600;color:#aaa}

/* ─── Empty ───────────────────────────────────────── */
.empty-state{text-align:center;padding:70px 24px;color:#ccc}
.empty-state i{font-size:48px;display:block;margin-bottom:16px}
.empty-state p{font-size:14px;font-weight:600;color:#999;margin-bottom:4px}
.empty-state small{font-size:12.5px}

/* ─── Cart FAB ────────────────────────────────────── */
#fab{position:fixed;bottom:64px;left:50%;transform:translateX(-50%);width:100%;max-width:430px;padding:12px 16px 14px;background:transparent;pointer-events:none;z-index:40}
#fab-btn{width:100%;pointer-events:all;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;border:none;border-radius:16px;padding:15px 20px;display:flex;align-items:center;justify-content:space-between;font-size:14.5px;font-weight:800;cursor:pointer;box-shadow:0 6px 24px rgba(232,25,44,.45);transition:opacity .2s,transform .2s;font-family:inherit}
#fab-btn.hidden{opacity:0;transform:translateY(8px);pointer-events:none}
#fab-btn .left{display:flex;align-items:center;gap:10px}
#fab-btn .badge{background:rgba(255,255,255,.22);border-radius:8px;padding:3px 10px;font-size:12px;font-weight:800}

/* ─── Cart drawer ─────────────────────────────────── */
#cart-overlay{position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:50;opacity:0;pointer-events:none;transition:opacity .25s;backdrop-filter:blur(2px)}
#cart-overlay.open{opacity:1;pointer-events:all}
#cart-drawer{position:fixed;bottom:0;left:50%;transform:translateX(-50%) translateY(100%);width:100%;max-width:430px;background:#fff;border-radius:24px 24px 0 0;z-index:51;max-height:92vh;display:flex;flex-direction:column;transition:transform .32s cubic-bezier(.32,.72,0,1)}
#cart-drawer.open{transform:translateX(-50%) translateY(0)}
.drawer-pill{width:40px;height:4px;background:#e5e7eb;border-radius:2px;margin:12px auto 0}
.drawer-head{padding:14px 20px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #f0f0f0}
.drawer-head h3{font-size:17px;font-weight:800;color:#111}
.drawer-close{width:32px;height:32px;border-radius:10px;border:none;background:#f5f5f7;color:#666;font-size:14px;display:grid;place-items:center;cursor:pointer}

.drawer-items{overflow-y:auto;flex:1;padding:0 20px}
.d-item{display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid #f5f5f7}
.d-item:last-child{border-bottom:none}
.d-item-img{width:48px;height:48px;border-radius:10px;background:#f0f0f0;flex-shrink:0;overflow:hidden;background-size:cover;background-position:center}
.d-item-info{flex:1;min-width:0}
.d-item-name{font-size:13.5px;font-weight:700;color:#111;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.d-item-price{font-size:12px;color:#888;margin-top:2px}
.d-item-sub{font-size:13px;font-weight:800;color:var(--ac);margin-top:1px}
.d-empty{text-align:center;padding:40px 0;color:#ccc;font-size:13px}
.d-empty i{font-size:32px;display:block;margin-bottom:10px}

.drawer-foot{padding:16px 20px 20px;border-top:1px solid #f0f0f0;display:flex;flex-direction:column;gap:12px}
.total-row{display:flex;justify-content:space-between;align-items:center}
.total-label{font-size:14px;color:#666;font-weight:600}
.total-amount{font-size:22px;font-weight:800;color:#111}

/* ─── Checkout form ───────────────────────────────── */
.cf-group{display:flex;flex-direction:column;gap:3px}
.cf-label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#aaa}
.cf-input{padding:11px 14px;border-radius:12px;border:1.5px solid #e5e7eb;background:#fafafa;font-size:14px;color:#111;outline:none;font-family:inherit;transition:border-color .15s;width:100%}
.cf-input:focus{border-color:var(--ac);background:#fff}
.cf-input.error{border-color:#ef4444;animation:shake .3s}
@keyframes shake{0%,100%{transform:translateX(0)}25%{transform:translateX(-4px)}75%{transform:translateX(4px)}}
.cf-row{display:grid;grid-template-columns:1fr 1fr;gap:10px}

.btn-submit{padding:15px;border-radius:14px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:15px;font-weight:800;cursor:pointer;font-family:inherit;width:100%;display:flex;align-items:center;justify-content:center;gap:8px}
.btn-submit:disabled{opacity:.5;cursor:not-allowed}

/* ─── Success screen ──────────────────────────────── */
#success-screen{position:fixed;inset:0;background:#fff;z-index:100;display:none;flex-direction:column;align-items:stretch;max-width:430px;margin:0 auto}
#success-screen.show{display:flex}
.success-top{background:linear-gradient(160deg,#1a1a2e,#0f3460);flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:40px 32px;position:relative;overflow:hidden}
.success-top::after{content:'';position:absolute;width:300px;height:300px;border-radius:50%;border:60px solid rgba(255,255,255,.04);top:-80px;right:-80px}
.s-icon{width:88px;height:88px;border-radius:50%;background:rgba(52,211,153,.2);display:grid;place-items:center;font-size:40px;color:#34d399;margin-bottom:20px;border:2px solid rgba(52,211,153,.3)}
.s-greeting{font-size:14px;color:rgba(255,255,255,.6);margin-bottom:6px;font-weight:500}
.s-name{font-size:24px;font-weight:800;color:#fff;margin-bottom:20px;letter-spacing:-.3px}
.s-num-label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:rgba(255,255,255,.4);margin-bottom:8px}
.s-num{font-size:22px;font-weight:800;color:#fff;letter-spacing:3px;background:rgba(255,255,255,.1);padding:12px 28px;border-radius:14px;border:1px solid rgba(255,255,255,.15)}

.success-bot{padding:24px 24px 32px;display:flex;flex-direction:column;gap:10px}
.success-bot p{font-size:13px;color:#888;text-align:center;line-height:1.6}
.btn-track{padding:15px;border-radius:14px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:15px;font-weight:800;cursor:pointer;font-family:inherit;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:8px}
.btn-again{padding:13px;border-radius:14px;border:1.5px solid #e5e7eb;background:transparent;color:#555;font-size:14px;font-weight:700;cursor:pointer;font-family:inherit}

/* ─── Safe area ───────────────────────────────────── */
@supports(padding:env(safe-area-inset-bottom)){
  .bottom-nav{padding-bottom:env(safe-area-inset-bottom)}
  #cart-drawer{padding-bottom:env(safe-area-inset-bottom)}
  #info-sheet{padding-bottom:env(safe-area-inset-bottom)}
}

/* ─── Product image lightbox ──────────────────────── */
.prod-img.has-img{cursor:pointer;position:relative}
.img-zoom{position:absolute;bottom:6px;right:6px;width:26px;height:26px;border-radius:7px;background:rgba(0,0,0,.5);color:#fff;display:grid;place-items:center;font-size:11px;opacity:0;transition:opacity .15s;pointer-events:none}
.prod-img.has-img:active .img-zoom,.prod-img.has-img:hover .img-zoom{opacity:1}
#img-lb{position:fixed;inset:0;background:rgba(0,0,0,.88);z-index:200;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px;opacity:0;pointer-events:none;transition:opacity .22s;backdrop-filter:blur(6px)}
#img-lb.open{opacity:1;pointer-events:all}
#img-lb img{max-width:100%;max-height:68vh;border-radius:16px;object-fit:contain;box-shadow:0 8px 40px rgba(0,0,0,.6)}
#img-lb-name{color:#fff;font-size:15px;font-weight:700;margin-top:14px;text-align:center;max-width:320px;line-height:1.4}
#img-lb-close{position:absolute;top:18px;right:18px;width:36px;height:36px;border-radius:50%;background:rgba(255,255,255,.15);border:none;color:#fff;font-size:16px;cursor:pointer;display:grid;place-items:center}
#img-lb-close:hover{background:rgba(255,255,255,.25)}

/* ─── Peta lokasi outlet ──────────────────────────── */
#outlet-map{height:180px;border-radius:14px;overflow:hidden;margin:0 20px 14px;border:1px solid #f0f0f0}
.map-link{display:flex;align-items:center;justify-content:center;gap:7px;margin:0 20px 16px;padding:11px;border-radius:12px;background:var(--red-soft);color:var(--red);font-size:12.5px;font-weight:700;text-decoration:none}
</style>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
</x-slot>

@php
  $waPhone = null;
  if ($outlet->phone) {
    $digits = preg_replace('/\D/', '', $outlet->phone);
    if (str_starts_with($digits, '0')) $digits = '62'.substr($digits, 1);
    elseif (!str_starts_with($digits, '62')) $digits = '62'.$digits;
    $waPhone = $digits;
  }
@endphp

<div id="app">

  {{-- ── Hero ── --}}
  <div class="hero">
    <div class="hero-top">
      <span class="hero-brand"><img src="/img/logo-pabalu.png" alt="Pabalu">Pabalu</span>
      <span class="hero-brand-tag">Self Order</span>
    </div>
    @if($outlet->outletType)
    <div class="hero-type">
      <i class="fa-solid {{ $outlet->outletType->icon ?? 'fa-store' }}" style="font-size:10px"></i>
      {{ $outlet->outletType->name }}
    </div>
    @endif
    <div class="hero-name">{{ $outlet->name }}</div>
    @if($outlet->address)
    <div class="hero-addr">
      <i class="fa-solid fa-location-dot"></i>
      {{ Str::limit($outlet->address, 55) }}
    </div>
    @endif
    <div class="hero-wave">
      <svg viewBox="0 0 430 20" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" style="width:100%;height:20px">
        <path d="M0,10 C100,20 330,0 430,10 L430,20 L0,20 Z" fill="#f5f5f7"/>
      </svg>
    </div>
  </div>

  {{-- ── Banner: self order dinonaktifkan ── --}}
  @if(!$selfOrderEnabled)
  <div style="margin:14px 16px 0;padding:14px 16px;background:#fef2f2;border:1.5px solid #fecaca;border-radius:14px;display:flex;align-items:flex-start;gap:10px">
    <i class="fa-solid fa-ban" style="color:#ef4444;font-size:16px;margin-top:1px;flex-shrink:0"></i>
    <div>
      <div style="font-size:13.5px;font-weight:800;color:#991b1b">Pemesanan Mandiri Dinonaktifkan</div>
      <div style="font-size:12px;color:#b91c1c;margin-top:2px">Fitur self order sedang dinonaktifkan oleh outlet. Kamu masih bisa melihat menu, namun pemesanan dilayani langsung oleh kasir.</div>
    </div>
  </div>
  @endif

  {{-- ── Banner: belum opening ── --}}
  @if($selfOrderEnabled && !$openingDone)
  <div style="margin:14px 16px 0;padding:14px 16px;background:#fff8ed;border:1.5px solid #fde68a;border-radius:14px;display:flex;align-items:flex-start;gap:10px">
    <i class="fa-solid fa-clock" style="color:#f59e0b;font-size:16px;margin-top:1px;flex-shrink:0"></i>
    <div>
      <div style="font-size:13.5px;font-weight:800;color:#92400e">Menu belum bisa dipesan</div>
      <div style="font-size:12px;color:#b45309;margin-top:2px">Outlet belum membuka sesi hari ini. Pemesanan akan aktif setelah opening stok dilakukan.</div>
    </div>
  </div>
  @endif

  @php
    $totalProducts = $categories->sum(fn($c) => $c->products->count()) + $uncategorized->count();
    $showCatBar    = $categories->count() > 0;
  @endphp

  @if($totalProducts === 0)
    <div class="empty-state">
      <i class="fa-solid fa-bowl-food"></i>
      <p>Menu belum tersedia</p>
      <small>Produk belum ditambahkan untuk outlet ini.</small>
    </div>
  @else

    {{-- ── Search + Category bar (sticky) ── --}}
    <div class="cat-wrap">
      <div class="search-bar">
        <div class="search-box">
          <i class="fa-solid fa-magnifying-glass search-ico"></i>
          <input type="search" id="search-inp" class="search-inp"
            placeholder="Cari menu..." autocomplete="off"
            oninput="doSearch(this.value)">
          <button class="search-clr" id="search-clr" onclick="clearSearch()" style="display:none">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>
      </div>
      @if($showCatBar)
      <div class="cat-scroll" id="cat-scroll">
        <button class="cat-chip active" data-cat="all" onclick="filterCat('all',this)">
          <i class="fa-solid fa-grip" style="font-size:11px;margin-right:5px"></i>Semua
        </button>
        @foreach($categories as $cat)
        <button class="cat-chip" data-cat="cat-{{ $cat->id }}" onclick="filterCat('cat-{{ $cat->id }}',this)">
          {{ $cat->name }}
        </button>
        @endforeach
        @if($uncategorized->isNotEmpty())
        <button class="cat-chip" data-cat="cat-uncat" onclick="filterCat('cat-uncat',this)">Lainnya</button>
        @endif
      </div>
      @endif
    </div>

    {{-- ── Products by category ── --}}
    @foreach($categories as $cat)
    <div class="sec-head" data-section="cat-{{ $cat->id }}">
      <span class="sec-head-label">{{ $cat->name }}</span>
      <div class="sec-head-line"></div>
    </div>
    <div class="prod-grid" data-grid="cat-{{ $cat->id }}">
      @foreach($cat->products as $prod)
        @include('public.menu._product-card', ['prod' => $prod])
      @endforeach
    </div>
    @endforeach

    @if($uncategorized->isNotEmpty())
      @if($categories->isNotEmpty())
      <div class="sec-head" data-section="cat-uncat">
        <span class="sec-head-label">Lainnya</span>
        <div class="sec-head-line"></div>
      </div>
      @endif
      <div class="prod-grid" data-grid="cat-uncat">
        @foreach($uncategorized as $prod)
          @include('public.menu._product-card', ['prod' => $prod])
        @endforeach
      </div>
    @endif

    <div id="search-empty">
      <i class="fa-solid fa-magnifying-glass"></i>
      <p>Menu tidak ditemukan</p>
    </div>

    <div style="height:90px"></div>

  @endif
</div>

{{-- ── FAB — hanya tampil jika ordering aktif --}}
<div id="fab" @if(!$openingDone || !$selfOrderEnabled) style="display:none" @endif>
  <button id="fab-btn" class="hidden" onclick="openCart()">
    <div class="left">
      <i class="fa-solid fa-bag-shopping"></i>
      <span id="fab-label">Lihat Keranjang</span>
    </div>
    <div style="display:flex;align-items:center;gap:8px">
      <span id="fab-total" style="font-weight:700"></span>
      <span id="fab-badge" class="badge">0</span>
    </div>
  </button>
</div>

@if($openingDone && $selfOrderEnabled)
{{-- ── Overlay ── --}}
<div id="cart-overlay" onclick="closeCart()"></div>

{{-- ── Cart drawer ── --}}
<div id="cart-drawer">
  <div class="drawer-pill"></div>
  <div class="drawer-head">
    <h3>Pesanan Kamu</h3>
    <button class="drawer-close" onclick="closeCart()"><i class="fa-solid fa-xmark"></i></button>
  </div>

  <div class="drawer-items" id="drawer-items">
    <div class="d-empty"><i class="fa-solid fa-bag-shopping"></i>Keranjang masih kosong</div>
  </div>

  <div class="drawer-foot">
    <div class="total-row">
      <span class="total-label">Total</span>
      <span class="total-amount" id="drawer-total">Rp 0</span>
    </div>

    <div class="cf-row">
      <div class="cf-group">
        <label class="cf-label">Nama Pemesan <span style="color:#ef4444">*</span></label>
        <input id="inp-name" type="text" class="cf-input" placeholder="Nama kamu" maxlength="100" autocomplete="name">
      </div>
      <div class="cf-group">
        <label class="cf-label">No. Meja</label>
        <input id="inp-table" type="text" class="cf-input" placeholder="Meja 3" maxlength="30">
      </div>
    </div>

    <div class="cf-group">
      <label class="cf-label">Catatan</label>
      <textarea id="inp-notes" class="cf-input" rows="2" placeholder="Permintaan khusus, alergi..." maxlength="500" style="resize:none"></textarea>
    </div>

    <button class="btn-submit" id="btn-submit" onclick="submitOrder()">
      <i class="fa-solid fa-paper-plane"></i>
      Kirim Pesanan
    </button>
  </div>
</div>

{{-- ── Success screen ── --}}
@endif{{-- end @if($openingDone) --}}
<div id="success-screen">
  <div class="success-top">
    <div class="s-icon"><i class="fa-solid fa-check"></i></div>
    <div class="s-greeting">Hei, <strong id="s-customer-name" style="color:#fff"></strong>!</div>
    <div class="s-name">Pesananmu sudah masuk 🎉</div>
    <div class="s-num-label">Nomor Pesanan</div>
    <div class="s-num" id="s-order-num">—</div>
  </div>
  <div class="success-bot">
    <p>Tunjukkan nomor pesananmu ke kasir,<br>atau pantau status lewat link di bawah.</p>
    <a id="s-track-link" href="#" class="btn-track">
      <i class="fa-solid fa-wave-square"></i>Pantau Status Pesanan
    </a>
    <button class="btn-again" onclick="backToMenu()">
      <i class="fa-solid fa-plus" style="margin-right:6px"></i>Tambah Pesanan Lagi
    </button>
  </div>
</div>

{{-- ── Bottom navigation ── --}}
<nav class="bottom-nav" id="bottomNav">
  <a href="{{ url('/') }}" class="bn-item" data-bn="beranda">
    <i class="fa-solid fa-house"></i><span>Beranda</span>
  </a>
  <a href="#app" class="bn-item active" data-bn="produk" onclick="event.preventDefault();goProduk()">
    <i class="fa-solid fa-utensils"></i><span>Produk</span>
  </a>
  <button type="button" class="bn-item" data-bn="info" onclick="openInfo()">
    <i class="fa-solid fa-circle-info"></i><span>Info</span>
  </button>
</nav>

{{-- ── Info outlet sheet ── --}}
<div id="info-overlay" onclick="closeInfo()"></div>
<div id="info-sheet">
  <div class="drawer-pill"></div>
  <div class="drawer-head">
    <h3>Info Outlet</h3>
    <button class="drawer-close" onclick="closeInfo()"><i class="fa-solid fa-xmark"></i></button>
  </div>
  <div class="info-icon"><i class="fa-solid {{ $outlet->outletType->icon ?? 'fa-store' }}"></i></div>
  <div style="padding:0 20px 4px">
    <div style="font-size:17px;font-weight:800;color:#111">{{ $outlet->name }}</div>
    @if($outlet->outletType)
    <div style="font-size:12.5px;color:#9ca3af;margin-top:2px">{{ $outlet->outletType->name }}</div>
    @endif
  </div>
  @if($outlet->address)
  <div class="info-row">
    <i class="fa-solid fa-location-dot"></i>
    <div><div class="lbl">Alamat</div><div class="val">{{ $outlet->address }}</div></div>
  </div>
  @endif
  @if($outlet->phone)
  <div class="info-row">
    <i class="fa-solid fa-phone"></i>
    <div><div class="lbl">Kontak</div><div class="val">{{ $outlet->phone }}</div></div>
  </div>
  @endif
  @if($outlet->latitude && $outlet->longitude)
  <div style="padding:14px 0 0">
    <div id="outlet-map"></div>
    <a class="map-link" href="https://www.google.com/maps?q={{ $outlet->latitude }},{{ $outlet->longitude }}" target="_blank" rel="noopener">
      <i class="fa-solid fa-diamond-turn-right"></i> Buka di Google Maps
    </a>
  </div>
  @endif
  @if($outlet->phone)
  <div class="info-actions">
    <a class="tel" href="tel:{{ $outlet->phone }}"><i class="fa-solid fa-phone"></i>Telepon</a>
    <a class="wa" href="https://wa.me/{{ $waPhone }}" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp"></i>WhatsApp</a>
  </div>
  @endif
  <div class="info-foot">Dilayani lewat <a href="{{ url('/') }}">Pabalu</a> — Aplikasi Kasir UMKM</div>
</div>

<x-slot name="scripts">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
const ORDER_URL  = '{{ route("public.menu.order", $outlet->code) }}';
const CSRF       = document.querySelector('meta[name="csrf-token"]').content;
const MENU_OPEN  = {{ ($openingDone && $selfOrderEnabled) ? 'true' : 'false' }};
const SELF_ORDER = {{ $selfOrderEnabled ? 'true' : 'false' }};
let cart = {};

/* ── Format ─────────────── */
const fmt = n => 'Rp ' + n.toLocaleString('id-ID');

/* ── Cart ops ───────────── */
function addToCart(id, name, price, img) {
  if (!MENU_OPEN) {
    showMenuClosedToast();
    return;
  }
  cart[id] ? cart[id].qty++ : (cart[id] = {id, name, price, img, qty:1});
  syncCard(id);
  syncFab();
}

function showMenuClosedToast() {
  const el = document.createElement('div');
  el.style.cssText = 'position:fixed;bottom:80px;left:50%;transform:translateX(-50%);background:#1f2937;color:#fff;padding:12px 20px;border-radius:12px;font-size:13px;font-weight:700;z-index:200;white-space:nowrap;box-shadow:0 4px 16px rgba(0,0,0,.25)';
  el.textContent = SELF_ORDER ? '⏰ Outlet belum buka — coba lagi nanti' : '🚫 Pemesanan mandiri tidak tersedia';
  document.body.appendChild(el);
  setTimeout(() => el.remove(), 3000);
}

function setQty(id, name, price, img, qty) {
  if (qty <= 0) delete cart[id];
  else cart[id] = {id, name, price, img, qty};
  syncCard(id);
  syncFab();
  if (document.getElementById('cart-drawer').classList.contains('open')) renderItems();
}

function syncCard(id) {
  const card = document.querySelector(`.prod-card[data-pid="${id}"]`);
  if (!card) return;
  const qty = cart[id]?.qty ?? 0;
  const add = card.querySelector('.btn-add');
  const ctrl = card.querySelector('.qty-ctrl');
  const n = card.querySelector('.n');
  if (qty > 0) {
    card.classList.add('in-cart');
    if (add)  add.style.display  = 'none';
    if (ctrl) ctrl.style.display = 'flex';
    if (n)    n.textContent      = qty;
  } else {
    card.classList.remove('in-cart');
    if (add)  add.style.display  = 'grid';
    if (ctrl) ctrl.style.display = 'none';
  }
}

const cartTotal = () => Object.values(cart).reduce((s,i) => s + i.price*i.qty, 0);
const cartCount = () => Object.values(cart).reduce((s,i) => s + i.qty, 0);

function syncFab() {
  const cnt = cartCount();
  document.getElementById('fab-badge').textContent = cnt;
  document.getElementById('fab-total').textContent  = fmt(cartTotal());
  const btn = document.getElementById('fab-btn');
  cnt > 0 ? btn.classList.remove('hidden') : (btn.classList.add('hidden'), closeCart());
}

/* ── Render items ───────── */
function renderItems() {
  const wrap = document.getElementById('drawer-items');
  const items = Object.values(cart);
  document.getElementById('drawer-total').textContent = fmt(cartTotal());

  if (!items.length) {
    wrap.innerHTML = '<div class="d-empty"><i class="fa-solid fa-bag-shopping"></i>Keranjang masih kosong</div>';
    return;
  }
  wrap.innerHTML = items.map(i => {
    const safeName = i.name.replace(/\\/g,'\\\\').replace(/'/g,"\\'");
    return `
    <div class="d-item">
      <div class="d-item-img" style="${i.img ? `background-image:url('${i.img}')` : 'background:#f0f0f0'}">
        ${i.img ? '' : '<div style="width:100%;height:100%;display:grid;place-items:center;color:#ccc"><i class="fa-solid fa-image" style="font-size:16px"></i></div>'}
      </div>
      <div class="d-item-info">
        <div class="d-item-name">${i.name}</div>
        <div class="d-item-price">${fmt(i.price)}</div>
        <div class="d-item-sub">${fmt(i.price * i.qty)}</div>
      </div>
      <div class="qty-ctrl" style="display:flex;flex-direction:column;align-items:center;gap:2px">
        <button onclick="setQty(${i.id},'${safeName}',${i.price},'${i.img??''}',${i.qty+1})" style="width:28px;height:28px;border-radius:8px;border:none;background:#f5f5f7;font-size:13px;display:grid;place-items:center;cursor:pointer"><i class="fa-solid fa-plus"></i></button>
        <span class="n" style="font-size:15px;font-weight:800;color:var(--ac);min-width:20px;text-align:center">${i.qty}</span>
        <button onclick="setQty(${i.id},'${safeName}',${i.price},'${i.img??''}',${i.qty-1})" style="width:28px;height:28px;border-radius:8px;border:none;background:#f5f5f7;font-size:13px;display:grid;place-items:center;cursor:pointer"><i class="fa-solid fa-minus"></i></button>
      </div>
    </div>`;
  }).join('');
}

/* ── Cart open/close ────── */
function openCart() {
  renderItems();
  document.getElementById('cart-overlay').classList.add('open');
  document.getElementById('cart-drawer').classList.add('open');
}
function closeCart() {
  document.getElementById('cart-overlay').classList.remove('open');
  document.getElementById('cart-drawer').classList.remove('open');
}

/* ── Submit ─────────────── */
async function submitOrder() {
  if (!MENU_OPEN) { showMenuClosedToast(); return; }

  const name = document.getElementById('inp-name').value.trim();
  if (!name) {
    const el = document.getElementById('inp-name');
    el.classList.add('error');
    el.focus();
    setTimeout(() => el.classList.remove('error'), 600);
    return;
  }
  const items = Object.values(cart).map(i => ({product_id:i.id, qty:i.qty}));
  if (!items.length) return;

  const btn = document.getElementById('btn-submit');
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Mengirim...';

  try {
    const res  = await fetch(ORDER_URL, {
      method:'POST',
      headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
      body:JSON.stringify({
        customer_name: name,
        table_number:  document.getElementById('inp-table').value.trim()||null,
        notes:         document.getElementById('inp-notes').value.trim()||null,
        items,
      }),
    });
    const data = await res.json();
    if (!res.ok) {
      if (data.track_url && confirm((data.message||'Terjadi kesalahan.') + '\n\nLihat status pesanan sebelumnya?')) {
        location.href = data.track_url;
        return;
      }
      throw new Error(data.message||'Terjadi kesalahan.');
    }

    closeCart();
    document.getElementById('s-customer-name').textContent = name;
    document.getElementById('s-order-num').textContent     = data.order_number;
    document.getElementById('s-track-link').href           = data.track_url;
    document.getElementById('success-screen').classList.add('show');

  } catch(e) {
    alert('Gagal: ' + e.message);
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Kirim Pesanan';
  }
}

/* ── Back to menu ────────── */
function backToMenu() {
  cart = {};
  document.querySelectorAll('.prod-card[data-pid]').forEach(c => {
    c.classList.remove('in-cart');
    const a = c.querySelector('.btn-add'), q = c.querySelector('.qty-ctrl');
    if (a) a.style.display = 'grid';
    if (q) q.style.display = 'none';
  });
  syncFab();
  document.getElementById('success-screen').classList.remove('show');
  ['inp-name','inp-table','inp-notes'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.value = '';
  });
}

/* ── Filter category ──────── */
function filterCat(cat, btn) {
  document.querySelectorAll('.cat-chip').forEach(c => c.classList.remove('active'));
  btn.classList.add('active');
  btn.scrollIntoView({behavior:'smooth', block:'nearest', inline:'center'});
  const all = cat === 'all';
  document.querySelectorAll('[data-section],[data-grid]').forEach(el => {
    const k = el.dataset.section || el.dataset.grid;
    el.style.display = all || k === cat ? '' : 'none';
  });
  // reset search when changing category
  const inp = document.getElementById('search-inp');
  if (inp && inp.value) { inp.value = ''; _applySearch(''); }
}

/* ── Search ──────────────── */
function doSearch(q) {
  const q2 = q.trim().toLowerCase();
  const clr = document.getElementById('search-clr');
  if (clr) clr.style.display = q2 ? '' : 'none';
  _applySearch(q2);
}

function _applySearch(q2) {
  const cats  = document.getElementById('cat-scroll');
  const empty = document.getElementById('search-empty');

  // Saat searching, sembunyikan chip kategori
  if (cats) cats.style.display = q2 ? 'none' : '';

  // Reset category chips ke "Semua" kalau search aktif
  if (q2) {
    document.querySelectorAll('.cat-chip').forEach(c => c.classList.remove('active'));
    const all = document.querySelector('.cat-chip[data-cat="all"]');
    if (all) all.classList.add('active');
  }

  let total = 0;

  // Untuk setiap grid produk
  document.querySelectorAll('[data-grid]').forEach(grid => {
    let vis = 0;
    grid.querySelectorAll('.prod-card').forEach(card => {
      const name = card.querySelector('.prod-name')?.textContent?.trim().toLowerCase() ?? '';
      const show = !q2 || name.includes(q2);
      card.style.display = show ? '' : 'none';
      if (show) vis++;
    });
    grid.style.display = q2 && vis === 0 ? 'none' : '';
    // Sembunyikan section header yang bersesuaian jika kosong
    const key = grid.dataset.grid;
    const head = document.querySelector(`[data-section="${key}"]`);
    if (head) head.style.display = q2 && vis === 0 ? 'none' : '';
    total += vis;
  });

  if (empty) empty.style.display = q2 && total === 0 ? '' : 'none';
}

function clearSearch() {
  const inp = document.getElementById('search-inp');
  if (inp) { inp.value = ''; doSearch(''); inp.focus(); }
}

/* ── Bottom nav: Info sheet ─ */
let outletMapInited = false;
function openInfo() {
  document.getElementById('info-overlay').classList.add('open');
  document.getElementById('info-sheet').classList.add('open');
  initOutletMapOnce();
}

function initOutletMapOnce() {
  const el = document.getElementById('outlet-map');
  if (!el || outletMapInited) return;
  outletMapInited = true;
  const lat = {{ $outlet->latitude ?? 'null' }};
  const lng = {{ $outlet->longitude ?? 'null' }};
  const map = L.map('outlet-map', { zoomControl: false, dragging: false, scrollWheelZoom: false }).setView([lat, lng], 16);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap',
    maxZoom: 19,
  }).addTo(map);
  L.marker([lat, lng]).addTo(map);
  setTimeout(() => map.invalidateSize(), 200);
}
function closeInfo() {
  document.getElementById('info-overlay').classList.remove('open');
  document.getElementById('info-sheet').classList.remove('open');
}

/* ── Bottom nav: Produk ───── */
function goProduk() {
  document.getElementById('app').scrollIntoView({behavior:'smooth', block:'start'});
}

function showImg(url, name) {
  const lb = document.getElementById('img-lb');
  document.getElementById('img-lb-img').src = url;
  document.getElementById('img-lb-name').textContent = name;
  lb.classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeImg() {
  document.getElementById('img-lb').classList.remove('open');
  document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeImg(); });
</script>
</x-slot>

{{-- Lightbox --}}
<div id="img-lb" onclick="if(event.target===this)closeImg()">
  <button id="img-lb-close" onclick="closeImg()"><i class="fa-solid fa-xmark"></i></button>
  <img id="img-lb-img" src="" alt="">
  <div id="img-lb-name"></div>
</div>

</x-public-menu-layout>
