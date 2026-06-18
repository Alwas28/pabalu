<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ config('app.name', 'Pabalu') }} — Sistem Manajemen UMKM</title>
<meta name="description" content="Pabalu membantu pelaku UMKM mengelola kasir, stok, multi-outlet, dan laporan penjualan dalam satu sistem yang simpel dan modern.">
<link rel="icon" href="/img/Logo.ico">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
:root{
  --red:#E63329; --red-dark:#C42820; --red-soft:#FDECEA; --red-tint:#FBE3E0;
  --ink:#1A1614; --muted:#6E6763; --line:#EFE9E6;
  --canvas:#FFFFFF; --cream:#FCF7F4; --gold:#F5A524;
  --shadow:0 18px 40px -18px rgba(230,51,41,.18);
  --shadow-sm:0 8px 24px -12px rgba(26,22,20,.12);
  --r:18px; --maxw:1320px;
}
*{margin:0;padding:0;box-sizing:border-box}
html{scroll-behavior:smooth}
body{font-family:'Inter',sans-serif;color:var(--ink);background:var(--canvas);line-height:1.55;-webkit-font-smoothing:antialiased}
h1,h2,h3,h4,.brand,nav a{font-family:'Plus Jakarta Sans',sans-serif}
a{text-decoration:none;color:inherit}
img{display:block;max-width:100%}
.wrap{max-width:var(--maxw);margin:0 auto;padding:0 24px}
.btn{display:inline-flex;align-items:center;gap:9px;font-family:'Plus Jakarta Sans';font-weight:700;font-size:15px;padding:14px 26px;border-radius:999px;border:none;cursor:pointer;transition:.25s;white-space:nowrap}
.btn-red{background:var(--red);color:#fff;box-shadow:0 10px 24px -8px rgba(230,51,41,.5)}
.btn-red:hover{background:var(--red-dark);transform:translateY(-2px)}
.btn-ghost{background:#fff;color:var(--ink);border:1.5px solid var(--line)}
.btn-ghost:hover{border-color:var(--red);color:var(--red)}
.arrow{transition:transform .25s;display:inline-flex}
.btn:hover .arrow{transform:translateX(4px)}

/* ═══ HEADER ═══ */
header{position:sticky;top:0;z-index:100;background:rgba(255,255,255,.82);backdrop-filter:blur(14px);border-bottom:1px solid transparent;transition:.3s}
header.scrolled{border-bottom:1px solid var(--line);box-shadow:0 6px 24px -18px rgba(0,0,0,.3)}
.nav{display:flex;align-items:center;gap:24px;height:78px}
.brand{display:flex;align-items:center;gap:11px;font-weight:800}
.brand-text{display:flex;flex-direction:column;line-height:1.1}
.logo-mark{
  width:46px;height:46px;background:#fff;border:1.5px solid var(--line);border-radius:13px;
  display:grid;place-items:center;box-shadow:0 8px 18px -6px rgba(230,51,41,.25);overflow:hidden;flex-shrink:0;
}
.logo-mark img{width:34px;height:34px;object-fit:contain}
.brand-name{font-size:20px;line-height:1}
.brand-sub{font-size:10.5px;color:var(--red);font-weight:700;letter-spacing:.5px;font-family:'Inter'}
.menu{display:flex;gap:28px;margin-left:24px}
.menu a{font-size:15px;font-weight:600;color:var(--muted);position:relative;padding:2px 0;transition:.2s;white-space:nowrap}
.menu a.active,.menu a:hover{color:var(--ink)}
.menu a::after{content:'';position:absolute;left:0;bottom:-3px;height:2.5px;width:0;background:var(--red);border-radius:2px;transition:.25s}
.menu a.active::after,.menu a:hover::after{width:100%}
.nav-right{margin-left:auto;display:flex;align-items:center;gap:14px}
.btn-login{padding:11px 20px;font-size:14px}
.search{display:flex;align-items:center;gap:10px;background:#fff;border:1.5px solid var(--line);border-radius:999px;padding:11px 18px;width:300px;transition:.2s}
.search:focus-within{border-color:var(--red);box-shadow:0 0 0 4px var(--red-soft)}
.search input{border:none;outline:none;font-size:14px;width:100%;background:none;font-family:'Inter';color:var(--ink)}
.search i{color:#9b918c;font-size:14px}
.hamburger{display:none;width:46px;height:46px;border-radius:12px;border:1.5px solid var(--line);background:#fff;cursor:pointer;align-items:center;justify-content:center;font-size:17px;color:var(--ink)}
@media(max-width:1180px){.search{width:220px}.menu{display:none}.hamburger{display:flex}}
@media(max-width:560px){.search{display:none}}

/* ═══ MOBILE DRAWER (slide dari kanan) ═══ */
.drawer-overlay{position:fixed;inset:0;background:rgba(20,16,15,.5);opacity:0;visibility:hidden;transition:opacity .3s;z-index:150}
.drawer-overlay.show{opacity:1;visibility:visible}
.mobile-menu{
  position:fixed;top:0;right:0;height:100vh;width:300px;max-width:84vw;background:#fff;z-index:200;
  transform:translateX(100%);transition:transform .32s ease;box-shadow:-16px 0 48px -16px rgba(0,0,0,.25);
  display:flex;flex-direction:column;padding:22px 22px 28px;overflow-y:auto;
}
.mobile-menu.open{transform:translateX(0)}
.drawer-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px}
.drawer-close{width:38px;height:38px;border-radius:10px;border:1.5px solid var(--line);background:#fff;display:grid;place-items:center;cursor:pointer;color:var(--ink);font-size:15px}
.mobile-menu a{padding:13px 12px;border-radius:12px;font-weight:600;font-family:'Plus Jakarta Sans';color:var(--ink)}
.mobile-menu a:hover{background:var(--cream);color:var(--red)}
.mobile-menu .btn{margin-top:10px;justify-content:center}

/* ═══ HERO ═══ */
.hero{margin:24px auto 0}
.hero-inner{background:linear-gradient(135deg,#FDEEEC 0%,#FBE3E0 100%);border-radius:30px;padding:62px;display:grid;grid-template-columns:1.05fr 1fr;gap:40px;align-items:center;position:relative;overflow:hidden}
.hero-inner::before{content:'';position:absolute;width:420px;height:420px;border:50px solid rgba(230,51,41,.06);border-radius:50%;right:-180px;bottom:-200px}
.dots{position:absolute;top:34px;left:34px;display:grid;grid-template-columns:repeat(6,8px);gap:9px;opacity:.5}
.dots i{width:8px;height:8px;border-radius:50%;background:var(--red);opacity:.35;display:block}
.eyebrow{display:inline-flex;align-items:center;gap:8px;background:#fff;color:var(--red);font-weight:700;font-size:13px;padding:7px 15px;border-radius:999px;margin-bottom:22px;font-family:'Plus Jakarta Sans';box-shadow:var(--shadow-sm)}
.eyebrow .pulse{width:8px;height:8px;border-radius:50%;background:var(--red);box-shadow:0 0 0 0 rgba(230,51,41,.5);animation:pulse 2s infinite}
@keyframes pulse{0%{box-shadow:0 0 0 0 rgba(230,51,41,.5)}70%{box-shadow:0 0 0 10px rgba(230,51,41,0)}100%{box-shadow:0 0 0 0 rgba(230,51,41,0)}}
.hero h1{font-size:58px;font-weight:800;line-height:1.06;letter-spacing:-1.5px;margin-bottom:20px}
.hero h1 .accent{color:var(--red);display:block}
.hero p{font-size:17px;color:var(--muted);max-width:460px;margin-bottom:34px}
.hero-cta{display:flex;align-items:center;gap:22px;flex-wrap:wrap}
.play{display:inline-flex;align-items:center;gap:13px;font-weight:700;font-family:'Plus Jakarta Sans';cursor:pointer}
.play-circle{width:54px;height:54px;border-radius:50%;background:#fff;display:grid;place-items:center;color:var(--red);box-shadow:var(--shadow);transition:.25s}
.play:hover .play-circle{transform:scale(1.08)}
.hero-visual{position:relative;min-height:380px}
.hv-tile{position:absolute;width:108px;height:108px;border-radius:22px;background:#fff;box-shadow:0 16px 40px rgba(26,22,20,.1);display:grid;place-items:center;font-size:32px;color:var(--red)}
.hv-center{
  position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:190px;height:190px;border-radius:28px;
  background:#fff;box-shadow:0 24px 60px rgba(26,22,20,.12);display:grid;place-items:center;font-size:60px;color:var(--red);
}
.stats-card{position:absolute;right:-8px;top:14px;background:#fff;border-radius:22px;padding:24px;box-shadow:var(--shadow);display:flex;flex-direction:column;gap:18px;min-width:220px}
.stat{display:flex;align-items:center;gap:14px}
.stat-ic{width:44px;height:44px;border-radius:13px;background:var(--red-soft);display:grid;place-items:center;color:var(--red);flex-shrink:0;font-size:16px}
.stat-num{font-family:'Plus Jakarta Sans';font-weight:800;font-size:19px;line-height:1}
.stat-lbl{font-size:12px;color:var(--muted);margin-top:3px}
@media(max-width:900px){
  .hero-inner{grid-template-columns:1fr;padding:42px 32px;border-radius:20px}
  .hero h1{font-size:40px}
  .hero-visual{display:none}
}

/* ═══ SECTION ═══ */
section{padding:64px 0}
.sec-head{display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:34px}
.sec-head h2{font-size:32px;font-weight:800;letter-spacing:-.8px}
.sec-head p{color:var(--muted);margin-top:6px}
.sec-center{justify-content:center;text-align:center;flex-direction:column;gap:6px}
.link-more{display:inline-flex;align-items:center;gap:8px;color:var(--red);font-weight:700;font-family:'Plus Jakarta Sans';font-size:15px}
.link-more:hover .arrow{transform:translateX(4px)}
@media(max-width:820px){.sec-head h2{font-size:25px}}

/* ═══ KATEGORI ═══ */
.cat-grid{display:grid;grid-template-columns:repeat(8,1fr);gap:16px}
.cat{background:#fff;border:1.5px solid var(--line);border-radius:var(--r);padding:22px 16px;display:flex;flex-direction:column;align-items:center;gap:12px;text-align:center;transition:.25s}
.cat:hover{border-color:transparent;box-shadow:var(--shadow);transform:translateY(-5px);background:var(--cream)}
.cat-ic{width:50px;height:50px;border-radius:14px;background:var(--red-soft);display:grid;place-items:center;color:var(--red);transition:.25s;font-size:19px}
.cat:hover .cat-ic{background:var(--red);color:#fff}
.cat span{font-size:13px;font-weight:600;line-height:1.3}
@media(max-width:1180px){.cat-grid{grid-template-columns:repeat(4,1fr)}}
@media(max-width:560px){.cat-grid{grid-template-columns:repeat(2,1fr)}}

/* ═══ PRODUK ═══ */
.prod-layout{display:grid;grid-template-columns:1fr 300px;gap:24px;align-items:stretch}
.prod-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:20px}
.card{background:#fff;border:1.5px solid var(--line);border-radius:20px;overflow:hidden;transition:.28s}
.card:hover{box-shadow:var(--shadow);transform:translateY(-6px);border-color:transparent}
.card-img{position:relative;height:170px;overflow:hidden;background:var(--cream)}
.card-img img{width:100%;height:100%;object-fit:cover;transition:.4s}
.card:hover .card-img img{transform:scale(1.07)}
.tag{position:absolute;top:12px;left:12px;background:var(--red);color:#fff;font-size:11px;font-weight:700;padding:5px 11px;border-radius:999px;font-family:'Plus Jakarta Sans'}
.card-body{padding:16px 16px 18px}
.card-body h3{font-size:15px;font-weight:700;margin-bottom:3px}
.seller{font-size:12.5px;color:var(--muted);margin-bottom:10px}
.price{font-family:'Plus Jakarta Sans';font-weight:800;font-size:17px}

.join{background:linear-gradient(160deg,var(--red) 0%,var(--red-dark) 100%);border-radius:24px;padding:32px;color:#fff;position:relative;overflow:hidden;display:flex;flex-direction:column}
.join::after{content:'';position:absolute;right:-40px;bottom:-40px;width:200px;height:200px;border:30px solid rgba(255,255,255,.08);border-radius:50%}
.join small{font-weight:600;opacity:.85;font-size:14px}
.join h3{font-size:24px;font-weight:800;margin:6px 0 12px;line-height:1.15}
.join p{font-size:14px;opacity:.92;margin-bottom:auto}
.join .btn{background:#fff;color:var(--red);margin-top:24px;align-self:flex-start}
.join .btn:hover{transform:translateY(-2px)}
.store-ill{position:relative;margin-top:24px;display:flex;justify-content:center;font-size:80px;opacity:.95}
@media(max-width:1180px){.prod-layout{grid-template-columns:1fr}.prod-grid{grid-template-columns:repeat(3,1fr)}.join{flex-direction:row;align-items:center;gap:24px}.join .store-ill{margin-top:0}.join p{margin-bottom:0}}
@media(max-width:820px){.prod-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:560px){.prod-grid{grid-template-columns:1fr}.join{flex-direction:column}}

/* ═══ FITUR UNGGULAN ═══ */
.feat-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:22px}
.feat{background:#fff;border:1.5px solid var(--line);border-radius:20px;padding:30px 26px;transition:.25s}
.feat:hover{box-shadow:var(--shadow);transform:translateY(-5px);border-color:transparent}
.feat-ic{width:54px;height:54px;border-radius:15px;background:var(--red-soft);color:var(--red);display:grid;place-items:center;margin-bottom:18px;transition:.25s;font-size:22px}
.feat:hover .feat-ic{background:var(--red);color:#fff}
.feat h3{font-size:18px;font-weight:700;margin-bottom:8px}
.feat p{color:var(--muted);font-size:14px}
@media(max-width:1180px){.feat-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:560px){.feat-grid{grid-template-columns:1fr}}

/* ═══ CARA MENDAPATKAN APLIKASI ═══ */
.how{background:var(--cream)}
.steps{display:grid;grid-template-columns:repeat(3,1fr);gap:24px}
.step{background:#fff;border-radius:20px;padding:32px;border:1.5px solid var(--line);position:relative;transition:.25s}
.step:hover{box-shadow:var(--shadow);transform:translateY(-5px)}
.step-num{font-family:'Plus Jakarta Sans';font-weight:800;font-size:15px;color:var(--red);background:var(--red-soft);width:42px;height:42px;border-radius:12px;display:grid;place-items:center;margin-bottom:18px}
.step h3{font-size:18px;font-weight:700;margin-bottom:8px}
.step p{color:var(--muted);font-size:14.5px}
@media(max-width:820px){.steps{grid-template-columns:1fr}}

/* ═══ JENIS OUTLET ═══ */
.outlet-wrap{background:var(--cream)}
.outlet-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
.outlet{background:#fff;border:1.5px solid var(--line);border-radius:20px;padding:26px;display:flex;align-items:center;gap:18px;transition:.25s}
.outlet:hover{box-shadow:var(--shadow);transform:translateY(-4px);border-color:transparent}
.outlet-ic{width:58px;height:58px;border-radius:16px;display:grid;place-items:center;font-size:23px;flex-shrink:0;background:var(--red-soft);color:var(--red)}
.outlet h3{font-size:16.5px;font-weight:700;margin-bottom:3px}
.outlet p{font-size:13px;color:var(--muted)}
.outlet .go{margin-left:auto;color:var(--red);opacity:0;transition:.25s}
.outlet:hover .go{opacity:1;transform:translateX(3px)}
.outlet-empty{grid-column:1/-1;border:1.5px dashed var(--line);border-radius:20px;padding:44px 24px;text-align:center;color:var(--muted);background:#fff}
.outlet-empty i{font-size:24px;color:#d9d3d0;margin-bottom:10px;display:block}
@media(max-width:1180px){.outlet-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:820px){.outlet-grid{grid-template-columns:1fr}}

/* ═══ OUTLET TERDAFTAR ═══ */
.reg-outlet-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:18px}
.reg-outlet{background:#fff;border:1.5px solid var(--line);border-radius:18px;padding:22px;display:flex;flex-direction:column;align-items:flex-start;gap:12px;transition:.25s}
.reg-outlet:hover{box-shadow:var(--shadow);transform:translateY(-4px);border-color:transparent}
.reg-outlet-body h3{font-size:15.5px;font-weight:700;margin-bottom:2px}
.reg-outlet-body p{font-size:12.5px;color:var(--muted)}
.reg-outlet-link{display:inline-flex;align-items:center;gap:7px;font-size:13px;font-weight:700;color:var(--red);margin-top:auto}
.reg-outlet:hover .reg-outlet-link{text-decoration:underline}
@media(max-width:1180px){.reg-outlet-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:560px){.reg-outlet-grid{grid-template-columns:1fr}}

/* ═══ LOGO MITRA ═══ */
.mitra-row{display:grid;grid-template-columns:repeat(6,1fr);gap:16px}
.mitra-card{border:1.5px solid var(--line);border-radius:var(--r);padding:18px 12px;text-align:center;background:#fff;transition:.25s}
.mitra-card:hover{box-shadow:var(--shadow);border-color:transparent;transform:translateY(-4px)}
.mitra-logo{height:52px;display:grid;place-items:center;margin-bottom:10px}
.mitra-logo img{max-height:100%;max-width:100%;object-fit:contain}
.mitra-name{font-size:12px;font-weight:600;color:var(--muted);overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.mitra-empty{grid-column:1/-1;border:1.5px dashed var(--line);border-radius:18px;padding:44px 24px;text-align:center;color:var(--muted)}
.mitra-empty i{font-size:24px;color:#d9d3d0;margin-bottom:10px;display:block}
@media(max-width:1180px){.mitra-row{grid-template-columns:repeat(3,1fr)}}
@media(max-width:560px){.mitra-row{grid-template-columns:repeat(2,1fr)}}

/* ═══ KATA OWNER ═══ */
.owner{display:grid;grid-template-columns:320px 1fr;gap:48px;align-items:center;background:#fff;border:1.5px solid var(--line);border-radius:28px;padding:48px;box-shadow:var(--shadow-sm)}
.owner-photo{position:relative}
.owner-photo img{border-radius:24px;width:100%;height:340px;object-fit:cover}
.owner-photo .quote-mark{position:absolute;bottom:-18px;right:-18px;width:60px;height:60px;border-radius:18px;background:var(--red);color:#fff;display:grid;place-items:center;font-family:'Plus Jakarta Sans';font-size:38px;font-weight:800;box-shadow:0 12px 28px -10px rgba(230,51,41,.6)}
.owner-body .stars{color:var(--gold);font-size:17px;margin-bottom:18px;display:flex;gap:4px}
.owner-body blockquote{font-family:'Plus Jakarta Sans';font-size:22px;font-weight:600;line-height:1.45;letter-spacing:-.3px;margin-bottom:24px}
.owner-name b{font-family:'Plus Jakarta Sans';font-size:17px}
.owner-name span{color:var(--muted);font-size:14.5px}
.owner-name .role{color:var(--red);font-weight:600}
@media(max-width:820px){.owner{grid-template-columns:1fr;padding:32px;gap:28px}.owner-photo img{height:280px}.owner-body blockquote{font-size:19px}}

/* ═══ TESTIMONI ═══ */
.test-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px}
.test{background:#fff;border:1.5px solid var(--line);border-radius:20px;padding:30px;transition:.25s}
.test:hover{box-shadow:var(--shadow);transform:translateY(-5px)}
.stars{color:var(--gold);display:flex;gap:3px;margin-bottom:16px;font-size:14px}
.test p{font-size:15px;color:var(--ink);margin-bottom:22px;line-height:1.6}
.who{display:flex;align-items:center;gap:13px}
.avatar{width:44px;height:44px;border-radius:50%;background:var(--red-soft);color:var(--red);display:grid;place-items:center;font-family:'Plus Jakarta Sans';font-weight:800;font-size:15px}
.who b{font-family:'Plus Jakarta Sans';font-size:14.5px}
.who span{font-size:12.5px;color:var(--muted)}
@media(max-width:820px){.test-grid{grid-template-columns:1fr}}

/* ═══ CTA BAND ═══ */
.cta-band{background:linear-gradient(135deg,var(--red) 0%,var(--red-dark) 100%);border-radius:30px;padding:56px;display:grid;grid-template-columns:1.3fr auto;gap:40px;align-items:center;color:#fff;position:relative;overflow:hidden}
.cta-band::before{content:'';position:absolute;left:-100px;top:-100px;width:300px;height:300px;border:40px solid rgba(255,255,255,.07);border-radius:50%}
.cta-band h2{font-size:34px;font-weight:800;letter-spacing:-1px;line-height:1.1;margin-bottom:14px}
.cta-band p{opacity:.92;font-size:16px}
.cta-band .btn{background:#fff;color:var(--red)}
.cta-band .btn:hover{transform:translateY(-2px)}
@media(max-width:820px){.cta-band{grid-template-columns:1fr;padding:40px 32px;text-align:center}.cta-band .btn{justify-content:center}}

/* ═══ FOOTER ═══ */
footer{background:var(--ink);color:#fff;padding:64px 0 30px;margin-top:64px}
.foot-grid{display:grid;grid-template-columns:1.6fr 1fr 1fr 1fr;gap:40px;padding-bottom:44px;border-bottom:1px solid rgba(255,255,255,.1)}
.foot-brand .brand-name{color:#fff}
.foot-brand .logo-mark{background:#fff}
.foot-brand p{color:rgba(255,255,255,.6);font-size:14px;margin:18px 0 22px;max-width:300px}
.socials{display:flex;gap:11px}
.soc{width:42px;height:42px;border-radius:12px;background:rgba(255,255,255,.08);display:grid;place-items:center;transition:.2s;color:#fff;font-size:15px}
.soc:hover{background:var(--red)}
.foot-col h4{font-family:'Plus Jakarta Sans';font-size:16px;margin-bottom:18px}
.foot-col a{display:block;color:rgba(255,255,255,.6);font-size:14px;padding:7px 0;transition:.2s}
.foot-col a:hover{color:#fff;padding-left:5px}
.foot-bottom{display:flex;justify-content:space-between;align-items:center;padding-top:26px;color:rgba(255,255,255,.5);font-size:13.5px;flex-wrap:wrap;gap:12px}
.foot-bottom .flag{color:var(--red);font-weight:700}
@media(max-width:820px){.foot-grid{grid-template-columns:1fr 1fr}}
@media(max-width:560px){.foot-grid{grid-template-columns:1fr}}
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
      <a href="#hero">Beranda</a>
      <a href="#kategori">Kategori</a>
      <a href="#fitur">Fitur</a>
      <a href="#cara-kerja">Cara Daftar</a>
      <a href="#testimoni">Testimoni</a>
      <a href="#kontak">Kontak</a>
    </nav>
    <div class="nav-right">
      <div class="search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input placeholder="Cari fitur, panduan, atau bantuan">
      </div>
      @auth
        <a href="{{ url('/dashboard') }}" class="btn btn-red btn-login">Dashboard</a>
      @else
        @if (Route::has('login') || Route::has('register'))
          <a href="{{ Route::has('login') ? route('login') : route('register') }}" class="btn btn-red btn-login">Masuk / Daftar</a>
        @endif
      @endauth
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
  <a href="#hero">Beranda</a>
  <a href="#kategori">Kategori</a>
  <a href="#fitur">Fitur</a>
  <a href="#cara-kerja">Cara Daftar</a>
  <a href="#testimoni">Testimoni</a>
  <a href="#kontak">Kontak</a>
  @auth
    <a class="btn btn-red" href="{{ url('/dashboard') }}">Dashboard</a>
  @else
    @if (Route::has('login') || Route::has('register'))
      <a class="btn btn-red" href="{{ Route::has('login') ? route('login') : route('register') }}">Masuk / Daftar</a>
    @endif
  @endauth
</div>

<!-- HERO -->
<div class="wrap hero" id="hero">
  <div class="hero-inner">
    <div class="dots">
      <i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i>
    </div>
    <div class="hero-copy">
      <span class="eyebrow"><span class="pulse"></span>Solusi Manajemen UMKM Terpercaya</span>
      <h1>Dukung UMKM,<span class="accent">Bangun Negeri</span></h1>
      <p>Pabalu membantu pelaku UMKM mengelola kasir, stok, dan laporan penjualan dengan mudah — agar usahamu semakin kuat, rapi, dan siap berkembang.</p>
      <div class="hero-cta">
        @if (Route::has('register'))
        <a class="btn btn-red" href="{{ route('register') }}">Mulai Sekarang <span class="arrow"><i class="fa-solid fa-arrow-right"></i></span></a>
        @endif
        <a href="#fitur" class="play">
          <span class="play-circle"><i class="fa-solid fa-play"></i></span> Lihat Fitur
        </a>
      </div>
    </div>
    <div class="hero-visual">
      <div class="hv-tile" style="top:6px;left:0;transform:rotate(-6deg)"><i class="fa-solid fa-bag-shopping"></i></div>
      <div class="hv-tile" style="bottom:12px;right:0;transform:rotate(7deg)"><i class="fa-solid fa-seedling" style="color:#34d399"></i></div>
      <div class="hv-center"><i class="fa-solid fa-shop"></i></div>
      <div class="hv-tile" style="bottom:-10px;left:30px;transform:rotate(-4deg)"><i class="fa-solid fa-cash-register" style="color:#818cf8"></i></div>
      <div class="stats-card">
        <div class="stat"><span class="stat-ic"><i class="fa-solid fa-receipt"></i></span><div><div class="stat-num" data-count="15000" data-suffix="+">0</div><div class="stat-lbl">Transaksi Tercatat</div></div></div>
        <div class="stat"><span class="stat-ic"><i class="fa-solid fa-store"></i></span><div><div class="stat-num" data-count="3500" data-suffix="+">0</div><div class="stat-lbl">UMKM Terdaftar</div></div></div>
        <div class="stat"><span class="stat-ic"><i class="fa-solid fa-layer-group"></i></span><div><div class="stat-num" data-count="5" data-suffix="+">0</div><div class="stat-lbl">Jenis Usaha Didukung</div></div></div>
        <div class="stat"><span class="stat-ic"><i class="fa-solid fa-medal"></i></span><div><div class="stat-num" data-count="99" data-suffix="%">0</div><div class="stat-lbl">Kepuasan Pengguna</div></div></div>
      </div>
    </div>
  </div>
</div>

<!-- KATEGORI POPULER -->
<section id="kategori">
  <div class="wrap">
    <div class="sec-head">
      <div><h2>Kategori Populer</h2><p>Jelajahi berbagai kategori usaha UMKM di Indonesia</p></div>
    </div>
    <div class="cat-grid">
      <div class="cat"><span class="cat-ic"><i class="fa-solid fa-utensils"></i></span><span>Makanan &amp; Minuman</span></div>
      <div class="cat"><span class="cat-ic"><i class="fa-solid fa-shirt"></i></span><span>Fashion</span></div>
      <div class="cat"><span class="cat-ic"><i class="fa-solid fa-palette"></i></span><span>Kerajinan Tangan</span></div>
      <div class="cat"><span class="cat-ic"><i class="fa-solid fa-spa"></i></span><span>Kecantikan</span></div>
      <div class="cat"><span class="cat-ic"><i class="fa-solid fa-house"></i></span><span>Rumah Tangga</span></div>
      <div class="cat"><span class="cat-ic"><i class="fa-solid fa-gem"></i></span><span>Aksesoris</span></div>
      <div class="cat"><span class="cat-ic"><i class="fa-solid fa-seedling"></i></span><span>Tanaman Hias</span></div>
      <div class="cat"><span class="cat-ic"><i class="fa-solid fa-mobile-screen"></i></span><span>Elektronik</span></div>
    </div>
  </div>
</section>

<!-- PRODUK UNGGULAN -->
<section id="produk" style="padding-top:0">
  <div class="wrap">
    <div class="sec-head">
      <div><h2>Produk Unggulan</h2><p>Produk asli dari UMKM F&amp;B dan Retail yang sudah bergabung dengan Pabalu</p></div>
    </div>
    <div class="prod-layout">
      @if($featuredProducts->isEmpty())
      <div class="outlet-empty" style="border-radius:20px">
        <i class="fa-solid fa-box-open"></i>
        Produk unggulan akan tampil di sini setelah outlet F&amp;B/Retail menambahkan produk lengkap dengan gambar.
      </div>
      @else
      <div class="prod-grid">
        @foreach($featuredProducts as $product)
        <div class="card">
          <div class="card-img">
            <span class="tag">{{ $product->outlet->outletType->name }}</span>
            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" loading="lazy">
          </div>
          <div class="card-body">
            <h3>{{ $product->name }}</h3>
            <div class="seller">{{ $product->outlet->name }}</div>
            <span class="price">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
          </div>
        </div>
        @endforeach
      </div>
      @endif
      <div class="join">
        <small>Jadi bagian dari</small>
        <h3>UMKM Indonesia</h3>
        <p>Daftarkan usahamu dan kelola operasional harian lebih rapi bersama Pabalu.</p>
        @if (Route::has('register'))
        <a class="btn" href="{{ route('register') }}">Daftar Sekarang <span class="arrow"><i class="fa-solid fa-arrow-right"></i></span></a>
        @endif
        <div class="store-ill"><i class="fa-solid fa-shop"></i></div>
      </div>
    </div>
  </div>
</section>

<!-- FITUR UNGGULAN -->
<section id="fitur" style="padding-top:0">
  <div class="wrap">
    <div class="sec-head sec-center">
      <h2>Fitur Unggulan</h2>
      <p>Semua yang Anda butuhkan untuk mengelola usaha dengan tenang</p>
    </div>
    <div class="feat-grid">
      <div class="feat">
        <div class="feat-ic"><i class="fa-solid fa-cash-register"></i></div>
        <h3>Kasir &amp; POS Cepat</h3>
        <p>Mode quick order dan kitchen order untuk transaksi harian yang lebih cepat.</p>
      </div>
      <div class="feat">
        <div class="feat-ic"><i class="fa-solid fa-qrcode"></i></div>
        <h3>QRIS &amp; Non-Tunai</h3>
        <p>Terima pembayaran tunai, QRIS, transfer, hingga kartu dalam satu sistem.</p>
      </div>
      <div class="feat">
        <div class="feat-ic"><i class="fa-solid fa-boxes-stacked"></i></div>
        <h3>Manajemen Stok</h3>
        <p>Opening, closing, dan waste stok tercatat otomatis dan real-time.</p>
      </div>
      <div class="feat">
        <div class="feat-ic"><i class="fa-solid fa-chart-line"></i></div>
        <h3>Laporan Penjualan</h3>
        <p>Ringkasan harian dan bulanan yang jelas untuk memantau performa usaha.</p>
      </div>
    </div>
  </div>
</section>

<!-- CARA MENDAPATKAN APLIKASI -->
<section class="how" id="cara-kerja">
  <div class="wrap">
    <div class="sec-head sec-center">
      <h2>Cara Mendapatkan Aplikasi</h2>
      <p>Mulai gunakan Pabalu hanya dengan beberapa langkah sederhana</p>
    </div>
    <div class="steps">
      <div class="step"><div class="step-num">01</div><h3>Daftar Akun Gratis</h3><p>Buat akun owner gratis hanya dengan email dan nomor WhatsApp aktif.</p></div>
      <div class="step"><div class="step-num">02</div><h3>Atur Outlet &amp; Usaha</h3><p>Pilih jenis usaha, lengkapi profil, dan atur outlet pertama Anda.</p></div>
      <div class="step"><div class="step-num">03</div><h3>Mulai Kelola Bisnis</h3><p>Tambahkan produk, undang kasir, dan mulai transaksi hari ini juga.</p></div>
    </div>
  </div>
</section>

<!-- JENIS OUTLET -->
<section class="outlet-wrap" id="jenis-outlet">
  <div class="wrap">
    <div class="sec-head sec-center">
      <h2>Jenis Usaha yang Bisa Didaftarkan</h2>
      <p>Apa pun bidang usahamu, Pabalu siap membantu mengelolanya</p>
    </div>
    @if($outletTypes->isEmpty())
    <div class="outlet-grid">
      <div class="outlet-empty">
        <i class="fa-solid fa-shop"></i>
        Jenis outlet akan tampil di sini setelah ditambahkan oleh admin.
      </div>
    </div>
    @else
    <div class="outlet-grid">
      @foreach($outletTypes as $type)
      <div class="outlet">
        <span class="outlet-ic"><i class="fa-solid {{ $type->icon ?: 'fa-shop' }}"></i></span>
        <div><h3>{{ $type->name }}</h3>@if($type->description)<p>{{ $type->description }}</p>@endif</div>
        <span class="go"><i class="fa-solid fa-arrow-right"></i></span>
      </div>
      @endforeach
    </div>
    @endif
  </div>
</section>

<!-- OUTLET TERDAFTAR -->
<section id="outlet-terdaftar">
  <div class="wrap">
    <div class="sec-head sec-center">
      <h2>Outlet yang Sudah Terdaftar</h2>
      <p>Kunjungi langsung menu atau katalog outlet UMKM yang sudah bergabung dengan Pabalu</p>
    </div>
    @if($registeredOutlets->isEmpty())
    <div class="reg-outlet-grid">
      <div class="outlet-empty">
        <i class="fa-solid fa-store-slash"></i>
        Outlet terdaftar akan tampil di sini setelah outlet pertama dibuat.
      </div>
    </div>
    @else
    <div class="reg-outlet-grid">
      @foreach($registeredOutlets as $outlet)
      @php
        $isRetail = $outlet->rp() === 'retail';
        $menuUrl  = $isRetail ? route('public.katalog', $outlet->code) : route('public.menu', $outlet->code);
        $linkLabel = $isRetail ? 'Lihat Katalog' : 'Lihat Menu';
      @endphp
      <a href="{{ $menuUrl }}" target="_blank" rel="noopener" class="reg-outlet">
        <span class="outlet-ic"><i class="fa-solid {{ $outlet->outletType->icon ?? 'fa-shop' }}"></i></span>
        <div class="reg-outlet-body">
          <h3>{{ $outlet->name }}</h3>
          <p>{{ $outlet->outletType->name ?? '-' }}</p>
        </div>
        <span class="reg-outlet-link">{{ $linkLabel }} <i class="fa-solid fa-arrow-up-right-from-square"></i></span>
      </a>
      @endforeach
    </div>
    @endif
  </div>
</section>

<!-- LOGO MITRA -->
<section id="mitra">
  <div class="wrap">
    <div class="sec-head sec-center">
      <h2>Logo Mitra UMKM</h2>
      <p>UMKM dan brand yang telah bekerja sama dengan Pabalu</p>
    </div>
    @if($partners->isEmpty())
    <div class="mitra-row">
      <div class="mitra-empty">
        <i class="fa-solid fa-handshake"></i>
        Logo UMKM mitra/kerjasama akan tampil di sini setelah ditambahkan oleh admin.
      </div>
    </div>
    @else
    <div class="mitra-row">
      @foreach($partners as $partner)
      <a href="{{ $partner->website_url ?: '#' }}" @if($partner->website_url) target="_blank" rel="noopener" @endif class="mitra-card">
        <div class="mitra-logo"><img src="{{ asset('storage/' . $partner->logo) }}" alt="{{ $partner->name }}"></div>
        <div class="mitra-name">{{ $partner->name }}</div>
      </a>
      @endforeach
    </div>
    @endif
  </div>
</section>

<!-- KATA OWNER -->
<section id="owner">
  <div class="wrap">
    <div class="sec-head sec-center">
      <h2>Kata Owner</h2>
      <p>Pesan dari tim di balik Pabalu untuk para pejuang UMKM Indonesia</p>
    </div>
    <div class="owner">
      <div class="owner-photo">
        <img src="/img/owner.png" alt="Tim Pabalu">
        <span class="quote-mark">"</span>
      </div>
      <div class="owner-body">
        <div class="stars"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div>
        <blockquote>"Kami membangun Pabalu karena percaya satu hal: UMKM yang tertata rapi dari sisi kasir, stok, dan laporan akan tumbuh lebih cepat dan lebih kuat. Setiap fitur yang kami buat adalah jawaban langsung dari masalah yang dihadapi pelaku UMKM setiap hari."</blockquote>
        <div class="owner-name">
          <b>Tim Pabalu</b> &nbsp;<span class="role">Founder &amp; Pengembang</span><br>
          <span>Pabalu — Sistem Manajemen UMKM</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- TESTIMONI -->
<section id="testimoni">
  <div class="wrap">
    <div class="sec-head sec-center">
      <h2>Testimoni Pengguna</h2>
      <p>Cerita langsung dari para owner yang sudah mengelola usahanya bersama Pabalu</p>
    </div>
    <div class="test-grid">
      <div class="test">
        <div class="stars"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div>
        <p>"Sejak pakai Pabalu, catat transaksi warung makan saya jadi rapi. Laporan harian langsung kelihatan tanpa harus hitung manual lagi."</p>
        <div class="who"><span class="avatar">RS</span><div><b>Rina Susanti</b><br><span>Owner, Warung Makan Bahagia</span></div></div>
      </div>
      <div class="test">
        <div class="stars"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div>
        <p>"Fitur multi-outlet sangat membantu karena saya punya 3 cabang kafe. Semua kasir bisa input transaksi sendiri-sendiri tanpa bingung."</p>
        <div class="who"><span class="avatar">AP</span><div><b>Andi Pratama</b><br><span>Owner, Kopi Senja</span></div></div>
      </div>
      <div class="test">
        <div class="stars"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div>
        <p>"Stok barang di toko saya sering selisih sebelum pakai Pabalu. Sekarang opening dan closing stok tercatat otomatis, jadi lebih akurat."</p>
        <div class="who"><span class="avatar">SH</span><div><b>Siti Hartati</b><br><span>Owner, Toko Kelontong Berkah</span></div></div>
      </div>
    </div>
  </div>
</section>

<!-- CTA BAND -->
<section style="padding-top:0">
  <div class="wrap">
    <div class="cta-band">
      <div>
        <h2>Mulai Kelola Bisnis Anda Hari Ini</h2>
        <p>Daftar gratis dan rasakan kemudahan mengelola kasir, stok, dan laporan dalam satu platform.</p>
      </div>
      @if (Route::has('register'))
      <a class="btn" href="{{ route('register') }}">Daftar Sekarang <span class="arrow"><i class="fa-solid fa-arrow-right"></i></span></a>
      @endif
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer id="kontak">
  <div class="wrap">
    <div class="foot-grid">
      <div class="foot-brand">
        <a class="brand" href="{{ url('/') }}"><span class="logo-mark"><img src="/img/logo-pabalu.png" alt="Pabalu"></span><span class="brand-text"><span class="brand-name">Pabalu</span><span class="brand-sub">UMKM KUAT BERSAMA</span></span></a>
        <p>Sistem manajemen UMKM yang membantu pelaku usaha mengelola kasir, stok, multi-outlet, dan laporan penjualan dalam satu platform.</p>
        <div class="socials">
          <span class="soc"><i class="fa-brands fa-instagram"></i></span>
          <span class="soc"><i class="fa-brands fa-facebook-f"></i></span>
          <span class="soc"><i class="fa-brands fa-whatsapp"></i></span>
        </div>
      </div>
      <div class="foot-col">
        <h4>Produk</h4>
        <a href="#fitur">Fitur Unggulan</a>
        <a href="#cara-kerja">Cara Daftar</a>
        <a href="#jenis-outlet">Jenis Outlet</a>
      </div>
      <div class="foot-col">
        <h4>UMKM</h4>
        @if (Route::has('register'))<a href="{{ route('register') }}">Daftar Akun</a>@endif
        <a href="#mitra">Mitra Kami</a>
        <a href="#testimoni">Testimoni</a>
      </div>
      <div class="foot-col">
        <h4>Perusahaan</h4>
        <a href="#owner">Tentang Kami</a>
        <a href="#kontak">Kontak</a>
        @if (Route::has('login'))<a href="{{ route('login') }}">Masuk</a>@endif
      </div>
    </div>
    <div class="foot-bottom">
      <span>&copy; {{ date('Y') }} Pabalu. Seluruh hak cipta dilindungi.</span>
      <span>Dibuat dengan <span class="flag">&hearts;</span> oleh <a href="https://viteks.id" target="_blank" rel="noopener" style="color:#fff;font-weight:700">Viteks</a></span>
    </div>
  </div>
</footer>

<script>
// sticky header shadow
const hdr=document.getElementById('hdr');
addEventListener('scroll',()=>hdr.classList.toggle('scrolled',scrollY>10));

// mobile drawer menu (slide dari kanan)
const burger=document.getElementById('burger'),mm=document.getElementById('mobileMenu'),
      overlay=document.getElementById('drawerOverlay'),drawerClose=document.getElementById('drawerClose');
function openDrawer(){mm.classList.add('open');overlay.classList.add('show');document.body.style.overflow='hidden';}
function closeDrawer(){mm.classList.remove('open');overlay.classList.remove('show');document.body.style.overflow='';}
burger.addEventListener('click',openDrawer);
drawerClose.addEventListener('click',closeDrawer);
overlay.addEventListener('click',closeDrawer);
mm.querySelectorAll('a').forEach(a=>a.addEventListener('click',closeDrawer));

// count-up stats
function animateStat(el){
  const target=+el.dataset.count,suf=el.dataset.suffix||'';let cur=0;
  const step=target/60;
  const t=setInterval(()=>{cur+=step;if(cur>=target){cur=target;clearInterval(t);}
    el.textContent=Math.floor(cur).toLocaleString('id-ID')+suf;},20);
}
const io=new IntersectionObserver(es=>es.forEach(e=>{if(e.isIntersecting){animateStat(e.target);io.unobserve(e.target);}}));
document.querySelectorAll('.stat-num').forEach(s=>io.observe(s));

// scroll-spy: underline menu sesuai section yang sedang dilihat
const spySections=[
  {id:'hero',nav:'#hero'},
  {id:'kategori',nav:'#kategori'},
  {id:'produk',nav:'#kategori'},
  {id:'fitur',nav:'#fitur'},
  {id:'cara-kerja',nav:'#cara-kerja'},
  {id:'jenis-outlet',nav:'#cara-kerja'},
  {id:'outlet-terdaftar',nav:'#cara-kerja'},
  {id:'mitra',nav:'#cara-kerja'},
  {id:'owner',nav:'#cara-kerja'},
  {id:'testimoni',nav:'#testimoni'},
];
const navAnchors=document.querySelectorAll('.menu a');
function updateActiveNav(){
  const y=scrollY+110;
  let current=spySections[0].nav;
  for(const s of spySections){
    const el=document.getElementById(s.id);
    if(el && el.offsetTop<=y) current=s.nav;
  }
  if(scrollY+innerHeight>=document.body.scrollHeight-40) current='#kontak';
  navAnchors.forEach(a=>a.classList.toggle('active',a.getAttribute('href')===current));
}
addEventListener('scroll',updateActiveNav);
updateActiveNav();
</script>
</body>
</html>
