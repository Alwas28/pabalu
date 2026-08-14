{{-- Tab: Menu --}}
<div class="card">
  <div class="card-header">
    <div class="card-title"><i class="fa-solid fa-bars a-text" style="margin-right:8px"></i>Menu Homepage</div>
    <button onclick="openModal('modal-add-menu')"
      style="display:flex;align-items:center;gap:7px;padding:8px 16px;border-radius:10px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
      <i class="fa-solid fa-plus"></i> Tambah Menu
    </button>
  </div>

  @if($menus->isEmpty())
  <div style="padding:56px 24px;text-align:center">
    <div style="width:60px;height:60px;border-radius:16px;background:var(--surface2);display:grid;place-items:center;margin:0 auto 16px;font-size:22px;color:var(--muted)">
      <i class="fa-solid fa-bars"></i>
    </div>
    <div style="font-size:15px;font-weight:600;color:var(--text);margin-bottom:6px">Belum Ada Menu</div>
    <p style="font-size:13px;color:var(--muted)">Tambahkan menu pertama untuk navigasi homepage.</p>
  </div>
  @else
  <div style="overflow-x:auto">
    <table class="tbl">
      <thead>
        <tr>
          <th>Label</th>
          <th style="width:110px">Tipe / Kolom</th>
          <th>URL</th>
          <th style="text-align:center;width:80px">Urutan</th>
          <th style="text-align:center;width:90px">Status</th>
          <th style="text-align:center;width:100px">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @foreach($menus as $item)
        <tr>
          <td class="td-main"><i class="fa-solid fa-bars" style="color:var(--muted);font-size:11px;margin-right:8px"></i>{{ $item->label }}</td>
          <td>
            @if($item->isMega())
              <span class="badge badge-amber"><i class="fa-solid fa-table-cells" style="font-size:8px"></i>Mega Menu</span>
            @else
              <span class="badge badge-gray"><i class="fa-solid fa-list" style="font-size:8px"></i>Biasa</span>
            @endif
          </td>
          <td style="font-size:12.5px;color:var(--sub);max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $item->url }}</td>
          <td style="text-align:center"><span style="font-size:13px;font-weight:600;color:var(--sub)">{{ $item->sort_order }}</span></td>
          <td style="text-align:center">
            @if($item->is_active)
              <span class="badge badge-green"><i class="fa-solid fa-circle" style="font-size:6px"></i>Aktif</span>
            @else
              <span class="badge badge-gray"><i class="fa-solid fa-circle" style="font-size:6px"></i>Nonaktif</span>
            @endif
          </td>
          <td style="text-align:center">
            <div style="display:flex;align-items:center;justify-content:center;gap:6px">
              <button title="Edit"
                onclick='openEditMenu({{ $item->id }}, {{ json_encode(["parent_id"=>$item->parent_id,"group_label"=>$item->group_label,"label"=>$item->label,"type"=>$item->type,"url"=>$item->url,"sort_order"=>$item->sort_order]) }})'
                style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:transparent;cursor:pointer;color:var(--sub);font-size:12px">
                <i class="fa-solid fa-pen"></i>
              </button>
              <form method="POST" action="{{ route('home-menus.toggle-active', $item) }}" style="margin:0">
                @csrf
                <button type="submit" title="{{ $item->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                  style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:transparent;cursor:pointer;font-size:12px;color:{{ $item->is_active ? '#fbbf24' : '#34d399' }}">
                  <i class="fa-solid {{ $item->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                </button>
              </form>
              <button title="Hapus" onclick="openDeleteMenu({{ $item->id }}, '{{ addslashes($item->label) }}')"
                style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:transparent;cursor:pointer;color:#f87171;font-size:12px">
                <i class="fa-solid fa-trash-can"></i>
              </button>
            </div>
          </td>
        </tr>
        @foreach($item->children as $child)
        <tr style="background:var(--surface2)">
          <td class="td-main" style="padding-left:34px"><i class="fa-solid fa-turn-up fa-rotate-90" style="color:var(--muted);font-size:11px;margin-right:8px"></i>{{ $child->label }}</td>
          <td>
            @if($item->isMega() && $child->group_label)
              <span style="font-size:11.5px;color:var(--muted)"><i class="fa-solid fa-table-columns" style="font-size:9px;margin-right:4px"></i>{{ $child->group_label }}</span>
            @else
              <span style="color:var(--muted)">—</span>
            @endif
          </td>
          <td style="font-size:12.5px;color:var(--sub);max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $child->url }}</td>
          <td style="text-align:center"><span style="font-size:13px;font-weight:600;color:var(--sub)">{{ $child->sort_order }}</span></td>
          <td style="text-align:center">
            @if($child->is_active)
              <span class="badge badge-green"><i class="fa-solid fa-circle" style="font-size:6px"></i>Aktif</span>
            @else
              <span class="badge badge-gray"><i class="fa-solid fa-circle" style="font-size:6px"></i>Nonaktif</span>
            @endif
          </td>
          <td style="text-align:center">
            <div style="display:flex;align-items:center;justify-content:center;gap:6px">
              <button title="Edit"
                onclick='openEditMenu({{ $child->id }}, {{ json_encode(["parent_id"=>$child->parent_id,"group_label"=>$child->group_label,"label"=>$child->label,"type"=>$child->type,"url"=>$child->url,"sort_order"=>$child->sort_order]) }})'
                style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:transparent;cursor:pointer;color:var(--sub);font-size:12px">
                <i class="fa-solid fa-pen"></i>
              </button>
              <form method="POST" action="{{ route('home-menus.toggle-active', $child) }}" style="margin:0">
                @csrf
                <button type="submit" title="{{ $child->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                  style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:transparent;cursor:pointer;font-size:12px;color:{{ $child->is_active ? '#fbbf24' : '#34d399' }}">
                  <i class="fa-solid {{ $child->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                </button>
              </form>
              <button title="Hapus" onclick="openDeleteMenu({{ $child->id }}, '{{ addslashes($child->label) }}')"
                style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:transparent;cursor:pointer;color:#f87171;font-size:12px">
                <i class="fa-solid fa-trash-can"></i>
              </button>
            </div>
          </td>
        </tr>
        @endforeach
        @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>

{{-- Modal Tambah Menu --}}
<div class="modal-backdrop" id="modal-add-menu" onclick="if(event.target===this)closeModal('modal-add-menu')">
  <div class="modal-box">
    <form method="POST" action="{{ route('home-menus.store') }}">
      @csrf
      <div style="padding:20px 24px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <h3 class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">Tambah Menu</h3>
        <button type="button" onclick="closeModal('modal-add-menu')"
          style="width:30px;height:30px;border-radius:8px;border:none;background:var(--surface2);cursor:pointer;color:var(--sub);font-size:14px">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <div style="padding:20px 24px;display:flex;flex-direction:column;gap:14px">
        <div>
          <label class="f-label">Label <span style="color:var(--ac)">*</span></label>
          <input name="label" class="f-input" required maxlength="100" placeholder="cth: Belanja, Toko, Halaman">
        </div>
        <div>
          <label class="f-label">URL</label>
          <input name="url" class="f-input" maxlength="255" placeholder="# atau https://...">
        </div>
        <div>
          <label class="f-label">Induk Menu <span style="color:var(--muted);font-weight:400">(kosongkan untuk menu utama)</span></label>
          <select name="parent_id" class="f-input" id="cr-menu-parent" onchange="syncMenuFields('cr')">
            <option value="">— Menu utama —</option>
            @foreach($parents as $p)
            <option value="{{ $p->id }}" data-type="{{ $p->type }}">{{ $p->label }}</option>
            @endforeach
          </select>
        </div>

        {{-- Hanya untuk menu utama --}}
        <div id="cr-type-box">
          <label class="f-label">Tipe Menu</label>
          <select name="type" class="f-input">
            <option value="simple">Biasa — dropdown daftar sederhana</option>
            <option value="mega">Mega Menu — grid berkolom</option>
          </select>
        </div>

        {{-- Hanya untuk sub-menu dari induk bertipe mega --}}
        <div id="cr-group-box" style="display:none">
          <label class="f-label">Judul Kolom <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
          <input name="group_label" class="f-input" maxlength="100" placeholder="cth: Berdasarkan Kategori">
          <p style="font-size:11px;color:var(--muted);margin-top:4px">Sub-menu dengan judul kolom yang sama akan dikelompokkan satu kolom di mega menu.</p>
        </div>

        <div style="width:140px">
          <label class="f-label">Urutan Tampil</label>
          <input name="sort_order" type="number" class="f-input" value="0" min="0">
        </div>
      </div>
      <div style="padding:16px 24px 20px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
        <button type="button" onclick="closeModal('modal-add-menu')"
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

{{-- Modal Edit Menu --}}
<div class="modal-backdrop" id="modal-edit-menu" onclick="if(event.target===this)closeModal('modal-edit-menu')">
  <div class="modal-box">
    <form id="form-edit-menu" method="POST" action="">
      @csrf @method('PUT')
      <div style="padding:20px 24px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <h3 class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">Edit Menu</h3>
        <button type="button" onclick="closeModal('modal-edit-menu')"
          style="width:30px;height:30px;border-radius:8px;border:none;background:var(--surface2);cursor:pointer;color:var(--sub);font-size:14px">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <div style="padding:20px 24px;display:flex;flex-direction:column;gap:14px">
        <div>
          <label class="f-label">Label <span style="color:var(--ac)">*</span></label>
          <input id="ed-menu-label" name="label" class="f-input" required maxlength="100">
        </div>
        <div>
          <label class="f-label">URL</label>
          <input id="ed-menu-url" name="url" class="f-input" maxlength="255">
        </div>
        <div>
          <label class="f-label">Induk Menu <span style="color:var(--muted);font-weight:400">(kosongkan untuk menu utama)</span></label>
          <select id="ed-menu-parent" name="parent_id" class="f-input" onchange="syncMenuFields('ed')">
            <option value="">— Menu utama —</option>
            @foreach($parents as $p)
            <option value="{{ $p->id }}" data-type="{{ $p->type }}">{{ $p->label }}</option>
            @endforeach
          </select>
        </div>

        <div id="ed-type-box">
          <label class="f-label">Tipe Menu</label>
          <select id="ed-menu-type" name="type" class="f-input">
            <option value="simple">Biasa — dropdown daftar sederhana</option>
            <option value="mega">Mega Menu — grid berkolom</option>
          </select>
        </div>

        <div id="ed-group-box" style="display:none">
          <label class="f-label">Judul Kolom <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
          <input id="ed-menu-group" name="group_label" class="f-input" maxlength="100" placeholder="cth: Berdasarkan Kategori">
          <p style="font-size:11px;color:var(--muted);margin-top:4px">Sub-menu dengan judul kolom yang sama akan dikelompokkan satu kolom di mega menu.</p>
        </div>

        <div style="width:140px">
          <label class="f-label">Urutan Tampil</label>
          <input id="ed-menu-sort" name="sort_order" type="number" class="f-input" min="0">
        </div>
      </div>
      <div style="padding:16px 24px 20px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
        <button type="button" onclick="closeModal('modal-edit-menu')"
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

{{-- Modal Hapus Menu --}}
<div class="modal-backdrop" id="modal-delete-menu" onclick="if(event.target===this)closeModal('modal-delete-menu')">
  <div class="modal-box" style="max-width:380px">
    <form id="form-delete-menu" method="POST" action="">
      @csrf @method('DELETE')
      <div style="padding:28px 24px 20px;text-align:center">
        <div style="width:52px;height:52px;border-radius:14px;background:rgba(239,68,68,.15);display:grid;place-items:center;margin:0 auto 16px;color:#f87171;font-size:20px">
          <i class="fa-solid fa-trash-can"></i>
        </div>
        <h3 class="font-display" style="font-size:17px;font-weight:700;color:var(--text);margin-bottom:7px">Hapus Menu?</h3>
        <p style="font-size:13px;color:var(--muted)">
          Menu <strong id="del-menu-name" style="color:var(--text)"></strong> beserta sub-menunya (jika ada) akan dihapus permanen.
        </p>
      </div>
      <div style="padding:0 24px 20px;display:flex;gap:10px">
        <button type="button" onclick="closeModal('modal-delete-menu')"
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
// Tampilkan/sembunyikan "Tipe Menu" (untuk menu utama) vs "Judul Kolom" (untuk sub-menu
// dari induk bertipe mega), tergantung pilihan Induk Menu.
function syncMenuFields(pfx) {
  const parentSelect = document.getElementById(pfx + '-menu-parent');
  const typeBox  = document.getElementById(pfx + '-type-box');
  const groupBox = document.getElementById(pfx + '-group-box');
  const opt = parentSelect.options[parentSelect.selectedIndex];
  const parentType = opt ? opt.dataset.type : undefined;

  if (!parentSelect.value) {
    // Menu utama: tampilkan pilihan tipe, sembunyikan judul kolom.
    typeBox.style.display  = '';
    groupBox.style.display = 'none';
  } else {
    // Sub-menu: sembunyikan tipe, tampilkan judul kolom hanya jika induknya mega.
    typeBox.style.display  = 'none';
    groupBox.style.display = parentType === 'mega' ? '' : 'none';
  }
}

function openEditMenu(id, data) {
  document.getElementById('form-edit-menu').action = '/pengaturan/menu/items/' + id;
  document.getElementById('ed-menu-label').value  = data.label || '';
  document.getElementById('ed-menu-url').value    = data.url || '';
  document.getElementById('ed-menu-parent').value = data.parent_id || '';
  document.getElementById('ed-menu-type').value   = data.type || 'simple';
  document.getElementById('ed-menu-group').value  = data.group_label || '';
  document.getElementById('ed-menu-sort').value   = data.sort_order ?? 0;
  syncMenuFields('ed');
  openModal('modal-edit-menu');
}

function openDeleteMenu(id, label) {
  document.getElementById('form-delete-menu').action = '/pengaturan/menu/items/' + id;
  document.getElementById('del-menu-name').textContent = label;
  openModal('modal-delete-menu');
}

document.addEventListener('DOMContentLoaded', () => syncMenuFields('cr'));
</script>
