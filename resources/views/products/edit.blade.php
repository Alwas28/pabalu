<x-app-layout title="Edit Produk">

@push('styles')
<style>
.form-grid-2col {
  display: grid;
  grid-template-columns: 1fr 300px;
  gap: 20px;
  align-items: start;
}
@media (max-width: 768px) {
  .form-grid-2col {
    grid-template-columns: 1fr;
  }
}
</style>
@endpush

  <div style="display:flex;align-items:center;gap:8px;font-size:12.5px;color:var(--muted)">
    <a href="{{ route('products.index') }}" style="color:var(--muted);text-decoration:none;transition:color .15s"
      onmouseover="this.style.color='var(--ac)'" onmouseout="this.style.color='var(--muted)'">
      <i class="fa-solid fa-cubes"></i> Kelola Produk
    </a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px"></i>
    <span style="color:var(--text)">Edit: {{ $product->nama }}</span>
  </div>

  <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="form-grid-2col">

      {{-- Kiri --}}
      <div style="display:flex;flex-direction:column;gap:20px">

        <div class="card animate-fadeUp">
          <div class="card-header">
            <div class="card-title"><i class="fa-solid fa-cube" style="color:var(--ac);margin-right:8px"></i>Informasi Produk</div>
          </div>
          <div class="card-body">

            <div class="f-row">
              <div class="f-group">
                <label for="outlet_id" class="f-label">Outlet <span style="color:#f87171">*</span></label>
                <select id="outlet_id" name="outlet_id" class="f-input" required>
                  <option value="">— Pilih Outlet —</option>
                  @foreach($outlets as $o)
                  <option value="{{ $o->id }}" @selected(old('outlet_id', $product->outlet_id) == $o->id)>{{ $o->nama }}</option>
                  @endforeach
                </select>
                @error('outlet_id')<div class="f-error"><i class="fa-solid fa-circle-exclamation" style="font-size:10px"></i>{{ $message }}</div>@enderror
              </div>
              <div class="f-group">
                <label for="category_id" class="f-label">Kategori</label>
                <select id="category_id" name="category_id" class="f-input">
                  <option value="">— Tanpa Kategori —</option>
                  @foreach($categories as $c)
                  <option value="{{ $c->id }}" @selected(old('category_id', $product->category_id) == $c->id)>{{ $c->nama }}</option>
                  @endforeach
                </select>
              </div>
            </div>

            <div class="f-group">
              <label for="nama" class="f-label">Nama Produk <span style="color:#f87171">*</span></label>
              <input id="nama" name="nama" type="text" class="f-input"
                value="{{ old('nama', $product->nama) }}" required placeholder="cth. Nasi Goreng Spesial">
              @error('nama')<div class="f-error"><i class="fa-solid fa-circle-exclamation" style="font-size:10px"></i>{{ $message }}</div>@enderror
            </div>

            <div class="f-row">
              <div class="f-group">
                <label for="kode" class="f-label">Kode Produk</label>
                <input id="kode" name="kode" type="text" class="f-input"
                  value="{{ old('kode', $product->kode) }}" placeholder="cth. PRD-001" style="font-family:monospace">
              </div>
              <div class="f-group">
                <label for="satuan" class="f-label">Satuan <span style="color:#f87171">*</span></label>
                <input id="satuan" name="satuan" type="text" class="f-input"
                  value="{{ old('satuan', $product->satuan) }}" required placeholder="pcs, porsi, gelas…"
                  list="satuan-list">
                <datalist id="satuan-list">
                  <option value="porsi"><option value="pcs"><option value="gelas">
                  <option value="mangkuk"><option value="botol"><option value="kotak">
                  <option value="bungkus"><option value="loyang"><option value="kg">
                </datalist>
              </div>
            </div>

            <div class="f-group" style="margin-bottom:0">
              <label for="deskripsi" class="f-label">Deskripsi</label>
              <textarea id="deskripsi" name="deskripsi" class="f-input" rows="2"
                placeholder="Deskripsi singkat…">{{ old('deskripsi', $product->deskripsi) }}</textarea>
            </div>

          </div>
        </div>

        <div class="card animate-fadeUp d1">
          <div class="card-header">
            <div class="card-title"><i class="fa-solid fa-money-bill-wave" style="color:var(--ac);margin-right:8px"></i>Harga</div>
          </div>
          <div class="card-body">
            <div class="f-group" style="margin-bottom:0">
              <label for="harga_jual_display" class="f-label">Harga Jual <span style="color:#f87171">*</span></label>
              <div style="position:relative">
                <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:12.5px;font-weight:600">Rp</span>
                <input id="harga_jual_display" type="text" class="f-input" inputmode="numeric" autocomplete="off"
                  placeholder="0" required style="padding-left:34px"
                  value="{{ old('harga_jual') ? number_format((int)old('harga_jual'), 0, ',', '.') : number_format($product->harga_jual, 0, ',', '.') }}"
                  oninput="maskRupiah(this, 'harga_jual')">
              </div>
              <input type="hidden" name="harga_jual" id="harga_jual" value="{{ old('harga_jual', $product->harga_jual) }}">
              @error('harga_jual')<div class="f-error"><i class="fa-solid fa-circle-exclamation" style="font-size:10px"></i>{{ $message }}</div>@enderror
            </div>
          </div>
        </div>

      </div>

      {{-- Kanan --}}
      <div class="col-aside" style="display:flex;flex-direction:column;gap:16px">

        <div class="card animate-fadeUp" style="border-color:var(--ac)44">
          <div class="card-body" style="padding:18px;text-align:center">
            <div style="width:44px;height:44px;border-radius:12px;display:grid;place-items:center;
                        margin:0 auto 8px;background:var(--ac-lt);color:var(--ac);font-size:18px">
              <i class="fa-solid fa-cube"></i>
            </div>
            <div style="font-family:'Clash Display',sans-serif;font-size:14px;font-weight:700;color:var(--text)">{{ $product->nama }}</div>
            @if($product->kode)
            <code style="font-size:11px;color:var(--muted)">{{ $product->kode }}</code>
            @endif
            <div style="font-size:11px;color:var(--muted);margin-top:6px">
              {{ $product->outlet?->nama ?? '—' }} &bull; {{ $product->category?->nama ?? 'Tanpa Kategori' }}
            </div>
          </div>
        </div>

        {{-- Gambar Produk --}}
        <div class="card animate-fadeUp">
          <div class="card-header">
            <div class="card-title"><i class="fa-solid fa-image" style="color:var(--ac);margin-right:8px"></i>Foto Produk</div>
          </div>
          <div class="card-body">
            @if($product->gambar)
            <div id="img-current-wrap" style="margin-bottom:12px;position:relative">
              <img id="img-preview" src="{{ Storage::url($product->gambar) }}" alt="{{ $product->nama }}"
                style="width:100%;border-radius:10px;object-fit:cover;max-height:180px;border:1px solid var(--border)">
              <label style="display:flex;align-items:center;gap:6px;margin-top:8px;cursor:pointer;font-size:13px;color:var(--muted)">
                <input type="checkbox" name="hapus_gambar" value="1" id="hapus_gambar"
                  onchange="toggleHapusGambar(this)" style="accent-color:#f87171;cursor:pointer">
                Hapus foto ini
              </label>
            </div>
            @else
            <div id="img-preview-wrap" style="display:none;margin-bottom:12px">
              <img id="img-preview" src="" alt="Preview"
                style="width:100%;border-radius:10px;object-fit:cover;max-height:180px;border:1px solid var(--border)">
            </div>
            @endif
            <label for="gambar" id="img-drop-zone"
              style="display:flex;flex-direction:column;align-items:center;justify-content:center;
                     gap:8px;border:2px dashed var(--border);border-radius:12px;padding:20px 16px;
                     cursor:pointer;transition:border-color .15s;text-align:center">
              <i class="fa-solid fa-cloud-arrow-up" style="font-size:22px;color:var(--muted)"></i>
              <div style="font-size:13px;font-weight:600;color:var(--sub)">
                {{ $product->gambar ? 'Ganti foto' : 'Klik atau seret foto ke sini' }}
              </div>
              <div style="font-size:11.5px;color:var(--muted)">JPG, PNG, WEBP — maks. 2 MB</div>
            </label>
            <input type="file" id="gambar" name="gambar" accept="image/*" style="display:none"
              onchange="previewImg(this)">
            @error('gambar')<div class="f-error" style="margin-top:6px"><i class="fa-solid fa-circle-exclamation" style="font-size:10px"></i>{{ $message }}</div>@enderror
          </div>
        </div>

        <div class="card animate-fadeUp d1">
          <div class="card-header">
            <div class="card-title"><i class="fa-solid fa-toggle-on" style="color:var(--ac);margin-right:8px"></i>Status</div>
          </div>
          <div class="card-body">
            <label style="display:flex;align-items:center;justify-content:space-between;cursor:pointer;gap:12px">
              <div>
                <div style="font-size:13px;font-weight:600;color:var(--text)">Produk Aktif</div>
                <div style="font-size:12px;color:var(--muted);margin-top:2px">Tersedia untuk dijual di POS</div>
              </div>
              <div style="position:relative;flex-shrink:0">
                <input type="checkbox" name="is_active" value="1" id="is_active"
                  @checked(old('is_active', $product->is_active))
                  style="appearance:none;width:42px;height:24px;border-radius:99px;background:var(--surface2);
                         border:1px solid var(--border);cursor:pointer;transition:background .2s"
                  onchange="syncThumb()">
                <div id="toggle-thumb" style="position:absolute;top:3px;left:3px;width:16px;height:16px;border-radius:50%;background:#fff;pointer-events:none;transition:transform .2s"></div>
              </div>
            </label>
          </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:8px">
          <button type="submit" class="btn btn-primary" style="justify-content:center;padding:11px">
            <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
          </button>
          <a href="{{ route('products.index') }}" class="btn" style="justify-content:center;padding:11px;text-decoration:none">
            <i class="fa-solid fa-arrow-left"></i> Kembali
          </a>
        </div>
      </div>

    </div>
  </form>

  @push('scripts')
  <script>
  var cb    = document.getElementById('is_active');
  var thumb = document.getElementById('toggle-thumb');
  function syncThumb() {
    thumb.style.transform = cb.checked ? 'translateX(18px)' : 'translateX(0)';
    cb.style.background   = cb.checked ? 'var(--ac)' : 'var(--surface2)';
  }
  syncThumb();

  function previewImg(input) {
    if (!input.files || !input.files[0]) return;
    var reader = new FileReader();
    reader.onload = function(e) {
      var preview = document.getElementById('img-preview');
      var wrap    = document.getElementById('img-preview-wrap') || document.getElementById('img-current-wrap');
      if (preview) { preview.src = e.target.result; }
      if (wrap)    { wrap.style.display = 'block'; }
      document.getElementById('img-drop-zone').style.borderColor = 'var(--ac)';
      // Uncheck hapus jika ada
      var cb = document.getElementById('hapus_gambar');
      if (cb) cb.checked = false;
    };
    reader.readAsDataURL(input.files[0]);
  }

  function toggleHapusGambar(cb) {
    var wrap = document.getElementById('img-current-wrap');
    var img  = document.getElementById('img-preview');
    if (cb.checked) {
      img.style.opacity = '.3';
    } else {
      img.style.opacity = '1';
    }
  }

  function maskRupiah(input, hiddenId) {
    var raw = input.value.replace(/\D/g, '');
    var num = parseInt(raw) || 0;
    input.value = num > 0 ? num.toLocaleString('id-ID') : '';
    document.getElementById(hiddenId).value = num || '';
  }
  </script>
  @endpush

</x-app-layout>
