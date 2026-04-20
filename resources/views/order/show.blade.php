<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
  <title>Order Online — {{ $outlet->nama }}</title>
  <x-seo
      title="Order Online — {{ $outlet->nama }}"
      description="Pesan menu dari {{ $outlet->nama }} secara online. Cek menu lengkap dan lakukan pemesanan dengan mudah."
      url="{{ url()->current() }}"
  />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  @if($paymentEnabled)
  <script src="{{ $midtransProduction ? 'https://app.midtrans.com' : 'https://app.sandbox.midtrans.com' }}/snap/snap.js"
    data-client-key="{{ $midtransClientKey }}"></script>
  @endif
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --ac:     #6366f1;
      --ac-lt:  #6366f115;
      --bg:     #0f1117;
      --surface:#1a1d27;
      --surf2:  #22263a;
      --border: #2e3349;
      --text:   #e8eaf0;
      --sub:    #9ca3af;
      --muted:  #6b7280;
      --green:  #34d399;
      --amber:  #fbbf24;
      --red:    #f87171;
    }

    body {
      font-family: 'Inter', sans-serif;
      background: var(--bg);
      color: var(--text);
      min-height: 100vh;
      padding-bottom: 140px;
    }

    /* ── Header ── */
    .header {
      background: var(--surface);
      border-bottom: 1px solid var(--border);
      padding: 14px 16px;
      position: sticky; top: 0; z-index: 50;
    }
    .header-inner {
      max-width: 600px; margin: 0 auto;
      display: flex; align-items: center; gap: 12px;
    }
    .outlet-avatar {
      width: 38px; height: 38px; border-radius: 10px;
      background: var(--ac-lt); color: var(--ac);
      display: grid; place-items: center; font-size: 16px; flex-shrink: 0;
    }
    .outlet-name { font-size: 15px; font-weight: 700; color: var(--text); }
    .outlet-sub  { font-size: 12px; color: var(--muted); margin-top: 1px; }

    /* ── Search & Category ── */
    .search-wrap {
      max-width: 600px; margin: 12px auto; padding: 0 16px;
    }
    .search-input {
      width: 100%; background: var(--surface); border: 1px solid var(--border);
      border-radius: 12px; padding: 10px 14px 10px 38px;
      color: var(--text); font-size: 14px; font-family: inherit; outline: none;
      transition: border-color .15s;
    }
    .search-input:focus { border-color: var(--ac); }
    .search-icon { position: absolute; left: 26px; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: 13px; pointer-events: none; }

    .cats {
      max-width: 600px; margin: 8px auto; padding: 0 16px;
      display: flex; gap: 6px; overflow-x: auto;
      scrollbar-width: none;
    }
    .cats::-webkit-scrollbar { display: none; }
    .cat-btn {
      padding: 5px 14px; border-radius: 99px; font-size: 12.5px; font-weight: 600;
      border: 1.5px solid var(--border); background: var(--surface);
      color: var(--sub); cursor: pointer; white-space: nowrap;
      font-family: inherit; transition: all .15s;
    }
    .cat-btn.active {
      border-color: var(--ac); background: var(--ac-lt); color: var(--ac);
    }

    /* ── Product grid ── */
    .products {
      max-width: 600px; margin: 0 auto; padding: 4px 16px 8px;
      display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;
    }
    @media(min-width:480px) {
      .products { grid-template-columns: repeat(3, 1fr); }
    }

    .prod-card {
      background: var(--surface); border: 1.5px solid var(--border);
      border-radius: 14px; padding: 12px; cursor: pointer;
      transition: border-color .15s, transform .1s;
      user-select: none;
    }
    .prod-card:active { transform: scale(.97); }
    .prod-card.active { border-color: var(--ac); }
    .prod-card.out-of-stock { opacity: .4; pointer-events: none; }

    .prod-thumb {
      width: 100%; aspect-ratio: 4/3; border-radius: 10px; margin-bottom: 8px;
      object-fit: cover; background: var(--surf2);
    }
    .prod-thumb-placeholder {
      width: 100%; aspect-ratio: 4/3; border-radius: 10px; margin-bottom: 8px;
      background: var(--ac-lt); color: var(--ac);
      display: grid; place-items: center; font-size: 26px;
    }
    .prod-icon {
      width: 36px; height: 36px; border-radius: 10px;
      background: var(--ac-lt); color: var(--ac);
      display: grid; place-items: center; font-size: 15px; margin-bottom: 8px;
    }
    .prod-name  { font-size: 13px; font-weight: 600; color: var(--text); line-height: 1.3; margin-bottom: 4px; }
    .prod-price { font-size: 13.5px; font-weight: 700; color: var(--ac); }
    .prod-stok  { font-size: 11px; color: var(--muted); margin-top: 3px; }

    .qty-ctrl {
      display: flex; align-items: center; justify-content: space-between;
      margin-top: 8px;
    }
    .qty-btn {
      width: 26px; height: 26px; border-radius: 8px; border: none;
      display: grid; place-items: center; cursor: pointer; font-size: 14px;
      font-weight: 700; font-family: inherit; transition: background .1s;
    }
    .qty-btn.minus { background: var(--surf2); color: var(--sub); }
    .qty-btn.plus  { background: var(--ac); color: #fff; }
    .qty-num { font-size: 14px; font-weight: 700; color: var(--text); min-width: 22px; text-align: center; }

    /* ── Bottom cart bar ── */
    .cart-bar {
      position: fixed; bottom: 0; left: 0; right: 0; z-index: 60;
      background: var(--surface); border-top: 1px solid var(--border);
      padding: 12px 16px; transition: transform .25s;
    }
    .cart-bar.hidden { transform: translateY(100%); }
    .cart-bar-inner {
      max-width: 600px; margin: 0 auto;
      display: flex; align-items: center; gap: 12px;
    }
    .cart-info { flex: 1; }
    .cart-qty   { font-size: 12px; color: var(--muted); }
    .cart-total { font-size: 18px; font-weight: 700; color: var(--ac); }
    .btn-checkout {
      background: var(--ac); color: #fff; border: none; border-radius: 12px;
      padding: 11px 22px; font-size: 14px; font-weight: 700;
      font-family: inherit; cursor: pointer; white-space: nowrap;
      transition: opacity .15s;
    }
    .btn-checkout:hover { opacity: .88; }

    /* ── Checkout modal ── */
    .backdrop {
      display: none; position: fixed; inset: 0; z-index: 100;
      background: rgba(0,0,0,.75); backdrop-filter: blur(6px);
      align-items: flex-end; justify-content: center;
      opacity: 0; transition: opacity .2s;
    }
    .backdrop.show { display: flex; }
    .modal {
      background: var(--surface); border: 1px solid var(--border);
      border-radius: 20px 20px 0 0; width: 100%; max-width: 600px;
      max-height: 92vh; overflow-y: auto;
      transform: translateY(30px); opacity: 0;
      transition: transform .25s, opacity .25s;
    }
    @media(min-width:640px) {
      .backdrop { align-items: center; padding: 20px; }
      .modal { border-radius: 20px; max-height: 85vh; }
    }
    .modal.open { transform: translateY(0); opacity: 1; }
    .modal-header {
      padding: 16px 20px; border-bottom: 1px solid var(--border);
      display: flex; align-items: center; justify-content: space-between;
      position: sticky; top: 0; background: var(--surface); z-index: 1;
    }
    .modal-title { font-size: 16px; font-weight: 700; color: var(--text); }
    .btn-close {
      width: 32px; height: 32px; border-radius: 8px;
      border: 1px solid var(--border); background: var(--surf2);
      color: var(--muted); cursor: pointer; font-size: 13px; font-family: inherit;
    }
    .modal-body { padding: 16px 20px; }

    /* ── Form fields ── */
    .f-group { margin-bottom: 14px; }
    .f-label { display: block; font-size: 12.5px; font-weight: 600; color: var(--sub); margin-bottom: 5px; }
    .f-input {
      width: 100%; background: var(--surf2); border: 1.5px solid var(--border);
      border-radius: 10px; padding: 10px 12px; color: var(--text);
      font-size: 14px; font-family: inherit; outline: none; transition: border-color .15s;
    }
    .f-input:focus { border-color: var(--ac); }

    /* ── Order summary in modal ── */
    .order-summary {
      background: var(--surf2); border-radius: 12px;
      padding: 12px 14px; margin-bottom: 16px;
    }
    .order-row {
      display: flex; justify-content: space-between;
      font-size: 13px; margin-bottom: 6px;
    }
    .order-row:last-child { margin-bottom: 0; }
    .order-row .name { color: var(--sub); }
    .order-row .val  { font-weight: 600; color: var(--text); }
    .order-total-row {
      display: flex; justify-content: space-between;
      padding-top: 10px; border-top: 1px solid var(--border); margin-top: 8px;
    }
    .order-total-row .label { font-size: 14px; font-weight: 600; color: var(--sub); }
    .order-total-row .total { font-size: 18px; font-weight: 700; color: var(--ac); }

    .btn-submit {
      width: 100%; background: var(--ac); color: #fff; border: none;
      border-radius: 12px; padding: 13px; font-size: 15px; font-weight: 700;
      font-family: inherit; cursor: pointer; margin-top: 4px; transition: opacity .15s;
    }
    .btn-submit:hover { opacity: .88; }
    .btn-submit:disabled { opacity: .5; cursor: not-allowed; }

    /* ── Success screen ── */
    .success-wrap {
      display: none; text-align: center; padding: 20px;
    }
    .success-icon {
      width: 72px; height: 72px; border-radius: 50%;
      background: rgba(52,211,153,.15); margin: 0 auto 18px;
      display: grid; place-items: center;
    }
    .success-title { font-size: 20px; font-weight: 700; color: var(--text); margin-bottom: 6px; }
    .success-sub   { font-size: 13.5px; color: var(--sub); margin-bottom: 20px; line-height: 1.5; }
    .order-number-badge {
      display: inline-block; background: var(--ac-lt); color: var(--ac);
      border: 1px solid var(--ac); border-radius: 10px;
      padding: 8px 20px; font-size: 18px; font-weight: 700; margin-bottom: 20px;
    }
    .status-card {
      background: var(--surf2); border-radius: 14px; padding: 14px 16px;
      margin-bottom: 16px; text-align: left;
    }
    .status-label { font-size: 12px; color: var(--muted); margin-bottom: 4px; }
    .status-value { font-size: 16px; font-weight: 700; color: var(--amber); }
    .btn-new-order {
      width: 100%; background: var(--surf2); color: var(--text); border: 1px solid var(--border);
      border-radius: 12px; padding: 12px; font-size: 14px; font-weight: 600;
      font-family: inherit; cursor: pointer; transition: border-color .15s;
    }
    .btn-new-order:hover { border-color: var(--ac); }

    .empty-state {
      text-align: center; padding: 60px 20px; color: var(--muted);
    }
    .empty-state i { font-size: 44px; display: block; margin-bottom: 14px; opacity: .3; }

    /* ── Product detail modal ── */
    #detail-backdrop { align-items: flex-end; }
    @media(min-width:640px) { #detail-backdrop { align-items: center; padding: 20px; } }

    .detail-hero {
      width: 100%;
      background: linear-gradient(135deg, var(--ac-lt), rgba(99,102,241,.05));
      display: grid; place-items: center;
      font-size: 52px; color: var(--ac);
      min-height: 80px;
    }
    .detail-hero img {
      width: 100%; display: block;
      max-height: 55vh; object-fit: contain;
      background: #0f1117;
    }
    .detail-badge-price {
      display: inline-block; background: var(--ac); color: #fff;
      border-radius: 8px; padding: 4px 12px; font-size: 16px;
      font-weight: 700; margin-bottom: 10px;
    }
    .detail-desc {
      background: var(--surf2); border-radius: 10px;
      padding: 12px 14px; font-size: 13.5px; color: var(--sub);
      line-height: 1.65; margin-bottom: 16px; white-space: pre-wrap;
    }
    .detail-qty-row {
      display: flex; align-items: center; justify-content: space-between;
      background: var(--surf2); border-radius: 12px; padding: 12px 16px;
      margin-bottom: 14px;
    }
    .detail-qty-label { font-size: 13px; font-weight: 600; color: var(--sub); }
    .detail-qty-ctrl  { display: flex; align-items: center; gap: 14px; }
    .detail-qty-btn {
      width: 34px; height: 34px; border-radius: 10px; border: none;
      font-size: 18px; font-weight: 700; cursor: pointer; font-family: inherit;
      display: grid; place-items: center; transition: background .1s;
    }
    .detail-qty-btn.minus { background: var(--border); color: var(--sub); }
    .detail-qty-btn.plus  { background: var(--ac); color: #fff; }
    .detail-qty-num { font-size: 18px; font-weight: 700; color: var(--text); min-width: 28px; text-align: center; }
    .detail-total-row {
      display: flex; justify-content: space-between; align-items: center;
      padding: 12px 0; border-top: 1px solid var(--border); margin-bottom: 14px;
    }
    .detail-total-label { font-size: 14px; color: var(--sub); font-weight: 600; }
    .detail-total-val   { font-size: 22px; font-weight: 700; color: var(--ac); }
    .btn-add-cart {
      width: 100%; background: var(--ac); color: #fff; border: none;
      border-radius: 12px; padding: 14px; font-size: 15px; font-weight: 700;
      font-family: inherit; cursor: pointer; transition: opacity .15s;
      display: flex; align-items: center; justify-content: center; gap: 8px;
    }
    .btn-add-cart:hover   { opacity: .88; }
    .btn-add-cart:disabled { opacity: .4; cursor: not-allowed; }
  </style>
</head>
<body>

  <!-- Header -->
  <div class="header">
    <div class="header-inner">
      <div class="outlet-avatar"><i class="fa-solid fa-store"></i></div>
      <div>
        <div class="outlet-name">{{ $outlet->nama }}</div>
        <div class="outlet-sub">
          <i class="fa-solid fa-bag-shopping" style="font-size:10px;margin-right:4px"></i>Order Online · Pickup
        </div>
      </div>
    </div>
  </div>

  @if($products->isEmpty())
    <div class="empty-state" style="max-width:600px;margin:0 auto">
      <i class="fa-solid fa-box-open"></i>
      <div style="font-size:16px;font-weight:600;color:var(--text);margin-bottom:6px">Menu Belum Tersedia</div>
      <div>Produk aktif belum ada untuk outlet ini.</div>
    </div>
  @else

  <!-- Search -->
  <div class="search-wrap" style="position:relative">
    <i class="fa-solid fa-magnifying-glass search-icon"></i>
    <input type="text" id="search-input" class="search-input" placeholder="Cari menu…" oninput="filterProducts()">
  </div>

  <!-- Category tabs -->
  @if($categories->isNotEmpty())
  <div class="cats" id="cat-tabs">
    <button class="cat-btn active" data-cat="0" onclick="filterCat(0, this)">Semua</button>
    @foreach($categories as $cat)
    <button class="cat-btn" data-cat="{{ $cat->id }}" onclick="filterCat({{ $cat->id }}, this)">{{ $cat->nama }}</button>
    @endforeach
  </div>
  @endif

  <!-- Product grid -->
  <div class="products" id="product-grid">
    @foreach($products as $p)
    <div class="prod-card {{ $p->stok <= 0 ? 'out-of-stock' : '' }}"
      id="prod-{{ $p->id }}"
      data-id="{{ $p->id }}"
      data-nama="{{ e($p->nama) }}"
      data-harga="{{ $p->harga_jual }}"
      data-satuan="{{ $p->satuan }}"
      data-cat="{{ $p->category_id ?? 0 }}"
      data-stok="{{ $p->stok }}"
      data-deskripsi="{{ e($p->deskripsi ?? '') }}"
      data-gambar="{{ $p->gambar ? Storage::url($p->gambar) : '' }}"
      onclick="openDetail({{ $p->id }})">
      @if($p->gambar)
        <img src="{{ Storage::url($p->gambar) }}" alt="{{ $p->nama }}" class="prod-thumb" loading="lazy">
      @else
        <div class="prod-thumb-placeholder"><i class="fa-solid fa-bowl-food"></i></div>
      @endif
      <div class="prod-name">{{ $p->nama }}</div>
      <div class="prod-price">Rp {{ number_format($p->harga_jual, 0, ',', '.') }}</div>
      <div class="prod-stok">Stok: {{ $p->stok }} {{ $p->satuan }}</div>
      <div class="qty-ctrl" id="qty-ctrl-{{ $p->id }}" style="display:none">
        <button class="qty-btn minus" onclick="event.stopPropagation(); changeQty({{ $p->id }}, -1)">−</button>
        <span class="qty-num" id="qty-num-{{ $p->id }}">0</span>
        <button class="qty-btn plus" onclick="event.stopPropagation(); changeQty({{ $p->id }}, 1)">+</button>
      </div>
    </div>
    @endforeach
  </div>

  @endif

  <!-- Bottom cart bar -->
  <div class="cart-bar hidden" id="cart-bar">
    <div class="cart-bar-inner">
      <div class="cart-info">
        <div class="cart-qty" id="cart-qty-label">0 item</div>
        <div class="cart-total" id="cart-total-label">Rp 0</div>
      </div>
      <button class="btn-checkout" onclick="openCheckout()">
        <i class="fa-solid fa-bag-shopping"></i> Pesan Sekarang
      </button>
    </div>
  </div>

  <!-- Product detail modal -->
  <div class="backdrop" id="detail-backdrop">
    <div class="modal" id="detail-modal">
      <div class="detail-hero" id="detail-hero">
        <i class="fa-solid fa-bowl-food"></i>
      </div>
      <div class="modal-header" style="border-top:1px solid var(--border)">
        <div>
          <div class="modal-title" id="detail-nama">—</div>
          <div style="font-size:12px;color:var(--muted);margin-top:2px" id="detail-stok-label">—</div>
        </div>
        <button class="btn-close" onclick="closeDetail()"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="modal-body">

        <div class="detail-badge-price" id="detail-price">Rp 0</div>

        <div id="detail-desc-wrap" style="display:none">
          <div style="font-size:12px;font-weight:600;color:var(--sub);margin-bottom:6px;letter-spacing:.3px">
            <i class="fa-solid fa-align-left" style="margin-right:5px;color:var(--ac)"></i>DESKRIPSI
          </div>
          <div class="detail-desc" id="detail-desc"></div>
        </div>

        <div class="detail-qty-row">
          <div class="detail-qty-label">Jumlah</div>
          <div class="detail-qty-ctrl">
            <button class="detail-qty-btn minus" onclick="detailChangeQty(-1)">−</button>
            <span class="detail-qty-num" id="detail-qty">1</span>
            <button class="detail-qty-btn plus" onclick="detailChangeQty(1)">+</button>
          </div>
        </div>

        <div class="detail-total-row">
          <span class="detail-total-label">Total</span>
          <span class="detail-total-val" id="detail-total">Rp 0</span>
        </div>

        <button class="btn-add-cart" id="btn-add-cart" onclick="confirmAddToCart()">
          <i class="fa-solid fa-bag-shopping"></i>
          <span id="btn-add-cart-label">Tambah ke Pesanan</span>
        </button>

      </div>
    </div>
  </div>

  <!-- Checkout modal -->
  <div class="backdrop" id="checkout-backdrop">
    <div class="modal" id="checkout-modal">

      <!-- Form view -->
      <div id="checkout-form-view">
        <div class="modal-header">
          <div class="modal-title"><i class="fa-solid fa-bag-shopping" style="color:var(--ac);margin-right:8px"></i>Konfirmasi Pesanan</div>
          <button class="btn-close" onclick="closeCheckout()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">

          <!-- Order summary -->
          <div class="order-summary" id="order-summary-list"></div>

          <!-- Customer info -->
          <div class="f-group">
            <label class="f-label">Nama Pemesan <span style="color:var(--red)">*</span></label>
            <input type="text" id="input-name" class="f-input" placeholder="Nama lengkap kamu" oninput="validateForm()">
          </div>
          <div class="f-group">
            <label class="f-label">No. WhatsApp <span style="color:var(--red)">*</span></label>
            <input type="tel" id="input-phone" class="f-input" placeholder="08xx-xxxx-xxxx" oninput="validateForm()">
          </div>
          <div class="f-group">
            <label class="f-label">Catatan (opsional)</label>
            <input type="text" id="input-note" class="f-input" placeholder="cth. tidak pedas, extra saus…">
          </div>

          <div style="background:rgba(99,102,241,.08);border:1px solid rgba(99,102,241,.2);border-radius:10px;
                      padding:10px 12px;font-size:12.5px;color:var(--sub);margin-bottom:14px">
            <i class="fa-solid fa-circle-info" style="color:var(--ac);margin-right:6px"></i>
            Pesanan bersifat <strong style="color:var(--text)">Pickup</strong> — ambil langsung di <strong style="color:var(--text)">{{ $outlet->nama }}</strong>.
            Pembayaran dilakukan di tempat.
          </div>

          <button class="btn-submit" id="btn-submit" onclick="submitOrder()" disabled>
            <i class="fa-solid fa-check"></i> Buat Pesanan
          </button>
        </div>
      </div>

      <!-- Success view -->
      <div class="success-wrap" id="checkout-success-view">
        <div class="modal-header" style="justify-content:center;border-bottom:none;padding-bottom:8px">
          <div class="modal-title">Pesanan Diterima!</div>
        </div>
        <div class="modal-body">
          <div class="success-icon">
            <i class="fa-solid fa-circle-check" style="font-size:36px;color:var(--green)"></i>
          </div>
          <div class="success-title">Pesanan Berhasil Dibuat</div>
          <div class="success-sub">
            Tunjukkan nomor pesanan ini ke kasir saat mengambil pesananmu.
          </div>
          <div class="order-number-badge" id="suc-order-number">—</div>

          <div class="status-card">
            <div class="status-label">Status Pesanan</div>
            <div class="status-value" id="suc-status-value">Menunggu Konfirmasi</div>
            <div style="font-size:11.5px;color:var(--muted);margin-top:4px">
              <i class="fa-solid fa-rotate" style="margin-right:4px"></i>Otomatis diperbarui
            </div>
          </div>

          <div class="status-card">
            <div class="status-label">Total Pembayaran</div>
            <div style="font-size:18px;font-weight:700;color:var(--ac)" id="suc-total">—</div>
            <div style="font-size:12px;color:var(--muted);margin-top:3px">Bayar di kasir saat pickup</div>
          </div>

          <button class="btn-new-order" onclick="resetOrder()">
            <i class="fa-solid fa-plus" style="margin-right:6px"></i>Buat Pesanan Baru
          </button>
        </div>
      </div>

    </div>
  </div>

<script>
var cart      = {};
var activeCat = 0;
var currentOrderNumber = null;
var statusPollTimer    = null;

// ── Product detail modal ──
var detailProduct = null;
var detailQty     = 1;

function openDetail(id) {
  var card = document.querySelector('.prod-card[data-id="' + id + '"]');
  if (!card) return;

  detailProduct = {
    id:     id,
    nama:   card.dataset.nama,
    harga:  parseFloat(card.dataset.harga),
    stok:   parseInt(card.dataset.stok),
    satuan: card.dataset.satuan,
    desk:   card.dataset.deskripsi || '',
    gambar: card.dataset.gambar || '',
  };

  // Mulai dari qty yang sudah ada di cart, atau 1
  detailQty = cart[id] ? cart[id].qty : 1;

  // Hero: gambar atau placeholder
  var heroEl = document.getElementById('detail-hero');
  if (detailProduct.gambar) {
    heroEl.innerHTML = '<img src="' + detailProduct.gambar + '" alt="' + detailProduct.nama + '">';
  } else {
    heroEl.innerHTML = '<i class="fa-solid fa-bowl-food"></i>';
  }

  // Isi konten modal
  document.getElementById('detail-nama').textContent   = detailProduct.nama;
  document.getElementById('detail-price').textContent  = 'Rp ' + fmt(detailProduct.harga);
  document.getElementById('detail-stok-label').textContent =
    'Stok tersedia: ' + detailProduct.stok + ' ' + detailProduct.satuan;

  var descWrap = document.getElementById('detail-desc-wrap');
  var descEl   = document.getElementById('detail-desc');
  if (detailProduct.desk) {
    descEl.textContent   = detailProduct.desk;
    descWrap.style.display = 'block';
  } else {
    descWrap.style.display = 'none';
  }

  // Label tombol
  var btnLabel = document.getElementById('btn-add-cart-label');
  btnLabel.textContent = cart[id] ? 'Perbarui Pesanan' : 'Tambah ke Pesanan';

  detailUpdateTotal();

  // Scroll modal ke atas
  document.getElementById('detail-modal').scrollTop = 0;

  var backdrop = document.getElementById('detail-backdrop');
  var modal    = document.getElementById('detail-modal');
  backdrop.style.display = 'flex';
  requestAnimationFrame(function(){ requestAnimationFrame(function(){
    backdrop.style.opacity = '1';
    modal.classList.add('open');
  }); });
}

function closeDetail() {
  var backdrop = document.getElementById('detail-backdrop');
  var modal    = document.getElementById('detail-modal');
  backdrop.style.opacity = '0';
  modal.classList.remove('open');
  setTimeout(function(){ backdrop.style.display = 'none'; detailProduct = null; }, 220);
}

function detailChangeQty(delta) {
  var next = detailQty + delta;
  if (next < 1) return;
  if (next > detailProduct.stok) return;
  detailQty = next;
  detailUpdateTotal();
}

function detailUpdateTotal() {
  document.getElementById('detail-qty').textContent   = detailQty;
  document.getElementById('detail-total').textContent = 'Rp ' + fmt(detailProduct.harga * detailQty);
  document.getElementById('btn-add-cart').disabled = detailQty < 1;
}

function confirmAddToCart() {
  if (!detailProduct) return;
  var id = detailProduct.id;
  if (detailQty <= 0) {
    delete cart[id];
  } else {
    cart[id] = {
      id:    id,
      nama:  detailProduct.nama,
      harga: detailProduct.harga,
      stok:  detailProduct.stok,
      qty:   detailQty,
    };
  }
  updateCard(id);
  updateCartBar();
  closeDetail();
}

document.getElementById('detail-backdrop').addEventListener('click', function(e) {
  if (e.target === this) closeDetail();
});

// ── Category filter ──
function filterCat(catId, btn) {
  activeCat = catId;
  document.querySelectorAll('.cat-btn').forEach(function(b) {
    b.classList.toggle('active', b.dataset.cat == catId);
  });
  filterProducts();
}

function filterProducts() {
  var q = (document.getElementById('search-input')?.value || '').toLowerCase();
  document.querySelectorAll('.prod-card').forEach(function(card) {
    var matchCat  = activeCat === 0 || parseInt(card.dataset.cat) === activeCat;
    var matchName = !q || card.dataset.nama.toLowerCase().includes(q);
    card.style.display = (matchCat && matchName) ? '' : 'none';
  });
}

// ── Cart ──
function addItem(id) {
  var card = document.querySelector('.prod-card[data-id="' + id + '"]');
  if (!card) return;
  var stok = parseInt(card.dataset.stok);
  if (cart[id]) {
    if (cart[id].qty >= stok) return;
    cart[id].qty++;
  } else {
    cart[id] = {
      id:    id,
      nama:  card.dataset.nama,
      harga: parseFloat(card.dataset.harga),
      stok:  stok,
      qty:   1,
    };
  }
  updateCard(id);
  updateCartBar();
}

function changeQty(id, delta) {
  if (!cart[id]) return;
  var newQty = cart[id].qty + delta;
  if (newQty <= 0) {
    delete cart[id];
  } else if (newQty > cart[id].stok) {
    return;
  } else {
    cart[id].qty = newQty;
  }
  updateCard(id);
  updateCartBar();
  // Sync qty di modal detail jika sedang terbuka untuk item yang sama
  if (detailProduct && detailProduct.id == id) {
    detailQty = cart[id] ? cart[id].qty : 1;
    detailUpdateTotal();
  }
}

function updateCard(id) {
  var card = document.querySelector('.prod-card[data-id="' + id + '"]');
  var ctrl = document.getElementById('qty-ctrl-' + id);
  var num  = document.getElementById('qty-num-' + id);
  if (!cart[id]) {
    ctrl.style.display = 'none';
    num.textContent    = '0';
    card.classList.remove('active');
  } else {
    ctrl.style.display = 'flex';
    num.textContent    = cart[id].qty;
    card.classList.add('active');
  }
}

function updateCartBar() {
  var total = 0, qty = 0;
  Object.values(cart).forEach(function(i) { total += i.harga * i.qty; qty += i.qty; });
  document.getElementById('cart-qty-label').textContent  = qty + ' item';
  document.getElementById('cart-total-label').textContent = 'Rp ' + fmt(total);

  var bar = document.getElementById('cart-bar');
  if (qty > 0) bar.classList.remove('hidden');
  else         bar.classList.add('hidden');
}

// ── Checkout modal ──
function openCheckout() {
  var html = '';
  Object.values(cart).forEach(function(item) {
    html += '<div class="order-row">' +
      '<span class="name">' + item.nama + ' × ' + item.qty + '</span>' +
      '<span class="val">Rp ' + fmt(item.harga * item.qty) + '</span></div>';
  });
  var total = Object.values(cart).reduce(function(s,i){ return s + i.harga * i.qty; }, 0);
  html += '<div class="order-total-row"><span class="label">Total</span><span class="total">Rp ' + fmt(total) + '</span></div>';
  document.getElementById('order-summary-list').innerHTML = html;

  document.getElementById('checkout-form-view').style.display = 'block';
  document.getElementById('checkout-success-view').style.display = 'none';

  var backdrop = document.getElementById('checkout-backdrop');
  var modal    = document.getElementById('checkout-modal');
  backdrop.style.display = 'flex';
  requestAnimationFrame(function(){ requestAnimationFrame(function(){
    backdrop.style.opacity = '1';
    modal.classList.add('open');
    document.getElementById('input-name').focus();
  }); });
}

function closeCheckout() {
  var backdrop = document.getElementById('checkout-backdrop');
  var modal    = document.getElementById('checkout-modal');
  backdrop.style.opacity = '0';
  modal.classList.remove('open');
  setTimeout(function(){ backdrop.style.display = 'none'; }, 220);
}

function validateForm() {
  var name  = document.getElementById('input-name').value.trim();
  var phone = document.getElementById('input-phone').value.trim();
  document.getElementById('btn-submit').disabled = !(name && phone);
}

// ── Config dari server ──
var paymentEnabled = {{ $paymentEnabled ? 'true' : 'false' }};

// ── Submit order ──
function submitOrder() {
  var btn = document.getElementById('btn-submit');
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengirim…';

  var items = Object.values(cart).map(function(i) {
    return { product_id: i.id, qty: i.qty };
  });

  var body = {
    customer_name:  document.getElementById('input-name').value.trim(),
    customer_phone: document.getElementById('input-phone').value.trim(),
    catatan:        document.getElementById('input-note').value.trim(),
    items:          items,
  };

  fetch('{{ route("order.store", $outlet->slug) }}', {
    method:  'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept':       'application/json',
      'X-CSRF-TOKEN': '{{ csrf_token() }}',
    },
    body: JSON.stringify(body),
  })
  .then(function(res) {
    if (!res.ok) return res.json().then(function(e) { throw e; });
    return res.json();
  })
  .then(function(data) {
    currentOrderNumber = data.order_number;

    if (data.payment_token && paymentEnabled && typeof snap !== 'undefined') {
      // Ada payment token — buka Midtrans Snap
      btn.disabled = false;
      btn.innerHTML = '<i class="fa-solid fa-check"></i> Buat Pesanan';
      openMidtransSnap(data);
    } else {
      // Tanpa payment — langsung tampilkan sukses
      showOrderSuccess(data);
    }
  })
  .catch(function(err) {
    btn.disabled = false;
    btn.innerHTML = '<i class="fa-solid fa-check"></i> Buat Pesanan';
    var msg = 'Gagal mengirim pesanan. Coba lagi.';
    if (err && err.message) msg = err.message;
    else if (err && err.errors) msg = Object.values(err.errors)[0][0];
    alert(msg);
  });
}

// ── Buka Midtrans Snap ──
function openMidtransSnap(data) {
  snap.pay(data.payment_token, {
    onSuccess: function(result) {
      // Pembayaran berhasil
      showOrderSuccess(data, true);
    },
    onPending: function(result) {
      // Menunggu konfirmasi (transfer bank, dll)
      showOrderSuccess(data, false, true);
    },
    onError: function(result) {
      alert('Pembayaran gagal. Silakan coba lagi atau hubungi kasir.');
    },
    onClose: function() {
      // Popup ditutup tanpa bayar
      var submitBtn = document.getElementById('btn-submit');
      submitBtn.disabled = false;
      submitBtn.innerHTML = '<i class="fa-solid fa-check"></i> Buat Pesanan';
      alert('Pembayaran belum selesai. Pesanan akan otomatis dibatalkan jika tidak dibayar.');
    },
  });
}

// ── Tampilkan halaman sukses ──
function showOrderSuccess(data, paid, pending) {
  document.getElementById('suc-order-number').textContent = data.order_number;
  document.getElementById('suc-total').textContent = 'Rp ' + fmt(data.subtotal);

  // Update label status sesuai kondisi pembayaran
  var statusEl = document.getElementById('suc-status-value');
  if (paid) {
    statusEl.textContent = 'Sudah Dibayar — Menunggu';
    statusEl.style.color = '#34d399';
  } else if (pending) {
    statusEl.textContent = 'Menunggu Konfirmasi Pembayaran';
    statusEl.style.color = '#60a5fa';
  } else {
    statusEl.textContent = 'Menunggu';
    statusEl.style.color = '#fbbf24';
  }

  document.getElementById('checkout-form-view').style.display = 'none';
  document.getElementById('checkout-success-view').style.display = 'block';

  startStatusPoll(data.order_number);
}

// ── Status polling ──
var statusColors = {
  pending_payment: '#60a5fa',
  pending:         '#fbbf24',
  processing:      '#60a5fa',
  ready:           '#34d399',
  completed:       '#6b7280',
  cancelled:       '#f87171',
};

var statusLabels = {
  pending_payment: 'Menunggu Pembayaran',
  pending:         'Menunggu',
  processing:      'Diproses',
  ready:           'Siap Diambil',
  completed:       'Selesai',
  cancelled:       'Dibatalkan',
};

function startStatusPoll(orderNumber) {
  if (statusPollTimer) clearInterval(statusPollTimer);
  pollStatus(orderNumber);
  statusPollTimer = setInterval(function() { pollStatus(orderNumber); }, 8000);
}

function pollStatus(orderNumber) {
  fetch('{{ url("order/status") }}/' + orderNumber, {
    headers: { 'Accept': 'application/json' }
  })
  .then(function(r) { return r.json(); })
  .then(function(data) {
    var el = document.getElementById('suc-status-value');
    el.textContent = statusLabels[data.order_status] || data.label;
    el.style.color = statusColors[data.order_status] || '#fbbf24';
    if (data.order_status === 'completed' || data.order_status === 'cancelled') {
      clearInterval(statusPollTimer);
    }
  })
  .catch(function() {});
}

// ── Reset for new order ──
function resetOrder() {
  clearInterval(statusPollTimer);
  cart = {};
  document.querySelectorAll('.prod-card').forEach(function(card) {
    var id = card.dataset.id;
    var ctrl = document.getElementById('qty-ctrl-' + id);
    var num  = document.getElementById('qty-num-' + id);
    if (ctrl) ctrl.style.display = 'none';
    if (num)  num.textContent = '0';
    card.classList.remove('active');
  });
  document.getElementById('input-name').value  = '';
  document.getElementById('input-phone').value = '';
  document.getElementById('input-note').value  = '';
  updateCartBar();
  closeCheckout();
}

function fmt(n) { return Math.round(n).toLocaleString('id-ID'); }

// Close on backdrop click
document.getElementById('checkout-backdrop').addEventListener('click', function(e) {
  if (e.target === this) closeCheckout();
});
</script>

</body>
</html>
