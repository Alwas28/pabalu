<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>API Documentation — {{ config('app.name', 'Pabalu') }}</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#0d1117;--surface:#161b22;--surface2:#1c2333;--border:#30363d;
  --text:#e6edf3;--muted:#8b949e;--sub:#6e7681;
  --get:#1f8b4c;--get-bg:rgba(31,139,76,.15);--get-border:rgba(31,139,76,.4);
  --post:#1a65c0;--post-bg:rgba(26,101,192,.15);--post-border:rgba(26,101,192,.4);
  --put:#9a6700;--put-bg:rgba(154,103,0,.15);--put-border:rgba(154,103,0,.4);
  --del:#cf222e;--del-bg:rgba(207,34,46,.15);--del-border:rgba(207,34,46,.4);
  --accent:#f0883e;--accent2:#58a6ff;--radius:8px;
}
html{scroll-behavior:smooth}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);display:flex;min-height:100vh;font-size:14px;line-height:1.6}

/* ── Sidebar ── */
.sidebar{
  width:280px;min-width:280px;background:var(--surface);border-right:1px solid var(--border);
  height:100vh;position:sticky;top:0;overflow-y:auto;display:flex;flex-direction:column;
}
.sidebar::-webkit-scrollbar{width:4px}
.sidebar::-webkit-scrollbar-track{background:transparent}
.sidebar::-webkit-scrollbar-thumb{background:var(--border);border-radius:4px}
.sidebar-header{padding:20px 24px 16px;border-bottom:1px solid var(--border)}
.sidebar-logo{display:flex;align-items:center;gap:10px;margin-bottom:8px}
.sidebar-logo .icon{width:36px;height:36px;background:linear-gradient(135deg,#f0883e,#f59e0b);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:16px}
.sidebar-logo .name{font-weight:700;font-size:15px}
.sidebar-logo .version{font-size:11px;color:var(--muted);background:var(--surface2);padding:2px 8px;border-radius:20px;border:1px solid var(--border)}
.sidebar-desc{font-size:12px;color:var(--muted)}
.sidebar-nav{padding:12px 0;flex:1}
.nav-group{margin-bottom:4px}
.nav-group-title{
  padding:8px 24px 4px;font-size:11px;font-weight:600;letter-spacing:.08em;
  text-transform:uppercase;color:var(--sub);
}
.nav-item{
  display:flex;align-items:center;gap:10px;padding:7px 24px;
  color:var(--muted);text-decoration:none;font-size:13px;font-weight:500;
  border-left:2px solid transparent;transition:.15s;cursor:pointer;
}
.nav-item:hover{color:var(--text);background:rgba(255,255,255,.04);border-left-color:var(--border)}
.nav-item.active{color:var(--accent);background:rgba(240,136,62,.08);border-left-color:var(--accent)}
.nav-item .nav-icon{width:16px;text-align:center;font-size:13px}
.nav-count{margin-left:auto;background:var(--surface2);border:1px solid var(--border);font-size:10px;padding:1px 6px;border-radius:10px;font-weight:600}
.sidebar-footer{padding:16px 24px;border-top:1px solid var(--border);font-size:11px;color:var(--sub)}

/* ── Main ── */
.main{flex:1;overflow-y:auto;padding:0}
.main::-webkit-scrollbar{width:6px}
.main::-webkit-scrollbar-track{background:transparent}
.main::-webkit-scrollbar-thumb{background:var(--border);border-radius:4px}

/* ── Hero ── */
.hero{
  padding:40px 48px 32px;border-bottom:1px solid var(--border);
  background:linear-gradient(135deg,rgba(240,136,62,.05),rgba(88,166,255,.05));
}
.hero h1{font-size:28px;font-weight:700;margin-bottom:8px}
.hero h1 span{color:var(--accent)}
.hero p{color:var(--muted);max-width:640px;line-height:1.7}
.hero-meta{display:flex;gap:16px;margin-top:20px;flex-wrap:wrap}
.hero-badge{
  display:flex;align-items:center;gap:6px;padding:6px 14px;
  background:var(--surface2);border:1px solid var(--border);border-radius:20px;font-size:12px;
}
.hero-badge .dot{width:7px;height:7px;border-radius:50%;background:#3fb950}

/* ── Base URL box ── */
.base-url-box{
  margin:0 48px 32px;padding:16px 20px;background:var(--surface);
  border:1px solid var(--border);border-radius:var(--radius);display:flex;align-items:center;gap:12px;
  margin-top:24px;
}
.base-url-box .label{font-size:11px;color:var(--sub);font-weight:600;text-transform:uppercase;white-space:nowrap}
.base-url-box .url{font-family:'JetBrains Mono',monospace;color:var(--accent2);font-size:13px;flex:1;word-break:break-all}
.copy-btn{
  padding:5px 12px;background:var(--surface2);border:1px solid var(--border);
  border-radius:5px;color:var(--muted);font-size:11px;cursor:pointer;
  transition:.15s;white-space:nowrap;font-family:'Inter',sans-serif;
}
.copy-btn:hover{color:var(--text);border-color:var(--accent2)}

/* ── Sections ── */
.section{padding:0 48px 48px;scroll-margin-top:20px}
.section-header{
  display:flex;align-items:center;gap:12px;padding:28px 0 20px;
  border-bottom:1px solid var(--border);margin-bottom:28px;
}
.section-icon{
  width:36px;height:36px;border-radius:8px;display:flex;align-items:center;
  justify-content:center;font-size:15px;flex-shrink:0;
}
.section-header h2{font-size:18px;font-weight:700}
.section-header p{font-size:13px;color:var(--muted);margin-top:2px}

/* ── Endpoint Card ── */
.endpoint{
  background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);
  margin-bottom:16px;overflow:hidden;
}
.endpoint-header{
  display:flex;align-items:center;gap:12px;padding:14px 18px;cursor:pointer;
  transition:background .15s;user-select:none;
}
.endpoint-header:hover{background:rgba(255,255,255,.03)}
.endpoint-header.open{border-bottom:1px solid var(--border)}
.method{
  font-family:'JetBrains Mono',monospace;font-size:11px;font-weight:700;
  padding:3px 10px;border-radius:4px;min-width:60px;text-align:center;
}
.method-get {color:var(--get);background:var(--get-bg);border:1px solid var(--get-border)}
.method-post{color:var(--post);background:var(--post-bg);border:1px solid var(--post-border)}
.method-put {color:var(--put);background:var(--put-bg);border:1px solid var(--put-border)}
.method-del {color:var(--del);background:var(--del-bg);border:1px solid var(--del-border)}
.endpoint-path{font-family:'JetBrains Mono',monospace;font-size:13px;color:var(--text);flex:1}
.endpoint-path .param{color:var(--accent)}
.endpoint-summary{font-size:12px;color:var(--muted);margin-left:auto;white-space:nowrap}
.endpoint-auth{font-size:11px;color:#f0883e;background:rgba(240,136,62,.12);padding:2px 8px;border-radius:12px;white-space:nowrap}
.endpoint-body{padding:20px 22px;background:var(--bg);display:none}
.endpoint-body.open{display:block}
.endpoint-desc{color:var(--sub);margin-bottom:16px;font-size:13px;line-height:1.6}

/* ── Tables ── */
.params-title{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--sub);margin-bottom:8px;margin-top:16px}
.params-title:first-child{margin-top:0}
table{width:100%;border-collapse:collapse;font-size:12.5px;margin-bottom:4px}
th{text-align:left;padding:7px 12px;background:var(--surface2);color:var(--sub);font-weight:600;font-size:11px;text-transform:uppercase;letter-spacing:.05em}
th:first-child{border-radius:6px 0 0 0}
th:last-child{border-radius:0 6px 0 0}
td{padding:8px 12px;border-top:1px solid var(--border);color:var(--text);vertical-align:top}
tr:hover td{background:rgba(255,255,255,.02)}
.param-name{font-family:'JetBrains Mono',monospace;color:var(--accent2);font-size:12px}
.param-type{font-family:'JetBrains Mono',monospace;color:var(--muted);font-size:11px}
.badge-req{background:rgba(207,34,46,.15);color:#f85149;border:1px solid rgba(207,34,46,.3);font-size:10px;padding:1px 6px;border-radius:10px;font-weight:600}
.badge-opt{background:var(--surface2);color:var(--sub);border:1px solid var(--border);font-size:10px;padding:1px 6px;border-radius:10px}

/* ── Code blocks ── */
.code-block{
  background:var(--surface);border:1px solid var(--border);border-radius:6px;
  overflow:hidden;margin-top:16px;
}
.code-block-header{
  display:flex;justify-content:space-between;align-items:center;
  padding:8px 14px;background:var(--surface2);border-bottom:1px solid var(--border);
}
.code-lang{font-size:11px;color:var(--sub);font-weight:600}
.code-copy{font-size:11px;color:var(--muted);cursor:pointer;background:none;border:none;font-family:'Inter',sans-serif;cursor:pointer;transition:.15s}
.code-copy:hover{color:var(--accent2)}
pre{padding:16px;overflow-x:auto;font-family:'JetBrains Mono',monospace;font-size:12px;line-height:1.7;color:#adbac7}
pre::-webkit-scrollbar{height:4px}
pre::-webkit-scrollbar-thumb{background:var(--border);border-radius:4px}
.kw{color:#ff7b72}.st{color:#a5d6ff}.nm{color:#79c0ff}.cm{color:#8b949e;font-style:italic}.bool{color:#f47067}.fn{color:#d2a8ff}.key{color:#7ee787}.num{color:#f2cc60}

/* ── Response codes ── */
.response-codes{display:flex;gap:8px;margin-top:12px;flex-wrap:wrap}
.res-code{
  font-size:11px;font-family:'JetBrains Mono',monospace;
  padding:3px 10px;border-radius:4px;font-weight:600;
}
.res-200{background:rgba(31,139,76,.15);color:#3fb950;border:1px solid rgba(31,139,76,.3)}
.res-201{background:rgba(31,139,76,.1);color:#56d364;border:1px solid rgba(31,139,76,.25)}
.res-401{background:rgba(154,103,0,.15);color:#d29922;border:1px solid rgba(154,103,0,.3)}
.res-403{background:rgba(207,34,46,.15);color:#f85149;border:1px solid rgba(207,34,46,.3)}
.res-422{background:rgba(154,103,0,.1);color:#e3b341;border:1px solid rgba(154,103,0,.25)}

/* ── Auth section ── */
.auth-box{
  background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);
  padding:20px;margin-bottom:20px;
}
.auth-box h3{font-size:14px;font-weight:700;margin-bottom:8px;display:flex;align-items:center;gap:8px}
.auth-box p{font-size:13px;color:var(--muted);line-height:1.7}
.info-box{
  background:rgba(88,166,255,.08);border:1px solid rgba(88,166,255,.25);
  border-radius:6px;padding:12px 16px;font-size:12.5px;color:var(--sub);
  display:flex;gap:10px;align-items:flex-start;margin-top:12px;
}
.info-box i{color:var(--accent2);margin-top:2px;flex-shrink:0}
.warn-box{
  background:rgba(240,136,62,.08);border:1px solid rgba(240,136,62,.25);
  border-radius:6px;padding:12px 16px;font-size:12.5px;color:var(--sub);
  display:flex;gap:10px;align-items:flex-start;margin-top:12px;
}
.warn-box i{color:var(--accent);margin-top:2px;flex-shrink:0}

/* ── Chevron ── */
.chevron{margin-left:auto;color:var(--sub);transition:transform .2s;font-size:12px}
.chevron.open{transform:rotate(180deg)}

/* ── Responsive ── */
@media(max-width:768px){
  body{flex-direction:column}
  .sidebar{width:100%;height:auto;position:static;min-width:unset}
  .section,.hero,.base-url-box{padding-left:20px;padding-right:20px}
  .base-url-box{margin-left:20px;margin-right:20px}
  .endpoint-summary{display:none}
}
</style>
</head>
<body>

{{-- ═══════════════════════════════════════════════════
     SIDEBAR
════════════════════════════════════════════════════ --}}
<aside class="sidebar">
  <div class="sidebar-header">
    <div class="sidebar-logo">
      <div class="icon">🚀</div>
      <div>
        <div style="display:flex;align-items:center;gap:8px">
          <span class="name">Pabalu API</span>
          <span class="version">v1.0</span>
        </div>
      </div>
    </div>
    <p class="sidebar-desc">REST API untuk aplikasi mobile kasir</p>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-group">
      <div class="nav-group-title">Mulai</div>
      <a class="nav-item active" onclick="scrollTo('intro')">
        <span class="nav-icon"><i class="fa-solid fa-book-open"></i></span>Pengantar
      </a>
      <a class="nav-item" onclick="scrollTo('auth')">
        <span class="nav-icon"><i class="fa-solid fa-key"></i></span>Autentikasi
        <span class="nav-count">3</span>
      </a>
    </div>

    <div class="nav-group">
      <div class="nav-group-title">Kasir</div>
      <a class="nav-item" onclick="scrollTo('outlets')">
        <span class="nav-icon"><i class="fa-solid fa-store"></i></span>Outlet
        <span class="nav-count">1</span>
      </a>
      <a class="nav-item" onclick="scrollTo('products')">
        <span class="nav-icon"><i class="fa-solid fa-box"></i></span>Produk (POS)
        <span class="nav-count">1</span>
      </a>
      <a class="nav-item" onclick="scrollTo('transactions')">
        <span class="nav-icon"><i class="fa-solid fa-receipt"></i></span>Transaksi
        <span class="nav-count">5</span>
      </a>
      <a class="nav-item" onclick="scrollTo('orders')">
        <span class="nav-icon"><i class="fa-solid fa-list-check"></i></span>Antrian Order
        <span class="nav-count">4</span>
      </a>
    </div>

    <div class="nav-group">
      <div class="nav-group-title">Inventori</div>
      <a class="nav-item" onclick="scrollTo('stock')">
        <span class="nav-icon"><i class="fa-solid fa-warehouse"></i></span>Stok
        <span class="nav-count">6</span>
      </a>
      <a class="nav-item" onclick="scrollTo('expenses')">
        <span class="nav-icon"><i class="fa-solid fa-money-bill-wave"></i></span>Pengeluaran
        <span class="nav-count">4</span>
      </a>
      <a class="nav-item" onclick="scrollTo('closing')">
        <span class="nav-icon"><i class="fa-solid fa-cash-register"></i></span>Closing Harian
        <span class="nav-count">1</span>
      </a>
    </div>

    <div class="nav-group">
      <div class="nav-group-title">Manajemen</div>
      <a class="nav-item" onclick="scrollTo('manage-products')">
        <span class="nav-icon"><i class="fa-solid fa-boxes-stacked"></i></span>Kelola Produk
        <span class="nav-count">4</span>
      </a>
      <a class="nav-item" onclick="scrollTo('users')">
        <span class="nav-icon"><i class="fa-solid fa-users"></i></span>Kelola User
        <span class="nav-count">4</span>
      </a>
      <a class="nav-item" onclick="scrollTo('reports')">
        <span class="nav-icon"><i class="fa-solid fa-chart-line"></i></span>Laporan
        <span class="nav-count">2</span>
      </a>
    </div>
  </nav>

  <div class="sidebar-footer">
    <div>© {{ date('Y') }} Pabalu — All rights reserved</div>
    <div style="margin-top:4px">Base URL: <code style="color:var(--accent2)">{{ url('/api') }}</code></div>
  </div>
</aside>

{{-- ═══════════════════════════════════════════════════
     MAIN CONTENT
════════════════════════════════════════════════════ --}}
<main class="main">

  {{-- Hero --}}
  <div class="hero" id="intro">
    <h1>Pabalu <span>Mobile API</span></h1>
    <p>Dokumentasi lengkap REST API untuk pengembangan aplikasi mobile kasir. Semua endpoint dilindungi dengan Laravel Sanctum Bearer Token kecuali <code style="color:var(--accent2)">POST /api/auth/login</code>.</p>
    <div class="hero-meta">
      <div class="hero-badge"><span class="dot"></span> Live — Sanctum Auth</div>
      <div class="hero-badge"><i class="fa-solid fa-code-branch" style="color:var(--accent2)"></i> REST / JSON</div>
      <div class="hero-badge"><i class="fa-solid fa-layer-group" style="color:var(--accent)"></i> 35 Endpoints</div>
      <div class="hero-badge"><i class="fa-solid fa-shield-halved" style="color:#3fb950"></i> Laravel Sanctum</div>
    </div>
  </div>

  {{-- Base URL --}}
  <div class="base-url-box" style="margin:24px 48px 0">
    <span class="label">Base URL</span>
    <span class="url" id="base-url">{{ url('/api') }}</span>
    <button class="copy-btn" onclick="copyText('base-url')"><i class="fa-regular fa-copy"></i> Copy</button>
  </div>

  {{-- ── AUTENTIKASI ── --}}
  <div class="section" id="auth">
    <div class="section-header">
      <div class="section-icon" style="background:rgba(88,166,255,.15);color:var(--accent2)"><i class="fa-solid fa-key"></i></div>
      <div>
        <h2>Autentikasi</h2>
        <p>Login, logout, dan informasi user yang sedang login</p>
      </div>
    </div>

    <div class="auth-box">
      <h3><i class="fa-solid fa-shield-halved" style="color:var(--accent2)"></i> Laravel Sanctum — Bearer Token</h3>
      <p>Setelah login berhasil, gunakan token yang dikembalikan sebagai <strong>Bearer Token</strong> di header setiap request ke endpoint yang membutuhkan autentikasi.</p>
      <div class="info-box">
        <i class="fa-solid fa-circle-info"></i>
        <div><strong>Header wajib (semua endpoint kecuali login):</strong><br>
        <code style="font-family:'JetBrains Mono',monospace;color:var(--accent2)">Authorization: Bearer {token}</code><br>
        <code style="font-family:'JetBrains Mono',monospace;color:var(--accent2)">Accept: application/json</code></div>
      </div>
    </div>

    @php
    $authEndpoints = [
      ['POST','login','/auth/login','Login & dapatkan token','Login menggunakan email dan password. Token yang dikembalikan digunakan untuk semua request berikutnya.',false,[
        ['email','string','required','Email user yang terdaftar','kasir@outlet.com'],
        ['password','string','required','Password user','password123'],
      ],'{"token":"1|abc...xyz","token_type":"Bearer","user":{"id":1,"name":"Budi Kasir","email":"kasir@outlet.com","roles":["kasir"],"outlet_id":2}}','201'],
      ['POST','logout','/auth/logout','Logout & hapus token','Menghapus token saat ini sehingga tidak bisa digunakan lagi.',true,[],'{"message":"Logout berhasil."}','200'],
      ['GET','me','/auth/me','Info user yang login','Mengembalikan data user yang sedang login beserta outlet dan role-nya.',true,[],'{"id":1,"name":"Budi Kasir","email":"kasir@outlet.com","roles":["kasir"],"outlet_id":2,"outlet_nama":"Outlet Utama","permissions":["transaction.create","expense.create"]}','200'],
    ];
    @endphp

    @foreach($authEndpoints as $ep)
      @include('api._endpoint', ['ep' => $ep])
    @endforeach
  </div>

  {{-- ── OUTLET ── --}}
  <div class="section" id="outlets">
    <div class="section-header">
      <div class="section-icon" style="background:rgba(240,136,62,.15);color:var(--accent)"><i class="fa-solid fa-store"></i></div>
      <div><h2>Outlet</h2><p>Daftar outlet yang dapat diakses user yang sedang login</p></div>
    </div>

    @php
    $outletEndpoints = [
      ['GET','outlets','/outlets','Daftar outlet yang bisa diakses','Mengembalikan semua outlet aktif yang bisa diakses oleh user login. Kasir mendapat 1 outlet, Owner mendapat semua outlet miliknya.',true,[],'[{"id":1,"nama":"Outlet A","alamat":"Jl. Merdeka No. 1","telepon":"08123456789"},{"id":2,"nama":"Outlet B","alamat":"Jl. Sudirman No. 5","telepon":"08987654321"}]','200'],
    ];
    @endphp

    @foreach($outletEndpoints as $ep)
      @include('api._endpoint', ['ep' => $ep])
    @endforeach
  </div>

  {{-- ── PRODUK POS ── --}}
  <div class="section" id="products">
    <div class="section-header">
      <div class="section-icon" style="background:rgba(88,166,255,.15);color:var(--accent2)"><i class="fa-solid fa-box"></i></div>
      <div><h2>Produk (POS)</h2><p>Daftar produk aktif dengan stok realtime untuk keperluan kasir POS</p></div>
    </div>

    @php
    $productPosEndpoints = [
      ['GET','products','/products','Produk aktif + stok realtime','Mengembalikan semua produk aktif di outlet beserta stok hari ini. Digunakan di halaman POS kasir.',true,[
        ['outlet_id','integer','required','ID outlet','1'],
      ],'{"products":[{"id":1,"nama":"Ayam Geprek","harga":15000,"stok":8,"category_id":1,"category":"Makanan","foto":"http://app.test/storage/products/ayam.jpg"}],"categories":["Makanan","Minuman"]}','200'],
    ];
    @endphp

    @foreach($productPosEndpoints as $ep)
      @include('api._endpoint', ['ep' => $ep])
    @endforeach
  </div>

  {{-- ── TRANSAKSI ── --}}
  <div class="section" id="transactions">
    <div class="section-header">
      <div class="section-icon" style="background:rgba(31,139,76,.15);color:#3fb950"><i class="fa-solid fa-receipt"></i></div>
      <div><h2>Transaksi</h2><p>Proses penjualan POS — simpan transaksi, struk, riwayat, dan pembayaran gateway</p></div>
    </div>

    @php
    $trxEndpoints = [
      ['GET','trx-config','/transactions/config','Konfigurasi pembayaran outlet','Mengembalikan metode pembayaran aktif dan Midtrans client key untuk outlet. Panggil ini sebelum membuka layar POS.',true,[
        ['outlet_id','integer','required','ID outlet','1'],
      ],'{"active_methods":["tunai","qris","transfer"],"midtrans_client_key":"Mid-client-xxx","midtrans_snap_url":"https://app.sandbox.midtrans.com/snap/snap.js"}','200'],
      ['POST','trx-snap','/transactions/snap-token','Generate Snap Token Midtrans','Membuat sesi pembayaran Midtrans untuk metode gateway. Token dikembalikan ke mobile untuk ditampilkan ke Snap SDK.',true,[
        ['outlet_id','integer','required','ID outlet','1'],
        ['items','array','required','Array item yang dibeli','[...]'],
        ['items.*.product_id','integer','required','ID produk','1'],
        ['items.*.nama','string','required','Nama produk','Ayam Geprek'],
        ['items.*.harga','numeric','required','Harga satuan','15000'],
        ['items.*.qty','integer','required','Jumlah beli','2'],
        ['items.*.subtotal','numeric','required','Subtotal item','30000'],
      ],'{"snap_token":"token-xxx","order_id":"POS-20241201-1234"}','200'],
      ['POST','trx-store','/transactions','Simpan transaksi','Menyimpan transaksi baru. Validasi stok dilakukan otomatis. Stok akan dikurangi sesuai qty yang terjual.',true,[
        ['outlet_id','integer','required','ID outlet (auto-fill untuk kasir)','1'],
        ['metode_bayar','string','required','Metode: tunai / qris / transfer / gateway','tunai'],
        ['bayar','numeric','required jika tunai','Jumlah uang yang dibayarkan','50000'],
        ['items','array','required','Daftar item yang dibeli','[...]'],
        ['items.*.product_id','integer','required','ID produk','1'],
        ['items.*.nama','string','required','Nama produk','Ayam Geprek'],
        ['items.*.harga','numeric','required','Harga satuan','15000'],
        ['items.*.qty','integer','required','Jumlah beli','2'],
        ['items.*.subtotal','numeric','required','Subtotal item','30000'],
        ['keterangan','string','optional','Catatan transaksi','Meja 3'],
        ['payment_ref','string','required jika gateway','Order ID dari Midtrans','POS-20241201-1234'],
      ],'{"success":true,"id":42,"nomor":"TRX-001-20241201-001","total":30000,"bayar":50000,"kembalian":20000,"metode":"tunai","tanggal":"2024-12-01"}','201'],
      ['GET','trx-show','/transactions/{transaction}','Detail transaksi (struk)','Mengembalikan detail transaksi lengkap termasuk outlet, kasir, dan item. Kasir hanya bisa melihat transaksi miliknya sendiri.',true,[],'{"id":42,"nomor_transaksi":"TRX-001-20241201-001","tanggal":"2024-12-01","outlet":{...},"kasir":{...},"metode_bayar":"tunai","total":30000,"bayar":50000,"kembalian":20000,"items":[...]}','200'],
      ['GET','trx-index','/transactions','Riwayat transaksi hari ini','Mengembalikan riwayat transaksi hari ini. Kasir hanya melihat transaksinya sendiri.',true,[
        ['outlet_id','integer','optional','ID outlet','1'],
        ['tanggal','date','optional','Filter tanggal (default: hari ini)','2024-12-01'],
      ],'[{"id":42,"nomor_transaksi":"TRX-001-20241201-001","outlet":"Outlet A","total":30000,"metode_bayar":"tunai","status":"paid","items_count":2}]','200'],
    ];
    @endphp

    @foreach($trxEndpoints as $ep)
      @include('api._endpoint', ['ep' => $ep])
    @endforeach
  </div>

  {{-- ── STOK ── --}}
  <div class="section" id="stock">
    <div class="section-header">
      <div class="section-icon" style="background:rgba(154,103,0,.15);color:#d29922"><i class="fa-solid fa-warehouse"></i></div>
      <div><h2>Stok</h2><p>Opening stok, tambah stok masuk, waste, dan riwayat pergerakan</p></div>
    </div>

    @php
    $stockEndpoints = [
      ['GET','stock-index','/stock','Stok saat ini per produk','Mengembalikan stok realtime setiap produk di outlet untuk tanggal hari ini.',true,[
        ['outlet_id','integer','required','ID outlet','1'],
      ],'[{"id":1,"nama":"Ayam Geprek","category":"Makanan","stok":8}]','200'],
      ['GET','stock-opening-get','/stock/opening','Form opening stok','Mengembalikan produk beserta qty opening yang sudah diinput hari ini (jika ada). Digunakan untuk pre-fill form opening.',true,[
        ['outlet_id','integer','required','ID outlet','1'],
      ],'{"tanggal":"2024-12-01","products":[{"id":1,"nama":"Ayam Geprek","category":"Makanan","qty_opening":10,"stok_sekarang":8}]}','200'],
      ['POST','stock-opening-post','/stock/opening','Simpan opening stok','Menyimpan stok awal hari ini per produk. Menggunakan updateOrCreate — aman dipanggil ulang.',true,[
        ['outlet_id','integer','required','ID outlet (auto-fill untuk kasir)','1'],
        ['tanggal','date','required','Tanggal opening','2024-12-01'],
        ['items','array','required','Array produk','[...]'],
        ['items.*.product_id','integer','required','ID produk','1'],
        ['items.*.qty','integer','required','Qty opening (0 = lewati)','10'],
        ['items.*.keterangan','string','optional','Catatan','Stok awal pagi'],
      ],'{"message":"Opening stok berhasil disimpan."}','201'],
      ['POST','stock-in','/stock/in','Tambah stok masuk','Mencatat penambahan stok (kiriman / restok).',true,[
        ['outlet_id','integer','required','ID outlet (auto-fill untuk kasir)','1'],
        ['product_id','integer','required','ID produk','1'],
        ['tanggal','date','required','Tanggal','2024-12-01'],
        ['qty','integer','required','Jumlah stok masuk (min 1)','20'],
        ['keterangan','string','optional','Catatan','Kiriman supplier'],
      ],'{"message":"Tambah stok berhasil disimpan.","id":15}','201'],
      ['POST','stock-waste','/stock/waste','Catat barang rusak / waste','Mencatat stok yang terbuang, rusak, atau tidak layak jual.',true,[
        ['outlet_id','integer','required','ID outlet (auto-fill untuk kasir)','1'],
        ['product_id','integer','required','ID produk','1'],
        ['tanggal','date','required','Tanggal','2024-12-01'],
        ['qty','integer','required','Jumlah yang dibuang (min 1)','2'],
        ['keterangan','string','optional','Catatan','Basi'],
      ],'{"message":"Waste berhasil disimpan.","id":16}','201'],
      ['GET','stock-history','/stock/history','Riwayat pergerakan stok','Mengembalikan riwayat gerakan stok dengan filter opsional. Dibatasi 100 record terbaru.',true,[
        ['outlet_id','integer','required','ID outlet','1'],
        ['type','string','optional','Filter tipe: opening / in / waste','in'],
        ['product_id','integer','optional','Filter produk','1'],
        ['date_from','date','optional','Dari tanggal','2024-12-01'],
        ['date_to','date','optional','Sampai tanggal','2024-12-31'],
      ],'[{"id":15,"type":"in","product":"Ayam Geprek","qty":20,"tanggal":"2024-12-01","keterangan":"Kiriman supplier","user":"Admin"}]','200'],
    ];
    @endphp

    @foreach($stockEndpoints as $ep)
      @include('api._endpoint', ['ep' => $ep])
    @endforeach
  </div>

  {{-- ── PENGELUARAN ── --}}
  <div class="section" id="expenses">
    <div class="section-header">
      <div class="section-icon" style="background:rgba(207,34,46,.12);color:#f85149"><i class="fa-solid fa-money-bill-wave"></i></div>
      <div><h2>Pengeluaran</h2><p>CRUD pengeluaran operasional harian outlet</p></div>
    </div>

    @php
    $expenseKat = 'operasional / bahan_baku / gaji / utilitas / promosi / peralatan / lainnya';
    $expenseEndpoints = [
      ['GET','expense-index','/expenses','Daftar pengeluaran','Mengembalikan semua pengeluaran di outlet untuk tanggal yang dipilih.',true,[
        ['outlet_id','integer','required','ID outlet','1'],
        ['tanggal','date','optional','Tanggal (default: hari ini)','2024-12-01'],
        ['kategori','string','optional','Filter kategori','operasional'],
      ],'{"tanggal":"2024-12-01","total":75000,"expenses":[{"id":1,"tanggal":"2024-12-01","kategori":"operasional","keterangan":"Beli sabun cuci","jumlah":25000,"user":"Budi"}],"kategori_list":{"operasional":"Operasional",...}}','200'],
      ['POST','expense-store','/expenses','Simpan pengeluaran','Mencatat pengeluaran baru untuk outlet.',true,[
        ['outlet_id','integer','required','ID outlet (auto-fill untuk kasir)','1'],
        ['tanggal','date','required','Tanggal pengeluaran','2024-12-01'],
        ['kategori','string','required',$expenseKat,'operasional'],
        ['keterangan','string','optional','Deskripsi pengeluaran','Beli sabun cuci'],
        ['jumlah','numeric','required','Nominal (min 1)','25000'],
      ],'{"message":"Pengeluaran berhasil disimpan.","id":5}','201'],
      ['PUT','expense-update','/expenses/{expense}','Update pengeluaran','Memperbarui data pengeluaran yang sudah ada.',true,[
        ['tanggal','date','required','Tanggal pengeluaran','2024-12-01'],
        ['kategori','string','required',$expenseKat,'bahan_baku'],
        ['keterangan','string','optional','Deskripsi','Tepung terigu'],
        ['jumlah','numeric','required','Nominal','30000'],
      ],'{"message":"Pengeluaran berhasil diperbarui."}','200'],
      ['DELETE','expense-destroy','/expenses/{expense}','Hapus pengeluaran','Menghapus catatan pengeluaran.',true,[],'{"message":"Pengeluaran berhasil dihapus."}','200'],
    ];
    @endphp

    @foreach($expenseEndpoints as $ep)
      @include('api._endpoint', ['ep' => $ep])
    @endforeach
  </div>

  {{-- ── ANTRIAN ORDER ── --}}
  <div class="section" id="orders">
    <div class="section-header">
      <div class="section-icon" style="background:rgba(88,166,255,.12);color:var(--accent2)"><i class="fa-solid fa-list-check"></i></div>
      <div><h2>Antrian Order</h2><p>Kelola antrian order dari pelanggan online — advance status, cancel, dan polling notifikasi</p></div>
    </div>

    <div class="warn-box">
      <i class="fa-solid fa-triangle-exclamation"></i>
      <div>Order dibuat oleh pelanggan dari halaman order publik (<code>/order/{slug}</code>). API ini hanya untuk <strong>membaca dan mengelola status</strong> order yang masuk, bukan membuat order baru.</div>
    </div>

    @php
    $orderEndpoints = [
      ['GET','order-index','/orders','Daftar antrian order','Mengembalikan antrian order aktif. Filter status: active (pending+processing+ready), all, atau status spesifik.',true,[
        ['outlet_id','integer','required','ID outlet','1'],
        ['status','string','optional','active (default) / all / pending / processing / ready / completed / cancelled','active'],
      ],'{"stats":{"pending":2,"processing":1,"ready":0},"orders":[{"id":1,"order_number":"ORD-20241201-001","customer_name":"Andi","subtotal":30000,"order_status":"pending","status_label":"Menunggu","next_status":"processing","next_label":"Proses","items":[...]}]}','200'],
      ['GET','order-poll','/orders/poll','Polling order baru','Endpoint ringan untuk cek apakah ada order baru sejak timestamp tertentu. Cocok untuk polling interval pendek dari mobile.',true,[
        ['outlet_id','integer','required','ID outlet','1'],
        ['since','datetime','optional','ISO timestamp terakhir poll','2024-12-01T10:00:00Z'],
      ],'{"new_count":1,"pending":3,"now":"2024-12-01T10:05:00Z"}','200'],
      ['POST','order-advance','/orders/{order}/advance','Advance status order','Memajukan status order ke tahap berikutnya: pending→processing→ready→completed.',true,[],'{"message":"Order ORD-20241201-001 → Diproses","order":{...}}','200'],
      ['POST','order-cancel','/orders/{order}/cancel','Batalkan order','Membatalkan order yang belum selesai.',true,[],'{"message":"Order ORD-20241201-001 dibatalkan."}','200'],
    ];
    @endphp

    @foreach($orderEndpoints as $ep)
      @include('api._endpoint', ['ep' => $ep])
    @endforeach
  </div>

  {{-- ── CLOSING ── --}}
  <div class="section" id="closing">
    <div class="section-header">
      <div class="section-icon" style="background:rgba(31,139,76,.12);color:#3fb950"><i class="fa-solid fa-cash-register"></i></div>
      <div><h2>Closing Harian</h2><p>Ringkasan lengkap akhir hari — omzet, pengeluaran, laba kotor, dan rekap stok</p></div>
    </div>

    @php
    $closingEndpoints = [
      ['GET','closing','/closing','Ringkasan closing harian','Mengembalikan rangkuman lengkap untuk closing: omzet, jumlah transaksi, total pengeluaran, laba kotor, breakdown per metode bayar, dan rekap stok per produk.',true,[
        ['outlet_id','integer','required','ID outlet','1'],
        ['tanggal','date','optional','Tanggal (default: hari ini)','2024-12-01'],
      ],'{"tanggal":"2024-12-01","omzet":500000,"total_transaksi":15,"total_expense":75000,"laba_kotor":425000,"per_metode":{"tunai":{"jumlah":10,"total":350000},"qris":{"jumlah":5,"total":150000}},"expense_per_kategori":{"operasional":50000,"bahan_baku":25000},"stock_summary":[{"product_id":1,"nama":"Ayam Geprek","opening":20,"in":0,"waste":1,"sold":11,"akhir":8}]}','200'],
    ];
    @endphp

    @foreach($closingEndpoints as $ep)
      @include('api._endpoint', ['ep' => $ep])
    @endforeach
  </div>

  {{-- ── KELOLA PRODUK ── --}}
  <div class="section" id="manage-products">
    <div class="section-header">
      <div class="section-icon" style="background:rgba(240,136,62,.12);color:var(--accent)"><i class="fa-solid fa-boxes-stacked"></i></div>
      <div><h2>Kelola Produk</h2><p>CRUD produk — tambah, edit, hapus, dan upload foto</p></div>
    </div>

    @php
    $mProductEndpoints = [
      ['GET','mp-index','/manage/products','Daftar semua produk','Mengembalikan semua produk (aktif + nonaktif) untuk keperluan manajemen. Mendukung filter pencarian.',true,[
        ['outlet_id','integer','required','ID outlet','1'],
        ['q','string','optional','Cari nama atau kode produk','ayam'],
      ],'{"products":[{"id":1,"kode":"PRD-001","nama":"Ayam Geprek","harga":15000,"satuan":"porsi","is_active":true,"category":"Makanan","foto":null,"deskripsi":"Ayam geprek pedas"}],"categories":[{"id":1,"nama":"Makanan"}]}','200'],
      ['POST','mp-store','/manage/products','Tambah produk','Membuat produk baru. Mendukung upload foto (multipart/form-data).',true,[
        ['outlet_id','integer','required','ID outlet','1'],
        ['nama','string','required','Nama produk','Ayam Geprek Spesial'],
        ['harga_jual','numeric','required','Harga jual','15000'],
        ['satuan','string','required','Satuan porsi/pcs/kg/dll','porsi'],
        ['category_id','integer','optional','ID kategori','1'],
        ['kode','string','optional','Kode SKU produk','PRD-002'],
        ['deskripsi','string','optional','Deskripsi produk',''],
        ['is_active','boolean','optional','Status aktif (default: true)','1'],
        ['gambar','file','optional','Foto produk (jpg/png, max 2MB)',''],
      ],'{"message":"Produk \"Ayam Geprek Spesial\" berhasil ditambahkan.","id":5}','201'],
      ['PUT','mp-update','/manage/products/{product}','Update produk','Memperbarui produk. Kirim `hapus_gambar=1` untuk menghapus foto.',true,[
        ['nama','string','required','Nama produk','Ayam Geprek Special'],
        ['harga_jual','numeric','required','Harga jual','17000'],
        ['satuan','string','required','Satuan','porsi'],
        ['category_id','integer','optional','ID kategori','1'],
        ['is_active','boolean','optional','Status aktif','1'],
        ['gambar','file','optional','Foto baru (replace)',''],
        ['hapus_gambar','boolean','optional','Set 1 untuk hapus foto','0'],
      ],'{"message":"Produk \"Ayam Geprek Special\" berhasil diperbarui."}','200'],
      ['DELETE','mp-destroy','/manage/products/{product}','Hapus produk','Menghapus produk dan foto-nya dari storage.',true,[],'{"message":"Produk \"Ayam Geprek Special\" berhasil dihapus."}','200'],
    ];
    @endphp

    <div class="info-box" style="margin-bottom:20px">
      <i class="fa-solid fa-circle-info"></i>
      <div>Untuk upload foto, gunakan <strong>multipart/form-data</strong> bukan <code>application/json</code>. Endpoint lain cukup menggunakan <code>application/json</code>.</div>
    </div>

    @foreach($mProductEndpoints as $ep)
      @include('api._endpoint', ['ep' => $ep])
    @endforeach
  </div>

  {{-- ── KELOLA USER ── --}}
  <div class="section" id="users">
    <div class="section-header">
      <div class="section-icon" style="background:rgba(88,166,255,.12);color:var(--accent2)"><i class="fa-solid fa-users"></i></div>
      <div><h2>Kelola User</h2><p>CRUD user staff — hanya Owner dan Admin yang dapat mengelola user</p></div>
    </div>

    <div class="warn-box" style="margin-bottom:20px">
      <i class="fa-solid fa-triangle-exclamation"></i>
      <div>Owner hanya bisa mengelola user di outlet miliknya, dan hanya bisa assign role <strong>kasir</strong>. Admin bisa mengelola semua user.</div>
    </div>

    @php
    $userEndpoints = [
      ['GET','user-index','/users','Daftar user','Mengembalikan daftar user sesuai scope akses caller.',true,[
        ['q','string','optional','Cari nama atau email','budi'],
        ['role','string','optional','Filter role: kasir / owner / admin','kasir'],
      ],'{"users":[{"id":2,"name":"Budi Kasir","email":"budi@outlet.com","outlet_id":1,"roles":["kasir"],"is_active":true,"jabatan":"Kasir","no_hp":"08123456789"}],"available_roles":["kasir"]}','200'],
      ['POST','user-store','/users','Tambah user','Membuat user baru. Email langsung terverifikasi.',true,[
        ['name','string','required','Nama lengkap','Budi Kasir'],
        ['email','string','required','Email (unique)','budi@outlet.com'],
        ['password','string','required','Password (min 8 karakter)','password123'],
        ['role','string','optional','Role yang diassign','kasir'],
        ['outlet_id','integer','optional','Outlet default user','1'],
        ['jabatan','string','optional','Jabatan','Kasir'],
        ['no_hp','string','optional','Nomor HP','08123456789'],
      ],'{"message":"User \"Budi Kasir\" berhasil ditambahkan.","id":10}','201'],
      ['PUT','user-update','/users/{user}','Update user','Memperbarui data user. Password hanya diubah jika field `password` diisi.',true,[
        ['name','string','required','Nama lengkap','Budi Utama'],
        ['email','string','required','Email','budi@outlet.com'],
        ['password','string','optional','Password baru (opsional)','newpassword'],
        ['role','string','optional','Role baru','kasir'],
        ['outlet_id','integer','optional','Outlet','1'],
        ['jabatan','string','optional','Jabatan','Senior Kasir'],
        ['no_hp','string','optional','Nomor HP','08123456789'],
      ],'{"message":"User \"Budi Utama\" berhasil diperbarui."}','200'],
      ['DELETE','user-destroy','/users/{user}','Hapus user','Menghapus user. Tidak bisa menghapus diri sendiri atau user dengan role admin.',true,[],'{"message":"User \"Budi Utama\" berhasil dihapus."}','200'],
    ];
    @endphp

    @foreach($userEndpoints as $ep)
      @include('api._endpoint', ['ep' => $ep])
    @endforeach
  </div>

  {{-- ── LAPORAN ── --}}
  <div class="section" id="reports">
    <div class="section-header">
      <div class="section-icon" style="background:rgba(31,139,76,.12);color:#3fb950"><i class="fa-solid fa-chart-line"></i></div>
      <div><h2>Laporan</h2><p>Laporan penjualan dan laba rugi dengan rentang tanggal</p></div>
    </div>

    @php
    $reportEndpoints = [
      ['GET','report-sales','/reports/sales','Laporan penjualan','Mengembalikan ringkasan penjualan per hari dan per produk untuk rentang tanggal. Kasir hanya melihat transaksi miliknya sendiri.',true,[
        ['outlet_id','integer','required','ID outlet','1'],
        ['date_from','date','optional','Dari tanggal (default: hari ini)','2024-12-01'],
        ['date_to','date','optional','Sampai tanggal (default: hari ini)','2024-12-31'],
      ],'{"date_from":"2024-12-01","date_to":"2024-12-31","total_omzet":5000000,"total_transaksi":150,"per_hari":[{"tanggal":"2024-12-01","jumlah":15,"omzet":500000}],"per_produk":[{"nama":"Ayam Geprek","total_qty":120,"total_subtotal":1800000}]}','200'],
      ['GET','report-pl','/reports/profit-loss','Laporan laba & rugi','Mengembalikan omzet, pengeluaran, dan laba bersih per hari dalam rentang tanggal. Kasir tidak melihat data pengeluaran.',true,[
        ['outlet_id','integer','required','ID outlet','1'],
        ['date_from','date','optional','Dari tanggal (default: awal bulan ini)','2024-12-01'],
        ['date_to','date','optional','Sampai tanggal (default: hari ini)','2024-12-31'],
      ],'{"date_from":"2024-12-01","date_to":"2024-12-31","total_omzet":5000000,"total_expense":750000,"total_laba":4250000,"per_hari":[{"tanggal":"2024-12-01","omzet":500000,"expense":75000,"laba":425000}],"expense_per_kategori":[{"kategori":"bahan_baku","total":400000}]}','200'],
    ];
    @endphp

    @foreach($reportEndpoints as $ep)
      @include('api._endpoint', ['ep' => $ep])
    @endforeach
  </div>

  <div style="height:60px"></div>
</main>

<script>
function scrollTo(id){
  document.getElementById(id)?.scrollIntoView({behavior:'smooth',block:'start'});
  document.querySelectorAll('.nav-item').forEach(el=>el.classList.remove('active'));
  event.currentTarget.classList.add('active');
}

function toggleEndpoint(id){
  const header = document.getElementById('hdr-'+id);
  const body   = document.getElementById('body-'+id);
  const chev   = document.getElementById('chev-'+id);
  const isOpen = body.classList.contains('open');
  body.classList.toggle('open');
  header.classList.toggle('open');
  chev.classList.toggle('open');
}

function copyText(id){
  const text = document.getElementById(id)?.innerText;
  if(!text) return;
  navigator.clipboard.writeText(text).then(()=>{
    const btn = event.currentTarget;
    const orig = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-check"></i> Copied!';
    btn.style.color = '#3fb950';
    setTimeout(()=>{btn.innerHTML=orig;btn.style.color=''},1500);
  });
}

function copyCode(id){
  const text = document.getElementById(id)?.innerText;
  if(!text) return;
  navigator.clipboard.writeText(text).then(()=>{
    const btn = event.currentTarget;
    const orig = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-check"></i> Copied!';
    btn.style.color = '#3fb950';
    setTimeout(()=>{btn.innerHTML=orig;btn.style.color=''},1500);
  });
}

// Highlight active nav on scroll
const sections = document.querySelectorAll('.section,[id]');
const navItems = document.querySelectorAll('.nav-item');
window.addEventListener('scroll', ()=>{
  const main = document.querySelector('.main');
  // simple: just track which section is near top
}, {passive:true});
</script>
</body>
</html>
