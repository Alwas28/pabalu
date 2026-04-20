<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pabalu — Sistem Manajemen UMKM untuk Bisnis Indonesia</title>
<x-seo
    title="Pabalu — Sistem Manajemen UMKM untuk Bisnis Indonesia"
    description="Kelola produk, transaksi, stok, laporan, dan outlet bisnis Anda dalam satu platform. Gratis 30 hari trial, tanpa kartu kredit."
    url="{{ url('/') }}"
/>
<link href="https://fonts.googleapis.com/css2?family=Clash+Display:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{font-family:'Plus Jakarta Sans',sans-serif;background:#fffdf5;color:#1c1611;line-height:1.6;overflow-x:hidden}

/* ── VARS ── */
:root{
  --ac:#E8000D;--ac2:#C0000A;
  --ac-lt:rgba(232,0,13,.1);--ac2-lt:rgba(192,0,10,.1);
  --bg:#fffdf5;--surface:#ffffff;--surface2:#fef9ec;
  --border:#e8dfc8;--text:#1c1611;--muted:#9c8b6e;--sub:#6b5d4f;
}

/* ── NAV ── */
#nav{
  position:fixed;top:0;left:0;right:0;z-index:100;
  display:flex;align-items:center;justify-content:space-between;
  padding:0 6%;height:68px;
  background:rgba(255,253,245,.92);backdrop-filter:blur(14px);
  border-bottom:1px solid var(--border);
  transition:transform .3s ease;
}
#nav.hide{transform:translateY(-100%)}
.nav-logo{display:flex;align-items:center;gap:10px;text-decoration:none}
.nav-logo-icon{width:36px;height:36px;border-radius:10px;overflow:hidden;flex-shrink:0}
.nav-logo-text{font-family:'Clash Display',sans-serif;font-size:18px;font-weight:700;color:#1c1611}
.nav-logo-text span{color:#E8000D}
.nav-links{display:flex;align-items:center;gap:32px}
.nav-links a{font-size:13.5px;font-weight:500;color:var(--sub);text-decoration:none;transition:color .15s}
.nav-links a:hover{color:var(--ac)}
.nav-actions{display:flex;align-items:center;gap:10px}
.btn-ghost{padding:8px 18px;border-radius:10px;border:1px solid var(--border);background:transparent;color:var(--sub);font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;text-decoration:none;transition:color .15s,border-color .15s}
.btn-ghost:hover{color:var(--text);border-color:var(--ac)}
.btn-cta{padding:8px 20px;border-radius:10px;background:linear-gradient(135deg,#E8000D,#C0000A);border:none;color:#fff;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit;text-decoration:none;transition:opacity .15s,transform .15s}
.btn-cta:hover{opacity:.88;transform:translateY(-1px)}
#hamburger-btn{display:none;background:none;border:none;color:var(--sub);font-size:20px;cursor:pointer;padding:4px}
#mobile-menu{display:none;position:fixed;top:68px;left:0;right:0;background:rgba(255,253,245,.98);backdrop-filter:blur(16px);border-bottom:1px solid var(--border);padding:20px 6%;flex-direction:column;gap:4px;z-index:99}
#mobile-menu.open{display:flex}
#mobile-menu a{font-size:14px;color:var(--sub);text-decoration:none;padding:10px 0;border-bottom:1px solid var(--border)}
#mobile-menu a:last-child{border:none}

/* ── HERO ── */
#hero{
  min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;
  text-align:center;padding:100px 6% 80px;position:relative;overflow:hidden;
}
.hero-bg{
  position:absolute;inset:0;pointer-events:none;
  background:
    radial-gradient(ellipse 70% 50% at 20% 10%, rgba(232,0,13,.08) 0%, transparent 60%),
    radial-gradient(ellipse 60% 50% at 80% 20%, rgba(192,0,10,.1) 0%, transparent 60%),
    radial-gradient(ellipse 80% 40% at 50% 100%, rgba(232,0,13,.06) 0%, transparent 60%);
}
.hero-badge{
  display:inline-flex;align-items:center;gap:8px;
  padding:6px 16px;border-radius:99px;
  background:rgba(232,0,13,.1);border:1px solid rgba(232,0,13,.3);
  font-size:12px;font-weight:700;color:#E8000D;margin-bottom:28px;
  animation:fadeUp .6s ease both;letter-spacing:.3px;
}
.hero-title{
  font-family:'Clash Display',sans-serif;font-size:clamp(36px,6vw,72px);font-weight:700;
  color:#1c1611;line-height:1.1;margin-bottom:24px;
  animation:fadeUp .6s .08s ease both;
}
.hero-title .grad{background:linear-gradient(135deg,#E8000D,#C0000A);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.hero-sub{
  font-size:clamp(15px,2vw,18px);color:var(--sub);max-width:600px;margin:0 auto 40px;
  animation:fadeUp .6s .16s ease both;
}
.hero-btns{display:flex;align-items:center;justify-content:center;gap:12px;flex-wrap:wrap;animation:fadeUp .6s .24s ease both}
.btn-hero-primary{padding:14px 32px;border-radius:13px;background:linear-gradient(135deg,#E8000D,#C0000A);border:none;color:#fff;font-size:15px;font-weight:700;cursor:pointer;font-family:inherit;text-decoration:none;display:inline-flex;align-items:center;gap:8px;transition:opacity .15s,transform .15s;box-shadow:0 4px 20px rgba(232,0,13,.3)}
.btn-hero-primary:hover{opacity:.9;transform:translateY(-2px);box-shadow:0 8px 28px rgba(232,0,13,.35)}
.btn-hero-ghost{padding:14px 32px;border-radius:13px;background:#fff;border:1px solid var(--border);color:var(--sub);font-size:15px;font-weight:600;cursor:pointer;font-family:inherit;text-decoration:none;display:inline-flex;align-items:center;gap:8px;transition:color .15s,border-color .15s,transform .15s}
.btn-hero-ghost:hover{color:var(--ac);border-color:var(--ac);transform:translateY(-1px)}

/* ── DASHBOARD PREVIEW ── */
.hero-preview{
  margin-top:64px;width:100%;max-width:900px;
  border-radius:20px;overflow:hidden;
  border:1px solid var(--border);
  box-shadow:0 20px 60px rgba(232,0,13,.1),0 40px 100px rgba(0,0,0,.08);
  animation:fadeUp .6s .32s ease both;
  background:var(--surface);
}
.preview-bar{padding:12px 16px;background:#f5f0e8;display:flex;align-items:center;gap:8px;border-bottom:1px solid var(--border)}
.preview-dot{width:10px;height:10px;border-radius:50%}
.preview-content{padding:20px;display:grid;grid-template-columns:repeat(3,1fr);gap:12px;background:#fffdf5}
.preview-card{background:#fff;border:1px solid var(--border);border-radius:12px;padding:16px}
.preview-card-label{font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:8px}
.preview-card-val{font-family:'Clash Display',sans-serif;font-size:22px;font-weight:700}
.preview-bars{grid-column:1/-1;background:#fff;border:1px solid var(--border);border-radius:12px;padding:16px}
.preview-bar-row{display:flex;align-items:center;gap:10px;margin-bottom:8px}
.preview-bar-row:last-child{margin-bottom:0}
.preview-bar-label{font-size:11px;color:var(--muted);width:60px;flex-shrink:0}
.preview-bar-track{flex:1;height:8px;background:#f0ebe0;border-radius:99px;overflow:hidden}
.preview-bar-fill{height:100%;border-radius:99px;background:linear-gradient(135deg,#E8000D,#C0000A)}

/* ── SECTION ── */
section{padding:80px 6%}
.section-label{font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--ac);margin-bottom:12px}
.section-title{font-family:'Clash Display',sans-serif;font-size:clamp(26px,4vw,42px);font-weight:700;color:var(--text);margin-bottom:16px;line-height:1.2}
.section-sub{font-size:15px;color:var(--sub);max-width:520px;line-height:1.7}
.section-head{margin-bottom:56px}
.section-head.center{text-align:center}
.section-head.center .section-sub{margin:0 auto}

/* ── STATS ── */
#stats{background:#fff;border-top:1px solid var(--border);border-bottom:1px solid var(--border);padding:48px 6%}
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:0;max-width:1000px;margin:0 auto}
.stat-item{text-align:center;padding:20px;border-right:1px solid var(--border)}
.stat-item:last-child{border-right:none}
.stat-num{font-family:'Clash Display',sans-serif;font-size:38px;font-weight:700;background:linear-gradient(135deg,#E8000D,#C0000A);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;line-height:1}
.stat-desc{font-size:13px;color:var(--muted);margin-top:6px}

/* ── FEATURES ── */
#features{background:var(--bg)}
.features-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
.feat-card{background:#fff;border:1px solid var(--border);border-radius:18px;padding:28px;transition:border-color .2s,transform .2s,box-shadow .2s}
.feat-card:hover{border-color:rgba(232,0,13,.4);transform:translateY(-4px);box-shadow:0 12px 32px rgba(232,0,13,.1)}
.feat-icon{width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,rgba(232,0,13,.12),rgba(192,0,10,.1));color:var(--ac);display:grid;place-items:center;font-size:20px;margin-bottom:18px}
.feat-title{font-family:'Clash Display',sans-serif;font-size:16px;font-weight:600;color:var(--text);margin-bottom:8px}
.feat-desc{font-size:13.5px;color:var(--sub);line-height:1.6}

/* ── HOW IT WORKS ── */
#how{background:#fff}
.steps-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:20px;position:relative}
.steps-grid::before{content:'';position:absolute;top:28px;left:calc(12.5% + 24px);right:calc(12.5% + 24px);height:2px;background:linear-gradient(to right,transparent,#e8dfc8,#e8dfc8,transparent);pointer-events:none}
.step-card{text-align:center;padding:0 12px}
.step-num{width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,#E8000D,#C0000A);color:#fff;font-family:'Clash Display',sans-serif;font-size:20px;font-weight:700;display:grid;place-items:center;margin:0 auto 20px;box-shadow:0 4px 16px rgba(232,0,13,.25),0 0 0 8px rgba(232,0,13,.08)}
.step-title{font-family:'Clash Display',sans-serif;font-size:15px;font-weight:600;color:var(--text);margin-bottom:8px}
.step-desc{font-size:13px;color:var(--sub);line-height:1.6}

/* ── MARQUEE ── */
#outlets-marquee{background:var(--surface2);padding:72px 0;overflow:hidden;border-top:1px solid var(--border);border-bottom:1px solid var(--border)}
.marquee-label{text-align:center;margin-bottom:36px;padding:0 6%}
.marquee-track{display:flex;overflow:hidden;mask-image:linear-gradient(to right,transparent 0%,black 12%,black 88%,transparent 100%)}
.marquee-row{display:flex;gap:12px;animation:marquee-scroll 32s linear infinite;flex-shrink:0;padding-right:12px}
.marquee-row.reverse{animation:marquee-scroll-rev 38s linear infinite}
.marquee-item{
  display:inline-flex;align-items:center;gap:9px;
  padding:9px 18px;border-radius:99px;white-space:nowrap;
  background:#fff;border:1px solid var(--border);
  font-size:13px;font-weight:500;color:var(--sub);
  flex-shrink:0;transition:border-color .15s;
}
.marquee-item:hover{border-color:var(--ac);color:var(--ac)}
.marquee-item i{color:var(--ac);font-size:12px}
@keyframes marquee-scroll{0%{transform:translateX(0)}100%{transform:translateX(-50%)}}
@keyframes marquee-scroll-rev{0%{transform:translateX(-50%)}100%{transform:translateX(0)}}

/* ── CTA ── */
#cta{
  background:linear-gradient(135deg,#fff5f5 0%,#fff0f0 100%);
  border-top:1px solid var(--border);
  text-align:center;padding:88px 6%;position:relative;overflow:hidden;
}
#cta::before{
  content:'';position:absolute;inset:0;pointer-events:none;
  background:radial-gradient(ellipse 60% 80% at 50% 50%, rgba(232,0,13,.06) 0%, transparent 70%);
}

/* ── FOOTER ── */
footer{background:#1c1611;border-top:1px solid #2a2018;padding:56px 6% 32px}
.footer-top{display:grid;grid-template-columns:1.8fr 1fr 1fr 1fr;gap:40px;margin-bottom:40px}
.footer-logo{font-family:'Clash Display',sans-serif;font-size:20px;font-weight:700;color:#f5f0e8;margin-bottom:12px}
.footer-logo span{color:#E8000D}
.footer-tagline{font-size:13px;color:#78716c;line-height:1.6;max-width:260px}
.footer-col-title{font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#57534e;margin-bottom:14px}
.footer-links{display:flex;flex-direction:column;gap:10px}
.footer-links a{font-size:13px;color:#78716c;text-decoration:none;transition:color .15s}
.footer-links a:hover{color:#4ade80}
.footer-bottom{border-top:1px solid #2a2018;padding-top:24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px}
.footer-copy{font-size:12.5px;color:#57534e}

/* ── ANIMATIONS ── */
@keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
.reveal{opacity:0;transform:translateY(24px);transition:opacity .6s ease,transform .6s ease}
.reveal.visible{opacity:1;transform:translateY(0)}

/* ── RESPONSIVE ── */
@media(max-width:1024px){
  .features-grid{grid-template-columns:repeat(2,1fr)}
  .steps-grid{grid-template-columns:repeat(2,1fr)}
  .steps-grid::before{display:none}
  .footer-top{grid-template-columns:1fr 1fr}
  .stats-grid{grid-template-columns:repeat(2,1fr)}
  .stat-item:nth-child(2){border-right:none}
  .stat-item:nth-child(3){border-top:1px solid var(--border)}
  .stat-item:nth-child(4){border-top:1px solid var(--border);border-right:none}
}
@media(max-width:640px){
  .nav-links{display:none}
  .nav-actions .btn-ghost{display:none}
  #hamburger-btn{display:block}
  .features-grid,.steps-grid{grid-template-columns:1fr}
  .footer-top{grid-template-columns:1fr}
  .stats-grid{grid-template-columns:1fr}
  .stat-item{border-right:none;border-top:1px solid var(--border)}
  .stat-item:first-child{border-top:none}
  .preview-content{grid-template-columns:1fr 1fr}
  .hero-btns .btn-hero-ghost{display:none}
}
</style>
</head>
<body>

{{-- ── NAVBAR ── --}}
<nav id="nav">
  <a href="/" class="nav-logo">
    <div class="nav-logo-icon"><img src="{{ asset('img/Logo Pabalu.png') }}" alt="Pabalu" style="width:100%;height:100%;object-fit:cover"></div>
    <span class="nav-logo-text">Pa<span>balu</span></span>
  </a>

  <div class="nav-links">
    <a href="#features">Fitur</a>
    <a href="#how">Cara Kerja</a>
    <a href="#outlets-marquee">Outlet Terdaftar</a>
    @if(Route::has('owner.register'))<a href="{{ route('owner.register') }}">Daftar</a>@endif
  </div>

  <div class="nav-actions">
    @auth
      <a href="{{ url('/dashboard') }}" class="btn-cta"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
    @else
      <a href="{{ route('login') }}" class="btn-ghost">Masuk</a>
      @if(Route::has('owner.register'))
      <a href="{{ route('owner.register') }}" class="btn-cta"><i class="fa-solid fa-store"></i> Daftar Gratis</a>
      @endif
    @endauth
    <button id="hamburger-btn" onclick="toggleMobile()"><i class="fa-solid fa-bars"></i></button>
  </div>
</nav>

{{-- Mobile menu --}}
<div id="mobile-menu">
  <a href="#features" onclick="toggleMobile()"><i class="fa-solid fa-grid-2" style="margin-right:8px;color:var(--ac)"></i>Fitur</a>
  <a href="#how" onclick="toggleMobile()"><i class="fa-solid fa-list-check" style="margin-right:8px;color:var(--ac)"></i>Cara Kerja</a>
  <a href="#roles" onclick="toggleMobile()"><i class="fa-solid fa-users-gear" style="margin-right:8px;color:var(--ac)"></i>Role Pengguna</a>
  @auth
    <a href="{{ url('/dashboard') }}"><i class="fa-solid fa-chart-pie" style="margin-right:8px;color:var(--ac)"></i>Dashboard</a>
  @else
    <a href="{{ route('login') }}"><i class="fa-solid fa-right-to-bracket" style="margin-right:8px;color:var(--ac)"></i>Masuk</a>
    @if(Route::has('owner.register'))
    <a href="{{ route('owner.register') }}"><i class="fa-solid fa-store" style="margin-right:8px;color:var(--ac)"></i>Daftar Gratis</a>
    @endif
  @endauth
</div>

{{-- ── HERO ── --}}
<section id="hero">
  <div class="hero-bg"></div>
  <div class="hero-badge"><i class="fa-solid fa-bolt"></i> Kelola UMKM Lebih Cerdas</div>
  <h1 class="hero-title">
    Sistem Manajemen<br><span class="grad">UMKM All-in-One</span>
  </h1>
  <p class="hero-sub">
    Dari kasir, stok, hingga laporan keuangan — semua dalam satu platform yang mudah digunakan oleh tim Anda.
  </p>
  <div class="hero-btns">
    @auth
      <a href="{{ url('/dashboard') }}" class="btn-hero-primary"><i class="fa-solid fa-chart-pie"></i> Buka Dashboard</a>
    @else
      @if(Route::has('owner.register'))
      <a href="{{ route('owner.register') }}" class="btn-hero-primary"><i class="fa-solid fa-store"></i> Mulai Gratis Sekarang</a>
      @endif
      <a href="{{ route('login') }}" class="btn-hero-ghost"><i class="fa-solid fa-right-to-bracket"></i> Masuk ke Akun</a>
    @endauth
  </div>

  {{-- Dashboard preview mockup --}}
  <div class="hero-preview">
    <div class="preview-bar">
      <div class="preview-dot" style="background:#f87171"></div>
      <div class="preview-dot" style="background:#fbbf24"></div>
      <div class="preview-dot" style="background:#34d399"></div>
      <div style="flex:1;height:8px;background:var(--border);border-radius:99px;margin-left:8px"></div>
    </div>
    <div class="preview-content">
      <div class="preview-card">
        <div class="preview-card-label">Total Omzet</div>
        <div class="preview-card-val" style="color:#E8000D">Rp 4,2 jt</div>
        <div style="font-size:11px;color:#E8000D;margin-top:4px"><i class="fa-solid fa-arrow-up"></i> +12% bulan ini</div>
      </div>
      <div class="preview-card">
        <div class="preview-card-label">Transaksi</div>
        <div class="preview-card-val" style="color:#C0000A">148</div>
        <div style="font-size:11px;color:#E8000D;margin-top:4px"><i class="fa-solid fa-arrow-up"></i> +8% bulan ini</div>
      </div>
      <div class="preview-card">
        <div class="preview-card-label">Produk Aktif</div>
        <div class="preview-card-val" style="color:#0891b2">32</div>
        <div style="font-size:11px;color:#9c8b6e;margin-top:4px">3 stok hampir habis</div>
      </div>
      <div class="preview-bars">
        <div style="font-size:11px;font-weight:600;color:var(--sub);margin-bottom:12px">Penjualan 7 Hari Terakhir</div>
        <div class="preview-bar-row"><span class="preview-bar-label">Sen</span><div class="preview-bar-track"><div class="preview-bar-fill" style="width:55%"></div></div></div>
        <div class="preview-bar-row"><span class="preview-bar-label">Sel</span><div class="preview-bar-track"><div class="preview-bar-fill" style="width:72%"></div></div></div>
        <div class="preview-bar-row"><span class="preview-bar-label">Rab</span><div class="preview-bar-track"><div class="preview-bar-fill" style="width:48%"></div></div></div>
        <div class="preview-bar-row"><span class="preview-bar-label">Kam</span><div class="preview-bar-track"><div class="preview-bar-fill" style="width:88%"></div></div></div>
        <div class="preview-bar-row"><span class="preview-bar-label">Jum</span><div class="preview-bar-track"><div class="preview-bar-fill" style="width:65%"></div></div></div>
        <div class="preview-bar-row"><span class="preview-bar-label">Sab</span><div class="preview-bar-track"><div class="preview-bar-fill" style="width:95%"></div></div></div>
        <div class="preview-bar-row"><span class="preview-bar-label">Min</span><div class="preview-bar-track"><div class="preview-bar-fill" style="width:40%"></div></div></div>
      </div>
    </div>
  </div>
</section>

{{-- ── STATS ── --}}
<section id="stats">
  <div class="stats-grid">
    <div class="stat-item reveal">
      <div class="stat-num">500+</div>
      <div class="stat-desc">Outlet Terdaftar</div>
    </div>
    <div class="stat-item reveal" style="transition-delay:.1s">
      <div class="stat-num">50rb+</div>
      <div class="stat-desc">Transaksi Diproses</div>
    </div>
    <div class="stat-item reveal" style="transition-delay:.2s">
      <div class="stat-num">3 Role</div>
      <div class="stat-desc">Manajemen Akses</div>
    </div>
    <div class="stat-item reveal" style="transition-delay:.3s">
      <div class="stat-num">99%</div>
      <div class="stat-desc">Uptime Sistem</div>
    </div>
  </div>
</section>

{{-- ── FEATURES ── --}}
<section id="features">
  <div class="section-head center reveal">
    <div class="section-label">Fitur Unggulan</div>
    <div class="section-title">Semua yang Anda butuhkan<br>dalam satu platform</div>
    <div class="section-sub">Dirancang khusus untuk UMKM Indonesia — sederhana namun lengkap untuk operasional bisnis sehari-hari.</div>
  </div>
  <div class="features-grid">
    <div class="feat-card reveal">
      <div class="feat-icon"><i class="fa-solid fa-cash-register"></i></div>
      <div class="feat-title">POS / Kasir</div>
      <div class="feat-desc">Proses transaksi penjualan dengan cepat. Pilih produk, hitung otomatis, cetak struk — semua dalam hitungan detik.</div>
    </div>
    <div class="feat-card reveal" style="transition-delay:.06s">
      <div class="feat-icon"><i class="fa-solid fa-warehouse"></i></div>
      <div class="feat-title">Manajemen Stok</div>
      <div class="feat-desc">Pantau stok secara real-time. Opening stok, tambah masuk, catat waste, dan lihat pergerakan lengkap setiap harinya.</div>
    </div>
    <div class="feat-card reveal" style="transition-delay:.12s">
      <div class="feat-icon"><i class="fa-solid fa-chart-line"></i></div>
      <div class="feat-title">Laporan Lengkap</div>
      <div class="feat-desc">Laporan penjualan, stok, dan laba rugi tersedia dalam grafik interaktif. Filter per outlet, per periode, atau per kasir.</div>
    </div>
    <div class="feat-card reveal" style="transition-delay:.18s">
      <div class="feat-icon"><i class="fa-solid fa-shop"></i></div>
      <div class="feat-title">Multi Outlet</div>
      <div class="feat-desc">Kelola beberapa cabang sekaligus. Setiap outlet punya data stok dan transaksi sendiri, admin bisa pantau semua dari satu akun.</div>
    </div>
    <div class="feat-card reveal" style="transition-delay:.24s">
      <div class="feat-icon"><i class="fa-solid fa-wallet"></i></div>
      <div class="feat-title">Pengeluaran Harian</div>
      <div class="feat-desc">Catat semua biaya operasional harian. Data otomatis masuk ke laporan laba rugi untuk kalkulasi profit yang akurat.</div>
    </div>
    <div class="feat-card reveal" style="transition-delay:.30s">
      <div class="feat-icon"><i class="fa-solid fa-qrcode"></i></div>
      <div class="feat-title">Order Online</div>
      <div class="feat-desc">Terima pesanan online langsung dari link outlet Anda. Pelanggan bisa order mandiri, Anda kelola antrian dari dashboard.</div>
    </div>
  </div>
</section>

{{-- ── HOW IT WORKS ── --}}
<section id="how">
  <div class="section-head center reveal">
    <div class="section-label">Cara Kerja</div>
    <div class="section-title">Mulai dalam 4 langkah mudah</div>
    <div class="section-sub">Tidak perlu keahlian teknis — siapapun bisa menggunakan Pabalu.</div>
  </div>
  <div class="steps-grid">
    <div class="step-card reveal">
      <div class="step-num">1</div>
      <div class="step-title">Daftar & Buat Outlet</div>
      <div class="step-desc">Buat akun owner gratis dan daftarkan outlet atau usaha Anda dalam beberapa menit.</div>
    </div>
    <div class="step-card reveal" style="transition-delay:.1s">
      <div class="step-num">2</div>
      <div class="step-title">Tambah Produk</div>
      <div class="step-desc">Input katalog produk lengkap dengan harga, kategori, dan stok awal untuk setiap outlet.</div>
    </div>
    <div class="step-card reveal" style="transition-delay:.2s">
      <div class="step-num">3</div>
      <div class="step-title">Undang Tim Anda</div>
      <div class="step-desc">Buat akun kasir untuk karyawan. Atur akses sesuai peran — kasir hanya bisa lihat data transaksinya sendiri.</div>
    </div>
    <div class="step-card reveal" style="transition-delay:.3s">
      <div class="step-num">4</div>
      <div class="step-title">Mulai Operasional</div>
      <div class="step-desc">Opening stok, proses transaksi, catat pengeluaran, dan pantau laporan — semua otomatis tersinkron.</div>
    </div>
  </div>
</section>

{{-- ── OUTLETS MARQUEE ── --}}
@if($outlets->isNotEmpty())
<section id="outlets-marquee">
  <div class="marquee-label reveal">
    <div class="section-label">Bergabung Bersama Kami</div>
    <div class="section-title" style="font-size:clamp(22px,3vw,34px)">Outlet yang sudah terdaftar</div>
  </div>

  @php
    $list   = $outlets->values();
    $half   = (int) ceil($list->count() / 2);
    $row1   = $list->take($half);
    $row2   = $list->skip($half);
    // Duplikat agar loop mulus
    $row1x2 = $row1->concat($row1);
    $row2x2 = $row2->concat($row2);
  @endphp

  <div class="marquee-track">
    <div class="marquee-row">
      @foreach($row1x2 as $nama)
      <div class="marquee-item"><i class="fa-solid fa-store"></i>{{ $nama }}</div>
      @endforeach
    </div>
  </div>
  <div style="margin-top:12px">
    <div class="marquee-track">
      <div class="marquee-row reverse">
        @foreach($row2x2 as $nama)
        <div class="marquee-item"><i class="fa-solid fa-utensils"></i>{{ $nama }}</div>
        @endforeach
      </div>
    </div>
  </div>
</section>
@endif

{{-- ── CTA ── --}}
<section id="cta">
  <div style="position:relative;z-index:1">
    <div class="reveal">
      <div class="section-label" style="margin-bottom:16px">Siap memulai?</div>
      <div class="section-title" style="font-size:clamp(28px,4vw,46px);margin-bottom:16px">
        Kelola bisnis Anda lebih<br>teratur mulai hari ini
      </div>
      <p style="font-size:15px;color:var(--sub);margin-bottom:36px">
        Gratis untuk owner yang baru mendaftar. Tidak perlu kartu kredit.
      </p>
    </div>
    <div style="display:flex;align-items:center;justify-content:center;gap:12px;flex-wrap:wrap" class="reveal" style="transition-delay:.1s">
      @auth
        <a href="{{ url('/dashboard') }}" class="btn-hero-primary"><i class="fa-solid fa-chart-pie"></i> Buka Dashboard</a>
      @else
        @if(Route::has('owner.register'))
        <a href="{{ route('owner.register') }}" class="btn-hero-primary"><i class="fa-solid fa-store"></i> Daftar Sebagai Owner</a>
        @endif
        <a href="{{ route('login') }}" class="btn-hero-ghost"><i class="fa-solid fa-right-to-bracket"></i> Sudah punya akun? Masuk</a>
      @endauth
    </div>
  </div>
</section>

{{-- ── FOOTER ── --}}
<footer>
  <div class="footer-top">
    <div>
      <div class="footer-logo" style="display:flex;align-items:center;gap:10px">
        <img src="{{ asset('img/Logo Pabalu.png') }}" alt="Pabalu" style="width:32px;height:32px;object-fit:cover;border-radius:8px">
        Pa<span>balu</span>
      </div>
      <div class="footer-tagline">Sistem manajemen UMKM yang sederhana, lengkap, dan terjangkau untuk bisnis Indonesia.</div>
    </div>
    <div>
      <div class="footer-col-title">Produk</div>
      <div class="footer-links">
        <a href="#features">Fitur</a>
        <a href="#how">Cara Kerja</a>
        <a href="#outlets-marquee">Outlet Terdaftar</a>
      </div>
    </div>
    <div>
      <div class="footer-col-title">Akun</div>
      <div class="footer-links">
        <a href="{{ route('login') }}">Masuk</a>
        @if(Route::has('owner.register'))<a href="{{ route('owner.register') }}">Daftar Gratis</a>@endif
      </div>
    </div>
    <div>
      <div class="footer-col-title">Sistem</div>
      <div class="footer-links">
        <a href="{{ route('login') }}">Dashboard</a>
        <a href="#">Panduan</a>
      </div>
    </div>
  </div>
  <div class="footer-bottom">
    <div class="footer-copy">&copy; {{ date('Y') }} Pabalu — Sistem Manajemen UMKM</div>
    <div style="font-size:12px;color:var(--muted)">Dibuat dengan <i class="fa-solid fa-heart" style="color:#f87171"></i> untuk UMKM Indonesia</div>
  </div>
</footer>

<script>
/* Scroll hide nav */
var lastY = 0;
var nav = document.getElementById('nav');
window.addEventListener('scroll', function(){
  var y = window.scrollY;
  if(y > lastY && y > 100) nav.classList.add('hide');
  else nav.classList.remove('hide');
  lastY = y;
}, {passive:true});

/* Mobile menu */
function toggleMobile(){
  document.getElementById('mobile-menu').classList.toggle('open');
}
document.addEventListener('click', function(e){
  var menu = document.getElementById('mobile-menu');
  var btn  = document.getElementById('hamburger-btn');
  if(menu.classList.contains('open') && !menu.contains(e.target) && !btn.contains(e.target)){
    menu.classList.remove('open');
  }
});

/* Scroll reveal */
var obs = new IntersectionObserver(function(entries){
  entries.forEach(function(e){ if(e.isIntersecting) e.target.classList.add('visible'); });
}, {threshold:.12});
document.querySelectorAll('.reveal').forEach(function(el){ obs.observe(el); });
</script>
</body>
</html>
