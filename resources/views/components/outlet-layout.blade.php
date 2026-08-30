@props(['outlet', 'pageTitle' => 'Dashboard Outlet'])

{{--
  Smart router: delegate ke layout tipe-spesifik.
  Setiap tipe punya layout independen — tambah tipe baru: buat komponen baru
  dan tambah @elseif di sini tanpa sentuh layout yang sudah ada.
--}}
@if($outlet->rp() === 'retail')
  <x-retail-layout :outlet="$outlet" :pageTitle="$pageTitle">
    @if(isset($headerAction))
    <x-slot:headerAction>{{ $headerAction }}</x-slot:headerAction>
    @endif
    {{ $slot }}
  </x-retail-layout>
@elseif($outlet->rp() === 'salon')
  <x-salon-layout :outlet="$outlet" :pageTitle="$pageTitle">
    @if(isset($headerAction))
    <x-slot:headerAction>{{ $headerAction }}</x-slot:headerAction>
    @endif
    {{ $slot }}
  </x-salon-layout>
@elseif($outlet->rp() === 'laundry')
  <x-laundry-layout :outlet="$outlet" :pageTitle="$pageTitle">
    @if(isset($headerAction))
    <x-slot:headerAction>{{ $headerAction }}</x-slot:headerAction>
    @endif
    {{ $slot }}
  </x-laundry-layout>
@elseif($outlet->rp() === 'sewa')
  <x-sewa-layout :outlet="$outlet" :pageTitle="$pageTitle">
    @if(isset($headerAction))
    <x-slot:headerAction>{{ $headerAction }}</x-slot:headerAction>
    @endif
    {{ $slot }}
  </x-sewa-layout>
@elseif($outlet->rp() === 'fnb')

{{-- ══════════════════════════════════════════════════════════════════════════════ --}}
{{-- F&B LAYOUT — digunakan oleh: warung makan, kafe              --}}
{{-- ══════════════════════════════════════════════════════════════════════════════ --}}

{{-- ══════════════════════════════════════════════════════════════════════════════ --}}
{{-- F&B LAYOUT — warung makan, kafe       --}}
{{-- ══════════════════════════════════════════════════════════════════════════════ --}}
@php
  $user       = Auth::user();
  $type       = $outlet->outletType;
  $hasOpening = $outlet->requiresOpeningStock();

  $p       = fn(string $s) => $user->hasPermission($s);
  $role    = $user->role ?? null;
  $isKasir = $role === 'kasir';
  $isAdmin = $role === 'admin';
  $isOwner = $role === 'owner';

  $roleLabels = ['admin'=>'Administrator','owner'=>'Pemilik Toko','admin_outlet'=>'Admin Outlet','kasir'=>'Kasir'];
  $roleLabel  = $roleLabels[$role] ?? 'Pengguna';
  $initial    = strtoupper(substr($user->name, 0, 1));

  // Current route segment for active detection
  $seg = request()->segment(3); // e.g. /outlets/{id}/pos → 'pos'

  // Kasir sidebar: all assigned outlets for switching
  $sbOutlets = $isKasir
    ? $user->assignedOutlets()->with('outletType')->where('is_active', true)->get()
    : collect();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $pageTitle }} — {{ $outlet->name }} · {{ config('app.name','Pabalu') }}</title>
<link rel="icon" href="/img/Logo.ico">
<link href="https://fonts.googleapis.com/css2?family=Clash+Display:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@vite(['resources/css/app.css','resources/js/app.js'])

<script>
(function(){
  var p={pabalu:{ac:'#e8192c',ac2:'#c41020',rgb:'232,25,44'},amber:{ac:'#f59e0b',ac2:'#ef4444',rgb:'245,158,11'},emerald:{ac:'#10b981',ac2:'#06b6d4',rgb:'16,185,129'},blue:{ac:'#4f6ef7',ac2:'#7c3aed',rgb:'79,110,247'},violet:{ac:'#8b5cf6',ac2:'#ec4899',rgb:'139,92,246'},rose:{ac:'#f43f5e',ac2:'#f97316',rgb:'244,63,94'},cyan:{ac:'#06b6d4',ac2:'#3b82f6',rgb:'6,182,212'},lime:{ac:'#84cc16',ac2:'#10b981',rgb:'132,204,22'},pink:{ac:'#ec4899',ac2:'#8b5cf6',rgb:'236,72,153'},orange:{ac:'#f97316',ac2:'#f59e0b',rgb:'249,115,22'},sky:{ac:'#38bdf8',ac2:'#6366f1',rgb:'56,189,248'}};
  var c=p[localStorage.getItem('pabalu_accent')]||p.pabalu;
  var r=document.documentElement;
  r.style.setProperty('--ac',c.ac);r.style.setProperty('--ac2',c.ac2);r.style.setProperty('--ac-rgb',c.rgb);
  r.style.setProperty('--ac-lt','rgba('+c.rgb+',.14)');r.style.setProperty('--ac-lt2','rgba('+c.rgb+',.08)');
})();
</script>

<style>
/* ── Copy identik dari app.blade.php ── */
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Plus Jakarta Sans',sans-serif;transition:background .25s,color .25s}
.font-display,h1,h2,h3,h4{font-family:'Clash Display',sans-serif}
:root{--ac:#e8192c;--ac2:#c41020;--ac-rgb:232,25,44;--ac-lt:rgba(232,25,44,.14);--ac-lt2:rgba(232,25,44,.08)}
body{--bg:#0f1117;--surface:#161b27;--surface2:#1c2336;--border:#252d42;--text:#e2e8f0;--muted:#64748b;--sub:#94a3b8;--card-hover:rgba(255,255,255,.025);--scrollbar:#252d42;background:var(--bg);color:var(--text)}
body.light{--bg:#f1f5f9;--surface:#ffffff;--surface2:#f8fafc;--border:#e2e8f0;--text:#1e293b;--muted:#94a3b8;--sub:#64748b;--card-hover:rgba(0,0,0,.025);--scrollbar:#cbd5e1}
.a-text{color:var(--ac)!important}.a-bg{background:var(--ac)!important}.a-bg-lt{background:var(--ac-lt)!important}
.a-grad{background:linear-gradient(135deg,var(--ac),var(--ac2))!important}

/* ── Outlet context banner ── */
#outlet-ctx{
  display:flex;align-items:center;gap:12px;
  padding:14px 16px;margin-bottom:2px;
  background:var(--ac-lt2);border-bottom:1px solid rgba(var(--ac-rgb),.15);
  flex-shrink:0;
}
#outlet-ctx .oc-icon{
  width:36px;height:36px;border-radius:10px;
  background:var(--ac-lt);color:var(--ac);
  display:grid;place-items:center;font-size:14px;flex-shrink:0
}

/* ── Sidebar ── */
#sb{position:fixed;top:0;left:0;height:100%;width:258px;display:flex;flex-direction:column;z-index:50;background:var(--surface);border-right:1px solid var(--border);transition:transform .3s,background .25s,border-color .25s}
@media(max-width:1023px){#sb{transform:translateX(-100%)}#sb.open{transform:translateX(0)}}
#sb-nav{overflow-y:auto;flex:1;scrollbar-width:thin;scrollbar-color:var(--scrollbar) transparent}
#sb-nav::-webkit-scrollbar{width:4px}
#sb-nav::-webkit-scrollbar-thumb{background:var(--scrollbar);border-radius:99px}

/* ── Nav items ── */
.nav-item{display:flex;align-items:center;gap:10px;padding:9px 12px;border-radius:10px;font-size:13.5px;font-weight:500;text-decoration:none;margin-bottom:2px;transition:background .15s,color .15s}
.nav-active{background:var(--ac-lt)!important;color:var(--ac)!important;border-left:2.5px solid var(--ac)!important;padding-left:calc(12px - 2.5px)!important}
.nav-inactive{color:var(--sub)}
.nav-inactive:hover{background:var(--surface2);color:var(--text)}
.nav-section{font-size:10px;font-weight:600;letter-spacing:1.3px;text-transform:uppercase;color:var(--muted);padding:4px 8px;margin-top:14px;margin-bottom:4px}

/* ── Main ── */
#main{margin-left:258px;transition:margin .3s;min-height:100vh}
@media(max-width:1023px){#main{margin-left:0}}
#header{position:sticky;top:0;z-index:40;display:flex;align-items:center;justify-content:space-between;padding:14px 24px;background:var(--surface);border-bottom:1px solid var(--border);transition:background .25s,border-color .25s}
#content{padding:24px;max-width:1400px;display:flex;flex-direction:column;gap:20px}

/* ── Cards ── */
.card{background:var(--surface);border:1px solid var(--border);border-radius:16px}
.card-header{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.card-title{font-family:'Clash Display',sans-serif;font-size:15px;font-weight:600;color:var(--text)}
.card-body{padding:20px}

/* ── Stats ── */
.stat-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
@media(max-width:1200px){.stat-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:600px){.stat-grid{grid-template-columns:1fr}}
.stat-card{background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:20px;display:flex;align-items:center;gap:16px;transition:border-color .2s,box-shadow .2s}
.stat-card:hover{border-color:var(--ac);box-shadow:0 0 0 1px var(--ac-lt),0 8px 24px rgba(0,0,0,.15)}
.stat-icon{width:44px;height:44px;border-radius:12px;display:grid;place-items:center;font-size:17px;flex-shrink:0}
.stat-num{font-family:'Clash Display',sans-serif;font-size:26px;font-weight:700;color:var(--text);line-height:1}
.stat-label{font-size:11.5px;color:var(--muted);margin-top:3px}
.stat-trend{font-size:11px;font-weight:600;margin-top:4px;display:flex;align-items:center;gap:3px}
.trend-up{color:#34d399}.trend-down{color:#f87171}

/* ── Table ── */
.tbl{width:100%;border-collapse:collapse;font-size:13px}
.tbl thead th{padding:10px 14px;text-align:left;font-size:11px;font-weight:600;letter-spacing:.8px;text-transform:uppercase;color:var(--muted);background:var(--surface2);border-bottom:1px solid var(--border)}
.tbl thead th:first-child{border-radius:10px 0 0 10px}.tbl thead th:last-child{border-radius:0 10px 10px 0}
.tbl tbody tr{border-bottom:1px solid var(--border);transition:background .12s}
.tbl tbody tr:last-child{border-bottom:none}
.tbl tbody tr:hover{background:var(--surface2)}
.tbl tbody td{padding:12px 14px;color:var(--sub)}.tbl tbody td.td-main{color:var(--text);font-weight:500}

/* ── Badges ── */
.badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:99px;font-size:11px;font-weight:600}
.badge-green{background:rgba(16,185,129,.15);color:#34d399}
.badge-amber{background:rgba(245,158,11,.15);color:#fbbf24}
.badge-red{background:rgba(239,68,68,.15);color:#f87171}
.badge-blue{background:rgba(99,102,241,.15);color:#818cf8}
.badge-gray{background:rgba(148,163,184,.12);color:#94a3b8}

/* ── Layout helpers ── */
.two-col{display:grid;grid-template-columns:1fr 360px;gap:20px;align-items:start}
@media(max-width:1100px){.two-col{grid-template-columns:1fr}}
.prog-bar{height:6px;border-radius:99px;background:var(--surface2);overflow:hidden;margin-top:6px}
.prog-fill{height:100%;border-radius:99px;background:linear-gradient(135deg,var(--ac),var(--ac2))}

/* ── Action cards ── */
.action-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:14px}
@media(min-width:640px){.action-grid{grid-template-columns:repeat(3,1fr)}}
.action-card{background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:20px 16px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:9px;text-decoration:none;cursor:pointer;transition:border-color .2s,background .2s,box-shadow .2s;text-align:center;position:relative}
.action-card:hover{background:var(--surface2);box-shadow:0 4px 20px rgba(0,0,0,.15)}
.action-card-icon{width:48px;height:48px;border-radius:14px;display:grid;place-items:center;font-size:19px}
.action-card-label{font-size:13.5px;font-weight:600;color:var(--text)}
.action-card-sub{font-size:11.5px;color:var(--muted);line-height:1.4}

/* ── Theme panel ── */
#tp{position:absolute;right:0;top:calc(100% + 8px);background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:10px;display:flex;flex-direction:column;gap:2px;min-width:210px;box-shadow:0 8px 32px rgba(0,0,0,.35);transition:opacity .2s,transform .2s}
#tp.hidden{opacity:0;pointer-events:none;transform:translateY(6px)}
.tp-btn{display:flex;align-items:center;gap:8px;padding:8px 12px;border-radius:10px;font-size:13px;font-weight:500;cursor:pointer;transition:background .12s;background:none;border:none;color:var(--text);text-align:left;width:100%}
.tp-btn:hover{background:var(--surface2)}.tp-btn.active{background:var(--ac-lt);color:var(--ac)}
.tp-divider{height:1px;background:var(--border);margin:6px 4px}
.tp-section-label{font-size:10px;font-weight:600;letter-spacing:1px;text-transform:uppercase;color:var(--muted);padding:4px 12px 6px}
.color-swatches{display:flex;flex-wrap:wrap;gap:7px;padding:4px 12px 8px}
.swatch{width:26px;height:26px;border-radius:8px;cursor:pointer;border:2px solid transparent;transition:transform .15s,border-color .15s;position:relative}
.swatch:hover{transform:scale(1.15)}.swatch.active{border-color:#fff;box-shadow:0 0 0 1px rgba(255,255,255,.25)}
body.light .swatch.active{border-color:#0f1117;box-shadow:0 0 0 1px rgba(0,0,0,.2)}
.swatch .check{position:absolute;inset:0;display:grid;place-items:center;font-size:11px;color:#fff;opacity:0;transition:opacity .15s}
body.light .swatch .check{color:#000}.swatch.active .check{opacity:1}

/* ── Modal ── */
.modal-backdrop{position:fixed;inset:0;background:rgba(0,0,0,.6);backdrop-filter:blur(4px);z-index:60;display:flex;align-items:center;justify-content:center;padding:16px;opacity:0;pointer-events:none;transition:opacity .2s}
.modal-backdrop.open{opacity:1;pointer-events:all}
.modal-box{background:var(--surface);border:1px solid var(--border);border-radius:20px;width:100%;max-width:440px;box-shadow:0 24px 64px rgba(0,0,0,.5);transform:translateY(16px);transition:transform .25s}
.modal-backdrop.open .modal-box{transform:translateY(0)}

/* ── Form ── */
.f-label{display:block;font-size:12px;font-weight:600;color:var(--sub);margin-bottom:5px;letter-spacing:.3px}
.f-input{width:100%;background:var(--surface2);border:1px solid var(--border);color:var(--text);border-radius:12px;padding:9px 13px;font-size:13.5px;font-family:inherit;outline:none;transition:border-color .15s,box-shadow .15s}
.f-input:focus{border-color:var(--ac);box-shadow:0 0 0 3px var(--ac-lt)}
select.f-input option{background:var(--surface2);color:var(--text)}

/* ── Misc ── */
#ov{position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:45;display:none;backdrop-filter:blur(3px)}
#toast-container{position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:10px;pointer-events:none}
.toast{display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:14px;font-size:13px;font-weight:500;pointer-events:auto;box-shadow:0 4px 24px rgba(0,0,0,.35);border:1px solid var(--border);background:var(--surface);color:var(--text);min-width:240px;max-width:340px}
.toast.success .ti{color:#10b981}.toast.error .ti{color:#f87171}.toast.info .ti{color:#60a5fa}
@keyframes fadeUp{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
.animate-fadeUp{animation:fadeUp .35s ease both}
.d1{animation-delay:.05s}.d2{animation-delay:.10s}.d3{animation-delay:.15s}.d4{animation-delay:.20s}.d5{animation-delay:.25s}
.toast{animation:fadeUp .25s ease both}
::-webkit-scrollbar{width:6px;height:6px}::-webkit-scrollbar-track{background:transparent}::-webkit-scrollbar-thumb{background:var(--scrollbar);border-radius:99px}
</style>
</head>

<body>
<script>if(localStorage.getItem('pabalu_theme')==='light')document.body.classList.add('light');</script>
<div id="toast-container"></div>
<div id="ov" onclick="closeSB()"></div>

{{-- ══════════════ SIDEBAR OUTLET ══════════════ --}}
<aside id="sb">

  {{-- Outlet branding --}}
  <div id="outlet-ctx">
    <div class="oc-icon">
      <i class="fa-solid {{ $type?->icon ?? 'fa-shop' }}"></i>
    </div>
    <div style="flex:1;min-width:0">
      <div style="font-size:13.5px;font-weight:700;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
        {{ $outlet->name }}
      </div>
      <div style="font-size:11px;color:var(--muted)">
        {{ $type?->name ?? 'Outlet' }}
        @if(!$outlet->is_active)
          · <span style="color:#f87171">Nonaktif</span>
        @endif
      </div>
    </div>
    @if($outlet->is_active)
    <div style="width:8px;height:8px;border-radius:50%;background:#34d399;flex-shrink:0"></div>
    @endif
  </div>

  {{-- Back link: hanya untuk non-kasir --}}
  @if(!$isKasir)
  <div style="padding:10px 10px 0">
    <a href="{{ route('outlets.index') }}"
       style="display:flex;align-items:center;gap:8px;padding:8px 12px;border-radius:10px;font-size:12.5px;font-weight:600;color:var(--muted);text-decoration:none;transition:background .15s,color .15s"
       onmouseover="this.style.background='var(--surface2)';this.style.color='var(--text)'"
       onmouseout="this.style.background='transparent';this.style.color='var(--muted)'">
      <i class="fa-solid fa-arrow-left" style="font-size:10px"></i>
      Semua Outlet
    </a>
  </div>
  @endif

  {{-- Nav --}}
  <div id="sb-nav" style="padding:4px 10px 10px">
  @php
    $pendingCount = \App\Models\Order::where('outlet_id', $outlet->id)
        ->whereNull('user_id')->where('status', 'pending')->count();
    $unpaidInvoiceCount = \App\Models\ProOwnerInvoice::where('outlet_id', $outlet->id)
        ->where('status', 'belum_lunas')->count();
  @endphp

  @if($isKasir)
    {{-- ══════════════════════════════════════════════════════════ --}}
    {{-- KASIR NAVIGATION — identik dengan app.blade.php kasir nav --}}
    {{-- ══════════════════════════════════════════════════════════ --}}
    <p class="nav-section" style="margin-top:8px">Utama</p>
    <a href="{{ route('dashboard') }}"
       class="nav-item nav-inactive">
      <i class="fa-solid fa-chart-pie" style="width:15px;text-align:center;font-size:13px"></i>Dashboard
    </a>

    <p class="nav-section">{{ \Illuminate\Support\Str::limit($outlet->name, 22) }}</p>
    @if($p('pos.access'))
    <a href="{{ $outlet->route('pos.index') }}"
       class="nav-item {{ $outlet->routeIs('pos.*') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-cash-register" style="width:15px;text-align:center;font-size:13px"></i>POS / Kasir
    </a>
    @endif
    @if($p('order.view') && $outlet->isKitchenMode())
    <a href="{{ $outlet->route('orders.index') }}"
       class="nav-item {{ $outlet->routeIs('orders.*') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-list-check" style="width:15px;text-align:center;font-size:13px"></i>Antrian Order
    </a>
    @endif
    <a href="{{ $outlet->route('self-orders.index') }}"
       class="nav-item {{ $outlet->routeIs('self-orders.*') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-clipboard-list" style="width:15px;text-align:center;font-size:13px;color:#f59e0b"></i>Pesanan Masuk
      @if($pendingCount > 0)
      <span style="margin-left:auto;background:#ef4444;color:#fff;border-radius:99px;font-size:10px;font-weight:700;padding:1px 6px;min-width:18px;text-align:center;display:inline-block">{{ $pendingCount }}</span>
      @endif
    </a>
    @if($p('transaction.view'))
    <a href="{{ $outlet->route('transactions.index') }}"
       class="nav-item {{ $outlet->routeIs('transactions.*') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-clock-rotate-left" style="width:15px;text-align:center;font-size:13px"></i>Transaksi
    </a>
    @endif
    @if($p('product.view'))
    <a href="{{ $outlet->route('products.index') }}"
       class="nav-item {{ $outlet->routeIs('products.*') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-cubes" style="width:15px;text-align:center;font-size:13px"></i>Produk
    </a>
    @endif
    @if($p('stock.view'))
    <a href="{{ $outlet->route('stock.index') }}"
       class="nav-item {{ $outlet->routeIs('stock.*') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-warehouse" style="width:15px;text-align:center;font-size:13px"></i>Stok &amp; Pergerakan
    </a>
    @endif
    @if($p('expense.view'))
    <a href="{{ $outlet->route('expenses.index') }}"
       class="nav-item {{ $outlet->routeIs('expenses.*') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-money-bill-wave" style="width:15px;text-align:center;font-size:13px"></i>Pengeluaran
    </a>
    @endif
    @if($outlet->enable_opening_shift)
    <a href="{{ $outlet->route('shifts.index') }}"
       class="nav-item {{ $outlet->routeIs('shifts.*') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-clock" style="width:15px;text-align:center;font-size:13px"></i>Opening Shift
    </a>
    @endif

    {{-- Outlet lain (jika kasir punya lebih dari satu) --}}
    @if($sbOutlets->count() > 1)
    <p class="nav-section">Outlet Lain</p>
    @foreach($sbOutlets->where('id', '!=', $outlet->id) as $ao)
    <a href="{{ route('dashboard') }}?outlet={{ $ao->id }}"
       class="nav-item nav-inactive">
      <i class="fa-solid fa-shop" style="width:15px;text-align:center;font-size:13px"></i>{{ \Illuminate\Support\Str::limit($ao->name, 20) }}
    </a>
    @endforeach
    @endif

    <p class="nav-section">Personal</p>
    <a href="{{ route('my-salary') }}"
       class="nav-item {{ request()->routeIs('my-salary') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-hand-holding-dollar" style="width:15px;text-align:center;font-size:13px"></i>Riwayat Gaji
    </a>

    <p class="nav-section">Pengaturan</p>
    <a href="{{ route('profile.edit') }}"
       class="nav-item {{ request()->routeIs('profile.*') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-user-gear" style="width:15px;text-align:center;font-size:13px"></i>Akun Saya
    </a>

  @else
    {{-- ══════════════════════════════════════════ --}}
    {{-- ADMIN / OWNER / ADMIN_OUTLET NAVIGATION   --}}
    {{-- ══════════════════════════════════════════ --}}

    {{-- UTAMA --}}
    <p class="nav-section" style="margin-top:8px">Utama</p>
    <a href="{{ $outlet->route('show') }}"
       class="nav-item {{ $outlet->routeIs('show') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-chart-pie" style="width:15px;text-align:center;font-size:13px"></i>Dashboard Outlet
    </a>

    {{-- OPERASIONAL --}}
    @if($p('pos.access') || $p('order.view') || $p('stock.in') || $p('expense.view') || $p('stock.waste'))
    <p class="nav-section">Operasional Harian</p>
    @if($hasOpening && $p('stock.opening'))
    <a href="{{ $outlet->route('opening.index') }}"
       class="nav-item {{ $outlet->routeIs('opening.*') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-sun" style="width:15px;text-align:center;font-size:13px;color:#818cf8"></i>
      Opening Stok
    </a>
    @endif
    @if($p('pos.access'))
    <a href="{{ $outlet->route('pos.index') }}"
       class="nav-item {{ $outlet->routeIs('pos.*') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-cash-register" style="width:15px;text-align:center;font-size:13px"></i>POS / Kasir
    </a>
    @endif
    @if($p('order.view') && $outlet->isKitchenMode())
    <a href="{{ $outlet->route('orders.index') }}"
       class="nav-item {{ $outlet->routeIs('orders.*') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-bell-concierge" style="width:15px;text-align:center;font-size:13px"></i>Antrian Order
    </a>
    @endif
    <a href="{{ $outlet->route('self-orders.index') }}"
       class="nav-item {{ $outlet->routeIs('self-orders.*') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-clipboard-list" style="width:15px;text-align:center;font-size:13px;color:#f59e0b"></i>Pesanan Masuk
      @if($pendingCount > 0)
      <span style="margin-left:auto;background:#ef4444;color:#fff;border-radius:99px;font-size:10px;font-weight:700;padding:1px 6px;min-width:18px;text-align:center;display:inline-block">{{ $pendingCount }}</span>
      @endif
    </a>
    @if($p('stock.in'))
    <a href="{{ $outlet->route('stock-in.index') }}"
       class="nav-item {{ $outlet->routeIs('stock-in.*') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-box-open" style="width:15px;text-align:center;font-size:13px"></i>Tambah Stok
    </a>
    @endif
    @if($p('expense.view'))
    <a href="{{ $outlet->route('expenses.index') }}"
       class="nav-item {{ $outlet->routeIs('expenses.*') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-money-bill-wave" style="width:15px;text-align:center;font-size:13px"></i>Pengeluaran
    </a>
    @endif
    @if($p('stock.waste'))
    <a href="{{ $outlet->route('waste.index') }}"
       class="nav-item {{ $outlet->routeIs('waste.*') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-trash-can" style="width:15px;text-align:center;font-size:13px"></i>Waste / Rusak
    </a>
    @endif
    @if($outlet->enable_opening_shift)
    <a href="{{ $outlet->route('shifts.index') }}"
       class="nav-item {{ $outlet->routeIs('shifts.*') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-clock" style="width:15px;text-align:center;font-size:13px;color:#34d399"></i>Opening Shift
    </a>
    @endif
    @if($hasOpening && $p('stock.closing'))
    <a href="{{ $outlet->route('closing.index') }}"
       class="nav-item {{ $outlet->routeIs('closing.*') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-moon" style="width:15px;text-align:center;font-size:13px;color:#818cf8"></i>Closing Harian
    </a>
    @endif
    @endif

    {{-- TRANSAKSI --}}
    @if($p('transaction.view'))
    <p class="nav-section">Transaksi</p>
    <a href="{{ $outlet->route('transactions.index') }}"
       class="nav-item {{ $outlet->routeIs('transactions.*') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-receipt" style="width:15px;text-align:center;font-size:13px"></i>Riwayat Transaksi
    </a>
    @endif

    {{-- PRODUK & STOK --}}
    @if($p('product.view') || $p('category.view') || $p('stock.view'))
    <p class="nav-section">Produk &amp; Stok</p>
    @if($p('product.view'))
    <a href="{{ $outlet->route('products.index') }}"
       class="nav-item {{ $outlet->routeIs('products.*') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-cubes" style="width:15px;text-align:center;font-size:13px"></i>Produk
    </a>
    @endif
    @if($p('category.view'))
    <a href="{{ $outlet->route('categories.index') }}"
       class="nav-item {{ $outlet->routeIs('categories.*') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-tags" style="width:15px;text-align:center;font-size:13px"></i>Kategori
    </a>
    @endif
    @if($p('stock.view'))
    <a href="{{ $outlet->route('stock.index') }}"
       class="nav-item {{ $outlet->routeIs('stock.*') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-warehouse" style="width:15px;text-align:center;font-size:13px"></i>Stok &amp; Pergerakan
    </a>
    @endif
    @endif

    {{-- LAPORAN --}}
    @if($p('report.sales') || $p('report.profit_loss'))
    <p class="nav-section">Laporan</p>
    @if($p('report.sales'))
    <a href="{{ $outlet->route('reports.sales') }}"
       class="nav-item {{ $outlet->routeIs('reports.sales*') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-chart-line" style="width:15px;text-align:center;font-size:13px"></i>Laporan Penjualan
    </a>
    @endif
    @if($p('report.profit_loss'))
    <a href="{{ $outlet->route('reports.profit-loss') }}"
       class="nav-item {{ $outlet->routeIs('reports.profit-loss*') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-scale-balanced" style="width:15px;text-align:center;font-size:13px"></i>
      Laba &amp; Rugi
    </a>
    @endif
    @endif

    {{-- PERSONAL --}}
    <p class="nav-section">Personal</p>
    <a href="{{ route('my-salary') }}"
       class="nav-item {{ request()->routeIs('my-salary') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-hand-holding-dollar" style="width:15px;text-align:center;font-size:13px"></i>Riwayat Gaji
    </a>

    {{-- KONFIGURASI (owner/admin) --}}
    @if($isAdmin || $isOwner || $outlet->owner_id === $user->id)
    <p class="nav-section">Konfigurasi</p>
    <a href="{{ $outlet->route('settings.edit') }}"
       class="nav-item {{ $outlet->routeIs('settings.*') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-gear" style="width:15px;text-align:center;font-size:13px"></i>
      Pengaturan Outlet
    </a>
    <a href="{{ route('outlet.tagihan.index', $outlet) }}"
       class="nav-item {{ request()->routeIs('outlet.tagihan.*') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-file-invoice-dollar" style="width:15px;text-align:center;font-size:13px"></i>
      Tagihan
      @if($unpaidInvoiceCount > 0)
      <span style="margin-left:auto;background:#ef4444;color:#fff;border-radius:99px;font-size:10px;font-weight:700;padding:1px 6px;min-width:18px;text-align:center;display:inline-block">{{ $unpaidInvoiceCount }}</span>
      @endif
    </a>
    @endif

  @endif {{-- end kasir / non-kasir nav --}}

  </div>

  {{-- User card --}}
  <div style="padding:12px 14px;border-top:1px solid var(--border);flex-shrink:0">
    <div style="display:flex;align-items:center;gap:10px;padding:10px;border-radius:12px;background:var(--surface2)">
      <div class="a-grad" style="width:34px;height:34px;border-radius:9px;display:grid;place-items:center;flex-shrink:0;font-weight:700;color:#fff;font-size:13px">
        {{ $initial }}
      </div>
      <div style="flex:1;min-width:0">
        <div style="font-size:13px;font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $user->name }}</div>
        <div style="font-size:11px;color:var(--muted)">{{ $roleLabel }}</div>
      </div>
    </div>
  </div>
</aside>

{{-- ══════════════ MAIN ══════════════ --}}
<div id="main">

  {{-- Header --}}
  <header id="header">
    <div style="display:flex;align-items:center;gap:14px">
      <button onclick="toggleSB()" id="hamburger"
        style="display:none;background:none;border:none;cursor:pointer;color:var(--sub);font-size:18px">
        <i class="fa-solid fa-bars"></i>
      </button>
      <div>
        <div style="font-size:11px;color:var(--muted);margin-bottom:1px;display:flex;align-items:center;gap:6px">
          <i class="fa-solid {{ $type?->icon ?? 'fa-shop' }}" style="color:var(--ac);font-size:10px"></i>
          {{ $outlet->name }}
        </div>
        <h1 id="page-title" class="font-display" style="font-size:18px;font-weight:700;color:var(--text)">
          {{ $pageTitle }}
        </h1>
      </div>
    </div>

    <div style="display:flex;align-items:center;gap:10px">

      {{-- Notification --}}
      <button onclick="showToast('info','3 notifikasi baru')"
        style="position:relative;width:38px;height:38px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);cursor:pointer;color:var(--sub);font-size:14px;transition:color .15s"
        onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--sub)'">
        <i class="fa-solid fa-bell"></i>
        <span style="position:absolute;top:6px;right:7px;width:7px;height:7px;border-radius:50%;background:var(--ac);border:2px solid var(--surface2)"></span>
      </button>

      {{-- Theme toggle --}}
      <div style="position:relative">
        <button id="theme-btn" onclick="toggleTP()"
          style="width:38px;height:38px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);cursor:pointer;color:var(--sub);font-size:14px;transition:color .15s"
          onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--sub)'">
          <i class="fa-solid fa-circle-half-stroke"></i>
        </button>
        <div id="tp" class="hidden">
          <button class="tp-btn" id="tp-dark" onclick="setTheme('dark')"><i class="fa-solid fa-moon" style="width:14px;text-align:center"></i>Mode Gelap</button>
          <button class="tp-btn" id="tp-light" onclick="setTheme('light')"><i class="fa-solid fa-sun" style="width:14px;text-align:center"></i>Mode Terang</button>
          <div class="tp-divider"></div>
          <div class="tp-section-label">Warna Aksen</div>
          <div class="color-swatches" id="color-swatches"></div>
        </div>
      </div>

      {{-- Header action slot --}}
      @isset($headerAction){{ $headerAction }}@endisset

      {{-- Profile dropdown --}}
      <div style="position:relative" id="prof-wrap">
        <button onclick="toggleProfMenu(event)" title="{{ $user->name }}"
          style="display:flex;align-items:center;gap:7px;padding:5px 10px 5px 5px;border-radius:11px;border:1px solid var(--border);background:var(--surface2);cursor:pointer;transition:border-color .2s;outline:none"
          onmouseover="this.style.borderColor='var(--ac)'" onmouseout="this.style.borderColor='var(--border)'">
          <div class="a-grad" style="width:28px;height:28px;border-radius:8px;display:grid;place-items:center;color:#fff;font-size:11px;font-weight:700;flex-shrink:0">{{ $initial }}</div>
          <i class="fa-solid fa-chevron-down" id="prof-chevron" style="font-size:10px;color:var(--muted);transition:transform .2s"></i>
        </button>
        <div id="prof-menu" style="display:none;position:absolute;right:0;top:calc(100% + 8px);width:220px;background:var(--surface);border:1px solid var(--border);border-radius:14px;box-shadow:0 8px 32px rgba(0,0,0,.25);z-index:300;overflow:hidden">
          <div style="padding:14px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px">
            <div class="a-grad" style="width:36px;height:36px;border-radius:10px;display:grid;place-items:center;color:#fff;font-size:13px;font-weight:700;flex-shrink:0">{{ $initial }}</div>
            <div style="min-width:0">
              <div style="font-size:13px;font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $user->name }}</div>
              <div style="font-size:11px;color:var(--muted)">{{ $roleLabel }}</div>
            </div>
          </div>
          <div style="padding:6px">
            <a href="{{ route('profile.edit') }}" onclick="closeProfMenu()" style="display:flex;align-items:center;gap:10px;padding:9px 12px;border-radius:9px;text-decoration:none;color:var(--sub);font-size:13px;transition:background .15s" onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'"><i class="fa-solid fa-user" style="width:15px;text-align:center;color:var(--muted);font-size:13px"></i>Profile</a>
            <a href="{{ route('profile.edit') }}#update-password" onclick="closeProfMenu()" style="display:flex;align-items:center;gap:10px;padding:9px 12px;border-radius:9px;text-decoration:none;color:var(--sub);font-size:13px;transition:background .15s" onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'"><i class="fa-solid fa-key" style="width:15px;text-align:center;color:var(--muted);font-size:13px"></i>Ubah Password</a>
            <div style="border-top:1px solid var(--border);margin:4px 2px"></div>
            <form method="POST" action="{{ route('logout') }}">@csrf
              <button type="submit" style="width:100%;display:flex;align-items:center;gap:10px;padding:9px 12px;border-radius:9px;font-family:inherit;background:none;border:none;cursor:pointer;color:#f87171;font-size:13px;transition:background .15s;text-align:left" onmouseover="this.style.background='rgba(239,68,68,.10)'" onmouseout="this.style.background='transparent'"><i class="fa-solid fa-right-from-bracket" style="width:15px;text-align:center;font-size:13px"></i>Logout</button>
            </form>
          </div>
        </div>
      </div>

    </div>
  </header>

  {{-- Content --}}
  <div id="content">
    {{ $slot }}
  </div>
</div>

<script>
/* ── Sidebar ── */
function toggleSB(){document.getElementById('sb').classList.toggle('open');document.getElementById('ov').style.display=document.getElementById('sb').classList.contains('open')?'block':'none'}
function closeSB(){document.getElementById('sb').classList.remove('open');document.getElementById('ov').style.display='none'}
function checkWidth(){const hb=document.getElementById('hamburger');if(window.innerWidth<1024){hb.style.display='block'}else{hb.style.display='none';closeSB()}}
window.addEventListener('resize',checkWidth);checkWidth();

/* ── Accent ── */
const accentPalette=[{id:'pabalu',label:'Pabalu',ac:'#e8192c',ac2:'#c41020',rgb:'232,25,44'},{id:'amber',label:'Amber',ac:'#f59e0b',ac2:'#ef4444',rgb:'245,158,11'},{id:'emerald',label:'Hijau',ac:'#10b981',ac2:'#06b6d4',rgb:'16,185,129'},{id:'blue',label:'Biru',ac:'#4f6ef7',ac2:'#7c3aed',rgb:'79,110,247'},{id:'violet',label:'Ungu',ac:'#8b5cf6',ac2:'#ec4899',rgb:'139,92,246'},{id:'rose',label:'Rose',ac:'#f43f5e',ac2:'#f97316',rgb:'244,63,94'},{id:'cyan',label:'Cyan',ac:'#06b6d4',ac2:'#3b82f6',rgb:'6,182,212'},{id:'lime',label:'Lime',ac:'#84cc16',ac2:'#10b981',rgb:'132,204,22'},{id:'pink',label:'Pink',ac:'#ec4899',ac2:'#8b5cf6',rgb:'236,72,153'},{id:'orange',label:'Oranye',ac:'#f97316',ac2:'#f59e0b',rgb:'249,115,22'},{id:'sky',label:'Sky',ac:'#38bdf8',ac2:'#6366f1',rgb:'56,189,248'}];
let currentAccent=localStorage.getItem('pabalu_accent')||'pabalu';
function renderSwatches(){const c=document.getElementById('color-swatches');if(!c)return;c.innerHTML=accentPalette.map(p=>`<div class="swatch${p.id===currentAccent?' active':''}" style="background:linear-gradient(135deg,${p.ac},${p.ac2})" title="${p.label}" onclick="setAccent('${p.id}')"><span class="check"><i class="fa-solid fa-check" style="font-size:10px"></i></span></div>`).join('')}
function setAccent(id){const c=accentPalette.find(x=>x.id===id);if(!c)return;currentAccent=id;localStorage.setItem('pabalu_accent',id);const r=document.documentElement;r.style.setProperty('--ac',c.ac);r.style.setProperty('--ac2',c.ac2);r.style.setProperty('--ac-rgb',c.rgb);r.style.setProperty('--ac-lt',`rgba(${c.rgb},.14)`);r.style.setProperty('--ac-lt2',`rgba(${c.rgb},.08)`);renderSwatches();if(typeof rebuildChart==='function')rebuildChart();showToast('success',`Warna diubah ke ${c.label}`)}

/* ── Profile ── */
function toggleProfMenu(e){e.stopPropagation();const m=document.getElementById('prof-menu'),ch=document.getElementById('prof-chevron'),o=m.style.display!=='none';m.style.display=o?'none':'block';ch.style.transform=o?'':'rotate(180deg)'}
function closeProfMenu(){const m=document.getElementById('prof-menu'),ch=document.getElementById('prof-chevron');if(m)m.style.display='none';if(ch)ch.style.transform=''}
document.addEventListener('click',e=>{if(!e.target.closest('#prof-wrap'))closeProfMenu()});

/* ── Theme ── */
let tpOpen=false;
function toggleTP(){tpOpen=!tpOpen;document.getElementById('tp').classList.toggle('hidden',!tpOpen)}
document.addEventListener('click',e=>{const btn=document.getElementById('theme-btn'),tp=document.getElementById('tp');if(btn&&tp&&!btn.contains(e.target)&&!tp.contains(e.target)){tpOpen=false;tp.classList.add('hidden')}});
function setTheme(t){localStorage.setItem('pabalu_theme',t);document.body.classList.toggle('light',t==='light');document.getElementById('tp-dark').classList.toggle('active',t==='dark');document.getElementById('tp-light').classList.toggle('active',t==='light');document.getElementById('tp-dark').style.background=t==='dark'?'var(--ac-lt)':'';document.getElementById('tp-dark').style.color=t==='dark'?'var(--ac)':'';document.getElementById('tp-light').style.background=t==='light'?'var(--ac-lt)':'';document.getElementById('tp-light').style.color=t==='light'?'var(--ac)':'';tpOpen=false;document.getElementById('tp').classList.add('hidden');if(typeof rebuildChart==='function')rebuildChart()}

/* ── Modal ── */
function openModal(id){document.getElementById(id).classList.add('open')}
function closeModal(id){document.getElementById(id).classList.remove('open')}

/* ── Toast ── */
function showToast(type,msg){const ic={success:'fa-circle-check',error:'fa-circle-xmark',info:'fa-circle-info'};const el=document.createElement('div');el.className=`toast ${type}`;el.innerHTML=`<i class="fa-solid ${ic[type]||ic.info} ti" style="font-size:16px;flex-shrink:0"></i><span>${msg}</span>`;document.getElementById('toast-container').appendChild(el);setTimeout(()=>el.style.cssText+=';opacity:0;transition:opacity .3s',2700);setTimeout(()=>el.remove(),3000)}

/* ── Init ── */
(function(){
  const t=localStorage.getItem('pabalu_theme')||'dark';
  const d=t==='dark';
  document.getElementById('tp-dark').classList.toggle('active',d);
  document.getElementById('tp-light').classList.toggle('active',!d);
  document.getElementById('tp-dark').style.background=d?'var(--ac-lt)':'';
  document.getElementById('tp-dark').style.color=d?'var(--ac)':'';
  document.getElementById('tp-light').style.background=!d?'var(--ac-lt)':'';
  document.getElementById('tp-light').style.color=!d?'var(--ac)':'';
})();
renderSwatches();

@if(session('success'))showToast('success',@json(session('success')));@endif
@if(session('error'))showToast('error',@json(session('error')));@endif
@if(session('info'))showToast('info',@json(session('info')));@endif
</script>

<x-system-report-fab />
@stack('scripts')
</body>
</html>
@endif{{-- end fnb / retail delegation --}}
