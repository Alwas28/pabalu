<x-app-layout title="Edit Kategori">

  <div style="display:flex;align-items:center;gap:8px;font-size:12.5px;color:var(--muted)">
    <a href="{{ route('categories.index') }}" style="color:var(--muted);text-decoration:none;transition:color .15s"
      onmouseover="this.style.color='var(--ac)'" onmouseout="this.style.color='var(--muted)'">
      <i class="fa-solid fa-tags"></i> Kelola Kategori
    </a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px"></i>
    <span style="color:var(--text)">Edit: {{ $category->nama }}</span>
  </div>

  <form method="POST" action="{{ route('categories.update', $category) }}">
    @csrf
    @method('PUT')
    <div style="display:grid;grid-template-columns:1fr 280px;gap:20px;align-items:start">

      <div class="card animate-fadeUp">
        <div class="card-header">
          <div class="card-title"><i class="fa-solid fa-tag" style="color:var(--ac);margin-right:8px"></i>Informasi Kategori</div>
        </div>
        <div class="card-body">
          <div class="f-group">
            <label for="nama" class="f-label">Nama Kategori <span style="color:#f87171">*</span></label>
            <input id="nama" name="nama" type="text" class="f-input"
              value="{{ old('nama', $category->nama) }}" required placeholder="cth. Makanan, Minuman, Snack…">
            @error('nama')<div class="f-error"><i class="fa-solid fa-circle-exclamation" style="font-size:10px"></i>{{ $message }}</div>@enderror
          </div>
          <div class="f-group" style="margin-bottom:0">
            <label for="deskripsi" class="f-label">Deskripsi</label>
            <textarea id="deskripsi" name="deskripsi" class="f-input" rows="3"
              placeholder="Deskripsi singkat…">{{ old('deskripsi', $category->deskripsi) }}</textarea>
          </div>
        </div>
      </div>

      <div style="display:flex;flex-direction:column;gap:16px">
        <div class="card animate-fadeUp" style="border-color:var(--ac)44">
          <div class="card-body" style="padding:18px;text-align:center">
            <div style="width:44px;height:44px;border-radius:12px;display:grid;place-items:center;
                        margin:0 auto 8px;background:var(--ac-lt);color:var(--ac);font-size:18px">
              <i class="fa-solid fa-tag"></i>
            </div>
            <div style="font-family:'Clash Display',sans-serif;font-size:14px;font-weight:700;color:var(--text)">{{ $category->nama }}</div>
            <div style="font-size:22px;font-weight:700;color:var(--ac);font-family:'Clash Display',sans-serif;margin-top:8px">
              {{ $category->products_count }}
            </div>
            <div style="font-size:11px;color:var(--muted)">produk menggunakan kategori ini</div>
          </div>
        </div>

        <div class="card animate-fadeUp d1">
          <div class="card-header">
            <div class="card-title"><i class="fa-solid fa-toggle-on" style="color:var(--ac);margin-right:8px"></i>Status</div>
          </div>
          <div class="card-body">
            <label style="display:flex;align-items:center;justify-content:space-between;cursor:pointer;gap:12px">
              <div>
                <div style="font-size:13px;font-weight:600;color:var(--text)">Aktif</div>
                <div style="font-size:12px;color:var(--muted);margin-top:2px">Tampil sebagai pilihan kategori</div>
              </div>
              <div style="position:relative;flex-shrink:0">
                <input type="checkbox" name="is_active" value="1" id="is_active"
                  @checked(old('is_active', $category->is_active))
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
          <a href="{{ route('categories.index') }}" class="btn" style="justify-content:center;padding:11px;text-decoration:none">
            <i class="fa-solid fa-arrow-left"></i> Kembali
          </a>
        </div>
      </div>

    </div>
  </form>

  @push('scripts')
  <script>
  var cb = document.getElementById('is_active');
  var thumb = document.getElementById('toggle-thumb');
  function syncThumb() {
    thumb.style.transform = cb.checked ? 'translateX(18px)' : 'translateX(0)';
    cb.style.background   = cb.checked ? 'var(--ac)' : 'var(--surface2)';
  }
  syncThumb();
  </script>
  @endpush

</x-app-layout>
