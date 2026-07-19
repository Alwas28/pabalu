<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Daftar — {{ config('app.name', 'Pabalu') }}</title>
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
.auth-wrap{padding:48px 0 72px;background:linear-gradient(180deg,#fdf6f6 0%,#fff 280px)}
.auth-card{max-width:480px;margin:0 auto;background:#fff;border:1px solid #f0eeee;border-radius:22px;box-shadow:0 20px 50px rgba(0,0,0,.06);overflow:hidden}
.auth-head{padding:30px 32px 4px}
.auth-head h1{font-size:21px;font-weight:700;color:#15181d;margin-bottom:8px}
.auth-head p{font-size:13.5px;color:#6b7280;line-height:1.6}
.auth-form{padding:22px 32px 8px;display:flex;flex-direction:column;gap:15px}
.f-label{display:block;font-size:12.5px;font-weight:600;color:#4b5160;margin-bottom:6px}
.f-input{
  width:100%;background:#f7f7f8;border:1px solid #ece9ea;color:#1f2329;border-radius:12px;padding:11px 14px;
  font-size:13.5px;font-family:inherit;outline:none;transition:border-color .15s,box-shadow .15s;
}
.f-input:focus{border-color:var(--ac);box-shadow:0 0 0 3px rgba(232,25,44,.1)}
.f-input.f-input-error{border-color:#dc2626;background:#fff8f8}
.f-hint{font-size:11.5px;color:#9aa0ab;margin-top:5px}
.f-error{font-size:12px;color:#dc2626;margin-top:6px}
.f-icon-wrap{position:relative}
.f-icon-wrap i.f-ico{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:#9aa0ab;font-size:13px;pointer-events:none}
.f-icon-wrap .f-input{padding-left:38px}
.f-eye{position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9aa0ab;font-size:14px;line-height:1}
.terms-check{
  display:flex;align-items:flex-start;gap:10px;cursor:pointer;
  padding:12px 14px;border-radius:12px;border:1px solid #ece9ea;background:#f7f7f8;
  transition:border-color .2s,background .2s;
}
.terms-check:has(input:checked){border-color:var(--ac);background:rgba(232,25,44,.05)}
.auth-submit{
  width:100%;padding:12px;border-radius:99px;border:none;color:#fff;font-size:14px;font-weight:700;
  cursor:pointer;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:8px;
  box-shadow:0 8px 20px rgba(232,25,44,.28);margin-top:2px;transition:.2s;
}
.auth-submit:disabled{opacity:.5;cursor:not-allowed;box-shadow:none}
.auth-foot{padding:18px 32px 28px;text-align:center;border-top:1px solid #f5f3f3;margin-top:6px;font-size:13px;color:#6b7280}
.auth-foot a{color:var(--ac);font-weight:700}
.trust-row{margin-top:18px;text-align:center;display:flex;align-items:center;justify-content:center;gap:18px;flex-wrap:wrap}
.trust-row span{font-size:12px;color:#9aa0ab}

/* ════ ERROR MODAL ════ */
.modal-backdrop{
  position:fixed;inset:0;background:rgba(20,16,15,.55);backdrop-filter:blur(4px);
  z-index:500;display:flex;align-items:center;justify-content:center;padding:20px;
  opacity:0;visibility:hidden;transition:opacity .25s,visibility .25s;
}
.modal-backdrop.show{opacity:1;visibility:visible}
.modal-box{
  background:#fff;border-radius:22px;box-shadow:0 32px 80px rgba(0,0,0,.18);
  max-width:380px;width:100%;padding:32px;text-align:center;
  transform:scale(.92) translateY(16px);transition:transform .28s ease;
}
.modal-backdrop.show .modal-box{transform:scale(1) translateY(0)}
.modal-icon{
  width:68px;height:68px;border-radius:18px;margin:0 auto 18px;
  background:linear-gradient(135deg,#ff4757,#c41020);
  display:grid;place-items:center;
  box-shadow:0 10px 28px -8px rgba(220,38,38,.45);
}
.modal-icon i{font-size:28px;color:#fff}
.modal-box h2{font-size:18px;font-weight:700;color:#15181d;margin-bottom:8px}
.modal-box p{font-size:13.5px;color:#6b7280;line-height:1.65;margin-bottom:22px}
.modal-email-badge{
  display:inline-flex;align-items:center;gap:7px;
  background:#fff0f0;border:1px solid #fecaca;color:#dc2626;
  border-radius:99px;padding:6px 14px;font-size:13px;font-weight:600;
  margin-bottom:22px;word-break:break-all;
}
.modal-btn{
  width:100%;padding:12px;border-radius:99px;border:none;
  background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;
  font-size:14px;font-weight:700;cursor:pointer;font-family:inherit;
  display:flex;align-items:center;justify-content:center;gap:8px;
  box-shadow:0 8px 20px rgba(232,25,44,.3);transition:.2s;
}
.modal-btn:hover{transform:translateY(-1px)}

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
      @if (Route::has('login'))
      <a href="{{ route('login') }}" class="btn btn-red btn-login">Masuk</a>
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
  @if (Route::has('login'))
  <a class="btn btn-red" href="{{ route('login') }}">Masuk</a>
  @endif
</div>

<section class="auth-wrap">
  <div class="wrap">
    <div class="auth-card animate-fadeUp">

      <div class="auth-head">
        <h1>Daftar sebagai Pemilik Toko</h1>
        <p>Kelola outlet, produk, stok, dan laporan bisnis Anda dalam satu platform.</p>
      </div>

      <form method="POST" action="{{ route('register') }}" class="auth-form">
        @csrf

        <div>
          <label class="f-label" for="name">Nama Lengkap <span style="color:var(--ac)">*</span></label>
          <input id="name" name="name" type="text" class="f-input"
            value="{{ old('name') }}" placeholder="Nama lengkap Anda..."
            autofocus autocomplete="name">
          @error('name')
            <p class="f-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
          @enderror
        </div>

        <div>
          <label class="f-label" for="email">Alamat Email <span style="color:var(--ac)">*</span></label>
          <div class="f-icon-wrap">
            <i class="fa-solid fa-envelope f-ico"></i>
            <input id="email" name="email" type="email" class="f-input"
              value="{{ old('email') }}" placeholder="email@domain.com" autocomplete="username">
          </div>
          <p class="f-hint"><i class="fa-solid fa-circle-info"></i> Link verifikasi dikirim ke email ini.</p>
          @error('email')
            <p class="f-error" id="email-error-inline">
              <i class="fa-solid fa-circle-exclamation"></i>
              Email ini sudah terdaftar — silakan gunakan email lain.
            </p>
          @enderror
        </div>

        <div>
          <label class="f-label" for="phone">Nomor WhatsApp Aktif <span style="color:var(--ac)">*</span></label>
          <div class="f-icon-wrap">
            <i class="fa-brands fa-whatsapp f-ico" style="color:#25d366"></i>
            <input id="phone" name="phone" type="tel" class="f-input"
              value="{{ old('phone') }}" placeholder="628xx-xxxx-xxxx" autocomplete="tel">
          </div>
          <p class="f-hint"><i class="fa-solid fa-circle-info"></i> Untuk notifikasi penting dan konfirmasi akun.</p>
          @error('phone')
            <p class="f-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
          @enderror
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
          <div>
            <label class="f-label" for="password">Password <span style="color:var(--ac)">*</span></label>
            <div class="f-icon-wrap">
              <input id="password" name="password" type="password" class="f-input"
                placeholder="Min. 8 karakter" autocomplete="new-password" style="padding-left:14px;padding-right:40px">
              <button type="button" onclick="togglePass('password','eye-pw')" class="f-eye">
                <i class="fa-solid fa-eye" id="eye-pw"></i>
              </button>
            </div>
            @error('password')
              <p class="f-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
            @enderror
          </div>
          <div>
            <label class="f-label" for="password_confirmation">Konfirmasi <span style="color:var(--ac)">*</span></label>
            <div class="f-icon-wrap">
              <input id="password_confirmation" name="password_confirmation" type="password" class="f-input"
                placeholder="Ulangi password" autocomplete="new-password" style="padding-left:14px;padding-right:40px">
              <button type="button" onclick="togglePass('password_confirmation','eye-pc')" class="f-eye">
                <i class="fa-solid fa-eye" id="eye-pc"></i>
              </button>
            </div>
          </div>
        </div>

        <div>
          <label class="terms-check" for="terms">
            <input type="checkbox" name="terms" id="terms"
              {{ old('terms') ? 'checked' : '' }}
              style="width:16px;height:16px;accent-color:var(--ac);margin-top:2px;flex-shrink:0;cursor:pointer">
            <span style="font-size:13px;color:#4b5160;line-height:1.6">
              Saya telah membaca dan menyetujui
              <a href="#" style="color:var(--ac);font-weight:600" onclick="event.preventDefault()" title="Akan segera tersedia">Syarat &amp; Ketentuan</a>
              serta
              <a href="#" style="color:var(--ac);font-weight:600" onclick="event.preventDefault()" title="Akan segera tersedia">Kebijakan Privasi</a>
              Pabalu.
            </span>
          </label>
          @error('terms')
            <p class="f-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
          @enderror
        </div>

        <button type="submit" id="btn-submit" class="auth-submit a-grad"
          @if($errors->has('email')) disabled @endif>
          <i class="fa-solid fa-user-plus"></i> Buat Akun Gratis
        </button>
      </form>

      <div class="auth-foot">
        Sudah punya akun?
        <a href="{{ route('login') }}">Masuk di sini</a>
      </div>
    </div>

    <div class="trust-row">
      <span><i class="fa-solid fa-gift" style="color:var(--ac)"></i> Gratis 14 hari trial</span>
      <span><i class="fa-solid fa-shield-halved" style="color:var(--ac)"></i> Data aman & terenkripsi</span>
      <span><i class="fa-solid fa-credit-card-slash" style="color:var(--ac)"></i> Tanpa kartu kredit</span>
    </div>
  </div>
</section>

{{-- Modal: email sudah terdaftar --}}
@if($errors->has('email'))
<div class="modal-backdrop" id="emailModal">
  <div class="modal-box">
    <div class="modal-icon"><i class="fa-solid fa-envelope-circle-check" style="text-decoration:line-through;font-size:26px"></i></div>
    <h2>Email Sudah Terdaftar</h2>
    <p>Alamat email berikut sudah digunakan oleh akun lain. Silakan gunakan email yang berbeda untuk mendaftar.</p>
    <div class="modal-email-badge">
      <i class="fa-solid fa-envelope"></i>
      <span>{{ old('email') }}</span>
    </div>
    <button class="modal-btn" onclick="closeEmailModal()">
      <i class="fa-solid fa-pen-to-square"></i> Ganti Email
    </button>
  </div>
</div>
@endif

<script>
function togglePass(fieldId, iconId) {
  const f=document.getElementById(fieldId), i=document.getElementById(iconId);
  f.type=f.type==='password'?'text':'password';
  i.className=f.type==='password'?'fa-solid fa-eye':'fa-solid fa-eye-slash';
}

// ── Email modal & submit guard ──────────────────────────────────
const takenEmail = '{{ old('email') }}';
const hasEmailError = {{ $errors->has('email') ? 'true' : 'false' }};
const emailInput  = document.getElementById('email');
const submitBtn   = document.getElementById('btn-submit');

function closeEmailModal() {
  const m = document.getElementById('emailModal');
  if (m) { m.classList.remove('show'); setTimeout(()=>m.remove(), 280); }
  emailInput.focus();
  emailInput.select();
}

function syncSubmitState() {
  if (!hasEmailError) return;
  const changed = emailInput.value.trim().toLowerCase() !== takenEmail.trim().toLowerCase();
  submitBtn.disabled = !changed;
  emailInput.classList.toggle('f-input-error', !changed);
  const inlineErr = document.getElementById('email-error-inline');
  if (inlineErr) inlineErr.style.display = changed ? 'none' : '';
}

if (hasEmailError) {
  // Tunda sedikit agar animasi card selesai dulu
  setTimeout(() => {
    const m = document.getElementById('emailModal');
    if (m) m.classList.add('show');
  }, 400);

  emailInput.addEventListener('input', syncSubmitState);
  syncSubmitState();

  // Klik backdrop menutup modal
  document.getElementById('emailModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeEmailModal();
  });
}

// ── Header & drawer ─────────────────────────────────────────────
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
