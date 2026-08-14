<x-outlet-layout :outlet="$outlet" pageTitle="Barang Sewa">

{{-- ── HEADER ── --}}
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px" class="animate-fadeUp">
  <div>
    <h2 class="font-display" style="font-size:20px;font-weight:700;color:var(--text)">Barang Sewa</h2>
    <p style="font-size:13px;color:var(--muted);margin-top:2px">
      Master data barang yang disewakan outlet <strong style="color:var(--sub)">{{ $outlet->name }}</strong>
    </p>
  </div>
  <button onclick="openAddModal()"
    style="display:flex;align-items:center;gap:8px;padding:10px 18px;border-radius:12px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13.5px;font-weight:700;cursor:pointer;font-family:inherit;transition:opacity .15s"
    onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
    <i class="fa-solid fa-plus" style="font-size:12px"></i> Tambah Barang
  </button>
</div>

{{-- ── STATS ── --}}
@php
  $total    = $items->count();
  $aktif    = $items->where('is_active', true)->count();
  $nonaktif = $items->where('is_active', false)->count();
@endphp
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px" class="animate-fadeUp d1">
  <div class="stat-card">
    <div class="stat-icon" style="background:var(--ac-lt);color:var(--ac)"><i class="fa-solid fa-box"></i></div>
    <div><div class="stat-num">{{ $total }}</div><div class="stat-label">Total Barang</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(16,185,129,.15);color:#34d399"><i class="fa-solid fa-circle-check"></i></div>
    <div><div class="stat-num">{{ $aktif }}</div><div class="stat-label">Aktif</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(148,163,184,.12);color:#94a3b8"><i class="fa-solid fa-circle-xmark"></i></div>
    <div><div class="stat-num">{{ $nonaktif }}</div><div class="stat-label">Nonaktif</div></div>
  </div>
</div>

{{-- ── GRID BARANG ── --}}
<div class="card animate-fadeUp d2">
  <div class="card-header" style="flex-wrap:wrap;gap:10px">
    <span class="card-title">
      <i class="fa-solid fa-box" style="color:var(--ac);margin-right:8px;font-size:14px"></i>Daftar Barang
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
      <div style="position:relative">
        <input id="search-input" oninput="applyFilters()" placeholder="Cari nama barang..."
          style="padding:7px 12px 7px 32px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--text);font-size:13px;font-family:inherit;outline:none;width:190px;transition:border-color .15s"
          onfocus="this.style.borderColor='var(--ac)'" onblur="this.style.borderColor='var(--border)'">
        <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:12px;pointer-events:none"></i>
      </div>
    </div>
  </div>

  @if($items->isEmpty())
  <div style="padding:64px 24px;text-align:center">
    <div style="width:60px;height:60px;border-radius:16px;background:var(--ac-lt);display:grid;place-items:center;margin:0 auto 16px;font-size:22px;color:var(--ac)">
      <i class="fa-solid fa-box"></i>
    </div>
    <h3 class="font-display" style="font-size:17px;font-weight:700;color:var(--text);margin-bottom:6px">Belum Ada Barang</h3>
    <p style="font-size:13.5px;color:var(--muted);max-width:340px;margin:0 auto 20px">
      Tambahkan barang yang disewakan di outlet ini beserta fotonya.
    </p>
    <button onclick="openAddModal()"
      style="padding:10px 22px;border-radius:12px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13.5px;font-weight:700;cursor:pointer;font-family:inherit">
      <i class="fa-solid fa-plus" style="margin-right:6px;font-size:12px"></i>Tambah Barang Pertama
    </button>
  </div>
  @else
  <div class="card-body" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:14px" id="item-grid">
    @foreach($items as $item)
    <div class="item-card" style="border:1px solid var(--border);border-radius:14px;overflow:hidden;background:var(--surface2)"
      data-name="{{ strtolower($item->name) }}" data-cat="{{ $item->category_id ?? '' }}">
      <div style="height:130px;background:var(--surface);display:flex;align-items:center;justify-content:center;overflow:hidden;position:relative">
        @if($item->images->isNotEmpty())
        <img src="{{ Storage::url($item->images->first()->path) }}" alt="{{ $item->name }}" style="width:100%;height:100%;object-fit:cover">
        @if($item->images->count() > 1)
        <span style="position:absolute;bottom:6px;right:6px;background:rgba(0,0,0,.6);color:#fff;font-size:10.5px;font-weight:700;padding:2px 7px;border-radius:99px">
          <i class="fa-solid fa-images" style="font-size:9px;margin-right:3px"></i>{{ $item->images->count() }}
        </span>
        @endif
        @else
        <i class="fa-solid fa-image" style="font-size:26px;color:var(--muted);opacity:.5"></i>
        @endif
      </div>
      <div style="padding:12px">
        <div style="font-weight:700;color:var(--text);font-size:13.5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
          {{ $item->name }}
        </div>
        <div style="margin-top:4px;display:flex;align-items:center;justify-content:space-between;gap:6px">
          @if($item->category)
          <span class="badge" style="background:var(--ac-lt2);color:var(--ac);font-size:10.5px">{{ $item->category->name }}</span>
          @else
          <span style="font-size:11px;color:var(--muted)">Tanpa kategori</span>
          @endif
          @if($item->is_active)
          <span class="badge badge-green" style="font-size:10px"><i class="fa-solid fa-circle" style="font-size:5px"></i>Aktif</span>
          @else
          <span class="badge badge-gray" style="font-size:10px"><i class="fa-solid fa-circle" style="font-size:5px"></i>Nonaktif</span>
          @endif
        </div>
        <div style="display:flex;align-items:center;gap:6px;margin-top:10px">
          <button title="Edit"
            onclick='openEdit({{ $item->id }}, {{ json_encode([
              "name"        => $item->name,
              "category_id" => $item->category_id,
              "description" => $item->description,
              "images"      => $item->images->map(fn($img) => ["id" => $img->id, "url" => Storage::url($img->path)])->values(),
            ]) }})'
            style="flex:1;padding:7px;border-radius:8px;border:1px solid var(--border);background:transparent;cursor:pointer;color:var(--sub);font-size:12px"
            onmouseover="this.style.background='var(--surface)';this.style.color='var(--text)'"
            onmouseout="this.style.background='transparent';this.style.color='var(--sub)'">
            <i class="fa-solid fa-pen"></i>
          </button>
          <form method="POST" action="{{ $outlet->route('items.toggle-active', [$item]) }}" style="margin:0;flex:1">
            @csrf
            <button type="submit" title="{{ $item->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
              style="width:100%;padding:7px;border-radius:8px;border:1px solid var(--border);background:transparent;cursor:pointer;font-size:12px;color:{{ $item->is_active ? '#fbbf24' : '#34d399' }}">
              <i class="fa-solid {{ $item->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
            </button>
          </form>
          <button title="Hapus" onclick="openDelete({{ $item->id }}, {{ json_encode($item->name) }})"
            style="flex:1;padding:7px;border-radius:8px;border:1px solid var(--border);background:transparent;cursor:pointer;color:#f87171;font-size:12px"
            onmouseover="this.style.background='rgba(239,68,68,.1)'" onmouseout="this.style.background='transparent'">
            <i class="fa-solid fa-trash-can"></i>
          </button>
        </div>
      </div>
    </div>
    @endforeach
  </div>
  <div id="no-result" style="display:none;text-align:center;padding:32px;color:var(--muted);font-size:13px">
    <i class="fa-solid fa-magnifying-glass" style="margin-right:6px"></i>Tidak ditemukan
  </div>
  @endif
</div>

{{-- ══ MODAL TAMBAH ══ --}}
<div class="modal-backdrop" id="modal-add" onclick="if(event.target===this)closeModal('modal-add')">
  <div class="modal-box" style="max-width:480px">
    <form method="POST" action="{{ $outlet->route('items.store') }}" enctype="multipart/form-data">
      @csrf
      <div style="padding:20px 24px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <h3 class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">Tambah Barang Sewa</h3>
        <button type="button" onclick="closeModal('modal-add')" style="width:30px;height:30px;border-radius:8px;border:none;background:var(--surface2);cursor:pointer;color:var(--sub);font-size:14px">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <div style="padding:20px 24px;display:flex;flex-direction:column;gap:14px;max-height:68vh;overflow-y:auto">

        <div>
          <label class="f-label">Foto Barang <span style="color:var(--muted);font-weight:400">(opsional · maks 4 foto, @2MB)</span></label>
          <label id="add-img-label" for="add-img-input"
            style="display:flex;align-items:center;justify-content:center;flex-direction:column;gap:8px;border:2px dashed var(--border);border-radius:14px;padding:20px;cursor:pointer;transition:border-color .15s;min-height:90px"
            onmouseover="this.style.borderColor='var(--ac)'" onmouseout="this.style.borderColor='var(--border)'">
            <i class="fa-solid fa-images" style="font-size:22px;color:var(--muted)"></i>
            <span style="font-size:12.5px;color:var(--muted)">Klik untuk pilih hingga 4 gambar</span>
            <span style="font-size:11px;color:var(--muted);opacity:.7">JPG, PNG, WebP</span>
          </label>
          <input type="file" id="add-img-input" name="images[]" accept="image/jpeg,image/png,image/webp"
            multiple style="display:none" onchange="handleAddImages(this)">
          <div id="add-img-preview-grid" style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-top:10px"></div>
          <div id="add-img-error" style="display:none;font-size:12px;color:#f87171;margin-top:6px"></div>
        </div>

        <div>
          <label class="f-label">Nama Barang <span style="color:var(--ac)">*</span></label>
          <input name="name" class="f-input" required maxlength="150" placeholder="cth: Kamera Canon EOS 200D">
        </div>
        <div>
          <label class="f-label">Kategori</label>
          <select name="category_id" class="f-input">
            <option value="">— Tanpa Kategori —</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="f-label">Deskripsi <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
          <textarea name="description" class="f-input" rows="3" maxlength="1000" placeholder="Kelengkapan, kondisi, catatan barang..."></textarea>
        </div>
      </div>
      <div style="padding:16px 24px 20px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
        <button type="button" onclick="closeModal('modal-add')"
          style="padding:9px 18px;border-radius:11px;border:1px solid var(--border);background:transparent;cursor:pointer;font-size:13px;font-weight:600;color:var(--sub);font-family:inherit">Batal</button>
        <button type="submit" class="btn-save">Simpan</button>
      </div>
    </form>
  </div>
</div>

{{-- ══ MODAL EDIT ══ --}}
<div class="modal-backdrop" id="modal-edit" onclick="if(event.target===this)closeModal('modal-edit')">
  <div class="modal-box" style="max-width:480px">
    <form id="form-edit" method="POST" action="" enctype="multipart/form-data">
      @csrf @method('PUT')
      <div id="e-remove-images-inputs"></div>
      <div style="padding:20px 24px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <h3 class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">Edit Barang Sewa</h3>
        <button type="button" onclick="closeModal('modal-edit')" style="width:30px;height:30px;border-radius:8px;border:none;background:var(--surface2);cursor:pointer;color:var(--sub);font-size:14px">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <div style="padding:20px 24px;display:flex;flex-direction:column;gap:14px;max-height:68vh;overflow-y:auto">

        <div>
          <label class="f-label">Foto Barang <span style="color:var(--muted);font-weight:400">(maks 4 foto · @2MB)</span></label>
          <div id="e-img-grid" style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:10px"></div>
          <label id="e-img-label" for="e-img-input"
            style="display:flex;align-items:center;justify-content:center;flex-direction:column;gap:8px;border:2px dashed var(--border);border-radius:14px;padding:16px;cursor:pointer;transition:border-color .15s;min-height:80px"
            onmouseover="this.style.borderColor='var(--ac)'" onmouseout="this.style.borderColor='var(--border)'">
            <i class="fa-solid fa-arrow-up-from-bracket" style="font-size:16px;color:var(--muted)"></i>
            <span id="e-img-label-text" style="font-size:12.5px;color:var(--muted)">Klik untuk tambah foto</span>
          </label>
          <input type="file" id="e-img-input" name="images[]" accept="image/jpeg,image/png,image/webp"
            multiple style="display:none" onchange="handleEditImages(this)">
          <div id="e-img-new-preview" style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-top:8px"></div>
          <div id="e-img-error" style="display:none;font-size:12px;color:#f87171;margin-top:6px"></div>
        </div>

        <div>
          <label class="f-label">Nama Barang <span style="color:var(--ac)">*</span></label>
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
          <label class="f-label">Deskripsi</label>
          <textarea name="description" id="e-desc" class="f-input" rows="3" maxlength="1000"></textarea>
        </div>
      </div>
      <div style="padding:16px 24px 20px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
        <button type="button" onclick="closeModal('modal-edit')"
          style="padding:9px 18px;border-radius:11px;border:1px solid var(--border);background:transparent;cursor:pointer;font-size:13px;font-weight:600;color:var(--sub);font-family:inherit">Batal</button>
        <button type="submit" class="btn-save">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

{{-- ══ MODAL HAPUS ══ --}}
<div class="modal-backdrop" id="modal-delete" onclick="if(event.target===this)closeModal('modal-delete')">
  <div class="modal-box" style="max-width:380px">
    <form id="form-delete" method="POST" action="">
      @csrf @method('DELETE')
      <div style="padding:28px 24px 20px;text-align:center">
        <div style="width:52px;height:52px;border-radius:14px;background:rgba(239,68,68,.15);display:grid;place-items:center;margin:0 auto 16px;color:#f87171;font-size:20px">
          <i class="fa-solid fa-trash-can"></i>
        </div>
        <h3 class="font-display" style="font-size:17px;font-weight:700;color:var(--text);margin-bottom:6px">Hapus Barang?</h3>
        <p style="font-size:13.5px;color:var(--muted)">
          Barang <strong id="delete-name" style="color:var(--text)"></strong> akan dihapus permanen.
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

@push('scripts')
<script>
const outletId = {{ $outlet->id }};
const outletRp = '{{ $outlet->rp() }}';

const MAX_ITEM_IMAGES = {{ \App\Models\RentalItem::MAX_IMAGES }};

function thumbEl(src, onRemove) {
  const wrap = document.createElement('div');
  wrap.style.cssText = 'position:relative;border:1px solid var(--border);border-radius:10px;overflow:hidden;aspect-ratio:1;background:var(--surface)';
  const img = document.createElement('img');
  img.src = src;
  img.style.cssText = 'width:100%;height:100%;object-fit:cover';
  wrap.appendChild(img);
  const btn = document.createElement('button');
  btn.type = 'button';
  btn.innerHTML = '<i class="fa-solid fa-xmark"></i>';
  btn.style.cssText = 'position:absolute;top:3px;right:3px;width:20px;height:20px;border-radius:6px;background:rgba(239,68,68,.85);border:none;cursor:pointer;color:#fff;font-size:10px';
  btn.onclick = onRemove;
  wrap.appendChild(btn);
  return wrap;
}

/* ── Modal Tambah: multi-foto ── */
let addFiles = [];

function openAddModal() {
  addFiles = [];
  document.getElementById('add-img-input').value = '';
  document.getElementById('add-img-error').style.display = 'none';
  renderAddPreview();
  openModal('modal-add');
}

function syncAddFileInput() {
  const dt = new DataTransfer();
  addFiles.forEach(f => dt.items.add(f));
  document.getElementById('add-img-input').files = dt.files;
}

function handleAddImages(input) {
  const remaining = MAX_ITEM_IMAGES - addFiles.length;
  let files = Array.from(input.files);
  const err = document.getElementById('add-img-error');
  if (files.length > remaining) {
    err.textContent = `Maksimal ${MAX_ITEM_IMAGES} foto. Hanya ${remaining} foto pertama yang ditambahkan.`;
    err.style.display = 'block';
    files = files.slice(0, remaining);
  } else {
    err.style.display = 'none';
  }
  addFiles = addFiles.concat(files);
  syncAddFileInput();
  renderAddPreview();
}

function renderAddPreview() {
  const grid = document.getElementById('add-img-preview-grid');
  grid.innerHTML = '';
  addFiles.forEach((file, idx) => {
    const reader = new FileReader();
    reader.onload = e => grid.appendChild(thumbEl(e.target.result, () => removeAddFile(idx)));
    reader.readAsDataURL(file);
  });
}

function removeAddFile(idx) {
  addFiles.splice(idx, 1);
  syncAddFileInput();
  renderAddPreview();
}

/* ── Modal Edit: foto lama + foto baru ── */
let editExistingImages = [];
let editRemovedIds     = [];
let editNewFiles       = [];

function syncEditFileInput() {
  const dt = new DataTransfer();
  editNewFiles.forEach(f => dt.items.add(f));
  document.getElementById('e-img-input').files = dt.files;
}

function editUsedSlots() {
  return (editExistingImages.length - editRemovedIds.length) + editNewFiles.length;
}

function updateEditSlotState() {
  const remaining = MAX_ITEM_IMAGES - editUsedSlots();
  const label = document.getElementById('e-img-label');
  const labelText = document.getElementById('e-img-label-text');
  if (remaining <= 0) {
    label.style.display = 'none';
  } else {
    label.style.display = 'flex';
    labelText.textContent = `Klik untuk tambah foto (sisa ${remaining})`;
  }
}

function renderEditExistingGrid() {
  const grid = document.getElementById('e-img-grid');
  grid.innerHTML = '';
  editExistingImages
    .filter(img => !editRemovedIds.includes(img.id))
    .forEach(img => grid.appendChild(thumbEl(img.url, () => removeExistingImage(img.id))));
}

function removeExistingImage(id) {
  editRemovedIds.push(id);
  const input = document.createElement('input');
  input.type = 'hidden';
  input.name = 'remove_images[]';
  input.value = id;
  document.getElementById('e-remove-images-inputs').appendChild(input);
  renderEditExistingGrid();
  updateEditSlotState();
}

function handleEditImages(input) {
  const remaining = MAX_ITEM_IMAGES - editUsedSlots();
  let files = Array.from(input.files);
  const err = document.getElementById('e-img-error');
  if (files.length > remaining) {
    err.textContent = `Maksimal ${MAX_ITEM_IMAGES} foto. Hanya ${remaining} foto pertama yang ditambahkan.`;
    err.style.display = 'block';
    files = files.slice(0, remaining);
  } else {
    err.style.display = 'none';
  }
  editNewFiles = editNewFiles.concat(files);
  syncEditFileInput();
  renderEditNewPreview();
  updateEditSlotState();
}

function renderEditNewPreview() {
  const grid = document.getElementById('e-img-new-preview');
  grid.innerHTML = '';
  editNewFiles.forEach((file, idx) => {
    const reader = new FileReader();
    reader.onload = e => grid.appendChild(thumbEl(e.target.result, () => removeNewFile(idx)));
    reader.readAsDataURL(file);
  });
}

function removeNewFile(idx) {
  editNewFiles.splice(idx, 1);
  syncEditFileInput();
  renderEditNewPreview();
  updateEditSlotState();
}

function openEdit(id, d) {
  document.getElementById('form-edit').action = `/${outletRp}/${outletId}/items/${id}`;
  document.getElementById('e-name').value = d.name || '';
  document.getElementById('e-desc').value = d.description || '';
  const catEl = document.getElementById('e-cat');
  if (catEl) catEl.value = d.category_id ?? '';

  editExistingImages = d.images || [];
  editRemovedIds = [];
  editNewFiles = [];
  document.getElementById('e-remove-images-inputs').innerHTML = '';
  document.getElementById('e-img-input').value = '';
  document.getElementById('e-img-error').style.display = 'none';
  syncEditFileInput();
  renderEditExistingGrid();
  renderEditNewPreview();
  updateEditSlotState();

  openModal('modal-edit');
}

function openDelete(id, name) {
  document.getElementById('form-delete').action = `/${outletRp}/${outletId}/items/${id}`;
  document.getElementById('delete-name').textContent = name;
  openModal('modal-delete');
}

function applyFilters() {
  const q     = (document.getElementById('search-input')?.value || '').toLowerCase().trim();
  const catId = document.getElementById('filter-cat')?.value || '';
  let visible = 0;

  document.querySelectorAll('.item-card').forEach(c => {
    const ok = (!q || c.dataset.name.includes(q)) && (!catId || c.dataset.cat === catId);
    c.style.display = ok ? '' : 'none';
    if (ok) visible++;
  });
  const nr = document.getElementById('no-result');
  if (nr) nr.style.display = visible === 0 ? '' : 'none';
}

@if($errors->any())
  @if(old('_method') === 'PUT') openModal('modal-edit');
  @else openAddModal();
  @endif
@endif
</script>
@endpush

</x-outlet-layout>
