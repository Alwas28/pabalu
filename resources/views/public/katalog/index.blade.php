<x-public-menu-layout :title="$outlet->name . ' — Katalog Produk'">

<x-slot name="styles">
<style>
*{-webkit-tap-highlight-color:transparent}
#app{display:flex;flex-direction:column;min-height:100vh;max-width:430px;margin:0 auto;background:#f5f5f7;padding-bottom:64px}

/* Hero */
.hero{background:linear-gradient(160deg,var(--red) 0%,var(--red-dark) 75%);color:#fff;padding:18px 20px 24px;position:relative;overflow:hidden}
.hero::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E")}
.hero-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;position:relative}
.hero-brand{display:flex;align-items:center;gap:7px;font-size:12.5px;font-weight:800;color:#fff}
.hero-brand img{width:20px;height:20px;border-radius:6px;background:#fff;padding:2.5px;flex-shrink:0}
.hero-brand-tag{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.7);background:rgba(255,255,255,.16);padding:3px 9px;border-radius:99px}
.hero-badge{display:inline-flex;align-items:center;gap:6px;padding:4px 12px;border-radius:99px;background:rgba(255,255,255,.16);font-size:11px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;margin-bottom:12px}
.hero-name{font-size:26px;font-weight:800;line-height:1.15;letter-spacing:-.5px;margin-bottom:6px}
.hero-addr{display:flex;align-items:center;gap:6px;font-size:12.5px;color:rgba(255,255,255,.75)}
.hero-wave{position:absolute;bottom:-1px;left:0;right:0;line-height:0}

/* Bottom nav */
.bottom-nav{position:fixed;bottom:0;left:50%;transform:translateX(-50%);width:100%;max-width:430px;display:flex;background:#fff;border-top:1px solid #ececec;box-shadow:0 -4px 18px rgba(0,0,0,.06);z-index:45}
.bn-item{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:3px;padding:9px 0 8px;border:none;background:none;font-family:inherit;color:#9ca3af;text-decoration:none;cursor:pointer}
.bn-item i{font-size:18px}
.bn-item span{font-size:10.5px;font-weight:700}
.bn-item.active{color:var(--ac)}
@supports(padding:env(safe-area-inset-bottom)){.bottom-nav{padding-bottom:env(safe-area-inset-bottom)}#info-sheet{padding-bottom:env(safe-area-inset-bottom)}}

/* Info sheet */
#info-overlay{position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:60;opacity:0;pointer-events:none;transition:opacity .25s;backdrop-filter:blur(2px)}
#info-overlay.open{opacity:1;pointer-events:all}
#info-sheet{position:fixed;bottom:0;left:50%;transform:translateX(-50%) translateY(100%);width:100%;max-width:430px;background:#fff;border-radius:24px 24px 0 0;z-index:61;max-height:85vh;overflow-y:auto;transition:transform .32s cubic-bezier(.32,.72,0,1)}
#info-sheet.open{transform:translateX(-50%) translateY(0)}
.drawer-pill{width:40px;height:4px;background:#e5e7eb;border-radius:2px;margin:12px auto 0}
.drawer-head{padding:14px 20px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #f0f0f0}
.drawer-head h3{font-size:17px;font-weight:800;color:#111}
.drawer-close{width:32px;height:32px;border-radius:10px;border:none;background:#f5f5f7;color:#666;font-size:14px;display:grid;place-items:center;cursor:pointer}
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

/* Category bar */
.cat-wrap{position:sticky;top:0;z-index:20;background:#f5f5f7;padding:14px 16px 10px}
.cat-scroll{display:flex;gap:8px;overflow-x:auto;scrollbar-width:none;padding-bottom:2px}
.cat-scroll::-webkit-scrollbar{display:none}
.cat-chip{flex-shrink:0;padding:7px 16px;border-radius:99px;font-size:13px;font-weight:700;border:none;background:#fff;color:#555;cursor:pointer;box-shadow:0 1px 3px rgba(0,0,0,.08);transition:all .18s;white-space:nowrap}
.cat-chip.active{background:var(--ac);color:#fff;box-shadow:0 3px 10px rgba(230,51,41,.35)}

/* Section header */
.sec-head{padding:18px 16px 8px;display:flex;align-items:center;gap:8px}
.sec-line{flex:1;height:1px;background:#e5e7eb}
.sec-label{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:#aaa;white-space:nowrap}

/* Product list */
.prod-list{display:flex;flex-direction:column;gap:0;padding:0 16px}
.prod-row{display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid #f0f0f0}
.prod-row:last-child{border-bottom:none}
.prod-thumb{width:56px;height:56px;border-radius:12px;background:#e5e7eb;flex-shrink:0;overflow:hidden;background-size:cover;background-position:center;display:flex;align-items:center;justify-content:center}
.prod-thumb i{font-size:20px;color:#ccc}
.prod-info{flex:1;min-width:0}
.prod-name{font-size:14px;font-weight:700;color:#111;margin-bottom:2px}
.prod-price{font-size:13.5px;font-weight:800;color:var(--ac)}
.prod-badge{display:inline-flex;align-items:center;gap:3px;padding:2px 8px;border-radius:99px;font-size:10px;font-weight:700;margin-top:3px}
.badge-habis{background:#fef2f2;color:#ef4444;border:1px solid #fecaca}
.badge-tersedia{background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0}

/* Search */
.search-bar{padding:0 0 10px}
.search-box{position:relative;display:flex;align-items:center}
.search-ico{position:absolute;left:13px;color:#bbb;font-size:14px;pointer-events:none}
.search-inp{width:100%;padding:11px 40px 11px 40px;border:none;border-radius:12px;font-size:14px;font-family:inherit;outline:none;color:#111;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.08)}
.search-inp::placeholder{color:#bbb}
.search-clr{position:absolute;right:10px;width:26px;height:26px;border-radius:8px;border:none;background:#f0f0f0;color:#888;cursor:pointer;font-size:12px;display:grid;place-items:center}

/* Empty */
.empty-state{text-align:center;padding:70px 24px;color:#ccc}
.empty-state i{font-size:48px;display:block;margin-bottom:16px}
.empty-state p{font-size:14px;font-weight:600;color:#999}
#search-empty{display:none;text-align:center;padding:48px 24px;color:#ccc}
#search-empty i{font-size:36px;display:block;margin-bottom:10px}
#search-empty p{font-size:13.5px;font-weight:600;color:#aaa}

/* Thumbnail clickable */
.prod-thumb.has-img{cursor:pointer;position:relative;overflow:hidden}
.thumb-zoom{position:absolute;bottom:4px;right:4px;width:22px;height:22px;border-radius:6px;background:rgba(0,0,0,.5);color:#fff;display:grid;place-items:center;font-size:10px;opacity:0;transition:opacity .15s}
.prod-thumb.has-img:active .thumb-zoom,.prod-thumb.has-img:hover .thumb-zoom{opacity:1}

/* Lightbox */
#img-lb{position:fixed;inset:0;background:rgba(0,0,0,.88);z-index:300;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px;opacity:0;pointer-events:none;transition:opacity .22s;backdrop-filter:blur(6px)}
#img-lb.open{opacity:1;pointer-events:all}
#img-lb img{max-width:100%;max-height:68vh;border-radius:16px;object-fit:contain;box-shadow:0 8px 40px rgba(0,0,0,.6);transition:transform .2s}
#img-lb-name{color:#fff;font-size:15px;font-weight:700;margin-top:14px;text-align:center;max-width:320px;line-height:1.4}
#img-lb-close{position:absolute;top:18px;right:18px;width:36px;height:36px;border-radius:50%;background:rgba(255,255,255,.15);border:none;color:#fff;font-size:16px;cursor:pointer;display:grid;place-items:center;transition:background .15s}
#img-lb-close:hover{background:rgba(255,255,255,.25)}

/* Footer */
.cat-footer{text-align:center;padding:24px 16px 32px;font-size:12px;color:#bbb}
</style>
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

  {{-- Hero --}}
  <div class="hero">
    <div class="hero-top">
      <span class="hero-brand"><img src="/img/logo-pabalu.png" alt="Pabalu">Pabalu</span>
      <span class="hero-brand-tag">Katalog</span>
    </div>
    @if($outlet->outletType)
    <div class="hero-badge">
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

  @php
    $totalProducts = $categories->sum(fn($c) => $c->products->count()) + $uncategorized->count();
    $showCatBar    = $categories->count() > 0;
  @endphp

  @if($totalProducts === 0)
    <div class="empty-state">
      <i class="fa-solid fa-box-open"></i>
      <p>Belum ada produk tersedia</p>
    </div>
  @else

    {{-- Search + Category bar (sticky) --}}
    <div class="cat-wrap">
      <div class="search-bar">
        <div class="search-box">
          <i class="fa-solid fa-magnifying-glass search-ico"></i>
          <input type="search" id="search-inp" class="search-inp"
            placeholder="Cari produk..." autocomplete="off"
            oninput="doSearch(this.value)">
          <button class="search-clr" id="search-clr" onclick="clearSearch()" style="display:none">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>
      </div>
      @if($showCatBar)
      <div class="cat-scroll" id="cat-scroll">
        <button class="cat-chip active" onclick="filterCat('all', this)">
          <i class="fa-solid fa-grip" style="font-size:11px;margin-right:5px"></i>Semua
        </button>
        @foreach($categories as $cat)
        <button class="cat-chip" onclick="filterCat('cat-{{ $cat->id }}', this)">{{ $cat->name }}</button>
        @endforeach
        @if($uncategorized->isNotEmpty())
        <button class="cat-chip" onclick="filterCat('cat-uncat', this)">Lainnya</button>
        @endif
      </div>
      @endif
    </div>

    {{-- Products by category --}}
    @foreach($categories as $cat)
    <div data-section="cat-{{ $cat->id }}">
      <div class="sec-head">
        <span class="sec-label">{{ $cat->name }}</span>
        <div class="sec-line"></div>
      </div>
      <div class="prod-list">
        @foreach($cat->products as $prod)
          @include('public.katalog._product-row', ['prod' => $prod])
        @endforeach
      </div>
    </div>
    @endforeach

    @if($uncategorized->isNotEmpty())
    <div data-section="cat-uncat">
      @if($categories->isNotEmpty())
      <div class="sec-head">
        <span class="sec-label">Lainnya</span>
        <div class="sec-line"></div>
      </div>
      @endif
      <div class="prod-list">
        @foreach($uncategorized as $prod)
          @include('public.katalog._product-row', ['prod' => $prod])
        @endforeach
      </div>
    </div>
    @endif

    <div id="search-empty">
      <i class="fa-solid fa-magnifying-glass"></i>
      <p>Produk tidak ditemukan</p>
    </div>

    <div class="cat-footer" id="cat-footer">
      {{ $totalProducts }} produk tersedia &middot; {{ $outlet->name }}
    </div>

  @endif
</div>

{{-- Bottom navigation --}}
<nav class="bottom-nav" id="bottomNav">
  <a href="{{ url('/') }}" class="bn-item" data-bn="beranda">
    <i class="fa-solid fa-house"></i><span>Beranda</span>
  </a>
  <a href="#app" class="bn-item active" data-bn="produk" onclick="event.preventDefault();goProduk()">
    <i class="fa-solid fa-bag-shopping"></i><span>Produk</span>
  </a>
  <button type="button" class="bn-item" data-bn="info" onclick="openInfo()">
    <i class="fa-solid fa-circle-info"></i><span>Info</span>
  </button>
</nav>

{{-- Info outlet sheet --}}
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
  @if($outlet->phone)
  <div class="info-actions">
    <a class="tel" href="tel:{{ $outlet->phone }}"><i class="fa-solid fa-phone"></i>Telepon</a>
    <a class="wa" href="https://wa.me/{{ $waPhone }}" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp"></i>WhatsApp</a>
  </div>
  @endif
  <div class="info-foot">Dilayani lewat <a href="{{ url('/') }}">Pabalu</a> — Aplikasi Kasir UMKM</div>
</div>

<x-slot name="scripts">
<script>
function filterCat(key, btn) {
  document.querySelectorAll('.cat-chip').forEach(c => c.classList.remove('active'));
  btn.classList.add('active');
  document.querySelectorAll('[data-section]').forEach(sec => {
    sec.style.display = (key === 'all' || sec.dataset.section === key) ? '' : 'none';
  });
  // clear search when category changes
  const inp = document.getElementById('search-inp');
  if (inp && inp.value) { inp.value = ''; doSearch(''); }
}

function doSearch(q) {
  const q2     = q.trim().toLowerCase();
  const clr    = document.getElementById('search-clr');
  const empty  = document.getElementById('search-empty');
  const footer = document.getElementById('cat-footer');
  const cats   = document.getElementById('cat-scroll');

  if (clr) clr.style.display = q2 ? '' : 'none';
  if (cats) cats.style.display = q2 ? 'none' : '';

  let total = 0;
  document.querySelectorAll('[data-section]').forEach(sec => {
    let vis = 0;
    sec.querySelectorAll('.prod-row').forEach(row => {
      const name = row.querySelector('.prod-name')?.textContent?.trim().toLowerCase() ?? '';
      const show = !q2 || name.includes(q2);
      row.style.display = show ? '' : 'none';
      if (show) vis++;
    });
    const head = sec.querySelector('.sec-head');
    if (head) head.style.display = q2 && vis === 0 ? 'none' : '';
    sec.style.display = q2 && vis === 0 ? 'none' : '';
    total += vis;
  });

  if (empty)  empty.style.display  = q2 && total === 0 ? '' : 'none';
  if (footer) footer.style.display = q2 && total === 0 ? 'none' : '';
}

function clearSearch() {
  const inp = document.getElementById('search-inp');
  if (inp) { inp.value = ''; doSearch(''); inp.focus(); }
}

function openInfo() {
  document.getElementById('info-overlay').classList.add('open');
  document.getElementById('info-sheet').classList.add('open');
}
function closeInfo() {
  document.getElementById('info-overlay').classList.remove('open');
  document.getElementById('info-sheet').classList.remove('open');
}
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
