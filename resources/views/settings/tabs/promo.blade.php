{{-- Tab: Iklan/Promo (banner kecil di homepage, mis. "Buah & Sayur Segar") --}}
<div class="card">
  <div class="card-header">
    <div class="card-title"><i class="fa-solid fa-bullhorn a-text" style="margin-right:8px"></i>Banner Iklan/Promo Homepage</div>
    <button onclick="openModal('modal-add-promo')"
      style="display:flex;align-items:center;gap:7px;padding:8px 16px;border-radius:10px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
      <i class="fa-solid fa-plus"></i> Tambah Banner
    </button>
  </div>

  @if($promoBanners->isEmpty())
  <div style="padding:56px 24px;text-align:center">
    <div style="width:60px;height:60px;border-radius:16px;background:var(--surface2);display:grid;place-items:center;margin:0 auto 16px;font-size:22px;color:var(--muted)">
      <i class="fa-solid fa-bullhorn"></i>
    </div>
    <div style="font-size:15px;font-weight:600;color:var(--text);margin-bottom:6px">Belum Ada Banner Iklan</div>
    <p style="font-size:13px;color:var(--muted)">Tambahkan banner promo pertama untuk homepage (mis. "Buah & Sayur Segar — Hemat 30%").</p>
  </div>
  @else
  <div style="overflow-x:auto">
    <table class="tbl">
      <thead>
        <tr>
          <th style="width:110px">Gambar</th>
          <th>Judul</th>
          <th>Tombol</th>
          <th style="text-align:center;width:80px">Urutan</th>
          <th style="text-align:center;width:90px">Status</th>
          <th style="text-align:center;width:100px">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @foreach($promoBanners as $banner)
        <tr>
          <td>
            <img src="{{ asset('storage/' . $banner->image) }}" alt="{{ $banner->title }}"
              style="width:90px;height:52px;object-fit:cover;border-radius:8px;border:1px solid var(--border)">
          </td>
          <td class="td-main">
            @if($banner->badge)
            <span class="badge badge-amber" style="margin-bottom:4px;display:inline-block">{{ $banner->badge }}</span><br>
            @endif
            {{ $banner->title }}
          </td>
          <td style="font-size:12.5px;color:var(--sub)">
            @if($banner->button_text)
              {{ $banner->button_text }}
              @if($banner->button_url)
              <div style="font-size:11px;color:var(--muted);max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $banner->button_url }}</div>
              @endif
            @else
              <span style="color:var(--muted)">—</span>
            @endif
          </td>
          <td style="text-align:center">
            <span style="font-size:13px;font-weight:600;color:var(--sub)">{{ $banner->sort_order }}</span>
          </td>
          <td style="text-align:center">
            @if($banner->is_active)
              <span class="badge badge-green"><i class="fa-solid fa-circle" style="font-size:6px"></i>Aktif</span>
            @else
              <span class="badge badge-gray"><i class="fa-solid fa-circle" style="font-size:6px"></i>Nonaktif</span>
            @endif
          </td>
          <td style="text-align:center">
            <div style="display:flex;align-items:center;justify-content:center;gap:6px">
              <button title="Edit"
                onclick='openEditPromo({{ $banner->id }}, {{ json_encode(["badge"=>$banner->badge,"title"=>$banner->title,"button_text"=>$banner->button_text,"button_url"=>$banner->button_url,"sort_order"=>$banner->sort_order,"image"=>asset("storage/".$banner->image)]) }})'
                style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:transparent;cursor:pointer;color:var(--sub);font-size:12px">
                <i class="fa-solid fa-pen"></i>
              </button>
              <form method="POST" action="{{ route('promo-banners.toggle-active', $banner) }}" style="margin:0">
                @csrf
                <button type="submit" title="{{ $banner->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                  style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:transparent;cursor:pointer;font-size:12px;color:{{ $banner->is_active ? '#fbbf24' : '#34d399' }}">
                  <i class="fa-solid {{ $banner->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                </button>
              </form>
              <button title="Hapus" onclick="openDeletePromo({{ $banner->id }}, '{{ addslashes($banner->title) }}')"
                style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:transparent;cursor:pointer;color:#f87171;font-size:12px">
                <i class="fa-solid fa-trash-can"></i>
              </button>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>

{{-- Modal Tambah Banner --}}
<div class="modal-backdrop" id="modal-add-promo" onclick="if(event.target===this)closeModal('modal-add-promo')">
  <div class="modal-box" style="max-width:520px">
    <form method="POST" action="{{ route('promo-banners.store') }}" enctype="multipart/form-data">
      @csrf
      <div style="padding:20px 24px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <h3 class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">Tambah Banner Iklan</h3>
        <button type="button" onclick="closeModal('modal-add-promo')"
          style="width:30px;height:30px;border-radius:8px;border:none;background:var(--surface2);cursor:pointer;color:var(--sub);font-size:14px">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <div style="padding:20px 24px;display:flex;flex-direction:column;gap:14px;max-height:65vh;overflow-y:auto">
        <div>
          <label class="f-label">Gambar Banner <span style="color:var(--ac)">*</span></label>
          <input type="file" name="image" accept="image/*" required class="f-input">
          <p style="font-size:11px;color:var(--muted);margin-top:4px">Rasio landscape (mis. 700x300), maks. 2MB.</p>
        </div>
        <div>
          <label class="f-label">Badge <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
          <input name="badge" class="f-input" maxlength="100" placeholder="cth: Hemat hingga 30%">
        </div>
        <div>
          <label class="f-label">Judul <span style="color:var(--ac)">*</span></label>
          <input name="title" class="f-input" required maxlength="200" placeholder="cth: Buah & Sayur Segar">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
          <div>
            <label class="f-label">Teks Tombol</label>
            <input name="button_text" class="f-input" maxlength="60" placeholder="Belanja Sekarang">
          </div>
          <div>
            <label class="f-label">Urutan Tampil</label>
            <input name="sort_order" type="number" class="f-input" value="0" min="0">
          </div>
        </div>
        <div>
          <label class="f-label">URL Tombol</label>
          <input name="button_url" class="f-input" maxlength="255" placeholder="https://...">
        </div>
      </div>
      <div style="padding:16px 24px 20px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
        <button type="button" onclick="closeModal('modal-add-promo')"
          style="padding:9px 18px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
          Batal
        </button>
        <button type="submit"
          style="padding:9px 20px;border-radius:10px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
          Simpan
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Modal Edit Banner --}}
<div class="modal-backdrop" id="modal-edit-promo" onclick="if(event.target===this)closeModal('modal-edit-promo')">
  <div class="modal-box" style="max-width:520px">
    <form id="form-edit-promo" method="POST" action="" enctype="multipart/form-data">
      @csrf @method('PUT')
      <div style="padding:20px 24px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <h3 class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">Edit Banner Iklan</h3>
        <button type="button" onclick="closeModal('modal-edit-promo')"
          style="width:30px;height:30px;border-radius:8px;border:none;background:var(--surface2);cursor:pointer;color:var(--sub);font-size:14px">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <div style="padding:20px 24px;display:flex;flex-direction:column;gap:14px;max-height:65vh;overflow-y:auto">
        <img id="ed-promo-preview" src="" alt="" style="width:100%;height:120px;object-fit:cover;border-radius:10px;border:1px solid var(--border)">
        <div>
          <label class="f-label">Ganti Gambar <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
          <input type="file" name="image" accept="image/*" class="f-input">
        </div>
        <div>
          <label class="f-label">Badge</label>
          <input id="ed-promo-badge" name="badge" class="f-input" maxlength="100">
        </div>
        <div>
          <label class="f-label">Judul <span style="color:var(--ac)">*</span></label>
          <input id="ed-promo-title" name="title" class="f-input" required maxlength="200">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
          <div>
            <label class="f-label">Teks Tombol</label>
            <input id="ed-promo-btn-text" name="button_text" class="f-input" maxlength="60">
          </div>
          <div>
            <label class="f-label">Urutan Tampil</label>
            <input id="ed-promo-sort" name="sort_order" type="number" class="f-input" min="0">
          </div>
        </div>
        <div>
          <label class="f-label">URL Tombol</label>
          <input id="ed-promo-btn-url" name="button_url" class="f-input" maxlength="255">
        </div>
      </div>
      <div style="padding:16px 24px 20px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
        <button type="button" onclick="closeModal('modal-edit-promo')"
          style="padding:9px 18px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
          Batal
        </button>
        <button type="submit"
          style="padding:9px 20px;border-radius:10px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
          Simpan Perubahan
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Modal Hapus Banner --}}
<div class="modal-backdrop" id="modal-delete-promo" onclick="if(event.target===this)closeModal('modal-delete-promo')">
  <div class="modal-box" style="max-width:380px">
    <form id="form-delete-promo" method="POST" action="">
      @csrf @method('DELETE')
      <div style="padding:28px 24px 20px;text-align:center">
        <div style="width:52px;height:52px;border-radius:14px;background:rgba(239,68,68,.15);display:grid;place-items:center;margin:0 auto 16px;color:#f87171;font-size:20px">
          <i class="fa-solid fa-trash-can"></i>
        </div>
        <h3 class="font-display" style="font-size:17px;font-weight:700;color:var(--text);margin-bottom:7px">Hapus Banner?</h3>
        <p style="font-size:13px;color:var(--muted)">
          Banner <strong id="del-promo-name" style="color:var(--text)"></strong> akan dihapus permanen.
        </p>
      </div>
      <div style="padding:0 24px 20px;display:flex;gap:10px">
        <button type="button" onclick="closeModal('modal-delete-promo')"
          style="flex:1;padding:10px;border-radius:11px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
          Batal
        </button>
        <button type="submit"
          style="flex:1;padding:10px;border-radius:11px;border:none;background:linear-gradient(135deg,#ef4444,#dc2626);color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
          Ya, Hapus
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function openEditPromo(id, data) {
  document.getElementById('form-edit-promo').action = '/pengaturan/iklan/banners/' + id;
  document.getElementById('ed-promo-preview').src    = data.image;
  document.getElementById('ed-promo-badge').value    = data.badge || '';
  document.getElementById('ed-promo-title').value    = data.title || '';
  document.getElementById('ed-promo-btn-text').value = data.button_text || '';
  document.getElementById('ed-promo-btn-url').value  = data.button_url || '';
  document.getElementById('ed-promo-sort').value     = data.sort_order ?? 0;
  openModal('modal-edit-promo');
}

function openDeletePromo(id, title) {
  document.getElementById('form-delete-promo').action = '/pengaturan/iklan/banners/' + id;
  document.getElementById('del-promo-name').textContent = title;
  openModal('modal-delete-promo');
}
</script>
