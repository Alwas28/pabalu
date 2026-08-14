{{-- Tab: Kategori --}}
<div class="card">
  <div class="card-header">
    <div class="card-title"><i class="fa-solid fa-tags a-text" style="margin-right:8px"></i>Kategori Homepage</div>
    <button onclick="openModal('modal-add-hcat')"
      style="display:flex;align-items:center;gap:7px;padding:8px 16px;border-radius:10px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
      <i class="fa-solid fa-plus"></i> Tambah Kategori
    </button>
  </div>

  <div style="padding:14px 22px 0">
    <div style="display:flex;align-items:flex-start;gap:12px;padding:14px 16px;border-radius:12px;background:var(--ac-lt2);border:1px solid rgba(var(--ac-rgb),.2);margin-bottom:14px">
      <i class="fa-solid fa-circle-info" style="color:var(--ac);font-size:14px;margin-top:1px"></i>
      <div style="font-size:12.5px;color:var(--sub);line-height:1.6">
        Kategori di sini yang tampil pada dropdown <strong style="color:var(--text)">Semua Kategori</strong> di homepage.
        Satu kategori (mis. <strong style="color:var(--text)">Perawatan</strong>) bisa merangkum beberapa kategori produk
        asli dari jenis outlet berbeda (mis. "Perawatan Rambut" dari Salon, "Perawatan Bayi" dari Retail) — centang
        semua kategori produk yang termasuk di dalamnya.
      </div>
    </div>
  </div>

  @if($homeCategories->isEmpty())
  <div style="padding:56px 24px;text-align:center">
    <div style="width:60px;height:60px;border-radius:16px;background:var(--surface2);display:grid;place-items:center;margin:0 auto 16px;font-size:22px;color:var(--muted)">
      <i class="fa-solid fa-tags"></i>
    </div>
    <div style="font-size:15px;font-weight:600;color:var(--text);margin-bottom:6px">Belum Ada Kategori</div>
    <p style="font-size:13px;color:var(--muted)">Tambahkan kategori pertama untuk homepage, cth: Perawatan, Sembako, Minuman.</p>
  </div>
  @else
  <div style="overflow-x:auto">
    <table class="tbl">
      <thead>
        <tr>
          <th style="width:60px">Gambar</th>
          <th>Nama Kategori</th>
          <th>Deskripsi</th>
          <th style="text-align:center;width:130px">Kategori Produk</th>
          <th style="text-align:center;width:80px">Urutan</th>
          <th style="text-align:center;width:90px">Status</th>
          <th style="text-align:center;width:100px">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @foreach($homeCategories as $hcat)
        <tr>
          <td>
            @if($hcat->image)
            <img src="{{ asset('storage/' . $hcat->image) }}" alt="{{ $hcat->name }}"
              style="width:40px;height:40px;object-fit:cover;border-radius:9px;border:1px solid var(--border)">
            @else
            <div style="width:40px;height:40px;border-radius:9px;background:var(--ac-lt);color:var(--ac);display:grid;place-items:center;font-size:13px">
              <i class="fa-solid fa-tag"></i>
            </div>
            @endif
          </td>
          <td class="td-main">{{ $hcat->name }}</td>
          <td style="max-width:260px">
            <span style="font-size:12.5px;color:var(--muted)">{{ $hcat->description ?: '—' }}</span>
          </td>
          <td style="text-align:center">
            <span style="font-size:13px;font-weight:600;color:{{ $hcat->categories_count > 0 ? 'var(--ac)' : 'var(--muted)' }}">
              {{ $hcat->categories_count }} kategori
            </span>
          </td>
          <td style="text-align:center">
            <span style="font-size:13px;font-weight:600;color:var(--sub)">{{ $hcat->sort_order }}</span>
          </td>
          <td style="text-align:center">
            @if($hcat->is_active)
              <span class="badge badge-green"><i class="fa-solid fa-circle" style="font-size:6px"></i>Aktif</span>
            @else
              <span class="badge badge-gray"><i class="fa-solid fa-circle" style="font-size:6px"></i>Nonaktif</span>
            @endif
          </td>
          <td style="text-align:center">
            <div style="display:flex;align-items:center;justify-content:center;gap:6px">
              <button title="Edit"
                onclick='openEditHcat({{ $hcat->id }}, {{ json_encode(["name"=>$hcat->name,"description"=>$hcat->description,"sort_order"=>$hcat->sort_order,"category_ids"=>$hcat->categories->pluck("id"),"image"=>$hcat->image ? asset("storage/".$hcat->image) : null]) }})'
                style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:transparent;cursor:pointer;color:var(--sub);font-size:12px">
                <i class="fa-solid fa-pen"></i>
              </button>
              <form method="POST" action="{{ route('home-categories.toggle-active', $hcat) }}" style="margin:0">
                @csrf
                <button type="submit" title="{{ $hcat->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                  style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:transparent;cursor:pointer;font-size:12px;color:{{ $hcat->is_active ? '#fbbf24' : '#34d399' }}">
                  <i class="fa-solid {{ $hcat->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                </button>
              </form>
              <button title="Hapus" onclick="openDeleteHcat({{ $hcat->id }}, '{{ addslashes($hcat->name) }}')"
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

@php
  // Partial checklist dipakai ulang di modal Tambah & Edit
  $renderChecklist = function ($prefix) use ($categoriesByType) {
    return view('settings.tabs.partials.category-checklist', compact('categoriesByType', 'prefix'))->render();
  };
@endphp

{{-- Modal Tambah --}}
<div class="modal-backdrop" id="modal-add-hcat" onclick="if(event.target===this)closeModal('modal-add-hcat')">
  <div class="modal-box" style="max-width:560px">
    <form method="POST" action="{{ route('home-categories.store') }}" enctype="multipart/form-data">
      @csrf
      <div style="padding:20px 24px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <h3 class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">Tambah Kategori Homepage</h3>
        <button type="button" onclick="closeModal('modal-add-hcat')"
          style="width:30px;height:30px;border-radius:8px;border:none;background:var(--surface2);cursor:pointer;color:var(--sub);font-size:14px">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <div style="padding:20px 24px;display:flex;flex-direction:column;gap:14px;max-height:70vh;overflow-y:auto">
        <div>
          <label class="f-label">Nama Kategori <span style="color:var(--ac)">*</span></label>
          <input name="name" class="f-input" required maxlength="100" placeholder="cth: Perawatan, Sembako, Minuman">
        </div>
        <div>
          <label class="f-label">Gambar Kategori <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
          <input type="file" name="image" accept="image/*" class="f-input">
          <p style="font-size:11px;color:var(--muted);margin-top:4px">Ditampilkan di dropdown Semua Kategori pada homepage. Maks. 2MB.</p>
        </div>
        <div>
          <label class="f-label">Deskripsi <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
          <input name="description" class="f-input" maxlength="255">
        </div>
        <div style="width:140px">
          <label class="f-label">Urutan Tampil</label>
          <input name="sort_order" type="number" class="f-input" value="0" min="0">
        </div>
        <div>
          <label class="f-label">Kategori Produk yang Termasuk</label>
          {!! $renderChecklist('cr') !!}
        </div>
      </div>
      <div style="padding:16px 24px 20px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
        <button type="button" onclick="closeModal('modal-add-hcat')"
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

{{-- Modal Edit --}}
<div class="modal-backdrop" id="modal-edit-hcat" onclick="if(event.target===this)closeModal('modal-edit-hcat')">
  <div class="modal-box" style="max-width:560px">
    <form id="form-edit-hcat" method="POST" action="" enctype="multipart/form-data">
      @csrf @method('PUT')
      <div style="padding:20px 24px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <h3 class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">Edit Kategori Homepage</h3>
        <button type="button" onclick="closeModal('modal-edit-hcat')"
          style="width:30px;height:30px;border-radius:8px;border:none;background:var(--surface2);cursor:pointer;color:var(--sub);font-size:14px">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <div style="padding:20px 24px;display:flex;flex-direction:column;gap:14px;max-height:70vh;overflow-y:auto">
        <div>
          <label class="f-label">Nama Kategori <span style="color:var(--ac)">*</span></label>
          <input id="ed-hcat-name" name="name" class="f-input" required maxlength="100">
        </div>
        <div>
          <img id="ed-hcat-preview" src="" alt="" style="display:none;width:100%;height:120px;object-fit:cover;border-radius:10px;border:1px solid var(--border);margin-bottom:8px">
          <label class="f-label">Ganti Gambar Kategori <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
          <input type="file" name="image" accept="image/*" class="f-input">
        </div>
        <div>
          <label class="f-label">Deskripsi <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
          <input id="ed-hcat-desc" name="description" class="f-input" maxlength="255">
        </div>
        <div style="width:140px">
          <label class="f-label">Urutan Tampil</label>
          <input id="ed-hcat-sort" name="sort_order" type="number" class="f-input" min="0">
        </div>
        <div>
          <label class="f-label">Kategori Produk yang Termasuk</label>
          {!! $renderChecklist('ed') !!}
        </div>
      </div>
      <div style="padding:16px 24px 20px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
        <button type="button" onclick="closeModal('modal-edit-hcat')"
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

{{-- Modal Hapus --}}
<div class="modal-backdrop" id="modal-delete-hcat" onclick="if(event.target===this)closeModal('modal-delete-hcat')">
  <div class="modal-box" style="max-width:380px">
    <form id="form-delete-hcat" method="POST" action="">
      @csrf @method('DELETE')
      <div style="padding:28px 24px 20px;text-align:center">
        <div style="width:52px;height:52px;border-radius:14px;background:rgba(239,68,68,.15);display:grid;place-items:center;margin:0 auto 16px;color:#f87171;font-size:20px">
          <i class="fa-solid fa-trash-can"></i>
        </div>
        <h3 class="font-display" style="font-size:17px;font-weight:700;color:var(--text);margin-bottom:7px">Hapus Kategori?</h3>
        <p style="font-size:13px;color:var(--muted)">
          Kategori <strong id="del-hcat-name" style="color:var(--text)"></strong> akan dihapus permanen dari homepage.
        </p>
      </div>
      <div style="padding:0 24px 20px;display:flex;gap:10px">
        <button type="button" onclick="closeModal('modal-delete-hcat')"
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
function openEditHcat(id, data) {
  document.getElementById('form-edit-hcat').action = '/pengaturan/kategori/items/' + id;
  document.getElementById('ed-hcat-name').value = data.name || '';
  document.getElementById('ed-hcat-desc').value = data.description || '';
  document.getElementById('ed-hcat-sort').value = data.sort_order ?? 0;

  const preview = document.getElementById('ed-hcat-preview');
  if (data.image) {
    preview.src = data.image;
    preview.style.display = '';
  } else {
    preview.style.display = 'none';
  }

  // Reset semua checkbox, lalu centang yang termasuk kategori ini
  const boxes = document.querySelectorAll('#modal-edit-hcat input[type="checkbox"]');
  const selected = (data.category_ids || []).map(String);
  boxes.forEach(b => { b.checked = selected.includes(b.value); });

  openModal('modal-edit-hcat');
}

function openDeleteHcat(id, name) {
  document.getElementById('form-delete-hcat').action = '/pengaturan/kategori/items/' + id;
  document.getElementById('del-hcat-name').textContent = name;
  openModal('modal-delete-hcat');
}
</script>
