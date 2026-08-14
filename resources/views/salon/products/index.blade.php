<x-outlet-layout :outlet="$outlet" pageTitle="Produk">

{{-- ── HEADER ── --}}
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px" class="animate-fadeUp">
  <div>
    <h2 class="font-display" style="font-size:20px;font-weight:700;color:var(--text)">Kelola Produk</h2>
    <p style="font-size:13px;color:var(--muted);margin-top:2px">
      Produk outlet <strong style="color:var(--sub)">{{ $outlet->name }}</strong>
    </p>
  </div>
  @if($user->hasPermission('product.create'))
  <button onclick="openModal('modal-add')"
    style="display:flex;align-items:center;gap:8px;padding:10px 18px;border-radius:12px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13.5px;font-weight:700;cursor:pointer;font-family:inherit;transition:opacity .15s"
    onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
    <i class="fa-solid fa-plus" style="font-size:12px"></i> Tambah Produk
  </button>
  @endif
</div>

{{-- ── STATS ── --}}
@php
  $totalProduk = $products->count();
  $aktif       = $products->where('is_active', true)->count();
  $nonaktif    = $products->where('is_active', false)->count();
  $stokKritis  = $products->filter(fn($p) => $p->isLowStock() && $p->min_stock > 0)->count();
@endphp
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px" class="animate-fadeUp d1">
  <div class="stat-card">
    <div class="stat-icon" style="background:var(--ac-lt);color:var(--ac)"><i class="fa-solid fa-cubes"></i></div>
    <div><div class="stat-num">{{ $totalProduk }}</div><div class="stat-label">Total Produk</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(16,185,129,.15);color:#34d399"><i class="fa-solid fa-circle-check"></i></div>
    <div><div class="stat-num">{{ $aktif }}</div><div class="stat-label">Aktif</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(148,163,184,.12);color:#94a3b8"><i class="fa-solid fa-circle-xmark"></i></div>
    <div><div class="stat-num">{{ $nonaktif }}</div><div class="stat-label">Nonaktif</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(248,113,113,.15);color:#f87171"><i class="fa-solid fa-triangle-exclamation"></i></div>
    <div><div class="stat-num">{{ $stokKritis }}</div><div class="stat-label">Stok Kritis</div></div>
  </div>
</div>

{{-- ── TABLE ── --}}
<div class="card animate-fadeUp d2">
  <div class="card-header" style="flex-wrap:wrap;gap:10px">
    <span class="card-title">
      <i class="fa-solid fa-cubes" style="color:var(--ac);margin-right:8px;font-size:14px"></i>Daftar Produk
    </span>
    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-left:auto">
      @if($categories->isNotEmpty())
      <select id="filter-cat" onchange="applyFilters()"
        style="padding:7px 12px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13px;font-family:inherit;outline:none;cursor:pointer">
        <option value="">Semua Kategori</option>
        @foreach($categories as $cat)
        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
        @endforeach
      </select>
      @endif
      <select id="filter-status" onchange="applyFilters()"
        style="padding:7px 12px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13px;font-family:inherit;outline:none;cursor:pointer">
        <option value="">Semua Status</option>
        <option value="active">Aktif</option>
        <option value="inactive">Nonaktif</option>
        <option value="low">Stok Kritis</option>
      </select>
      <div style="position:relative">
        <input id="search-input" oninput="applyFilters()" placeholder="Cari nama / SKU…"
          style="padding:7px 12px 7px 32px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--text);font-size:13px;font-family:inherit;outline:none;width:190px;transition:border-color .15s"
          onfocus="this.style.borderColor='var(--ac)'" onblur="this.style.borderColor='var(--border)'">
        <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:12px;pointer-events:none"></i>
      </div>
    </div>
  </div>

  @if($products->isEmpty())
  <div style="padding:64px 24px;text-align:center">
    <div style="width:60px;height:60px;border-radius:16px;background:var(--ac-lt);display:grid;place-items:center;margin:0 auto 16px;font-size:22px;color:var(--ac)">
      <i class="fa-solid fa-cubes"></i>
    </div>
    <h3 class="font-display" style="font-size:17px;font-weight:700;color:var(--text);margin-bottom:6px">Belum Ada Produk</h3>
    <p style="font-size:13.5px;color:var(--muted);max-width:340px;margin:0 auto 20px">
      Tambahkan produk yang dijual di outlet ini.
    </p>
    @if($user->hasPermission('product.create'))
    <button onclick="openModal('modal-add')"
      style="padding:10px 22px;border-radius:12px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13.5px;font-weight:700;cursor:pointer;font-family:inherit">
      <i class="fa-solid fa-plus" style="margin-right:6px;font-size:12px"></i>Tambah Produk Pertama
    </button>
    @endif
  </div>
  @else
  <div style="overflow-x:auto">
    <table class="tbl" id="prod-table">
      <thead>
        <tr>
          <th style="width:44px">#</th>
          <th>Produk</th>
          <th>Kategori</th>
          <th style="text-align:right">Harga Jual</th>
          @if($trackCogs)
          <th style="text-align:right">HPP</th>
          @endif
          <th style="text-align:center;width:80px">Stok</th>
          <th style="text-align:center;width:90px">Status</th>
          <th style="text-align:center;width:110px">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @foreach($products as $i => $product)
        <tr class="prod-row"
          data-name="{{ strtolower($product->name) }}"
          data-sku="{{ strtolower($product->sku ?? '') }}"
          data-cat="{{ $product->category_id ?? '' }}"
          data-status="{{ $product->is_active ? 'active' : 'inactive' }}"
          data-low="{{ ($product->isLowStock() && $product->min_stock > 0) ? '1' : '0' }}">
          <td style="color:var(--muted);font-size:12px">{{ $i + 1 }}</td>
          <td class="td-main" style="min-width:200px">
            <div style="display:flex;align-items:center;gap:10px">
              {{-- Thumbnail --}}
              @if($product->image)
              <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}"
                style="width:38px;height:38px;border-radius:10px;object-fit:cover;flex-shrink:0;border:1px solid var(--border)">
              @else
              <div style="width:38px;height:38px;border-radius:10px;background:var(--ac-lt);color:var(--ac);display:grid;place-items:center;font-size:15px;flex-shrink:0">
                <i class="fa-solid fa-box"></i>
              </div>
              @endif
              <div style="min-width:0">
                <div style="font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:200px">
                  {{ $product->name }}
                </div>
                <div style="font-size:11.5px;color:var(--muted);display:flex;align-items:center;gap:5px;margin-top:1px">
                  @if($product->sku)
                    <span style="font-family:monospace;background:var(--surface2);padding:1px 5px;border-radius:4px">{{ $product->sku }}</span>
                    <span>·</span>
                  @endif
                  <span>{{ $product->unit }}</span>
                </div>
              </div>
            </div>
          </td>
          <td>
            @if($product->category)
              <span class="badge" style="background:var(--ac-lt2);color:var(--ac)">{{ $product->category->name }}</span>
            @else
              <span style="font-size:12px;color:var(--muted)">—</span>
            @endif
          </td>
          <td style="text-align:right;font-weight:700;color:var(--text)">
            Rp {{ number_format($product->price, 0, ',', '.') }}
          </td>
          @if($trackCogs)
          <td style="text-align:right;color:var(--sub);font-size:13px">
            {{ $product->cost_price !== null ? 'Rp '.number_format($product->cost_price, 0, ',', '.') : '—' }}
          </td>
          @endif
          <td style="text-align:center">
            @php $low = $product->isLowStock() && $product->min_stock > 0; @endphp
            <div style="display:flex;flex-direction:column;align-items:center;gap:1px">
              <span style="font-size:14px;font-weight:700;color:{{ $low ? '#f87171' : 'var(--text)' }}">
                {{ $product->stock }}
              </span>
              @if($product->min_stock > 0)
                <span style="font-size:10px;color:var(--muted)">min {{ $product->min_stock }}</span>
              @endif
              @if($low)
                <span style="font-size:9px;font-weight:700;background:rgba(248,113,113,.15);color:#f87171;padding:1px 5px;border-radius:99px;line-height:1.6">KRITIS</span>
              @endif
            </div>
          </td>
          <td style="text-align:center">
            @if($product->is_active)
              <span class="badge badge-green"><i class="fa-solid fa-circle" style="font-size:6px"></i>Aktif</span>
            @else
              <span class="badge badge-gray"><i class="fa-solid fa-circle" style="font-size:6px"></i>Nonaktif</span>
            @endif
          </td>
          <td style="text-align:center">
            <div style="display:flex;align-items:center;justify-content:center;gap:5px">
              @if($trackCogs && $user->hasPermission('product.edit'))
              <a href="{{ $outlet->route('products.price.edit', [$product]) }}"
                title="Ubah Harga Jual"
                style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:transparent;cursor:pointer;color:#a78bfa;font-size:12px;transition:all .15s;display:grid;place-items:center"
                onmouseover="this.style.background='rgba(167,139,250,.12)';this.style.color='#a78bfa'"
                onmouseout="this.style.background='transparent';this.style.color='#a78bfa'">
                <i class="fa-solid fa-tag"></i>
              </a>
              @endif
              @if($user->hasPermission('product.edit'))
              <button title="Edit"
                onclick='openEdit({{ $product->id }}, {{ json_encode([
                  "name"        => $product->name,
                  "category_id" => $product->category_id,
                  "sku"         => $product->sku,
                  "unit"        => $product->unit,
                  "price"       => $product->price,
                  "cost_price"  => $product->cost_price,
                  "min_stock"   => $product->min_stock,
                  "description" => $product->description,
                  "image"       => $product->image ? Storage::url($product->image) : null,
                ]) }})'
                style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:transparent;cursor:pointer;color:var(--sub);font-size:12px;transition:all .15s"
                onmouseover="this.style.background='var(--surface2)';this.style.color='var(--text)'"
                onmouseout="this.style.background='transparent';this.style.color='var(--sub)'">
                <i class="fa-solid fa-pen"></i>
              </button>
              <form method="POST" action="{{ $outlet->route('products.toggle-active', [$product]) }}" style="margin:0">
                @csrf
                <button type="submit" title="{{ $product->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                  style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:transparent;cursor:pointer;font-size:12px;color:{{ $product->is_active ? '#fbbf24' : '#34d399' }};transition:all .15s"
                  onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
                  <i class="fa-solid {{ $product->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                </button>
              </form>
              @endif
              @if($user->hasPermission('product.delete'))
              <button title="Hapus" onclick="openDelete({{ $product->id }}, '{{ addslashes($product->name) }}')"
                style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:transparent;cursor:pointer;color:#f87171;font-size:12px;transition:all .15s"
                onmouseover="this.style.background='rgba(239,68,68,.1)'" onmouseout="this.style.background='transparent'">
                <i class="fa-solid fa-trash-can"></i>
              </button>
              @endif
            </div>
          </td>
        </tr>
        @endforeach
        <tr id="no-result" style="display:none">
          <td colspan="{{ $trackCogs ? 8 : 7 }}" style="text-align:center;padding:32px;color:var(--muted);font-size:13px">
            <i class="fa-solid fa-magnifying-glass" style="margin-right:6px"></i>Tidak ditemukan
          </td>
        </tr>
      </tbody>
    </table>
  </div>
  @endif
</div>

{{-- ══ MODAL TAMBAH ══ --}}
@if($user->hasPermission('product.create'))
<div class="modal-backdrop" id="modal-add" onclick="if(event.target===this)closeModal('modal-add')">
  <div class="modal-box" style="max-width:500px">
    <form method="POST" action="{{ $outlet->route('products.store') }}" enctype="multipart/form-data">
      @csrf
      <div style="padding:20px 24px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <h3 class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">Tambah Produk</h3>
        <button type="button" onclick="closeModal('modal-add')" style="width:30px;height:30px;border-radius:8px;border:none;background:var(--surface2);cursor:pointer;color:var(--sub);font-size:14px">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <div style="padding:20px 24px;display:flex;flex-direction:column;gap:14px;max-height:68vh;overflow-y:auto">

        {{-- Gambar produk --}}
        <div>
          <label class="f-label">Foto Produk <span style="color:var(--muted);font-weight:400">(opsional · max 2MB)</span></label>
          <label id="add-img-label" for="add-img-input"
            style="display:flex;align-items:center;justify-content:center;flex-direction:column;gap:8px;border:2px dashed var(--border);border-radius:14px;padding:20px;cursor:pointer;transition:border-color .15s;position:relative;overflow:hidden;min-height:90px"
            onmouseover="this.style.borderColor='var(--ac)'" onmouseout="this.style.borderColor='var(--border)'">
            <div id="add-img-placeholder" style="display:flex;flex-direction:column;align-items:center;gap:6px;pointer-events:none">
              <i class="fa-solid fa-image" style="font-size:22px;color:var(--muted)"></i>
              <span style="font-size:12.5px;color:var(--muted)">Klik untuk pilih gambar</span>
              <span style="font-size:11px;color:var(--muted);opacity:.7">JPG, PNG, WebP</span>
            </div>
            <img id="add-img-preview" src="" alt=""
              style="display:none;max-height:130px;max-width:100%;border-radius:8px;object-fit:contain">
          </label>
          <input type="file" id="add-img-input" name="image" accept="image/jpeg,image/png,image/webp"
            style="display:none" onchange="previewImg(this,'add-img-preview','add-img-placeholder')">
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
          {{-- Nama --}}
          <div style="grid-column:1/-1">
            <label class="f-label">Nama Produk <span style="color:var(--ac)">*</span></label>
            <input name="name" class="f-input" required maxlength="150" placeholder="cth: Potong Rambut">
          </div>
          {{-- Kategori --}}
          <div>
            <label class="f-label">Kategori</label>
            <select name="category_id" class="f-input">
              <option value="">— Tanpa Kategori —</option>
              @foreach($categories as $cat)
              <option value="{{ $cat->id }}">{{ $cat->name }}</option>
              @endforeach
            </select>
          </div>
          {{-- Satuan --}}
          <div>
            <label class="f-label">Satuan <span style="color:var(--ac)">*</span></label>
            <input name="unit" class="f-input" required maxlength="30" value="sesi" list="unit-list-add"
              placeholder="sesi, paket, pcs…">
            <datalist id="unit-list-add">
              <option value="sesi"><option value="paket"><option value="pcs"><option value="kali">
              <option value="orang"><option value="jam"><option value="menit">
            </datalist>
          </div>
          {{-- SKU --}}
          <div>
            <label class="f-label">SKU / Barcode <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
            <input name="sku" class="f-input" maxlength="60" placeholder="cth: NGS-001">
          </div>
          {{-- Harga jual --}}
          <div>
            <label class="f-label">Harga Jual <span style="color:var(--ac)">*</span></label>
            <div style="position:relative">
              <span style="position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:12px;color:var(--muted);font-weight:600;pointer-events:none">Rp</span>
              <input type="text" id="add-price-display" inputmode="numeric" class="f-input" required placeholder="0"
                style="padding-left:30px" oninput="syncPrice(this,'add-price-raw')" onblur="formatOnBlur(this,'add-price-raw')">
              <input type="hidden" name="price" id="add-price-raw" value="">
            </div>
          </div>
          {{-- HPP (hanya jika track_cogs) --}}
          @if($trackCogs)
          <div>
            <label class="f-label">HPP / Harga Modal</label>
            <div style="position:relative">
              <span style="position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:12px;color:var(--muted);font-weight:600;pointer-events:none">Rp</span>
              <input type="text" id="add-cost-display" inputmode="numeric" class="f-input" placeholder="0"
                style="padding-left:30px" oninput="syncPrice(this,'add-cost-raw')" onblur="formatOnBlur(this,'add-cost-raw')">
              <input type="hidden" name="cost_price" id="add-cost-raw" value="">
            </div>
          </div>
          @endif
          {{-- Min stok (hanya jika track stok) --}}
          @if($trackCogs)
          <div>
            <label class="f-label">Min. Stok Kritis <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
            <input name="min_stock" type="number" class="f-input" value="0" min="0">
            <p style="font-size:11px;color:var(--muted);margin-top:3px">Alert jika stok ≤ nilai ini</p>
          </div>
          @endif
          {{-- Deskripsi --}}
          <div style="grid-column:1/-1">
            <label class="f-label">Deskripsi <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
            <textarea name="description" class="f-input" rows="2" maxlength="1000"
              placeholder="Deskripsi singkat produk"></textarea>
          </div>
        </div>
      </div>
      <div style="padding:16px 24px 20px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
        <button type="button" onclick="closeModal('modal-add')"
          style="padding:9px 18px;border-radius:11px;border:1px solid var(--border);background:transparent;cursor:pointer;font-size:13px;font-weight:600;color:var(--sub);font-family:inherit">Batal</button>
        <button type="submit"
          style="padding:9px 22px;border-radius:11px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit">Simpan</button>
      </div>
    </form>
  </div>
</div>

@endif

{{-- ══ MODAL EDIT ══ --}}
@if($user->hasPermission('product.edit'))
<div class="modal-backdrop" id="modal-edit" onclick="if(event.target===this)closeModal('modal-edit')">
  <div class="modal-box" style="max-width:500px">
    <form id="form-edit" method="POST" action="" enctype="multipart/form-data">
      @csrf @method('PUT')
      <input type="hidden" name="remove_image" id="e-remove-img" value="0">
      <div style="padding:20px 24px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <h3 class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">Edit Produk</h3>
        <button type="button" onclick="closeModal('modal-edit')" style="width:30px;height:30px;border-radius:8px;border:none;background:var(--surface2);cursor:pointer;color:var(--sub);font-size:14px">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <div style="padding:20px 24px;display:flex;flex-direction:column;gap:14px;max-height:68vh;overflow-y:auto">

        {{-- Gambar --}}
        <div>
          <label class="f-label">Foto Produk <span style="color:var(--muted);font-weight:400">(opsional · max 2MB)</span></label>
          <div id="e-img-wrap" style="position:relative">
            {{-- Existing / preview --}}
            <div id="e-img-current"
              style="display:none;position:relative;border:1px solid var(--border);border-radius:14px;overflow:hidden;background:var(--surface2)">
              <img id="e-img-thumb" src="" alt="foto produk"
                style="width:100%;max-height:130px;object-fit:contain;display:block;padding:8px">
              <button type="button" onclick="removeImg()"
                style="position:absolute;top:8px;right:8px;width:26px;height:26px;border-radius:7px;background:rgba(239,68,68,.85);border:none;cursor:pointer;color:#fff;font-size:11px"
                title="Hapus foto">
                <i class="fa-solid fa-xmark"></i>
              </button>
            </div>
            {{-- Upload area --}}
            <label id="e-img-label" for="e-img-input"
              style="display:flex;align-items:center;justify-content:center;flex-direction:column;gap:8px;border:2px dashed var(--border);border-radius:14px;padding:20px;cursor:pointer;transition:border-color .15s;min-height:90px"
              onmouseover="this.style.borderColor='var(--ac)'" onmouseout="this.style.borderColor='var(--border)'">
              <i class="fa-solid fa-arrow-up-from-bracket" style="font-size:18px;color:var(--muted)"></i>
              <span style="font-size:12.5px;color:var(--muted)">Klik untuk ganti gambar</span>
            </label>
            <input type="file" id="e-img-input" name="image" accept="image/jpeg,image/png,image/webp"
              style="display:none" onchange="editPreviewImg(this)">
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
          <div style="grid-column:1/-1">
            <label class="f-label">Nama Produk <span style="color:var(--ac)">*</span></label>
            <input name="name" id="e-name" class="f-input" required maxlength="150">
          </div>
          <div>
            <label class="f-label">Kategori</label>
            <select name="category_id" id="e-cat" class="f-input">
              <option value="">— Tanpa Kategori —</option>
              @foreach($categories as $cat)
              <option value="{{ $cat->id }}">{{ $cat->name }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="f-label">Satuan <span style="color:var(--ac)">*</span></label>
            <input name="unit" id="e-unit" class="f-input" required maxlength="30" list="unit-list-edit">
            <datalist id="unit-list-edit">
              <option value="pcs"><option value="porsi"><option value="gelas"><option value="piring">
              <option value="botol"><option value="kg"><option value="gram"><option value="liter"><option value="ml">
            </datalist>
          </div>
          <div>
            <label class="f-label">SKU / Barcode</label>
            <input name="sku" id="e-sku" class="f-input" maxlength="60">
          </div>
          <div>
            <label class="f-label">Harga Jual <span style="color:var(--ac)">*</span></label>
            <div style="position:relative">
              <span style="position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:12px;color:var(--muted);font-weight:600;pointer-events:none">Rp</span>
              <input type="text" id="e-price-display" inputmode="numeric" class="f-input" required
                style="padding-left:30px" oninput="syncPrice(this,'e-price-raw')" onblur="formatOnBlur(this,'e-price-raw')">
              <input type="hidden" name="price" id="e-price-raw">
            </div>
          </div>
          @if($trackCogs)
          <div>
            <label class="f-label">HPP / Harga Modal</label>
            <div style="position:relative">
              <span style="position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:12px;color:var(--muted);font-weight:600;pointer-events:none">Rp</span>
              <input type="text" id="e-cost-display" inputmode="numeric" class="f-input"
                style="padding-left:30px" oninput="syncPrice(this,'e-cost-raw')" onblur="formatOnBlur(this,'e-cost-raw')">
              <input type="hidden" name="cost_price" id="e-cost-raw">
            </div>
          </div>
          @endif
          @if($trackCogs)
          <div>
            <label class="f-label">Min. Stok Kritis</label>
            <input name="min_stock" id="e-min" type="number" class="f-input" min="0">
            <p style="font-size:11px;color:var(--muted);margin-top:3px">Alert jika stok ≤ nilai ini</p>
          </div>
          @endif
          <div style="grid-column:1/-1">
            <label class="f-label">Deskripsi</label>
            <textarea name="description" id="e-desc" class="f-input" rows="2" maxlength="1000"></textarea>
          </div>
        </div>
      </div>
      <div style="padding:16px 24px 20px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
        <button type="button" onclick="closeModal('modal-edit')"
          style="padding:9px 18px;border-radius:11px;border:1px solid var(--border);background:transparent;cursor:pointer;font-size:13px;font-weight:600;color:var(--sub);font-family:inherit">Batal</button>
        <button type="submit"
          style="padding:9px 22px;border-radius:11px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

@endif

{{-- ══ MODAL HAPUS ══ --}}
@if($user->hasPermission('product.delete'))
<div class="modal-backdrop" id="modal-delete" onclick="if(event.target===this)closeModal('modal-delete')">
  <div class="modal-box" style="max-width:380px">
    <form id="form-delete" method="POST" action="">
      @csrf @method('DELETE')
      <div style="padding:28px 24px 20px;text-align:center">
        <div style="width:52px;height:52px;border-radius:14px;background:rgba(239,68,68,.15);display:grid;place-items:center;margin:0 auto 16px;color:#f87171;font-size:20px">
          <i class="fa-solid fa-trash-can"></i>
        </div>
        <h3 class="font-display" style="font-size:17px;font-weight:700;color:var(--text);margin-bottom:6px">Hapus Produk?</h3>
        <p style="font-size:13.5px;color:var(--muted)">
          Produk <strong id="delete-name" style="color:var(--text)"></strong> akan dihapus permanen.
        </p>
      </div>
      <div style="padding:0 24px 24px;display:flex;gap:10px">
        <button type="button" onclick="closeModal('modal-delete')"
          style="flex:1;padding:10px;border-radius:11px;border:1px solid var(--border);background:transparent;cursor:pointer;font-size:13.5px;font-weight:600;color:var(--sub);font-family:inherit">Batal</button>
        <button type="submit"
          style="flex:1;padding:10px;border-radius:11px;border:none;background:linear-gradient(135deg,#ef4444,#dc2626);color:#fff;font-size:13.5px;font-weight:700;cursor:pointer;font-family:inherit">Hapus</button>
      </div>
    </form>
  </div>
</div>
@endif

@push('scripts')
<script>
const outletId = {{ $outlet->id }};
const outletRp = '{{ $outlet->rp() }}';

/* ── Rupiah format helpers ── */
function toFormatted(raw) {
  if (!raw) return '';
  return new Intl.NumberFormat('id-ID').format(parseInt(raw, 10));
}
function syncPrice(displayEl, rawId) {
  // Hitung jumlah digit sebelum kursor (abaikan titik pemisah)
  const before = displayEl.value.substring(0, displayEl.selectionStart);
  const digitsBeforeCursor = (before.match(/\d/g) || []).length;

  const raw = displayEl.value.replace(/\D/g, '');
  document.getElementById(rawId).value = raw;

  const formatted = raw ? toFormatted(raw) : '';
  displayEl.value = formatted;

  // Cari posisi kursor di string terformat berdasarkan jumlah digit yang sama
  let seen = 0, newPos = formatted.length;
  for (let i = 0; i < formatted.length; i++) {
    if (/\d/.test(formatted[i])) seen++;
    if (seen === digitsBeforeCursor) { newPos = i + 1; break; }
  }
  try { displayEl.setSelectionRange(newPos, newPos); } catch (_) {}
}
function formatOnBlur(displayEl, rawId) {
  const raw = displayEl.value.replace(/\D/g, '');
  document.getElementById(rawId).value = raw;
  displayEl.value = raw ? toFormatted(raw) : '';
}
function setPrice(displayId, rawId, value) {
  const raw = value != null ? String(value) : '';
  document.getElementById(rawId).value = raw;
  document.getElementById(displayId).value = raw ? toFormatted(raw) : '';
}

/* ── Image preview (add form) ── */
function previewImg(input, previewId, placeholderId) {
  const file = input.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = e => {
    const prev = document.getElementById(previewId);
    const ph   = document.getElementById(placeholderId);
    prev.src = e.target.result;
    prev.style.display = 'block';
    ph.style.display = 'none';
  };
  reader.readAsDataURL(file);
}

/* ── Image preview (edit form) ── */
function editPreviewImg(input) {
  const file = input.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = e => {
    document.getElementById('e-img-thumb').src = e.target.result;
    document.getElementById('e-img-current').style.display = 'block';
    document.getElementById('e-img-label').style.display = 'none';
    document.getElementById('e-remove-img').value = '0';
  };
  reader.readAsDataURL(file);
}
function removeImg() {
  document.getElementById('e-img-current').style.display = 'none';
  document.getElementById('e-img-label').style.display = 'flex';
  document.getElementById('e-remove-img').value = '1';
  document.getElementById('e-img-input').value = '';
}

/* ── Open edit ── */
function openEdit(id, d) {
  document.getElementById('form-edit').action = `/${outletRp}/${outletId}/products/${id}`;
  document.getElementById('e-name').value = d.name  || '';
  document.getElementById('e-unit').value = d.unit  || 'pcs';
  document.getElementById('e-sku').value  = d.sku   || '';
  const eMin = document.getElementById('e-min'); if (eMin) eMin.value = d.min_stock ?? 0;
  document.getElementById('e-desc').value = d.description || '';
  const catEl = document.getElementById('e-cat');
  if (catEl) catEl.value = d.category_id ?? '';
  setPrice('e-price-display', 'e-price-raw', d.price);
  const costDisp = document.getElementById('e-cost-display');
  if (costDisp) setPrice('e-cost-display', 'e-cost-raw', d.cost_price);

  // Handle existing image
  document.getElementById('e-remove-img').value = '0';
  document.getElementById('e-img-input').value = '';
  if (d.image) {
    document.getElementById('e-img-thumb').src = d.image;
    document.getElementById('e-img-current').style.display = 'block';
    document.getElementById('e-img-label').style.display = 'none';
  } else {
    document.getElementById('e-img-current').style.display = 'none';
    document.getElementById('e-img-label').style.display = 'flex';
  }

  openModal('modal-edit');
}

/* ── Open delete ── */
function openDelete(id, name) {
  document.getElementById('form-delete').action = `/${outletRp}/${outletId}/products/${id}`;
  document.getElementById('delete-name').textContent = name;
  openModal('modal-delete');
}

/* ── Filter table ── */
function applyFilters() {
  const q      = (document.getElementById('search-input')?.value || '').toLowerCase().trim();
  const catId  = document.getElementById('filter-cat')?.value  || '';
  const status = document.getElementById('filter-status')?.value || '';
  let visible  = 0;

  document.querySelectorAll('.prod-row').forEach(r => {
    const ok = (!q      || r.dataset.name.includes(q) || r.dataset.sku.includes(q))
            && (!catId  || r.dataset.cat === catId)
            && (!status ||
                (status === 'active'   && r.dataset.status === 'active') ||
                (status === 'inactive' && r.dataset.status === 'inactive') ||
                (status === 'low'      && r.dataset.low === '1'));
    r.style.display = ok ? '' : 'none';
    if (ok) visible++;
  });
  const nr = document.getElementById('no-result');
  if (nr) nr.style.display = visible === 0 ? '' : 'none';
}

@if($errors->any())
  @if(old('_method') === 'PUT') openModal('modal-edit');
  @else openModal('modal-add');
  @endif
@endif
</script>
@endpush

</x-outlet-layout>
