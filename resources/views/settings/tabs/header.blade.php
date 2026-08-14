{{-- Tab: Header (Slider) --}}
<div class="card">
  <div class="card-header">
    <div class="card-title"><i class="fa-solid fa-images a-text" style="margin-right:8px"></i>Slider Homepage</div>
    <button onclick="openModal('modal-add-slider')"
      style="display:flex;align-items:center;gap:7px;padding:8px 16px;border-radius:10px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
      <i class="fa-solid fa-plus"></i> Tambah Slide
    </button>
  </div>

  @if($sliders->isEmpty())
  <div style="padding:56px 24px;text-align:center">
    <div style="width:60px;height:60px;border-radius:16px;background:var(--surface2);display:grid;place-items:center;margin:0 auto 16px;font-size:22px;color:var(--muted)">
      <i class="fa-solid fa-images"></i>
    </div>
    <div style="font-size:15px;font-weight:600;color:var(--text);margin-bottom:6px">Belum Ada Slide</div>
    <p style="font-size:13px;color:var(--muted)">Tambahkan slide pertama untuk header homepage.</p>
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
        @foreach($sliders as $slide)
        <tr>
          <td>
            <img src="{{ asset('storage/' . $slide->image) }}" alt="{{ $slide->title }}"
              style="width:90px;height:52px;object-fit:cover;border-radius:8px;border:1px solid var(--border)">
          </td>
          <td class="td-main">
            @if($slide->badge)
            <span class="badge badge-amber" style="margin-bottom:4px;display:inline-block">{{ $slide->badge }}</span><br>
            @endif
            {{ $slide->title }}
            @if($slide->description)
            <div style="font-size:11.5px;color:var(--muted);margin-top:2px;max-width:320px">{{ Str::limit($slide->description, 80) }}</div>
            @endif
          </td>
          <td style="font-size:12.5px;color:var(--sub)">
            @if($slide->button_text)
              {{ $slide->button_text }}
              @if($slide->button_url)
              <div style="font-size:11px;color:var(--muted);max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $slide->button_url }}</div>
              @endif
            @else
              <span style="color:var(--muted)">—</span>
            @endif
          </td>
          <td style="text-align:center">
            <span style="font-size:13px;font-weight:600;color:var(--sub)">{{ $slide->sort_order }}</span>
          </td>
          <td style="text-align:center">
            @if($slide->is_active)
              <span class="badge badge-green"><i class="fa-solid fa-circle" style="font-size:6px"></i>Aktif</span>
            @else
              <span class="badge badge-gray"><i class="fa-solid fa-circle" style="font-size:6px"></i>Nonaktif</span>
            @endif
          </td>
          <td style="text-align:center">
            <div style="display:flex;align-items:center;justify-content:center;gap:6px">
              <button title="Edit"
                onclick='openEditSlider({{ $slide->id }}, {{ json_encode(["badge"=>$slide->badge,"title"=>$slide->title,"description"=>$slide->description,"button_text"=>$slide->button_text,"button_url"=>$slide->button_url,"sort_order"=>$slide->sort_order,"image"=>asset("storage/".$slide->image)]) }})'
                style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:transparent;cursor:pointer;color:var(--sub);font-size:12px">
                <i class="fa-solid fa-pen"></i>
              </button>
              <form method="POST" action="{{ route('sliders.toggle-active', $slide) }}" style="margin:0">
                @csrf
                <button type="submit" title="{{ $slide->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                  style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:transparent;cursor:pointer;font-size:12px;color:{{ $slide->is_active ? '#fbbf24' : '#34d399' }}">
                  <i class="fa-solid {{ $slide->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                </button>
              </form>
              <button title="Hapus" onclick="openDeleteSlider({{ $slide->id }}, '{{ addslashes($slide->title) }}')"
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

{{-- Modal Tambah Slide --}}
<div class="modal-backdrop" id="modal-add-slider" onclick="if(event.target===this)closeModal('modal-add-slider')">
  <div class="modal-box" style="max-width:520px">
    <form method="POST" action="{{ route('sliders.store') }}" enctype="multipart/form-data">
      @csrf
      <div style="padding:20px 24px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <h3 class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">Tambah Slide</h3>
        <button type="button" onclick="closeModal('modal-add-slider')"
          style="width:30px;height:30px;border-radius:8px;border:none;background:var(--surface2);cursor:pointer;color:var(--sub);font-size:14px">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <div style="padding:20px 24px;display:flex;flex-direction:column;gap:14px;max-height:65vh;overflow-y:auto">
        <div>
          <label class="f-label">Gambar Slide <span style="color:var(--ac)">*</span></label>
          <input type="file" name="image" accept="image/*" required class="f-input">
          <p style="font-size:11px;color:var(--muted);margin-top:4px">Rasio landscape disarankan, maks. 2MB.</p>
        </div>
        <div>
          <label class="f-label">Badge <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
          <input name="badge" class="f-input" maxlength="100" placeholder="cth: Aplikasi Belanja No. 1 di Sultra">
        </div>
        <div>
          <label class="f-label">Judul <span style="color:var(--ac)">*</span></label>
          <input name="title" class="f-input" required maxlength="200" placeholder="Judul besar pada slide">
        </div>
        <div>
          <label class="f-label">Deskripsi <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
          <textarea name="description" class="f-input" rows="2" maxlength="500"></textarea>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
          <div>
            <label class="f-label">Teks Tombol</label>
            <input name="button_text" class="f-input" maxlength="60" placeholder="Selengkapnya">
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
        <button type="button" onclick="closeModal('modal-add-slider')"
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

{{-- Modal Edit Slide --}}
<div class="modal-backdrop" id="modal-edit-slider" onclick="if(event.target===this)closeModal('modal-edit-slider')">
  <div class="modal-box" style="max-width:520px">
    <form id="form-edit-slider" method="POST" action="" enctype="multipart/form-data">
      @csrf @method('PUT')
      <div style="padding:20px 24px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <h3 class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">Edit Slide</h3>
        <button type="button" onclick="closeModal('modal-edit-slider')"
          style="width:30px;height:30px;border-radius:8px;border:none;background:var(--surface2);cursor:pointer;color:var(--sub);font-size:14px">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <div style="padding:20px 24px;display:flex;flex-direction:column;gap:14px;max-height:65vh;overflow-y:auto">
        <img id="ed-slider-preview" src="" alt="" style="width:100%;height:120px;object-fit:cover;border-radius:10px;border:1px solid var(--border)">
        <div>
          <label class="f-label">Ganti Gambar <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
          <input type="file" name="image" accept="image/*" class="f-input">
        </div>
        <div>
          <label class="f-label">Badge</label>
          <input id="ed-slider-badge" name="badge" class="f-input" maxlength="100">
        </div>
        <div>
          <label class="f-label">Judul <span style="color:var(--ac)">*</span></label>
          <input id="ed-slider-title" name="title" class="f-input" required maxlength="200">
        </div>
        <div>
          <label class="f-label">Deskripsi</label>
          <textarea id="ed-slider-desc" name="description" class="f-input" rows="2" maxlength="500"></textarea>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
          <div>
            <label class="f-label">Teks Tombol</label>
            <input id="ed-slider-btn-text" name="button_text" class="f-input" maxlength="60">
          </div>
          <div>
            <label class="f-label">Urutan Tampil</label>
            <input id="ed-slider-sort" name="sort_order" type="number" class="f-input" min="0">
          </div>
        </div>
        <div>
          <label class="f-label">URL Tombol</label>
          <input id="ed-slider-btn-url" name="button_url" class="f-input" maxlength="255">
        </div>
      </div>
      <div style="padding:16px 24px 20px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
        <button type="button" onclick="closeModal('modal-edit-slider')"
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

{{-- Modal Hapus Slide --}}
<div class="modal-backdrop" id="modal-delete-slider" onclick="if(event.target===this)closeModal('modal-delete-slider')">
  <div class="modal-box" style="max-width:380px">
    <form id="form-delete-slider" method="POST" action="">
      @csrf @method('DELETE')
      <div style="padding:28px 24px 20px;text-align:center">
        <div style="width:52px;height:52px;border-radius:14px;background:rgba(239,68,68,.15);display:grid;place-items:center;margin:0 auto 16px;color:#f87171;font-size:20px">
          <i class="fa-solid fa-trash-can"></i>
        </div>
        <h3 class="font-display" style="font-size:17px;font-weight:700;color:var(--text);margin-bottom:7px">Hapus Slide?</h3>
        <p style="font-size:13px;color:var(--muted)">
          Slide <strong id="del-slider-name" style="color:var(--text)"></strong> akan dihapus permanen.
        </p>
      </div>
      <div style="padding:0 24px 20px;display:flex;gap:10px">
        <button type="button" onclick="closeModal('modal-delete-slider')"
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
function openEditSlider(id, data) {
  document.getElementById('form-edit-slider').action = '/pengaturan/header/sliders/' + id;
  document.getElementById('ed-slider-preview').src   = data.image;
  document.getElementById('ed-slider-badge').value   = data.badge || '';
  document.getElementById('ed-slider-title').value   = data.title || '';
  document.getElementById('ed-slider-desc').value    = data.description || '';
  document.getElementById('ed-slider-btn-text').value = data.button_text || '';
  document.getElementById('ed-slider-btn-url').value  = data.button_url || '';
  document.getElementById('ed-slider-sort').value     = data.sort_order ?? 0;
  openModal('modal-edit-slider');
}

function openDeleteSlider(id, title) {
  document.getElementById('form-delete-slider').action = '/pengaturan/header/sliders/' + id;
  document.getElementById('del-slider-name').textContent = title;
  openModal('modal-delete-slider');
}
</script>
