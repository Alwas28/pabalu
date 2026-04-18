<x-app-layout title="POS / Kasir">

<style>
#content { padding:0 !important; max-width:100% !important; }

.pos-wrap {
  display:grid;
  grid-template-columns:1fr 360px;
  height:calc(100vh - 65px);
  overflow:hidden;
}

/* ── Category tabs ── */
.cat-tab { background:var(--surface2);color:var(--sub) }
.cat-tab.active { background:var(--ac-lt);color:var(--ac) }

/* ── Payment method tabs ── */
.pay-tab {
  flex:1;padding:9px 6px;border-radius:10px;font-size:13px;font-weight:600;
  border:2px solid var(--border);background:var(--surface2);color:var(--sub);
  cursor:pointer;transition:all .15s;font-family:inherit;
}
.pay-tab.active {
  border-color:var(--ac);background:var(--ac-lt);color:var(--ac);
}

/* ── Mobile ── */
@media(max-width:900px){
  .pos-wrap { grid-template-columns:1fr; height:auto; overflow:auto; }

  .pos-cart {
    position:fixed;bottom:0;left:0;right:0;
    z-index:35;                           /* BELOW sidebar overlay (z-index:45) */
    border-radius:20px 20px 0 0;
    max-height:75vh;
    overflow:hidden;
    box-shadow:0 -6px 32px rgba(0,0,0,.28);
    transition:transform .3s cubic-bezier(.4,0,.2,1);
    display:flex;flex-direction:column;
  }
  .pos-cart.cart-collapsed {
    transform:translateY(calc(100% - 58px));
  }
  .pos-products { padding-bottom:72px; }

  .cart-toggle-btn { display:flex !important; }
  .cart-clear-btn  { display:none  !important; }
  .cart-clear-btn-expanded { display:flex !important; }
}
@media(min-width:901px){
  .cart-toggle-btn { display:none !important; }
  .cart-clear-btn  { display:flex !important; }
  .cart-clear-btn-expanded { display:none !important; }
}
</style>

<div class="pos-wrap">

  {{-- ═══ PANEL PRODUK ═══ --}}
  <div class="pos-products" style="display:flex;flex-direction:column;overflow:hidden;border-right:1px solid var(--border)">

    {{-- Toolbar --}}
    <div style="padding:12px 16px;border-bottom:1px solid var(--border);display:flex;gap:10px;align-items:center;flex-wrap:wrap;background:var(--surface)">
      @if ($assignedOutletId)
        {{-- Kasir terikat outlet — tampilkan label, tidak bisa diubah --}}
        @php $assignedOutlet = $outlets->firstWhere('id', $assignedOutletId); @endphp
        <div style="display:flex;align-items:center;gap:8px;padding:7px 14px;border-radius:10px;
                    background:var(--ac-lt);border:1px solid var(--ac);font-size:13px;font-weight:600;color:var(--ac)">
          <i class="fa-solid fa-store"></i>
          {{ $assignedOutlet?->nama ?? 'Outlet #'.$assignedOutletId }}
          <span style="font-size:10px;font-weight:400;color:var(--ac);opacity:.7;margin-left:2px">
            <i class="fa-solid fa-lock"></i>
          </span>
        </div>
      @else
        {{-- Admin/owner dapat memilih outlet --}}
        <form method="GET" action="{{ route('transactions.pos') }}" id="outlet-form" style="display:contents">
          <select name="outlet_id" class="f-input" style="width:auto;padding:7px 12px;font-size:13px"
            onchange="document.getElementById('outlet-form').submit()">
            <option value="">— Pilih Outlet —</option>
            @foreach($outlets as $o)
            <option value="{{ $o->id }}" @selected($outletId == $o->id)>{{ $o->nama }}</option>
            @endforeach
          </select>
        </form>
      @endif
      <div style="position:relative;flex:1;min-width:140px">
        <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:12px;pointer-events:none"></i>
        <input type="text" id="pos-search" placeholder="Cari produk…" oninput="filterProducts()"
          class="f-input" style="padding-left:30px;padding-top:7px;padding-bottom:7px;font-size:13px">
      </div>
      <div style="font-size:12px;color:var(--muted);white-space:nowrap">
        <i class="fa-solid fa-calendar" style="margin-right:4px;color:var(--ac)"></i>
        {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d M Y') }}
      </div>
    </div>

    {{-- Kategori tabs --}}
    @if($categories->isNotEmpty())
    <div style="padding:8px 16px;border-bottom:1px solid var(--border);display:flex;gap:6px;overflow-x:auto;background:var(--surface);flex-shrink:0" id="cat-tabs">
      <button onclick="filterCat(0)" id="cat-0" class="cat-tab active"
        style="padding:5px 14px;border-radius:99px;font-size:12px;font-weight:600;white-space:nowrap;cursor:pointer;transition:background .15s,color .15s;border:none;font-family:inherit">
        Semua
      </button>
      @foreach($categories as $cat)
      <button onclick="filterCat({{ $cat->id }})" id="cat-{{ $cat->id }}" class="cat-tab"
        style="padding:5px 14px;border-radius:99px;font-size:12px;font-weight:600;white-space:nowrap;cursor:pointer;transition:background .15s,color .15s;border:none;font-family:inherit">
        {{ $cat->nama }}
      </button>
      @endforeach
    </div>
    @endif

    {{-- Grid Produk --}}
    <div id="product-grid" style="overflow-y:auto;padding:10px 12px;display:grid;
         grid-template-columns:repeat(auto-fill,minmax(110px,1fr));gap:8px;align-content:start;flex:1">
      @forelse($products as $p)
      <div class="prod-card" data-id="{{ $p->id }}" data-nama="{{ $p->nama }}"
        data-harga="{{ $p->harga_jual }}" data-satuan="{{ $p->satuan }}"
        data-cat="{{ $p->category_id ?? 0 }}" data-stok="{{ $p->stok }}"
        onclick="addToCart({{ $p->id }})"
        style="background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:10px 11px;
               cursor:pointer;transition:border-color .15s,box-shadow .15s,transform .1s;
               {{ $p->stok <= 0 ? 'opacity:.45;pointer-events:none' : '' }}"
        onmouseover="this.style.borderColor='var(--ac)';this.style.boxShadow='0 3px 12px rgba(0,0,0,.15)'"
        onmouseout="this.style.borderColor='var(--border)';this.style.boxShadow='none'">
        <div style="width:30px;height:30px;border-radius:8px;background:var(--ac-lt);color:var(--ac);
                    display:grid;place-items:center;font-size:13px;margin-bottom:7px">
          <i class="fa-solid fa-cube"></i>
        </div>
        <div style="font-size:11.5px;font-weight:600;color:var(--text);line-height:1.3;margin-bottom:3px">{{ $p->nama }}</div>
        <div style="font-family:'Clash Display',sans-serif;font-size:13px;font-weight:700;color:var(--ac)">
          Rp {{ number_format($p->harga_jual, 0, ',', '.') }}
        </div>
        <div style="font-size:10px;margin-top:3px;display:flex;align-items:center;justify-content:space-between">
          <span style="color:var(--muted)">/ {{ $p->satuan }}</span>
          <span style="font-weight:600;color:{{ $p->stok > 5 ? '#34d399' : ($p->stok > 0 ? '#fbbf24' : '#f87171') }}">
            {{ $p->stok }}
          </span>
        </div>
      </div>
      @empty
      <div style="grid-column:1/-1;text-align:center;padding:60px;color:var(--muted)">
        <i class="fa-solid fa-box-open" style="font-size:36px;display:block;margin-bottom:12px;opacity:.4"></i>
        @if(!$outletId) Pilih outlet terlebih dahulu. @else Belum ada produk aktif. @endif
      </div>
      @endforelse
    </div>
  </div>

  {{-- ═══ PANEL KERANJANG ═══ --}}
  <div class="pos-cart cart-collapsed" id="pos-cart" style="display:flex;flex-direction:column;background:var(--surface)">

    {{-- Cart header --}}
    <div id="cart-header"
      style="padding:0 16px;height:58px;border-bottom:1px solid var(--border);
             display:flex;align-items:center;justify-content:space-between;flex-shrink:0;cursor:default"
      onclick="toggleCartMobile()">
      <div style="font-family:'Clash Display',sans-serif;font-size:15px;font-weight:700;color:var(--text);display:flex;align-items:center;gap:8px">
        <i class="fa-solid fa-cart-shopping" style="color:var(--ac)"></i>Keranjang
        <span id="cart-badge" style="background:var(--ac);color:#fff;font-size:11px;font-weight:700;
              border-radius:99px;padding:1px 7px;display:none">0</span>
      </div>
      {{-- Mobile: total + chevron --}}
      <div class="cart-toggle-btn" style="display:none;align-items:center;gap:10px">
        <span id="mobile-total" style="font-family:'Clash Display',sans-serif;font-size:14px;font-weight:700;color:var(--ac)">Rp 0</span>
        <div style="width:30px;height:30px;border-radius:8px;background:var(--surface2);
                    border:1px solid var(--border);display:grid;place-items:center">
          <i class="fa-solid fa-chevron-up" id="cart-chevron" style="font-size:11px;color:var(--muted);transition:transform .3s"></i>
        </div>
      </div>
      {{-- Desktop: kosongkan button --}}
      <button onclick="event.stopPropagation();clearCart()" class="btn btn-danger cart-clear-btn"
        style="padding:5px 12px;font-size:12px;display:flex">
        <i class="fa-solid fa-trash"></i> Kosongkan
      </button>
    </div>

    {{-- Cart items --}}
    <div id="cart-items" style="flex:1;overflow-y:auto;padding:8px 0;min-height:0">
      <div id="cart-empty" style="text-align:center;padding:36px 20px;color:var(--muted)">
        <i class="fa-solid fa-cart-shopping" style="font-size:28px;display:block;margin-bottom:10px;opacity:.3"></i>
        <div style="font-size:13px">Belum ada item</div>
      </div>
    </div>

    {{-- Total & Payment --}}
    <div style="padding:12px 16px;border-top:1px solid var(--border);flex-shrink:0">
      {{-- Kosongkan (expanded mobile only) --}}
      <button onclick="clearCart()" class="btn btn-danger cart-clear-btn-expanded"
        style="display:none;width:100%;justify-content:center;padding:7px;font-size:12px;margin-bottom:10px">
        <i class="fa-solid fa-trash"></i> Kosongkan Keranjang
      </button>

      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:3px">
        <span style="font-size:12.5px;color:var(--muted)">Jumlah Item</span>
        <span id="summary-qty" style="font-size:13px;font-weight:600;color:var(--text)">0 item</span>
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
        <span style="font-size:14px;font-weight:600;color:var(--sub)">Total</span>
        <span id="summary-total" style="font-family:'Clash Display',sans-serif;font-size:22px;font-weight:700;color:var(--ac)">Rp 0</span>
      </div>
      <button id="btn-bayar" onclick="openPayment()"
        class="btn btn-primary" style="width:100%;justify-content:center;padding:12px;font-size:14px" disabled>
        <i class="fa-solid fa-cash-register"></i> Proses Pembayaran
      </button>
    </div>
  </div>
</div>

{{-- ═══ MODAL PEMBAYARAN ═══ --}}
<div id="pay-backdrop"
  style="display:none;position:fixed;inset:0;z-index:9000;background:rgba(0,0,0,.65);
         backdrop-filter:blur(5px);align-items:center;justify-content:center;padding:20px;
         opacity:0;transition:opacity .2s">
  <div id="pay-box"
    style="background:var(--surface);border:1px solid var(--border);border-radius:22px;
           width:100%;max-width:420px;box-shadow:0 24px 64px rgba(0,0,0,.5);
           transform:scale(.93) translateY(14px);transition:transform .25s,opacity .25s;
           opacity:0;overflow:hidden;max-height:90vh;overflow-y:auto">

    {{-- Header --}}
    <div style="padding:18px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;background:var(--surface);z-index:1">
      <div>
        <div style="font-family:'Clash Display',sans-serif;font-size:17px;font-weight:700;color:var(--text)">
          <i class="fa-solid fa-cash-register" style="color:var(--ac);margin-right:8px"></i>Pembayaran
        </div>
        <div id="pay-outlet-info" style="font-size:11.5px;color:var(--muted);margin-top:1px"></div>
      </div>
      <button onclick="closePayment()"
        style="width:32px;height:32px;border-radius:9px;border:1px solid var(--border);
               background:var(--surface2);cursor:pointer;color:var(--muted);font-size:13px;transition:color .15s"
        onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--muted)'">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <div style="padding:16px 24px">

      {{-- Item summary --}}
      <div id="pay-items" style="margin-bottom:12px;max-height:140px;overflow-y:auto"></div>
      <div style="height:1px;background:var(--border);margin-bottom:14px"></div>

      {{-- Total --}}
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
        <span style="font-size:14px;font-weight:600;color:var(--sub)">Total Tagihan</span>
        <span id="pay-total" style="font-family:'Clash Display',sans-serif;font-size:26px;font-weight:700;color:var(--ac)">Rp 0</span>
      </div>

      {{-- Metode Bayar Tabs --}}
      <div style="margin-bottom:16px">
        <div style="font-size:11.5px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:8px">Metode Pembayaran</div>
        @php $firstMethod = $activeMethods[0] ?? 'tunai'; @endphp
        <div style="display:flex;gap:8px;flex-wrap:wrap">
          @if(in_array('tunai', $activeMethods))
          <button type="button" class="pay-tab {{ $firstMethod === 'tunai' ? 'active' : '' }}" id="tab-tunai" onclick="setMetode('tunai')">
            <i class="fa-solid fa-money-bill-wave" style="display:block;font-size:18px;margin-bottom:4px"></i>Tunai
          </button>
          @endif
          @if(in_array('qris', $activeMethods))
          <button type="button" class="pay-tab {{ $firstMethod === 'qris' ? 'active' : '' }}" id="tab-qris" onclick="setMetode('qris')">
            <i class="fa-solid fa-qrcode" style="display:block;font-size:18px;margin-bottom:4px"></i>QRIS
          </button>
          @endif
          @if(in_array('transfer', $activeMethods))
          <button type="button" class="pay-tab {{ $firstMethod === 'transfer' ? 'active' : '' }}" id="tab-transfer" onclick="setMetode('transfer')">
            <i class="fa-solid fa-building-columns" style="display:block;font-size:18px;margin-bottom:4px"></i>Transfer
          </button>
          @endif
        </div>
      </div>

      {{-- Panel: Tunai --}}
      <div id="panel-tunai">
        <div class="f-group">
          <label class="f-label">Jumlah Bayar <span style="color:#f87171">*</span></label>
          <div style="position:relative">
            <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:13px;font-weight:600">Rp</span>
            <input type="text" id="input-bayar" class="f-input" inputmode="numeric" autocomplete="off"
              style="padding-left:34px;font-size:15px;font-weight:700"
              oninput="maskBayar(this)" placeholder="0">
          </div>
        </div>
        <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:14px" id="quick-nominal"></div>
        <div id="kembalian-box" style="padding:12px;border-radius:12px;text-align:center;display:none">
          <div style="font-size:12px;color:var(--muted);margin-bottom:2px">Kembalian</div>
          <div id="kembalian-val" style="font-family:'Clash Display',sans-serif;font-size:26px;font-weight:700;color:#34d399">Rp 0</div>
        </div>
      </div>

      {{-- Panel: QRIS --}}
      <div id="panel-qris" style="display:none">
        <div style="background:var(--ac-lt);border:1px solid var(--ac);border-radius:12px;padding:14px;text-align:center;margin-bottom:16px">
          <i class="fa-solid fa-qrcode" style="font-size:32px;color:var(--ac);display:block;margin-bottom:6px"></i>
          <div style="font-size:13px;font-weight:600;color:var(--ac)">Scan QR Code & Bayar Sesuai Tagihan</div>
          <div id="pay-total-qris" style="font-family:'Clash Display',sans-serif;font-size:22px;font-weight:700;color:var(--text);margin-top:4px">Rp 0</div>
        </div>
        <div class="f-group" style="margin-bottom:8px">
          <label class="f-label">Upload Bukti Bayar QRIS <span style="color:#f87171">*</span></label>
          <label id="label-qris" for="input-bukti-qris"
            style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;
                   border:2px dashed var(--border);border-radius:12px;padding:20px;cursor:pointer;
                   transition:border-color .15s;background:var(--surface2)">
            <i class="fa-solid fa-cloud-arrow-up" style="font-size:24px;color:var(--muted)"></i>
            <span style="font-size:13px;color:var(--sub)">Tap untuk memilih foto / screenshot</span>
            <span style="font-size:11px;color:var(--muted)">JPG, PNG, WEBP — maks 5 MB</span>
          </label>
          <input type="file" id="input-bukti-qris" accept="image/*" style="display:none"
            onchange="previewBukti('qris', this)">
        </div>
        <div id="preview-qris" style="display:none;margin-bottom:8px">
          <img id="img-qris" src="" alt="Bukti QRIS"
            style="width:100%;max-height:180px;object-fit:contain;border-radius:10px;border:1px solid var(--border)">
          <button type="button" onclick="clearBukti('qris')"
            style="margin-top:6px;font-size:12px;color:#f87171;background:none;border:none;cursor:pointer;padding:0">
            <i class="fa-solid fa-xmark"></i> Hapus foto
          </button>
        </div>
      </div>

      {{-- Panel: Transfer --}}
      <div id="panel-transfer" style="display:none">
        <div style="background:var(--surface2);border:1px solid var(--border);border-radius:12px;padding:14px;text-align:center;margin-bottom:16px">
          <i class="fa-solid fa-building-columns" style="font-size:28px;color:var(--ac);display:block;margin-bottom:6px"></i>
          <div style="font-size:13px;font-weight:600;color:var(--text)">Transfer Bank</div>
          <div id="pay-total-transfer" style="font-family:'Clash Display',sans-serif;font-size:22px;font-weight:700;color:var(--ac);margin-top:4px">Rp 0</div>
        </div>
        <div class="f-group" style="margin-bottom:8px">
          <label class="f-label">Upload Bukti Transfer <span style="color:#f87171">*</span></label>
          <label id="label-transfer" for="input-bukti-transfer"
            style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;
                   border:2px dashed var(--border);border-radius:12px;padding:20px;cursor:pointer;
                   transition:border-color .15s;background:var(--surface2)">
            <i class="fa-solid fa-cloud-arrow-up" style="font-size:24px;color:var(--muted)"></i>
            <span style="font-size:13px;color:var(--sub)">Tap untuk memilih foto / screenshot</span>
            <span style="font-size:11px;color:var(--muted)">JPG, PNG, WEBP — maks 5 MB</span>
          </label>
          <input type="file" id="input-bukti-transfer" accept="image/*" style="display:none"
            onchange="previewBukti('transfer', this)">
        </div>
        <div id="preview-transfer" style="display:none;margin-bottom:8px">
          <img id="img-transfer" src="" alt="Bukti Transfer"
            style="width:100%;max-height:180px;object-fit:contain;border-radius:10px;border:1px solid var(--border)">
          <button type="button" onclick="clearBukti('transfer')"
            style="margin-top:6px;font-size:12px;color:#f87171;background:none;border:none;cursor:pointer;padding:0">
            <i class="fa-solid fa-xmark"></i> Hapus foto
          </button>
        </div>
      </div>

      {{-- Keterangan --}}
      <div class="f-group" style="margin-bottom:0;margin-top:12px">
        <label class="f-label">Keterangan (opsional)</label>
        <input type="text" id="input-ket" class="f-input" placeholder="cth. meja 5, takeaway…" style="font-size:13px">
      </div>

    </div>

    {{-- Action buttons --}}
    <div style="padding:12px 24px 20px;display:flex;gap:10px;position:sticky;bottom:0;background:var(--surface);border-top:1px solid var(--border)">
      <button type="button" onclick="closePayment()" class="btn" style="flex:1;justify-content:center;padding:11px">Batal</button>
      <button type="button" id="btn-konfirmasi" onclick="konfirmasiTransaksi()"
        class="btn btn-primary" style="flex:2;justify-content:center;padding:11px;font-size:13.5px" disabled>
        <i class="fa-solid fa-check"></i> Konfirmasi
      </button>
    </div>

  </div>
</div>

{{-- ═══ MODAL SUKSES ═══ --}}
<div id="success-backdrop"
  style="display:none;position:fixed;inset:0;z-index:9500;background:rgba(0,0,0,.7);
         backdrop-filter:blur(6px);align-items:center;justify-content:center;padding:20px;
         opacity:0;transition:opacity .2s">
  <div id="success-box"
    style="background:var(--surface);border:1px solid var(--border);border-radius:24px;
           width:100%;max-width:380px;box-shadow:0 24px 80px rgba(0,0,0,.55);
           transform:scale(.92) translateY(16px);transition:transform .25s,opacity .25s;
           opacity:0;overflow:hidden;text-align:center;padding:32px 28px 24px">

    {{-- Ikon sukses --}}
    <div style="width:68px;height:68px;border-radius:50%;background:rgba(52,211,153,.15);
                display:grid;place-items:center;margin:0 auto 18px">
      <i class="fa-solid fa-circle-check" style="font-size:36px;color:#34d399"></i>
    </div>

    <div style="font-family:'Clash Display',sans-serif;font-size:20px;font-weight:700;color:var(--text);margin-bottom:4px">
      Transaksi Berhasil!
    </div>
    <div id="suc-nomor" style="font-size:13px;color:var(--muted);margin-bottom:20px"></div>

    {{-- Rincian --}}
    <div style="background:var(--surface2);border-radius:14px;padding:14px 16px;margin-bottom:20px;text-align:left">
      <div style="display:flex;justify-content:space-between;margin-bottom:6px">
        <span style="font-size:12.5px;color:var(--muted)">Total</span>
        <span id="suc-total" style="font-family:'Clash Display',sans-serif;font-size:15px;font-weight:700;color:var(--ac)"></span>
      </div>
      <div style="display:flex;justify-content:space-between;margin-bottom:6px">
        <span style="font-size:12.5px;color:var(--muted)">Metode</span>
        <span id="suc-metode" style="font-size:12.5px;font-weight:600;color:var(--text)"></span>
      </div>
      <div id="suc-kembalian-row" style="display:flex;justify-content:space-between">
        <span style="font-size:12.5px;color:var(--muted)">Kembalian</span>
        <span id="suc-kembalian" style="font-size:12.5px;font-weight:600;color:#34d399"></span>
      </div>
    </div>

    {{-- Tombol --}}
    <div style="display:flex;flex-direction:column;gap:10px">
      <a id="suc-btn-struk" href="#" target="_blank"
        class="btn btn-primary" style="justify-content:center;padding:12px;text-decoration:none;font-size:14px">
        <i class="fa-solid fa-print"></i> Print Struk
      </a>
      <button onclick="transaksiBerikutnya()"
        class="btn" style="justify-content:center;padding:12px;font-size:14px">
        <i class="fa-solid fa-arrow-right"></i> Transaksi Berikutnya
      </button>
    </div>
  </div>
</div>

@push('scripts')
<script>
// ── State ──
var cart      = {};
var activecat = 0;
var activeMethods = @json($activeMethods);
var metode    = activeMethods[0] || 'tunai';
var cartOpen  = false;   // mobile expand state
var outletNama = '{{ addslashes($outlets->firstWhere("id", $outletId)?->nama ?? "") }}';

// ── Mobile cart toggle ──
function isMobile() { return window.innerWidth <= 900; }

function toggleCartMobile() {
  if (!isMobile()) return;
  cartOpen = !cartOpen;
  var el  = document.getElementById('pos-cart');
  var chv = document.getElementById('cart-chevron');
  if (cartOpen) {
    el.classList.remove('cart-collapsed');
    chv.style.transform = 'rotate(180deg)';
  } else {
    el.classList.add('cart-collapsed');
    chv.style.transform = '';
  }
}

// ── Kategori filter ──
function filterCat(catId) {
  activecat = catId;
  document.querySelectorAll('.cat-tab').forEach(function(b){
    b.classList.toggle('active', parseInt(b.id.replace('cat-','')) === catId);
  });
  filterProducts();
}
function filterProducts() {
  var q = (document.getElementById('pos-search').value || '').toLowerCase();
  document.querySelectorAll('.prod-card').forEach(function(card){
    var show = (!q || card.dataset.nama.toLowerCase().includes(q)) &&
               (activecat === 0 || parseInt(card.dataset.cat) === activecat);
    card.style.display = show ? '' : 'none';
  });
}

// ── Keranjang ──
function addToCart(id) {
  var card  = document.querySelector('.prod-card[data-id="' + id + '"]');
  var stok  = parseInt(card.dataset.stok);
  var nama  = card.dataset.nama;
  var harga = parseFloat(card.dataset.harga);
  var sat   = card.dataset.satuan;

  if (cart[id]) {
    if (cart[id].qty >= stok) { showToast('warning','Stok ' + nama + ' tidak mencukupi.'); return; }
    cart[id].qty++;
  } else {
    cart[id] = { id:id, nama:nama, harga:harga, satuan:sat, qty:1, maxQty:stok };
  }
  renderCart();
  card.style.transform = 'scale(.96)';
  setTimeout(function(){ card.style.transform = ''; }, 120);
}

function changeQty(id, delta) {
  if (!cart[id]) return;
  var newQty = cart[id].qty + delta;
  if (newQty <= 0) { delete cart[id]; }
  else if (newQty > cart[id].maxQty) { showToast('warning','Stok tidak mencukupi.'); return; }
  else { cart[id].qty = newQty; }
  renderCart();
}

function removeItem(id) { delete cart[id]; renderCart(); }

function clearCart() { cart = {}; renderCart(); }

function renderCart() {
  var container = document.getElementById('cart-items');
  var empty     = document.getElementById('cart-empty');
  var ids = Object.keys(cart);

  container.querySelectorAll('.cart-row').forEach(function(el){ el.remove(); });

  if (ids.length === 0) {
    empty.style.display = 'block';
  } else {
    empty.style.display = 'none';
    ids.forEach(function(id){
      var item = cart[id];
      var sub  = item.harga * item.qty;
      var row  = document.createElement('div');
      row.className = 'cart-row';
      row.style.cssText = 'padding:10px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px';
      row.innerHTML =
        '<div style="flex:1;min-width:0">' +
          '<div style="font-size:13px;font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">' + item.nama + '</div>' +
          '<div style="font-size:11.5px;color:var(--muted)">Rp ' + fmt(item.harga) + ' / ' + item.satuan + '</div>' +
        '</div>' +
        '<div style="display:flex;align-items:center;gap:5px;flex-shrink:0">' +
          '<button onclick="changeQty(' + id + ',-1)" style="width:26px;height:26px;border-radius:7px;border:1px solid var(--border);background:var(--surface2);cursor:pointer;font-size:13px;color:var(--sub)">−</button>' +
          '<span style="font-weight:700;font-size:14px;color:var(--text);min-width:22px;text-align:center">' + item.qty + '</span>' +
          '<button onclick="changeQty(' + id + ',1)" style="width:26px;height:26px;border-radius:7px;background:var(--ac-lt);color:var(--ac);border:none;cursor:pointer;font-size:13px;font-weight:700">+</button>' +
        '</div>' +
        '<div style="text-align:right;flex-shrink:0;min-width:72px">' +
          '<div style="font-family:\'Clash Display\',sans-serif;font-size:13.5px;font-weight:700;color:var(--text)">Rp ' + fmt(sub) + '</div>' +
          '<button onclick="removeItem(' + id + ')" style="font-size:10.5px;color:#f87171;background:none;border:none;cursor:pointer;padding:0">hapus</button>' +
        '</div>';
      container.appendChild(row);
    });
  }
  updateSummary();
}

function updateSummary() {
  var total = 0, qty = 0;
  Object.values(cart).forEach(function(i){ total += i.harga * i.qty; qty += i.qty; });
  document.getElementById('summary-total').textContent = 'Rp ' + fmt(total);
  document.getElementById('summary-qty').textContent   = qty + ' item';
  document.getElementById('btn-bayar').disabled        = (qty === 0);

  // Mobile mini bar
  document.getElementById('mobile-total').textContent = 'Rp ' + fmt(total);
  var badge = document.getElementById('cart-badge');
  if (qty > 0) { badge.textContent = qty; badge.style.display = 'inline-block'; }
  else { badge.style.display = 'none'; }
}

// ── Metode Bayar ──
function setMetode(m) {
  metode = m;
  ['tunai','qris','transfer'].forEach(function(k){
    var tab   = document.getElementById('tab-' + k);
    var panel = document.getElementById('panel-' + k);
    if (tab)   tab.classList.toggle('active', k === m);
    if (panel) panel.style.display = (k === m) ? 'block' : 'none';
  });
  validatePayment();
}

// ── File preview / clear ──
function previewBukti(type, input) {
  if (!input.files || !input.files[0]) return;
  var reader = new FileReader();
  reader.onload = function(e) {
    document.getElementById('img-' + type).src = e.target.result;
    document.getElementById('preview-' + type).style.display = 'block';
    document.getElementById('label-' + type).style.borderColor = 'var(--ac)';
    document.getElementById('label-' + type).style.borderStyle = 'solid';
  };
  reader.readAsDataURL(input.files[0]);
  validatePayment();
}

function clearBukti(type) {
  var inp     = document.getElementById('input-bukti-' + type);
  var img     = document.getElementById('img-' + type);
  var preview = document.getElementById('preview-' + type);
  var label   = document.getElementById('label-' + type);
  if (inp)     inp.value = '';
  if (img)     img.src = '';
  if (preview) preview.style.display = 'none';
  if (label)   { label.style.borderColor = 'var(--border)'; label.style.borderStyle = 'dashed'; }
  validatePayment();
}

// ── Modal Pembayaran ──
function openPayment() {
  var total = getTotal();
  if (total === 0) return;

  var html = '';
  Object.values(cart).forEach(function(item){
    html += '<div style="display:flex;justify-content:space-between;font-size:12.5px;margin-bottom:5px">' +
      '<span style="color:var(--sub)">' + item.nama + ' × ' + item.qty + '</span>' +
      '<span style="font-weight:600;color:var(--text)">Rp ' + fmt(item.harga * item.qty) + '</span></div>';
  });
  document.getElementById('pay-items').innerHTML = html;
  document.getElementById('pay-total').textContent = 'Rp ' + fmt(total);
  document.getElementById('pay-total-qris').textContent = 'Rp ' + fmt(total);
  document.getElementById('pay-total-transfer').textContent = 'Rp ' + fmt(total);
  document.getElementById('pay-outlet-info').textContent = outletNama;

  // Reset
  document.getElementById('input-bayar').value = '';
  document.getElementById('kembalian-box').style.display = 'none';
  document.getElementById('btn-konfirmasi').disabled = true;
  setMetode(activeMethods[0] || 'tunai');
  if (document.getElementById('input-bukti-qris'))      { clearBukti('qris');     document.getElementById('input-bukti-qris').value = ''; }
  if (document.getElementById('input-bukti-transfer'))  { clearBukti('transfer'); document.getElementById('input-bukti-transfer').value = ''; }

  // Quick nominal
  var noms = [total, roundUp(total, 10000), roundUp(total, 50000), roundUp(total, 100000)];
  var unique = [...new Set(noms)].slice(0, 4);
  document.getElementById('quick-nominal').innerHTML = unique.map(function(n){
    return '<button type="button" onclick="setBayar(' + n + ')" ' +
      'style="padding:5px 12px;border-radius:8px;font-size:12px;font-weight:600;' +
      'background:var(--surface2);border:1px solid var(--border);cursor:pointer;color:var(--sub);font-family:inherit">' +
      'Rp ' + fmt(n) + '</button>';
  }).join('');

  var backdrop = document.getElementById('pay-backdrop');
  var box      = document.getElementById('pay-box');
  backdrop.style.display = 'flex';
  requestAnimationFrame(function(){ requestAnimationFrame(function(){
    backdrop.style.opacity = '1';
    box.style.opacity      = '1';
    box.style.transform    = 'scale(1) translateY(0)';
    if (metode === 'tunai') document.getElementById('input-bayar').focus();
  }); });
}

function closePayment() {
  var backdrop = document.getElementById('pay-backdrop');
  var box      = document.getElementById('pay-box');
  backdrop.style.opacity = '0';
  box.style.opacity      = '0';
  box.style.transform    = 'scale(.93) translateY(14px)';
  setTimeout(function(){ backdrop.style.display = 'none'; }, 220);
}

function maskBayar(input) {
  var raw = input.value.replace(/\D/g, '');
  var num = parseInt(raw) || 0;
  input.value = num > 0 ? num.toLocaleString('id-ID') : '';
  hitungKembalian();
}

function setBayar(nominal) {
  var input = document.getElementById('input-bayar');
  input.value = nominal > 0 ? nominal.toLocaleString('id-ID') : '';
  hitungKembalian();
}

function hitungKembalian() {
  var total = getTotal();
  var bayar = parseInt(document.getElementById('input-bayar').value.replace(/\D/g, '')) || 0;
  var kem   = bayar - total;
  var box   = document.getElementById('kembalian-box');
  var val   = document.getElementById('kembalian-val');

  if (bayar > 0) {
    box.style.display = 'block';
    if (kem >= 0) {
      val.textContent      = 'Rp ' + fmt(kem);
      val.style.color      = '#34d399';
      box.style.background = 'rgba(52,211,153,.08)';
      box.style.border     = '1px solid rgba(52,211,153,.2)';
    } else {
      val.textContent      = '− Rp ' + fmt(Math.abs(kem));
      val.style.color      = '#f87171';
      box.style.background = 'rgba(239,68,68,.08)';
      box.style.border     = '1px solid rgba(239,68,68,.2)';
    }
  } else {
    box.style.display = 'none';
  }
  validatePayment();
}

function validatePayment() {
  var btn   = document.getElementById('btn-konfirmasi');
  var total = getTotal();
  var ok    = false;

  if (metode === 'tunai') {
    var bayar = parseInt(document.getElementById('input-bayar').value.replace(/\D/g, '')) || 0;
    ok = bayar >= total && total > 0;
  } else {
    var input = document.getElementById('input-bukti-' + metode);
    ok = input && input.files && input.files.length > 0 && total > 0;
  }
  btn.disabled = !ok;
}

function konfirmasiTransaksi() {
  var btn = document.getElementById('btn-konfirmasi');
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses…';

  var items = Object.values(cart).map(function(item){
    return { product_id:item.id, nama:item.nama, harga:item.harga, qty:item.qty, subtotal:item.harga*item.qty };
  });

  var fd = new FormData();
  fd.append('_token',       '{{ csrf_token() }}');
  fd.append('outlet_id',    '{{ $outletId }}');
  fd.append('metode_bayar', metode);
  fd.append('items',        JSON.stringify(items));
  fd.append('keterangan',   document.getElementById('input-ket').value);

  if (metode === 'tunai') {
    fd.append('bayar', parseInt(document.getElementById('input-bayar').value.replace(/\D/g, '')) || 0);
  } else {
    fd.append('bayar', getTotal());
    var buktiInput = document.getElementById('input-bukti-' + metode);
    if (buktiInput.files && buktiInput.files[0]) {
      fd.append('bukti_bayar', buktiInput.files[0]);
    }
  }

  fetch('{{ route("transactions.store") }}', {
    method:  'POST',
    headers: { 'Accept': 'application/json' },
    body:    fd,
  })
  .then(function(res) {
    if (!res.ok) {
      return res.json().then(function(err) { throw err; });
    }
    return res.json();
  })
  .then(function(data) {
    closePayment();
    showSuccessModal(data);
  })
  .catch(function(err) {
    btn.disabled = false;
    btn.innerHTML = '<i class="fa-solid fa-check"></i> Konfirmasi';
    var msg = 'Terjadi kesalahan. Silahkan coba lagi.';
    if (err && err.message) {
      msg = err.message;
    } else if (err && err.errors) {
      // Laravel validation errors
      var first = Object.values(err.errors)[0];
      msg = Array.isArray(first) ? first[0] : first;
    }
    showToast('error', msg);
  });
}

var metodeLabel = { tunai:'Tunai', qris:'QRIS', transfer:'Transfer' };

function showSuccessModal(data) {
  document.getElementById('suc-nomor').textContent    = 'No. ' + data.nomor;
  document.getElementById('suc-total').textContent    = 'Rp ' + fmt(data.total);
  document.getElementById('suc-metode').textContent   = metodeLabel[data.metode] || data.metode;
  document.getElementById('suc-btn-struk').href       = data.receipt_url;

  var kemRow = document.getElementById('suc-kembalian-row');
  if (data.metode === 'tunai' && data.kembalian >= 0) {
    document.getElementById('suc-kembalian').textContent = 'Rp ' + fmt(data.kembalian);
    kemRow.style.display = 'flex';
  } else {
    kemRow.style.display = 'none';
  }

  var backdrop = document.getElementById('success-backdrop');
  var box      = document.getElementById('success-box');
  backdrop.style.display = 'flex';
  requestAnimationFrame(function(){ requestAnimationFrame(function(){
    backdrop.style.opacity  = '1';
    box.style.opacity       = '1';
    box.style.transform     = 'scale(1) translateY(0)';
  }); });
}

function closeSuccessModal() {
  var backdrop = document.getElementById('success-backdrop');
  var box      = document.getElementById('success-box');
  backdrop.style.opacity = '0';
  box.style.opacity      = '0';
  box.style.transform    = 'scale(.92) translateY(16px)';
  setTimeout(function(){ backdrop.style.display = 'none'; }, 220);
}

function transaksiBerikutnya() {
  closeSuccessModal();
  clearCart();
  var btn = document.getElementById('btn-konfirmasi');
  btn.disabled  = true;
  btn.innerHTML = '<i class="fa-solid fa-check"></i> Konfirmasi';
}

// ── Helpers ──
function getTotal() {
  return Object.values(cart).reduce(function(s,i){ return s + i.harga * i.qty; }, 0);
}
function fmt(n) { return Math.round(n).toLocaleString('id-ID'); }
function roundUp(n, to) { return Math.ceil(n / to) * to; }

// Escape tutup modal
document.addEventListener('keydown', function(e){
  if (e.key === 'Escape') {
    if (document.getElementById('success-backdrop').style.display === 'flex') transaksiBerikutnya();
    else closePayment();
  }
});
document.getElementById('pay-backdrop').addEventListener('click', function(e){ if (e.target===this) closePayment(); });
document.getElementById('success-backdrop').addEventListener('click', function(e){ if (e.target===this) transaksiBerikutnya(); });
</script>
@endpush

</x-app-layout>
