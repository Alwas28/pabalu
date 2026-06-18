<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Masuk — {{ config('app.name', 'Pabalu') }}</title>
<link rel="icon" href="/img/Logo.ico">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Clash+Display:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
*{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{font-family:'Plus Jakarta Sans',sans-serif;background:#fff;color:#1f2329}
.font-display,h1,h2,h3,h4{font-family:'Clash Display',sans-serif}
a{text-decoration:none;color:inherit}
img{display:block;max-width:100%}

:root{
  --ac:#e8192c;--ac2:#c41020;
  --red:#E63329;--red-dark:#C42820;--red-soft:#FDECEA;
  --ink:#1A1614;--muted:#6E6763;--line:#EFE9E6;--cream:#FCF7F4;
  --shadow:0 18px 40px -18px rgba(230,51,41,.18);
  --shadow-sm:0 8px 24px -12px rgba(26,22,20,.12);
}
.a-text{color:var(--ac)}
.a-grad{background:linear-gradient(135deg,var(--ac),var(--ac2))}

.wrap{max-width:1240px;margin:0 auto;padding:0 24px}

/* ════ HEADER (sama seperti homepage) ════ */
header{position:sticky;top:0;z-index:100;background:rgba(255,255,255,.82);backdrop-filter:blur(14px);border-bottom:1px solid transparent;transition:.3s}
header.scrolled{border-bottom:1px solid var(--line);box-shadow:0 6px 24px -18px rgba(0,0,0,.3)}
.nav{display:flex;align-items:center;gap:24px;height:78px}
.brand{display:flex;align-items:center;gap:11px;font-weight:800}
.brand-text{display:flex;flex-direction:column;line-height:1.1}
.logo-mark{width:46px;height:46px;background:#fff;border:1.5px solid var(--line);border-radius:13px;display:grid;place-items:center;box-shadow:0 8px 18px -6px rgba(230,51,41,.25);overflow:hidden;flex-shrink:0}
.logo-mark img{width:34px;height:34px;object-fit:contain}
.brand-name{font-family:'Plus Jakarta Sans',sans-serif;font-size:20px;font-weight:800;color:var(--ink);line-height:1}
.brand-sub{font-size:10.5px;color:var(--red);font-weight:700;letter-spacing:.5px}
.menu{display:flex;gap:28px;margin-left:24px}
.menu a{font-size:15px;font-weight:600;color:var(--muted);position:relative;padding:2px 0;transition:.2s;white-space:nowrap}
.menu a.active,.menu a:hover{color:var(--ink)}
.menu a::after{content:'';position:absolute;left:0;bottom:-3px;height:2.5px;width:0;background:var(--red);border-radius:2px;transition:.25s}
.menu a.active::after,.menu a:hover::after{width:100%}
.nav-right{margin-left:auto;display:flex;align-items:center;gap:14px}
.btn{display:inline-flex;align-items:center;gap:9px;font-family:'Plus Jakarta Sans';font-weight:700;font-size:15px;padding:14px 26px;border-radius:999px;border:none;cursor:pointer;transition:.25s;white-space:nowrap}
.btn-red{background:var(--red);color:#fff;box-shadow:0 10px 24px -8px rgba(230,51,41,.5)}
.btn-red:hover{background:var(--red-dark);transform:translateY(-2px)}
.btn-login{padding:11px 20px;font-size:14px}
.search{display:flex;align-items:center;gap:10px;background:#fff;border:1.5px solid var(--line);border-radius:999px;padding:11px 18px;width:280px;transition:.2s}
.search:focus-within{border-color:var(--red);box-shadow:0 0 0 4px var(--red-soft)}
.search input{border:none;outline:none;font-size:14px;width:100%;background:none;font-family:'Inter',sans-serif;color:var(--ink)}
.search i{color:#9b918c;font-size:14px}
.hamburger{display:none;width:46px;height:46px;border-radius:12px;border:1.5px solid var(--line);background:#fff;cursor:pointer;align-items:center;justify-content:center;font-size:17px;color:var(--ink)}
@media(max-width:1180px){.search{width:220px}.menu{display:none}.hamburger{display:flex}}
@media(max-width:560px){.search{display:none}}

/* ════ MOBILE DRAWER (slide dari kanan) ════ */
.drawer-overlay{position:fixed;inset:0;background:rgba(20,16,15,.5);opacity:0;visibility:hidden;transition:opacity .3s;z-index:150}
.drawer-overlay.show{opacity:1;visibility:visible}
.mobile-menu{position:fixed;top:0;right:0;height:100vh;width:300px;max-width:84vw;background:#fff;z-index:200;transform:translateX(100%);transition:transform .32s ease;box-shadow:-16px 0 48px -16px rgba(0,0,0,.25);display:flex;flex-direction:column;padding:22px 22px 28px;overflow-y:auto}
.mobile-menu.open{transform:translateX(0)}
.drawer-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px}
.drawer-close{width:38px;height:38px;border-radius:10px;border:1.5px solid var(--line);background:#fff;display:grid;place-items:center;cursor:pointer;color:var(--ink);font-size:15px}
.mobile-menu a{padding:13px 12px;border-radius:12px;font-weight:600;font-family:'Plus Jakarta Sans';color:var(--ink)}
.mobile-menu a:hover{background:var(--cream);color:var(--red)}
.mobile-menu .btn{margin-top:10px;justify-content:center}

/* ════ AUTH CARD ════ */
.auth-wrap{padding:56px 0 72px;background:linear-gradient(180deg,#fdf6f6 0%,#fff 280px)}
.auth-card{max-width:440px;margin:0 auto;background:#fff;border:1px solid #f0eeee;border-radius:22px;box-shadow:0 20px 50px rgba(0,0,0,.06);overflow:hidden}
.auth-head{padding:32px 32px 6px}
.auth-head h1{font-size:22px;font-weight:700;color:#15181d;margin-bottom:8px}
.auth-head p{font-size:13.5px;color:#6b7280;line-height:1.6}
.auth-status{margin:18px 32px 0;padding:11px 14px;border-radius:12px;background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;font-size:13px;font-weight:600}
.auth-form{padding:24px 32px 8px;display:flex;flex-direction:column;gap:16px}
.f-label{display:block;font-size:12.5px;font-weight:600;color:#4b5160;margin-bottom:6px}
.f-input{
  width:100%;background:#f7f7f8;border:1px solid #ece9ea;color:#1f2329;border-radius:12px;padding:11px 14px;
  font-size:13.5px;font-family:inherit;outline:none;transition:border-color .15s,box-shadow .15s;
}
.f-input:focus{border-color:var(--ac);box-shadow:0 0 0 3px rgba(232,25,44,.1)}
.f-error{font-size:12px;color:#dc2626;margin-top:6px}
.f-row{display:flex;align-items:center;justify-content:space-between;font-size:13px}
.f-remember{display:flex;align-items:center;gap:8px;color:#4b5160;cursor:pointer}
.f-remember input{width:15px;height:15px;accent-color:var(--ac);cursor:pointer}
.f-forgot{color:var(--ac);font-weight:600}
.auth-submit{
  width:100%;padding:12px;border-radius:99px;border:none;color:#fff;font-size:14px;font-weight:700;
  cursor:pointer;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:8px;
  box-shadow:0 8px 20px rgba(232,25,44,.28);margin-top:4px;
}
.auth-foot{padding:18px 32px 30px;text-align:center;border-top:1px solid #f5f3f3;margin-top:6px;font-size:13px;color:#6b7280}
.auth-foot a{color:var(--ac);font-weight:700}

@keyframes fadeUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
.animate-fadeUp{animation:fadeUp .5s ease both}
</style>
</head>
<body>

<header id="hdr">
  <div class="wrap nav">
    <a class="brand" href="{{ url('/') }}">
      <span class="logo-mark"><img src="/img/logo-pabalu.png" alt="Pabalu"></span>
      <span class="brand-text"><span class="brand-name">Pabalu</span><span class="brand-sub">UMKM KUAT BERSAMA</span></span>
    </a>
    <nav class="menu">
      <a href="{{ url('/') }}">Beranda</a>
      <a href="{{ url('/') }}#kategori">Kategori</a>
      <a href="{{ url('/') }}#fitur">Fitur</a>
      <a href="{{ url('/') }}#cara-kerja">Cara Daftar</a>
      <a href="{{ url('/') }}#testimoni">Testimoni</a>
      <a href="{{ url('/') }}#kontak">Kontak</a>
    </nav>
    <div class="nav-right">
      <div class="search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input placeholder="Cari fitur, panduan, atau bantuan">
      </div>
      @if (Route::has('register'))
      <a href="{{ route('register') }}" class="btn btn-red btn-login">Daftar</a>
      @endif
      <button class="hamburger" id="burger" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
    </div>
  </div>
</header>

<div class="drawer-overlay" id="drawerOverlay"></div>
<div class="mobile-menu" id="mobileMenu">
  <div class="drawer-head">
    <span class="brand-text"><span class="brand-name" style="font-size:17px">Pabalu</span><span class="brand-sub">UMKM KUAT BERSAMA</span></span>
    <button class="drawer-close" id="drawerClose" aria-label="Tutup menu"><i class="fa-solid fa-xmark"></i></button>
  </div>
  <a href="{{ url('/') }}">Beranda</a>
  <a href="{{ url('/') }}#kategori">Kategori</a>
  <a href="{{ url('/') }}#fitur">Fitur</a>
  <a href="{{ url('/') }}#cara-kerja">Cara Daftar</a>
  <a href="{{ url('/') }}#testimoni">Testimoni</a>
  <a href="{{ url('/') }}#kontak">Kontak</a>
  @if (Route::has('register'))
  <a class="btn btn-red" href="{{ route('register') }}">Daftar</a>
  @endif
</div>

<section class="auth-wrap">
  <div class="wrap">
    <div class="auth-card animate-fadeUp">

      <div class="auth-head">
        <h1>Masuk ke Akun Anda</h1>
        <p>Kelola outlet, produk, dan transaksi bisnis Anda di Pabalu.</p>
      </div>

      @if (session('status'))
        <div class="auth-status"><i class="fa-solid fa-circle-check"></i> {{ session('status') }}</div>
      @endif

      <form method="POST" action="{{ route('login') }}" class="auth-form">
        @csrf

        <div>
          <label class="f-label" for="email">Email</label>
          <input id="email" name="email" type="email" class="f-input"
            value="{{ old('email') }}" required autofocus autocomplete="username"
            placeholder="email@domain.com">
          @error('email')
            <p class="f-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
          @enderror
        </div>

        <div>
          <label class="f-label" for="password">Password</label>
          <input id="password" name="password" type="password" class="f-input"
            required autocomplete="current-password" placeholder="Masukkan password">
          @error('password')
            <p class="f-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
          @enderror
        </div>

        <div class="f-row">
          <label class="f-remember" for="remember_me">
            <input id="remember_me" type="checkbox" name="remember">
            Ingat saya
          </label>
          @if (Route::has('password.request'))
            <a class="f-forgot" href="{{ route('password.request') }}">Lupa password?</a>
          @endif
        </div>

        <button type="submit" class="auth-submit a-grad">
          <i class="fa-solid fa-right-to-bracket"></i> Masuk
        </button>
      </form>

      <div class="auth-foot">
        Belum punya akun?
        @if (Route::has('register'))
          <a href="{{ route('register') }}">Daftar di sini</a>
        @endif
      </div>
    </div>
  </div>
</section>

<script>
const hdr=document.getElementById('hdr');
addEventListener('scroll',()=>hdr.classList.toggle('scrolled',scrollY>10));
const burger=document.getElementById('burger'),mm=document.getElementById('mobileMenu'),
      overlay=document.getElementById('drawerOverlay'),drawerClose=document.getElementById('drawerClose');
function openDrawer(){mm.classList.add('open');overlay.classList.add('show');document.body.style.overflow='hidden';}
function closeDrawer(){mm.classList.remove('open');overlay.classList.remove('show');document.body.style.overflow='';}
burger.addEventListener('click',openDrawer);
drawerClose.addEventListener('click',closeDrawer);
overlay.addEventListener('click',closeDrawer);
mm.querySelectorAll('a').forEach(a=>a.addEventListener('click',closeDrawer));
</script>
</body>
</html>
