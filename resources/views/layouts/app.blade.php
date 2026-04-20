<!DOCTYPE html>
<html lang="id" id="html-root">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $title }} — {{ \App\Models\Setting::get('app_name', config('app.name', 'Pabalu')) }}</title>

<link href="https://fonts.googleapis.com/css2?family=Clash+Display:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Plus Jakarta Sans',sans-serif;transition:background .25s,color .25s}
.font-display,h1,h2,h3,h4{font-family:'Clash Display',sans-serif}

/* ════ ACCENT VARS ════ */
:root{--ac:#f59e0b;--ac2:#ef4444;--ac-rgb:245,158,11;--ac-lt:rgba(245,158,11,.14);--ac-lt2:rgba(245,158,11,.08)}

/* ════ DARK THEME (default) ════ */
body{
  --bg:#0f1117;--surface:#161b27;--surface2:#1c2336;--border:#252d42;
  --text:#e2e8f0;--muted:#64748b;--sub:#94a3b8;
  --card-hover:rgba(255,255,255,.025);--scrollbar:#252d42;
  background:var(--bg);color:var(--text);
}
body.light{
  --bg:#f1f5f9;--surface:#ffffff;--surface2:#f8fafc;--border:#e2e8f0;
  --text:#1e293b;--muted:#94a3b8;--sub:#64748b;
  --card-hover:rgba(0,0,0,.025);--scrollbar:#cbd5e1;
}

/* ════ SEMANTIC ════ */
.a-text{color:var(--ac)!important}
.a-bg{background:var(--ac)!important}
.a-bg-lt{background:var(--ac-lt)!important}
.a-grad{background:linear-gradient(135deg,var(--ac),var(--ac2))!important}

/* ════ SIDEBAR ════ */
#sb{
  position:fixed;top:0;left:0;height:100%;width:258px;
  display:flex;flex-direction:column;z-index:50;
  background:var(--surface);border-right:1px solid var(--border);
  transition:transform .3s,background .25s,border-color .25s;
}
@media(max-width:1023px){#sb{transform:translateX(-100%)}#sb.open{transform:translateX(0)}}
@media(max-width:1023px){#header-date,#header-date-divider{display:none!important}}
#sb-nav{overflow-y:auto;flex:1;scrollbar-width:thin;scrollbar-color:var(--scrollbar) transparent}
#sb-nav::-webkit-scrollbar{width:4px}
#sb-nav::-webkit-scrollbar-thumb{background:var(--scrollbar);border-radius:99px}

/* ════ NAV ITEMS ════ */
.nav-item{
  display:flex;align-items:center;gap:10px;padding:9px 12px;border-radius:10px;
  font-size:13.5px;font-weight:500;text-decoration:none;margin-bottom:2px;
  transition:background .15s,color .15s;
}
.nav-active{
  background:var(--ac-lt)!important;color:var(--ac)!important;
  border-left:2.5px solid var(--ac)!important;padding-left:calc(12px - 2.5px)!important;
}
.nav-inactive{color:var(--sub)}
.nav-inactive:hover{background:var(--surface2);color:var(--text)}
.nav-section{
  font-size:10px;font-weight:600;letter-spacing:1.3px;text-transform:uppercase;
  color:var(--muted);padding:4px 8px;margin-top:14px;margin-bottom:4px;
}
.nav-section:first-child{margin-top:4px}

/* ════ MAIN LAYOUT ════ */
#main{margin-left:258px;transition:margin .3s}
@media(max-width:1023px){#main{margin-left:0}}

/* ════ HEADER ════ */
#header{
  position:fixed;top:0;left:258px;right:0;z-index:40;
  display:flex;align-items:center;justify-content:space-between;
  padding:14px 24px;
  background:var(--surface);border-bottom:1px solid var(--border);
  transition:transform .3s ease,background .25s,border-color .25s;
}
#header.header-hidden{transform:translateY(-100%)}
@media(max-width:1023px){#header{left:0}}

/* ════ CONTENT ════ */
#content{padding:89px 24px 24px;max-width:1400px;display:flex;flex-direction:column;gap:20px}

/* ════ STAT CARDS ════ */
.stat-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
@media(max-width:1200px){.stat-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:600px){.stat-grid{grid-template-columns:1fr}}
.stat-card{
  background:var(--surface);border:1px solid var(--border);border-radius:16px;
  padding:20px;display:flex;align-items:center;gap:16px;
  transition:border-color .2s,box-shadow .2s;
}
.stat-card:hover{border-color:var(--ac);box-shadow:0 0 0 1px var(--ac-lt),0 8px 24px rgba(0,0,0,.15)}
.stat-icon{width:44px;height:44px;border-radius:12px;display:grid;place-items:center;font-size:17px;flex-shrink:0}
.stat-num{font-family:'Clash Display',sans-serif;font-size:26px;font-weight:700;color:var(--text);line-height:1}
.stat-label{font-size:11.5px;color:var(--muted);margin-top:3px}
.stat-trend{font-size:11px;font-weight:600;margin-top:4px;display:flex;align-items:center;gap:3px}
.trend-up{color:#34d399}.trend-down{color:#f87171}

/* ════ CARD ════ */
.card{background:var(--surface);border:1px solid var(--border);border-radius:16px}
.card-header{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.card-title{font-family:'Clash Display',sans-serif;font-size:15px;font-weight:600;color:var(--text)}
.card-body{padding:20px}

/* ════ TABLE ════ */
.tbl{width:100%;border-collapse:collapse;font-size:13px}
.tbl thead th{
  padding:10px 14px;text-align:left;font-size:11px;font-weight:600;
  letter-spacing:.8px;text-transform:uppercase;color:var(--muted);
  background:var(--surface2);border-bottom:1px solid var(--border)
}
.tbl thead th:first-child{border-radius:10px 0 0 10px}
.tbl thead th:last-child{border-radius:0 10px 10px 0}
.tbl tbody tr{border-bottom:1px solid var(--border);transition:background .12s}
.tbl tbody tr:last-child{border-bottom:none}
.tbl tbody tr:hover{background:var(--surface2)}
.tbl tbody td{padding:12px 14px;color:var(--sub)}
.tbl tbody td.td-main{color:var(--text);font-weight:500}

/* ════ BADGE ════ */
.badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:99px;font-size:11px;font-weight:600}
.badge-green{background:rgba(16,185,129,.15);color:#34d399}
.badge-amber{background:rgba(245,158,11,.15);color:#fbbf24}
.badge-red{background:rgba(239,68,68,.15);color:#f87171}
.badge-blue{background:rgba(99,102,241,.15);color:#818cf8}
.badge-gray{background:rgba(148,163,184,.12);color:#94a3b8}

/* ════ QUICK NAV ════ */
.qnav-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}
@media(max-width:768px){.qnav-grid{grid-template-columns:repeat(2,1fr)}}
.qnav-item{
  background:var(--surface);border:1px solid var(--border);border-radius:14px;
  padding:14px 16px;display:flex;align-items:center;gap:12px;
  text-decoration:none;transition:border-color .2s,background .2s;cursor:pointer;
}
.qnav-item:hover{background:var(--surface2)}

/* ════ PROGRESS BAR ════ */
.prog-bar{height:6px;border-radius:99px;background:var(--surface2);overflow:hidden;margin-top:6px}
.prog-fill{height:100%;border-radius:99px;background:linear-gradient(135deg,var(--ac),var(--ac2))}

/* ════ TWO-COL GRID ════ */
.two-col{display:grid;grid-template-columns:1fr 380px;gap:20px;align-items:start}
@media(max-width:1100px){.two-col{grid-template-columns:1fr}}

/* ════ THEME TOGGLE ════ */
#tp{
  position:absolute;right:0;top:calc(100% + 8px);
  background:var(--surface);border:1px solid var(--border);border-radius:16px;
  padding:10px;display:flex;flex-direction:column;gap:2px;min-width:210px;
  box-shadow:0 8px 32px rgba(0,0,0,.35);
  transition:opacity .2s,transform .2s;
}
#tp.hidden{opacity:0;pointer-events:none;transform:translateY(6px)}
.tp-btn{
  display:flex;align-items:center;gap:8px;padding:8px 12px;border-radius:10px;
  font-size:13px;font-weight:500;cursor:pointer;transition:background .12s;
  background:none;border:none;color:var(--text);text-align:left;width:100%;
}
.tp-btn:hover{background:var(--surface2)}
.tp-btn.active{background:var(--ac-lt);color:var(--ac)}
.tp-divider{height:1px;background:var(--border);margin:6px 4px}
.tp-section-label{font-size:10px;font-weight:600;letter-spacing:1px;text-transform:uppercase;color:var(--muted);padding:4px 12px 6px}
.color-swatches{display:flex;flex-wrap:wrap;gap:7px;padding:4px 12px 8px}
.swatch{
  width:26px;height:26px;border-radius:8px;cursor:pointer;border:2px solid transparent;
  transition:transform .15s,border-color .15s;position:relative;
}
.swatch:hover{transform:scale(1.15)}
.swatch.active{border-color:#fff;box-shadow:0 0 0 1px rgba(255,255,255,.25)}
body.light .swatch.active{border-color:#0f1117;box-shadow:0 0 0 1px rgba(0,0,0,.2)}
.swatch .check{position:absolute;inset:0;display:grid;place-items:center;font-size:11px;color:#fff;opacity:0;transition:opacity .15s}
body.light .swatch .check{color:#000}
.swatch.active .check{opacity:1}

/* ════ OVERLAY ════ */
#ov{position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:45;display:none;backdrop-filter:blur(3px)}

/* ════ MODAL ════ */
.modal-backdrop{
  position:fixed;inset:0;background:rgba(0,0,0,.6);backdrop-filter:blur(4px);
  z-index:60;display:flex;align-items:center;justify-content:center;padding:16px;
  opacity:0;pointer-events:none;transition:opacity .2s;
}
.modal-backdrop.open{opacity:1;pointer-events:all}
.modal-box{
  background:var(--surface);border:1px solid var(--border);border-radius:20px;
  width:100%;max-width:440px;box-shadow:0 24px 64px rgba(0,0,0,.5);
  transform:translateY(16px);transition:transform .25s;
}
.modal-backdrop.open .modal-box{transform:translateY(0)}

/* ════ FORM ════ */
.f-label{display:block;font-size:12px;font-weight:600;color:var(--sub);margin-bottom:5px;letter-spacing:.3px}
.f-input{
  width:100%;background:var(--surface2);border:1px solid var(--border);color:var(--text);
  border-radius:12px;padding:9px 13px;font-size:13.5px;font-family:inherit;
  outline:none;transition:border-color .15s,box-shadow .15s;
}
.f-input:focus{border-color:var(--ac);box-shadow:0 0 0 3px var(--ac-lt)}
.f-input::placeholder{color:var(--muted)}
select.f-input option{background:var(--surface2);color:var(--text)}
textarea.f-input{resize:vertical;min-height:80px}

/* ════ FORM ERRORS ════ */
.f-error{font-size:11.5px;color:#f87171;margin-top:5px;display:flex;align-items:center;gap:4px}

/* ════ BUTTONS ════ */
.btn{
  display:inline-flex;align-items:center;gap:7px;
  padding:9px 18px;border-radius:11px;border:1px solid var(--border);
  font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;
  transition:background .15s,color .15s;background:var(--surface2);color:var(--sub);
}
.btn:hover{background:var(--border);color:var(--text)}
.btn-primary{
  background:linear-gradient(135deg,var(--ac),var(--ac2));
  border-color:transparent;color:#fff;
}
.btn-primary:hover{opacity:.9;background:linear-gradient(135deg,var(--ac),var(--ac2))}
.btn-danger{background:rgba(239,68,68,.15);border-color:rgba(239,68,68,.3);color:#f87171}
.btn-danger:hover{background:rgba(239,68,68,.25)}

/* ════ ANIMATIONS ════ */
@keyframes fadeUp{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
.animate-fadeUp{animation:fadeUp .35s ease both}
.d1{animation-delay:.05s}.d2{animation-delay:.10s}.d3{animation-delay:.15s}.d4{animation-delay:.20s}

/* ════ TOAST ════ */
#toast-container{position:fixed;top:20px;right:20px;z-index:9999;display:flex;flex-direction:column;gap:8px;pointer-events:none}
@keyframes toastIn{from{opacity:0;transform:translateX(110%)}to{opacity:1;transform:translateX(0)}}
@keyframes toastOut{from{opacity:1;transform:translateX(0)}to{opacity:0;transform:translateX(110%)}}
.toast{
  display:flex;align-items:flex-start;gap:10px;padding:13px 16px;border-radius:14px;
  font-size:13px;font-weight:500;pointer-events:auto;
  box-shadow:0 8px 32px rgba(0,0,0,.35);animation:toastIn .3s cubic-bezier(.34,1.56,.64,1) both;
  border:1px solid var(--border);background:var(--surface);color:var(--text);
  min-width:260px;max-width:360px;position:relative;overflow:hidden;
}
.toast-icon{flex-shrink:0;width:20px;height:20px;border-radius:50%;display:grid;place-items:center;font-size:11px;margin-top:1px}
.toast.success .toast-icon{background:rgba(16,185,129,.2);color:#10b981}
.toast.error   .toast-icon{background:rgba(239,68,68,.2);color:#f87171}
.toast.info    .toast-icon{background:rgba(96,165,250,.2);color:#60a5fa}
.toast.warning .toast-icon{background:rgba(245,158,11,.2);color:var(--ac)}
.toast-body{flex:1;min-width:0}
.toast-title{font-weight:600;font-size:13px;color:var(--text);line-height:1.3}
.toast-msg{font-size:12px;color:var(--sub);margin-top:2px;line-height:1.4}
.toast-close{flex-shrink:0;background:none;border:none;cursor:pointer;color:var(--muted);font-size:11px;padding:0;margin-top:1px;transition:color .15s}
.toast-close:hover{color:var(--text)}
.toast-progress{position:absolute;bottom:0;left:0;height:3px;border-radius:0 0 14px 14px;animation:none}

/* ════ SCROLLBAR ════ */
::-webkit-scrollbar{width:6px;height:6px}
::-webkit-scrollbar-track{background:transparent}
::-webkit-scrollbar-thumb{background:var(--scrollbar);border-radius:99px}

/* ════ OPENING STOK ROW ════ */
.opn-row{display:grid;grid-template-columns:1fr auto auto;gap:16px;align-items:center;padding:12px 20px;border-bottom:1px solid var(--border);transition:background .1s}
@media(max-width:640px){
  .opn-row{grid-template-columns:1fr 1fr;gap:10px}
  .opn-row .opn-info{grid-column:1/-1}
  .opn-row .opn-stok{text-align:left}
  .opn-row .opn-input{justify-self:end}
}

/* ════ SETTINGS PAGE ════ */
.settings-section{background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:28px}
.settings-section+.settings-section{margin-top:20px}
.settings-title{font-family:'Clash Display',sans-serif;font-size:16px;font-weight:600;color:var(--text);margin-bottom:4px}
.settings-desc{font-size:13px;color:var(--sub);margin-bottom:24px}
.f-group{margin-bottom:18px}
.f-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
@media(max-width:600px){.f-row{grid-template-columns:1fr}}
</style>
@stack('styles')
</head>
<body>

<div id="toast-container"></div>
<div id="ov" onclick="closeSB()"></div>

{{-- ══════════ SIDEBAR ══════════ --}}
<aside id="sb">
  {{-- Logo --}}
  <div style="display:flex;align-items:center;gap:12px;padding:18px 20px;border-bottom:1px solid var(--border);flex-shrink:0">
    <div class="a-grad" style="width:38px;height:38px;border-radius:11px;display:grid;place-items:center;flex-shrink:0">
      <i class="fa-solid fa-store" style="color:#fff;font-size:16px"></i>
    </div>
    <div>
      <div class="font-display" style="font-size:17px;font-weight:700;color:var(--text);line-height:1.1">
        Pa<span class="a-text">balu</span>
      </div>
      <div style="font-size:9px;color:var(--muted);letter-spacing:.3px;margin-top:1px">Sistem Manajemen UMKM</div>
    </div>
  </div>

  {{-- Nav --}}
  <div id="sb-nav" style="padding:10px 10px">

    {{-- Utama --}}
    <p class="nav-section">Utama</p>
    <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-chart-pie" style="width:15px;text-align:center;font-size:13px"></i>Dashboard
    </a>

    {{-- Operasional Harian --}}
    @canany(['stock.opening','transaction.create','stock.in','expense.read','stock.waste','closing.read','order.read'])
    <p class="nav-section">Operasional Harian</p>
    @can('stock.opening')
    <a href="{{ route('opening.index') }}" class="nav-item {{ request()->routeIs('opening.*') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-box-open" style="width:15px;text-align:center;font-size:13px"></i>Opening Stok
    </a>
    @endcan
    @can('transaction.create')
    <a href="{{ route('transactions.pos') }}" class="nav-item {{ request()->routeIs('transactions.pos') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-cash-register" style="width:15px;text-align:center;font-size:13px"></i>POS / Kasir
    </a>
    @endcan
    @can('order.read')
    <a href="{{ route('orders.index') }}" class="nav-item {{ request()->routeIs('orders.*') ? 'nav-active' : 'nav-inactive' }}" style="position:relative">
      <i class="fa-solid fa-ticket" style="width:15px;text-align:center;font-size:13px"></i>Antrian Order
      @php $pendingOrders = \App\Models\Order::when(auth()->user()->assignedOutletId(), fn($q,$id) => $q->where('outlet_id',$id))->where('order_status','pending')->count(); @endphp
      @if($pendingOrders > 0)
        <span style="margin-left:auto;background:var(--ac);color:#fff;border-radius:99px;padding:1px 7px;font-size:10px;font-weight:700;min-width:18px;text-align:center">{{ $pendingOrders }}</span>
      @endif
    </a>
    @endcan
    @can('stock.in')
    <a href="{{ route('stock.in') }}" class="nav-item {{ request()->routeIs('stock.in*') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-cart-plus" style="width:15px;text-align:center;font-size:13px"></i>Tambah Stok
    </a>
    @endcan
    @can('expense.read')
    <a href="{{ route('expenses.index') }}" class="nav-item {{ request()->routeIs('expenses.*') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-wallet" style="width:15px;text-align:center;font-size:13px"></i>Pengeluaran
    </a>
    @endcan
    @can('stock.waste')
    <a href="{{ route('stock.waste') }}" class="nav-item {{ request()->routeIs('stock.waste*') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-trash-can-arrow-up" style="width:15px;text-align:center;font-size:13px"></i>Waste / Barang Rusak
    </a>
    @endcan
    @can('closing.read')
    <a href="{{ route('closing.index') }}" class="nav-item {{ request()->routeIs('closing.*') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-lock" style="width:15px;text-align:center;font-size:13px"></i>Closing Harian
    </a>
    @endcan
    @endcanany

    {{-- Transaksi --}}
    @can('transaction.read')
    <p class="nav-section">Transaksi</p>
    <a href="{{ route('transactions.index') }}" class="nav-item {{ request()->routeIs('transactions.index') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-receipt" style="width:15px;text-align:center;font-size:13px"></i>Riwayat Transaksi
    </a>
    @endcan

    {{-- Produk & Stok --}}
    @canany(['product.read','category.read','stock.read'])
    <p class="nav-section">Produk & Stok</p>
    @can('product.read')
    <a href="{{ route('products.index') }}" class="nav-item {{ request()->routeIs('products.*') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-cubes" style="width:15px;text-align:center;font-size:13px"></i>Produk
    </a>
    @endcan
    @can('category.read')
    <a href="{{ route('categories.index') }}" class="nav-item {{ request()->routeIs('categories.*') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-tags" style="width:15px;text-align:center;font-size:13px"></i>Kategori
    </a>
    @endcan
    @can('stock.read')
    <a href="{{ route('stock.index') }}" class="nav-item {{ request()->routeIs('stock.index') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-warehouse" style="width:15px;text-align:center;font-size:13px"></i>Stok & Pergerakan
    </a>
    @endcan
    @endcanany

    {{-- Laporan --}}
    @canany(['report.outlet','report.all'])
    <p class="nav-section">Laporan</p>
    <a href="{{ route('reports.sales') }}" class="nav-item {{ request()->routeIs('reports.sales') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-chart-line" style="width:15px;text-align:center;font-size:13px"></i>Laporan Penjualan
    </a>
    <a href="{{ route('reports.stock') }}" class="nav-item {{ request()->routeIs('reports.stock') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-clipboard-list" style="width:15px;text-align:center;font-size:13px"></i>Laporan Stok
    </a>
    <a href="{{ route('reports.profit-loss') }}" class="nav-item {{ request()->routeIs('reports.profit-loss') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-scale-balanced" style="width:15px;text-align:center;font-size:13px"></i>Laba & Rugi
    </a>
    @endcanany

    {{-- Manajemen Sistem --}}
    @canany(['outlet.read','role.read','user.read','log.read','setting.read'])
    <p class="nav-section">Manajemen</p>
    @can('outlet.read')
    <a href="{{ route('outlets.index') }}" class="nav-item {{ request()->routeIs('outlets.*') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-shop" style="width:15px;text-align:center;font-size:13px"></i>Kelola Outlet
    </a>
    @endcan
    @can('role.read')
    <a href="{{ route('rbac.roles.index') }}" class="nav-item {{ request()->routeIs('rbac.*') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-shield-halved" style="width:15px;text-align:center;font-size:13px"></i>Role & Permission
    </a>
    @endcan
    @can('user.read')
    <a href="{{ route('users.index') }}" class="nav-item {{ request()->routeIs('users.*') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-users-gear" style="width:15px;text-align:center;font-size:13px"></i>Kelola User
    </a>
    @endcan
    @endcanany

    {{-- Panduan & Billing --}}
    @canany(['guide.read','billing.read','billing.manage'])
    <p class="nav-section">Bantuan</p>
    @endcanany
    @can('guide.read')
    <a href="{{ route('guide.index') }}" class="nav-item {{ request()->routeIs('guide.*') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-book-open" style="width:15px;text-align:center;font-size:13px"></i>Panduan Penggunaan
    </a>
    @endcan
    @can('api.docs')
    <a href="{{ route('api.docs') }}" class="nav-item {{ request()->routeIs('api.docs') ? 'nav-active' : 'nav-inactive' }}" target="_blank">
      <i class="fa-solid fa-code" style="width:15px;text-align:center;font-size:13px"></i>Dokumentasi API
    </a>
    @endcan
    @can('billing.read')
    @php $hasBillingAlert = isset($billingInvoice) && $billingInvoice; @endphp
    <a href="{{ route('billing.index') }}" class="nav-item {{ request()->routeIs('billing.*') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-file-invoice-dollar" style="width:15px;text-align:center;font-size:13px"></i>
      Tagihan Aplikasi
      @if($hasBillingAlert)<span style="margin-left:auto;width:8px;height:8px;border-radius:50%;background:#f87171;flex-shrink:0"></span>@endif
    </a>
    @endcan
    @can('billing.manage')
    <a href="{{ route('admin.billing.index') }}" class="nav-item {{ request()->routeIs('admin.billing.*') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-file-invoice" style="width:15px;text-align:center;font-size:13px"></i>Manajemen Tagihan
    </a>
    @endcan

    {{-- Pengaturan Akun --}}
    <p class="nav-section">Pengaturan</p>
    <a href="{{ route('profile.edit') }}" class="nav-item {{ request()->routeIs('profile.edit') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-user-gear" style="width:15px;text-align:center;font-size:13px"></i>Akun Saya
    </a>
    @if(auth()->user()->isAdmin())
    <a href="{{ route('admin.owner-accounts.index') }}" class="nav-item {{ request()->routeIs('admin.owner-accounts.*') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-user-tie" style="width:15px;text-align:center;font-size:13px"></i>Akun Owner
    </a>
    @endif
    @can('log.read')
    <a href="{{ route('activity-logs.index') }}" class="nav-item {{ request()->routeIs('activity-logs.*') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-scroll" style="width:15px;text-align:center;font-size:13px"></i>Log Aktivitas
    </a>
    @endcan
    @can('setting.read')
    <a href="{{ route('settings.index') }}" class="nav-item {{ request()->routeIs('settings.*') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-sliders" style="width:15px;text-align:center;font-size:13px"></i>Pengaturan Sistem
    </a>
    @endcan
    @if(!auth()->user()->isAdmin() && auth()->user()->isOwner())
    <a href="{{ route('owner.payment-methods.index') }}" class="nav-item {{ request()->routeIs('owner.payment-methods.*') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-wallet" style="width:15px;text-align:center;font-size:13px"></i>Metode Pembayaran
    </a>
    @php $ownerMidtransKey = \App\Models\OwnerSetting::get('midtrans_server_key', auth()->id(), ''); @endphp
    @if(!empty($ownerMidtransKey))
    <a href="{{ route('owner.payment-settings.index') }}" class="nav-item {{ request()->routeIs('owner.payment-settings.*') ? 'nav-active' : 'nav-inactive' }}">
      <i class="fa-solid fa-credit-card" style="width:15px;text-align:center;font-size:13px"></i>Pembayaran Online
    </a>
    @endif
    @endif

  </div>

  {{-- User card --}}
  <div style="padding:12px 14px;border-top:1px solid var(--border);flex-shrink:0;position:relative">

    {{-- Dropdown (muncul ke atas) --}}
    <div id="user-dropdown"
      style="display:none;position:absolute;bottom:calc(100% - 12px);left:14px;right:14px;
             background:var(--surface);border:1px solid var(--border);border-radius:14px;
             box-shadow:0 -8px 32px rgba(0,0,0,.3);overflow:hidden;z-index:100;
             opacity:0;transform:translateY(6px);transition:opacity .18s,transform .18s">
      <div style="padding:10px 12px 8px;border-bottom:1px solid var(--border)">
        <div style="font-size:12px;font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
          {{ auth()->user()->name }}
        </div>
        <div style="font-size:11px;color:var(--muted);margin-top:1px">{{ auth()->user()->email }}</div>
      </div>
      <a href="{{ route('profile.edit') }}"
        style="display:flex;align-items:center;gap:8px;padding:9px 12px;font-size:13px;color:var(--sub);text-decoration:none;transition:background .12s"
        onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
        <i class="fa-solid fa-user-pen" style="width:14px;text-align:center;font-size:12px"></i>Profil Saya
      </a>
      <div style="height:1px;background:var(--border)"></div>
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit"
          style="display:flex;align-items:center;gap:8px;padding:9px 12px;font-size:13px;color:#f87171;
                 background:none;border:none;cursor:pointer;font-family:inherit;width:100%;text-align:left;transition:background .12s"
          onmouseover="this.style.background='rgba(239,68,68,.08)'" onmouseout="this.style.background='transparent'">
          <i class="fa-solid fa-right-from-bracket" style="width:14px;text-align:center;font-size:12px"></i>Keluar
        </button>
      </form>
    </div>

    {{-- Trigger button --}}
    <button id="user-card-btn" type="button" onclick="toggleUserDropdown()"
      style="display:flex;align-items:center;gap:10px;padding:10px;border-radius:12px;
             background:var(--surface2);cursor:pointer;width:100%;border:1px solid transparent;
             text-align:left;font-family:inherit;transition:background .15s,border-color .15s">
      <div class="a-grad" style="width:34px;height:34px;border-radius:9px;display:grid;place-items:center;flex-shrink:0;font-weight:700;color:#fff;font-size:13px">
        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
      </div>
      <div style="flex:1;min-width:0">
        <div style="font-size:13px;font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
          {{ auth()->user()->name }}
        </div>
        <div style="font-size:11px;color:var(--muted)">
          {{ auth()->user()->getRoleNames()->first() ?? 'User' }}
        </div>
      </div>
      <i id="user-card-chevron" class="fa-solid fa-chevron-up" style="color:var(--muted);font-size:11px;transition:transform .18s"></i>
    </button>

  </div>
</aside>

{{-- ══════════ MAIN ══════════ --}}
<div id="main">

  {{-- Header --}}
  <header id="header">
    <div style="display:flex;align-items:center;gap:14px">
      <button onclick="toggleSB()" id="hamburger"
        style="display:none;background:none;border:none;cursor:pointer;color:var(--sub);font-size:18px">
        <i class="fa-solid fa-bars"></i>
      </button>
      <h1 class="font-display" style="font-size:18px;font-weight:700;color:var(--text)">{{ $title }}</h1>
    </div>

    <div style="display:flex;align-items:center;gap:10px">

      {{-- Tanggal — disembunyikan di mobile --}}
      <div id="header-date" style="text-align:right;line-height:1.3">
        <div style="font-size:12px;font-weight:600;color:var(--text)">{{ now()->translatedFormat('l') }}</div>
        <div style="font-size:11px;color:var(--muted)">{{ now()->translatedFormat('d F Y') }}</div>
      </div>

      {{-- Divider --}}
      <div id="header-date-divider" style="width:1px;height:28px;background:var(--border)"></div>

      {{-- Notifikasi --}}
      <button onclick="showToast('info','Belum ada notifikasi')"
        style="position:relative;width:38px;height:38px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);cursor:pointer;color:var(--sub);font-size:14px;transition:color .15s"
        onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--sub)'">
        <i class="fa-solid fa-bell"></i>
      </button>

      {{-- Theme toggle --}}
      <div style="position:relative">
        <button id="theme-btn" onclick="toggleTP()"
          style="width:38px;height:38px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);cursor:pointer;color:var(--sub);font-size:14px;transition:color .15s"
          onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--sub)'">
          <i class="fa-solid fa-circle-half-stroke"></i>
        </button>
        <div id="tp" class="hidden">
          <button class="tp-btn" id="tp-dark" onclick="setTheme('dark')">
            <i class="fa-solid fa-moon" style="width:14px;text-align:center"></i>Mode Gelap
          </button>
          <button class="tp-btn" id="tp-light" onclick="setTheme('light')">
            <i class="fa-solid fa-sun" style="width:14px;text-align:center"></i>Mode Terang
          </button>
          <div class="tp-divider"></div>
          <div class="tp-section-label">Warna Aksen</div>
          <div class="color-swatches" id="color-swatches"></div>
        </div>
      </div>

    </div>
  </header>

  {{-- Page Content --}}
  <div id="content">

    {{-- Trial Banner --}}
    @php
      $authUser = auth()->user();
      $ownerAcc = $authUser->ownerAccount();
    @endphp
    @if($ownerAcc && !$authUser->isAdmin() && $ownerAcc->account_type === 'trial' && !$ownerAcc->isTrialExpired())
      @php
        $dLeft    = $ownerAcc->trialDaysLeft();
        $adminWa  = \App\Models\User::role('admin')->with('profile')->first()?->profile?->no_hp;
        $waLink   = $adminWa ? 'https://wa.me/' . preg_replace('/[^0-9]/', '', ltrim($adminWa, '0')) : null;
        $waLink   = $waLink ? str_replace('wa.me/0', 'wa.me/62', $waLink) : null;
      @endphp
      <div style="background:{{ $dLeft <= 5 ? 'rgba(239,68,68,.12)' : 'rgba(245,158,11,.08)' }};
                  border:1px solid {{ $dLeft <= 5 ? 'rgba(239,68,68,.25)' : 'rgba(245,158,11,.2)' }};
                  border-radius:12px;padding:10px 16px;
                  display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;font-size:12.5px">
        <div style="display:flex;align-items:center;gap:8px;color:{{ $dLeft <= 5 ? '#f87171' : '#fbbf24' }}">
          <i class="fa-solid fa-hourglass-half"></i>
          <span>
            Masa trial Anda berakhir dalam <strong>{{ $dLeft }} hari</strong>
            ({{ $ownerAcc->trial_ends_at?->translatedFormat('d F Y') ?? '-' }}).
            Hubungi admin untuk upgrade ke <strong>Premium</strong>.
          </span>
        </div>
        @if($waLink)
        <a href="{{ $waLink }}" target="_blank" rel="noopener"
          style="display:inline-flex;align-items:center;gap:7px;padding:7px 14px;border-radius:9px;
                 background:rgba(37,211,102,.15);border:1px solid rgba(37,211,102,.3);
                 color:#25d366;font-size:12px;font-weight:600;text-decoration:none;flex-shrink:0;
                 transition:background .15s"
          onmouseover="this.style.background='rgba(37,211,102,.25)'"
          onmouseout="this.style.background='rgba(37,211,102,.15)'">
          <i class="fa-brands fa-whatsapp" style="font-size:14px"></i> Hubungi Admin
        </a>
        @endif
      </div>
    @endif

    {{ $slot }}
  </div>

</div>{{-- /main --}}

<script>
/* ════ ACCENT PALETTE ════ */
const accentPalette=[
  {id:'amber',  label:'Amber',  ac:'#f59e0b',ac2:'#ef4444',rgb:'245,158,11'},
  {id:'emerald',label:'Hijau',  ac:'#10b981',ac2:'#06b6d4',rgb:'16,185,129'},
  {id:'blue',   label:'Biru',   ac:'#4f6ef7',ac2:'#7c3aed',rgb:'79,110,247'},
  {id:'violet', label:'Ungu',   ac:'#8b5cf6',ac2:'#ec4899',rgb:'139,92,246'},
  {id:'rose',   label:'Merah',  ac:'#f43f5e',ac2:'#f97316',rgb:'244,63,94'},
  {id:'cyan',   label:'Cyan',   ac:'#06b6d4',ac2:'#3b82f6',rgb:'6,182,212'},
  {id:'lime',   label:'Lime',   ac:'#84cc16',ac2:'#10b981',rgb:'132,204,22'},
  {id:'pink',   label:'Pink',   ac:'#ec4899',ac2:'#8b5cf6',rgb:'236,72,153'},
  {id:'orange', label:'Oranye', ac:'#f97316',ac2:'#f59e0b',rgb:'249,115,22'},
  {id:'sky',    label:'Sky',    ac:'#38bdf8',ac2:'#6366f1',rgb:'56,189,248'},
];
let currentAccent=localStorage.getItem('pb-accent')||'amber';
let currentTheme=localStorage.getItem('pb-theme')||'dark';

function applyTheme(t){
  document.body.classList.toggle('light',t==='light');
  document.getElementById('tp-dark').classList.toggle('active',t==='dark');
  document.getElementById('tp-light').classList.toggle('active',t==='light');
}
function setTheme(t){
  currentTheme=t;
  localStorage.setItem('pb-theme',t);
  applyTheme(t);
  tpOpen=false;
  document.getElementById('tp').classList.add('hidden');
  if(window.rebuildChart) window.rebuildChart();
}

function applyAccent(id){
  const c=accentPalette.find(x=>x.id===id);
  if(!c)return;
  const r=document.documentElement;
  r.style.setProperty('--ac',c.ac);
  r.style.setProperty('--ac2',c.ac2);
  r.style.setProperty('--ac-rgb',c.rgb);
  r.style.setProperty('--ac-lt',`rgba(${c.rgb},.14)`);
  r.style.setProperty('--ac-lt2',`rgba(${c.rgb},.08)`);
}
function setAccent(id){
  currentAccent=id;
  localStorage.setItem('pb-accent',id);
  applyAccent(id);
  renderSwatches();
  if(window.rebuildChart) window.rebuildChart();
  showToast('success','Warna diubah ke '+accentPalette.find(x=>x.id===id)?.label);
}
function renderSwatches(){
  document.getElementById('color-swatches').innerHTML=accentPalette.map(c=>`
    <div class="swatch${c.id===currentAccent?' active':''}"
      style="background:linear-gradient(135deg,${c.ac},${c.ac2})" title="${c.label}"
      onclick="setAccent('${c.id}')">
      <span class="check"><i class="fa-solid fa-check" style="font-size:10px"></i></span>
    </div>
  `).join('');
}

/* ════ THEME PANEL ════ */
let tpOpen=false;
function toggleTP(){
  tpOpen=!tpOpen;
  document.getElementById('tp').classList.toggle('hidden',!tpOpen);
}
document.addEventListener('click',e=>{
  const btn=document.getElementById('theme-btn');
  const panel=document.getElementById('tp');
  if(btn&&panel&&!btn.contains(e.target)&&!panel.contains(e.target)){
    tpOpen=false;panel.classList.add('hidden');
  }
});

/* ════ USER DROPDOWN ════ */
function toggleUserDropdown(){
  var dd      = document.getElementById('user-dropdown');
  var chevron = document.getElementById('user-card-chevron');
  var btn     = document.getElementById('user-card-btn');
  var open    = dd.style.display === 'block';
  if(open){
    dd.style.opacity   = '0';
    dd.style.transform = 'translateY(6px)';
    setTimeout(function(){ dd.style.display = 'none'; }, 180);
    chevron.style.transform = 'rotate(0deg)';
    btn.style.borderColor   = 'transparent';
  } else {
    dd.style.display   = 'block';
    requestAnimationFrame(function(){
      requestAnimationFrame(function(){
        dd.style.opacity   = '1';
        dd.style.transform = 'translateY(0)';
      });
    });
    chevron.style.transform = 'rotate(180deg)';
    btn.style.borderColor   = 'var(--border)';
  }
}
// Tutup dropdown jika klik di luar
document.addEventListener('click', function(e){
  var dd  = document.getElementById('user-dropdown');
  var btn = document.getElementById('user-card-btn');
  if(dd && dd.style.display === 'block' && !btn.contains(e.target) && !dd.contains(e.target)){
    dd.style.opacity   = '0';
    dd.style.transform = 'translateY(6px)';
    setTimeout(function(){ dd.style.display='none'; }, 180);
    document.getElementById('user-card-chevron').style.transform = 'rotate(0deg)';
    btn.style.borderColor = 'transparent';
  }
});

/* ════ SIDEBAR ════ */
function toggleSB(){
  document.getElementById('sb').classList.toggle('open');
  document.getElementById('ov').style.display=
    document.getElementById('sb').classList.contains('open')?'block':'none';
}
function closeSB(){
  document.getElementById('sb').classList.remove('open');
  document.getElementById('ov').style.display='none';
}
function checkWidth(){
  const hb=document.getElementById('hamburger');
  if(window.innerWidth<1024){hb.style.display='block';}
  else{hb.style.display='none';closeSB();}
}
window.addEventListener('resize',checkWidth);

/* ════ TOAST ════ */
const toastIcons={success:'fa-check',error:'fa-xmark',info:'fa-info',warning:'fa-triangle-exclamation'};
const toastTitles={success:'Berhasil',error:'Gagal',info:'Info',warning:'Perhatian'};
function showToast(type,msg,duration=3500){
  const container=document.getElementById('toast-container');
  const el=document.createElement('div');
  el.className=`toast ${type}`;
  const progColor={success:'#10b981',error:'#f87171',info:'#60a5fa',warning:'var(--ac)'}[type]||'var(--ac)';
  el.innerHTML=`
    <div class="toast-icon"><i class="fa-solid ${toastIcons[type]||'fa-info'}"></i></div>
    <div class="toast-body">
      <div class="toast-title">${toastTitles[type]||type}</div>
      <div class="toast-msg">${msg}</div>
    </div>
    <button class="toast-close" onclick="this.closest('.toast').remove()"><i class="fa-solid fa-xmark"></i></button>
    <div class="toast-progress" style="background:${progColor};width:100%;transition:width ${duration}ms linear"></div>
  `;
  container.appendChild(el);
  // start progress bar
  requestAnimationFrame(()=>requestAnimationFrame(()=>{
    el.querySelector('.toast-progress').style.width='0%';
  }));
  const t=setTimeout(()=>{
    el.style.animation=`toastOut .3s ease forwards`;
    setTimeout(()=>el.remove(),300);
  },duration);
  el.querySelector('.toast-close').addEventListener('click',()=>clearTimeout(t));
}

/* ════ MODAL ════ */
function openModal(id){document.getElementById(id).classList.add('open')}
function closeModal(id){document.getElementById(id).classList.remove('open')}

/* ════ SCROLL HIDE HEADER ════ */
(function(){
  var lastY = 0;
  var hdr   = document.getElementById('header');
  window.addEventListener('scroll', function(){
    var y = window.scrollY;
    if(y > lastY && y > 80){
      hdr.classList.add('header-hidden');
    } else {
      hdr.classList.remove('header-hidden');
    }
    lastY = y;
  }, {passive:true});
})();

/* ════ INIT ════ */
applyTheme(currentTheme);
applyAccent(currentAccent);
renderSwatches();
checkWidth();
</script>

{{-- Auto flash-to-toast --}}
@if(session('success') || session('error') || session('info') || session('warning'))
<script>
document.addEventListener('DOMContentLoaded',function(){
  @if(session('success')) showToast('success', @json(session('success'))); @endif
  @if(session('error'))   showToast('error',   @json(session('error')));   @endif
  @if(session('info'))    showToast('info',     @json(session('info')));    @endif
  @if(session('warning')) showToast('warning',  @json(session('warning'))); @endif
});
</script>
@endif

{{-- Page-level scripts --}}
@stack('scripts')

</body>
</html>
