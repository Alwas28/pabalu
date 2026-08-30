<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $title ?: 'Pabalu — Supermarket Belanja Segar Online' }}</title>
@if($description)
<meta name="description" content="{{ $description }}">
@endif
<meta property="og:title" content="{{ $title ?: 'Pabalu' }}">
@if($description)
<meta property="og:description" content="{{ $description }}">
@endif
@if($ogImage)
<meta property="og:image" content="{{ $ogImage }}">
@endif
<link rel="icon" type="image/png" href="/img/logo-pabalu.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
  :root{
    --green-900:#1A1A1A;
    --green-700:#B90F14;
    --green-600:#E8161C;
    --green-500:#FF4D4D;
    --mint-100:#FDEAEB;
    --mint-50:#FFF6F6;
    --clay:#FF7A3D;
    --sun:#FFB020;
    --ink:#122019;
    --ink-soft:#5B6B62;
    --line:#E3EBE5;
    --white:#ffffff;
    --radius:14px;
    --shadow:0 4px 18px rgba(14,59,42,.07);
    --shadow-lg:0 18px 40px rgba(14,59,42,.14);
  }
  *{box-sizing:border-box;margin:0;padding:0;}
  body{
    font-family:'Plus Jakarta Sans',sans-serif;
    color:var(--ink);
    background:var(--white);
    -webkit-font-smoothing:antialiased;
  }
  h1,h2,h3,.brand,.font-display{font-family:'Sora',sans-serif;}
  img{max-width:100%;display:block;object-fit:cover;}
  a{text-decoration:none;color:inherit;}
  ul{list-style:none;}
  button{font-family:inherit;cursor:pointer;border:none;}
  .container{max-width:1180px;margin:0 auto;padding:0 20px;}

  /* ===== Topbar ===== */
  .topbar{background:var(--green-900);color:#DDEFE3;font-size:11px;padding:6px 0;}
  .topbar .container{display:flex;justify-content:space-between;align-items:center;}
  .topbar .promo{display:flex;align-items:center;gap:6px;flex:1;min-width:0;overflow:hidden;}
  .topbar .promo span.tag{flex-shrink:0;background:var(--sun);color:var(--green-900);font-weight:700;padding:1px 7px;border-radius:4px;}
  .topbar .marquee{flex:1;min-width:0;overflow:hidden;-webkit-mask-image:linear-gradient(90deg,transparent,#000 24px,#000 calc(100% - 24px),transparent);mask-image:linear-gradient(90deg,transparent,#000 24px,#000 calc(100% - 24px),transparent);}
  .topbar .marquee-track{display:flex;width:max-content;white-space:nowrap;animation:marquee-scroll 16s linear infinite;}
  .topbar .marquee-track:hover{animation-play-state:paused;}
  .topbar .marquee-track span{padding-right:56px;}
  .topbar .marquee a{color:var(--sun);font-weight:700;text-decoration:underline;}
  @keyframes marquee-scroll{from{transform:translateX(0);}to{transform:translateX(-50%);}}
  .topbar .lang{opacity:.85;flex-shrink:0;}

  /* ===== Header ===== */
  header.site{position:sticky;top:0;z-index:200;background:var(--white);box-shadow:0 1px 0 var(--line);}
  .header-main{padding:11px 0;}
  .header-main .container{display:flex;align-items:center;gap:16px;}
  .brand{display:flex;align-items:center;gap:6px;font-size:19px;font-weight:800;color:var(--green-700);white-space:nowrap;}
  .brand-logo{width:26px;height:26px;object-fit:contain;flex-shrink:0;}
  .search-wrap{flex:1;display:flex;max-width:520px;border:1.5px solid var(--line);border-radius:8px;overflow:hidden;margin:0;}
  .search-wrap select.search-type{
    border:none;outline:none;background:#fff;color:var(--ink-soft);font-weight:600;font-size:12px;
    padding:0 8px;border-right:1.5px solid var(--line);cursor:pointer;max-width:112px;flex-shrink:0;
    -webkit-appearance:menulist;
  }
  .search-wrap select.search-type optgroup{font-style:normal;font-weight:700;color:var(--green-700);}
  .search-wrap select.search-type option{font-weight:500;color:var(--ink);}
  .search-wrap input{flex:1;border:none;outline:none;padding:8px 13px;font-size:12.5px;background:var(--mint-50);min-width:0;}
  .search-wrap button{background:var(--green-600);color:#fff;padding:0 14px;font-size:13px;}
  .loc-wrap{position:relative;}
  .loc-pill{display:flex;align-items:center;gap:5px;border:1.5px solid var(--line);border-radius:8px;padding:7px 11px;font-size:12px;color:var(--ink-soft);white-space:nowrap;cursor:pointer;background:#fff;}
  .loc-pill .car{font-size:9px;opacity:.6;transition:transform .2s;margin-left:2px;}
  .loc-wrap.open .loc-pill .car{transform:rotate(180deg);}
  .loc-pill strong{color:var(--ink);font-weight:700;}
  .loc-dropdown{
    position:absolute;top:calc(100% + 8px);left:0;background:#fff;min-width:220px;max-height:280px;overflow-y:auto;
    border-radius:10px;box-shadow:var(--shadow-lg);padding:8px;z-index:60;
    opacity:0;visibility:hidden;transform:translateY(6px);transition:.18s ease;
  }
  .loc-wrap.open .loc-dropdown{opacity:1;visibility:visible;transform:translateY(0);}
  .loc-dropdown button{display:flex;align-items:center;gap:8px;width:100%;text-align:left;background:none;padding:9px 12px;font-size:13px;border-radius:7px;color:var(--ink-soft);font-weight:500;}
  .loc-dropdown button:hover{background:var(--mint-100);color:var(--green-700);}
  .loc-dropdown button.active{background:var(--mint-100);color:var(--green-700);font-weight:700;}

  /* ===== WhatsApp floating button ===== */
  .wa-float{
    position:fixed;bottom:18px;right:18px;z-index:500;display:flex;align-items:center;gap:6px;
    background:#25D366;color:#fff;padding:8px 13px 8px 8px;border-radius:50px;
    box-shadow:0 6px 16px rgba(37,211,102,.4);font-size:11.5px;font-weight:700;
    transition:.2s ease;
  }
  .wa-float:hover{transform:translateY(-3px);box-shadow:0 10px 20px rgba(37,211,102,.5);}
  .wa-float .wa-icon{width:22px;height:22px;background:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0;}
  .wa-pulse{position:absolute;inset:0;border-radius:50px;background:#25D366;opacity:.55;animation:waPulse 2.2s ease-out infinite;z-index:-1;}
  @keyframes waPulse{0%{opacity:.5;transform:scale(1);}100%{opacity:0;transform:scale(1.25);}}
  @media(max-width:640px){
    .wa-float{bottom:14px;right:14px;padding:10px;border-radius:50%;}
    .wa-float span.wa-text{display:none;}
  }
  .header-icons{display:flex;align-items:center;gap:10px;margin-left:auto;font-size:13px;font-weight:700;white-space:nowrap;}
  .header-icons a{color:var(--ink);padding:6px 4px;}
  .header-icons a:hover{color:var(--green-700);}
  .header-icons .sep{color:var(--line);font-weight:400;}
  .header-icons .btn-signup{background:var(--green-600);color:#fff;padding:8px 16px;border-radius:8px;}
  .header-icons .btn-signup:hover{background:var(--green-700);}
  .burger{display:none;background:none;font-size:19px;color:var(--green-900);}

  /* ===== Nav / Mega menu ===== */
  nav.mainnav{border-top:1px solid var(--line);}
  nav.mainnav .container{display:flex;align-items:center;gap:20px;}
  .dept-wrap{position:relative;}
  .dept-btn{background:var(--green-600);color:#fff;font-weight:700;font-size:12.5px;padding:10px 14px;display:flex;align-items:center;gap:7px;border-radius:0;white-space:nowrap;}
  .dept-btn:hover{background:var(--green-700);}
  .dept-wrap:hover .mega{opacity:1;visibility:visible;transform:translateY(0);}
  .navlist{display:flex;align-items:center;gap:20px;}
  .navlist > li{position:relative;}
  .navlist > li > a{display:flex;align-items:center;gap:4px;font-size:12.5px;font-weight:600;padding:11px 0;color:var(--ink);}
  .navlist > li > a .car{font-size:10px;opacity:.6;transition:transform .2s;}
  .navlist > li:hover > a .car{transform:rotate(180deg);}
  .navlist > li:hover > a{color:var(--green-700);}

  .submenu{
    position:absolute;top:100%;left:0;background:#fff;min-width:190px;
    box-shadow:var(--shadow-lg);border-radius:9px;padding:8px;
    opacity:0;visibility:hidden;transform:translateY(8px);
    transition:.2s ease;z-index:50;
  }
  .navlist > li:hover .submenu{opacity:1;visibility:visible;transform:translateY(0);}
  .submenu li a{display:block;padding:8px 12px;font-size:12.5px;border-radius:6px;color:var(--ink-soft);font-weight:500;}
  .submenu li a:hover{background:var(--mint-100);color:var(--green-700);}
  .submenu.has-sub li{position:relative;}
  .sub-sub{
    position:absolute;left:100%;top:0;min-width:180px;background:#fff;
    box-shadow:var(--shadow-lg);border-radius:9px;padding:8px;
    opacity:0;visibility:hidden;transform:translateX(6px);transition:.18s ease;
  }
  .submenu.has-sub li:hover .sub-sub{opacity:1;visibility:visible;transform:translateX(0);}

  .mega{
    position:absolute;top:100%;left:0;background:#fff;width:520px;
    box-shadow:var(--shadow-lg);border-radius:10px;padding:18px;
    display:grid;grid-template-columns:repeat(3,1fr);gap:14px;
    opacity:0;visibility:hidden;transform:translateY(8px);transition:.2s ease;z-index:50;
  }
  .navlist > li:hover .mega{opacity:1;visibility:visible;transform:translateY(0);}
  .mega h4{font-size:11px;text-transform:uppercase;letter-spacing:.04em;color:var(--green-700);margin-bottom:8px;font-family:'Plus Jakarta Sans',sans-serif;font-weight:800;}
  .mega li a{display:block;padding:5px 0;font-size:12.5px;color:var(--ink-soft);font-weight:500;}
  .mega li a:hover{color:var(--green-700);}

  .dept-mega{width:640px;grid-template-columns:repeat(4,1fr);padding:20px;}
  .dept-mega .cat-item{display:flex;align-items:center;gap:9px;padding:7px 8px;border-radius:8px;}
  .dept-mega .cat-item:hover{background:var(--mint-100);}
  .dept-mega .cat-item img{width:30px;height:30px;border-radius:7px;object-fit:cover;flex-shrink:0;}
  .dept-mega .cat-item span{font-size:12px;font-weight:600;color:var(--ink);line-height:1.25;}

  /* ===== Section heading (dipakai halaman lain juga) ===== */
  .section{padding:26px 0;}
  .sec-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;}
  .sec-head h2{font-size:16px;font-weight:800;color:var(--green-900);}

  footer.site{background:var(--green-900);color:#CFE4D6;margin-top:40px;padding:44px 0 18px;}
  .foot-grid{display:grid;grid-template-columns:1.4fr 1fr 1fr 1fr;gap:28px;margin-bottom:30px;}
  .foot-grid h4{color:#fff;font-size:14px;margin-bottom:14px;font-weight:700;}
  .foot-grid li{font-size:13px;margin-bottom:9px;opacity:.85;}
  .foot-bottom{border-top:1px solid rgba(255,255,255,.12);padding-top:16px;font-size:12.5px;text-align:center;opacity:.7;}

  /* ===== Responsive ===== */
  @media(max-width:1080px){
    .mega,.dept-mega{width:480px;grid-template-columns:repeat(2,1fr);}
  }
  @media(max-width:860px){
    .navlist,.dept-wrap{display:none;}
    .burger{display:block;}
    .loc-pill{display:none;}
  }
  @media(max-width:640px){
    .header-main .container{flex-wrap:wrap;}
    .search-wrap{order:3;flex-basis:100%;max-width:none;}
    .foot-grid{grid-template-columns:1fr 1fr;}
  }

  /* mobile drawer menu */
  .mobile-drawer{
    position:fixed;top:0;right:-320px;width:300px;height:100%;background:#fff;
    box-shadow:-8px 0 30px rgba(0,0,0,.15);z-index:999;transition:.3s ease;padding:20px;overflow-y:auto;
  }
  .mobile-drawer.open{right:0;}
  .mobile-drawer .close-drawer{font-size:20px;margin-bottom:16px;display:block;text-align:right;color:var(--ink-soft);}
  .mobile-drawer details{border-bottom:1px solid var(--line);padding:10px 0;}
  .mobile-drawer summary{font-weight:700;font-size:14.5px;cursor:pointer;list-style:none;display:flex;justify-content:space-between;}
  .mobile-drawer summary::-webkit-details-marker{display:none;}
  .mobile-drawer ul{padding:8px 0 4px 10px;}
  .mobile-drawer ul li a{display:block;padding:7px 0;font-size:13.5px;color:var(--ink-soft);}
  .overlay{position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:998;opacity:0;visibility:hidden;transition:.3s;}
  .overlay.open{opacity:1;visibility:visible;}
@stack('styles')
</style>
</head>
<body>

<!-- TOPBAR -->
<div class="topbar">
  <div class="container">
    <div class="promo">
      <span class="tag">Penting</span>
      <div class="marquee">
        <div class="marquee-track">
          <span>Aplikasi Pabalu <strong>GRATIS</strong> 100% digunakan sesuai <a href="{{ route('pages.show', 'syarat-dan-ketentuan') }}">Syarat &amp; Ketentuan</a> yang berlaku.</span>
          <span>Aplikasi Pabalu <strong>GRATIS</strong> 100% digunakan sesuai <a href="{{ route('pages.show', 'syarat-dan-ketentuan') }}">Syarat &amp; Ketentuan</a> yang berlaku.</span>
        </div>
      </div>
    </div>
    <div class="lang">🇮🇩 Bahasa Indonesia ▾</div>
  </div>
</div>

<!-- HEADER -->
<header class="site">
  <div class="header-main">
    <div class="container">
      <a href="{{ url('/') }}" class="brand"><img class="brand-logo" src="/img/logo-pabalu.png" alt="Logo Pabalu"> Pabalu</a>
      <form class="search-wrap" action="{{ route('search.index') }}" method="GET">
        <select class="search-type" id="searchType" name="type" title="Cari berdasarkan">
          <option value="all" {{ request('type', 'all') === 'all' ? 'selected' : '' }}>Semua</option>
          <optgroup label="Produk">
            <option value="product" {{ request('type') === 'product' ? 'selected' : '' }}>Semua Produk</option>
          </optgroup>
          <optgroup label="Jenis Outlet">
            <option value="outlet" {{ request('type') === 'outlet' ? 'selected' : '' }}>Semua Outlet</option>
            @foreach($searchOutletTypes as $sType)
            <option value="outlet:{{ $sType->slug }}" {{ request('type') === 'outlet:'.$sType->slug ? 'selected' : '' }}>{{ $sType->name }}</option>
            @endforeach
          </optgroup>
        </select>
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari produk kebutuhan harianmu...">
        <button type="submit">🔍</button>
      </form>
      <div class="header-icons">
        @auth
          <a href="{{ route('dashboard') }}" class="btn-signup">Dashboard</a>
        @else
          <a href="{{ route('login') }}">Login</a>
          <span class="sep">|</span>
          <a href="{{ route('register') }}" class="btn-signup">Daftar</a>
        @endauth
        <button class="burger" id="burgerBtn">☰</button>
      </div>
    </div>
  </div>

  <!-- NAV -->
  <nav class="mainnav">
    <div class="container">
      <div class="dept-wrap">
        <a href="#" class="dept-btn">☰ Semua Kategori <span style="opacity:.7">▾</span></a>
        @if($homeCategories->isNotEmpty())
        <div class="mega dept-mega">
          @foreach($homeCategories as $hcat)
          <a href="{{ route('home-categories.show', $hcat) }}" class="cat-item">
            @if($hcat->image)
            <img src="{{ asset('storage/' . $hcat->image) }}" alt="{{ $hcat->name }}">
            @else
            <span style="width:30px;height:30px;border-radius:7px;background:var(--mint-100);color:var(--green-700);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-weight:800;font-size:12px">{{ mb_substr($hcat->name, 0, 1) }}</span>
            @endif
            <span>{{ $hcat->name }}</span>
          </a>
          @endforeach
        </div>
        @endif
      </div>
      <ul class="navlist">
        @foreach($homeMenus as $menu)
        <li>
          <a href="{{ $menu->url }}">{{ $menu->label }} @if($menu->children->isNotEmpty())<span class="car">▾</span>@endif</a>
          @if($menu->children->isNotEmpty())
            @if($menu->isMega())
              @php $groups = $menu->childrenGrouped(); @endphp
              <div class="mega {{ $groups->count() >= 4 ? 'dept-mega' : '' }}">
                @foreach($groups as $groupLabel => $items)
                <div>
                  <h4>{{ $groupLabel }}</h4>
                  <ul>
                    @foreach($items as $link)
                    <li><a href="{{ $link->url }}">{{ $link->label }}</a></li>
                    @endforeach
                  </ul>
                </div>
                @endforeach
              </div>
            @else
              <ul class="submenu">
                @foreach($menu->children as $link)
                <li><a href="{{ $link->url }}">{{ $link->label }}</a></li>
                @endforeach
              </ul>
            @endif
          @endif
        </li>
        @endforeach
      </ul>
    </div>
  </nav>
</header>

<!-- Mobile drawer -->
<div class="overlay" id="overlay"></div>
<div class="mobile-drawer" id="drawer">
  <span class="close-drawer" id="closeDrawer">✕</span>
  @foreach($homeMenus as $menu)
    @if($menu->children->isEmpty())
      <a href="{{ $menu->url }}" style="display:block;padding:13px 0;font-weight:700;font-size:14.5px;border-bottom:1px solid var(--line)">{{ $menu->label }}</a>
    @else
      <details @if($loop->first) open @endif>
        <summary>{{ $menu->label }}</summary>
        <ul>
          @if($menu->isMega())
            @foreach($menu->childrenGrouped() as $groupLabel => $items)
              <li style="font-weight:700;color:var(--ink);padding-top:10px">{{ $groupLabel }}</li>
              @foreach($items as $link)
              <li><a href="{{ $link->url }}">{{ $link->label }}</a></li>
              @endforeach
            @endforeach
          @else
            @foreach($menu->children as $link)
            <li><a href="{{ $link->url }}">{{ $link->label }}</a></li>
            @endforeach
          @endif
        </ul>
      </details>
    @endif
  @endforeach
</div>

{{ $slot }}

<!-- FOOTER -->
<footer class="site">
  <div class="container">
    <div class="foot-grid">
      <div>
        <a href="{{ url('/') }}" class="brand" style="color:#fff;font-size:20px;"><img class="brand-logo" src="/img/logo-pabalu.png" alt="Logo Pabalu"> Pabalu</a>
        <p style="font-size:13px;margin-top:12px;opacity:.75;line-height:1.6;max-width:280px;">Belanja kebutuhan harianmu jadi lebih mudah, segar, dan cepat sampai — hanya di Pabalu.</p>
      </div>
      @if($footerCompanyPages->isNotEmpty())
      <div>
        <h4>Perusahaan</h4>
        <ul>
          @foreach($footerCompanyPages as $fp)
          <li><a href="{{ route('pages.show', $fp->slug) }}">{{ $fp->title }}</a></li>
          @endforeach
        </ul>
      </div>
      @endif
      @if($footerHelpPages->isNotEmpty())
      <div>
        <h4>Bantuan</h4>
        <ul>
          @foreach($footerHelpPages as $fp)
          <li><a href="{{ route('pages.show', $fp->slug) }}">{{ $fp->title }}</a></li>
          @endforeach
        </ul>
      </div>
      @endif
      @if($homeCategories->isNotEmpty())
      <div>
        <h4>Kategori</h4>
        <ul>
          @foreach($homeCategories->take(6) as $hcat)
          <li><a href="{{ route('home-categories.show', $hcat) }}">{{ $hcat->name }}</a></li>
          @endforeach
        </ul>
      </div>
      @endif
    </div>
    <div class="foot-bottom">© {{ date('Y') }} Pabalu. Semua hak cipta dilindungi.</div>
  </div>
</footer>

<!-- WHATSAPP FLOATING BUTTON -->
<a href="https://wa.me/6281234567890?text=Halo%20Admin%20Pabalu%2C%20saya%20ingin%20bertanya%20mengenai%20produk%20%2F%20pesanan%20saya." target="_blank" rel="noopener" class="wa-float">
  <span class="wa-pulse"></span>
  <span class="wa-icon">💬</span>
  <span class="wa-text">Chat Admin</span>
</a>

<script>

  // ---- Mobile drawer ----
  const drawer = document.getElementById('drawer');
  const overlay = document.getElementById('overlay');
  document.getElementById('burgerBtn').addEventListener('click', () => {
    drawer.classList.add('open'); overlay.classList.add('open');
  });
  document.getElementById('closeDrawer').addEventListener('click', closeDrawer);
  overlay.addEventListener('click', closeDrawer);
  function closeDrawer(){ drawer.classList.remove('open'); overlay.classList.remove('open'); }
</script>
@stack('scripts')
</body>
</html>
