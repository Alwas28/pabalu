<x-app-layout>
<x-slot name="pageTitle">Detail Jenis Outlet</x-slot>

{{-- Breadcrumb --}}
<div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;font-size:13px;color:var(--muted)">
  <a href="{{ route('outlet-types.index') }}" style="color:var(--muted);text-decoration:none"
    onmouseover="this.style.color='var(--ac)'" onmouseout="this.style.color='var(--muted)'">
    <i class="fa-solid fa-tags"></i> Jenis Outlet
  </a>
  <i class="fa-solid fa-chevron-right" style="font-size:10px"></i>
  <span style="color:var(--text);font-weight:500">{{ $outletType->name }}</span>
</div>

{{-- Header --}}
<div class="card animate-fadeUp" style="margin-bottom:20px">
  <div style="padding:20px 22px;display:flex;align-items:center;gap:14px;flex-wrap:wrap">
    <div style="width:52px;height:52px;border-radius:14px;background:var(--ac-lt);color:var(--ac);display:grid;place-items:center;font-size:22px;flex-shrink:0">
      <i class="fa-solid {{ $outletType->icon ?? 'fa-store' }}"></i>
    </div>
    <div style="flex:1;min-width:180px">
      <div style="font-size:18px;font-weight:700;color:var(--text)">{{ $outletType->name }}</div>
      @if($outletType->description)
      <div style="font-size:12.5px;color:var(--muted);margin-top:2px">{{ $outletType->description }}</div>
      @endif
    </div>
    <div style="display:flex;gap:20px">
      <div style="text-align:center">
        <div style="font-size:19px;font-weight:800;color:var(--text)">{{ $outletType->outlets_count }}</div>
        <div style="font-size:11px;color:var(--muted)">Outlet</div>
      </div>
      <div style="text-align:center">
        <div style="font-size:19px;font-weight:800;color:var(--text)">{{ $categories->count() }}</div>
        <div style="font-size:11px;color:var(--muted)">Kategori</div>
      </div>
    </div>
  </div>
</div>

{{-- Info banner --}}
<div class="animate-fadeUp d1" style="display:flex;align-items:flex-start;gap:12px;padding:14px 16px;border-radius:12px;background:var(--ac-lt2);border:1px solid rgba(var(--ac-rgb),.2);margin-bottom:20px">
  <i class="fa-solid fa-circle-info" style="color:var(--ac);font-size:14px;margin-top:1px"></i>
  <div style="font-size:12.5px;color:var(--sub);line-height:1.6">
    Kategori di sini dipakai bersama oleh <strong style="color:var(--text)">semua outlet</strong> dengan jenis
    <strong style="color:var(--text)">{{ $outletType->name }}</strong>. Owner/kasir tidak bisa lagi menambah,
    mengubah, atau menghapus kategori sendiri — pengelolaan kategori sepenuhnya ditentukan admin di sini.
  </div>
</div>

{{-- Kategori table --}}
<div class="card animate-fadeUp d2">
  <div class="card-header">
    <span class="card-title"><i class="fa-solid fa-tags a-text" style="margin-right:8px"></i>Kategori Produk</span>
    <button onclick="openModal('modal-add')"
      style="display:flex;align-items:center;gap:7px;padding:8px 16px;border-radius:10px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
      <i class="fa-solid fa-plus"></i> Tambah Kategori
    </button>
  </div>

  @if($categories->isEmpty())
  <div style="padding:56px 24px;text-align:center">
    <div style="width:60px;height:60px;border-radius:16px;background:var(--surface2);display:grid;place-items:center;margin:0 auto 16px;font-size:22px;color:var(--muted)">
      <i class="fa-solid fa-tags"></i>
    </div>
    <div style="font-size:15px;font-weight:600;color:var(--text);margin-bottom:6px">Belum Ada Kategori</div>
    <p style="font-size:13px;color:var(--muted);max-width:320px;margin:0 auto 18px">
      Tambahkan kategori supaya produk outlet jenis {{ $outletType->name }} bisa dikelompokkan.
    </p>
    <button onclick="openModal('modal-add')"
      style="padding:10px 22px;border-radius:12px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13.5px;font-weight:700;cursor:pointer;font-family:inherit">
      <i class="fa-solid fa-plus" style="margin-right:6px;font-size:12px"></i>Tambah Kategori Pertama
    </button>
  </div>
  @else
  <div style="overflow-x:auto">
    <table class="tbl">
      <thead>
        <tr>
          <th style="width:44px">#</th>
          <th>Nama Kategori</th>
          <th>Deskripsi</th>
          <th style="text-align:center;width:100px">Pilihan</th>
          <th style="text-align:center;width:80px">Produk</th>
          <th style="text-align:center;width:80px">Urutan</th>
          <th style="text-align:center;width:90px">Status</th>
          <th style="text-align:center;width:100px">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @foreach($categories as $i => $cat)
        <tr>
          <td style="color:var(--muted);font-size:12px">{{ $i + 1 }}</td>
          <td class="td-main">
            <div style="display:flex;align-items:center;gap:9px">
              <div style="width:32px;height:32px;border-radius:9px;background:var(--ac-lt);color:var(--ac);display:grid;place-items:center;font-size:13px;flex-shrink:0">
                <i class="fa-solid {{ $cat->icon ?: 'fa-tag' }}"></i>
              </div>
              {{ $cat->name }}
            </div>
          </td>
          <td style="max-width:260px">
            <span style="font-size:12.5px;color:var(--muted)">{{ $cat->description ?: '—' }}</span>
          </td>
          <td style="text-align:center">
            @if($cat->is_featured)
              <span class="badge badge-amber"><i class="fa-solid fa-star" style="font-size:8px"></i>Pilihan</span>
            @else
              <span style="color:var(--muted);font-size:12px">—</span>
            @endif
          </td>
          <td style="text-align:center">
            <span style="font-size:13px;font-weight:600;color:{{ $cat->products_count > 0 ? 'var(--ac)' : 'var(--muted)' }}">
              {{ $cat->products_count }}
            </span>
          </td>
          <td style="text-align:center">
            <span style="font-size:13px;font-weight:600;color:var(--sub)">{{ $cat->sort_order }}</span>
          </td>
          <td style="text-align:center">
            @if($cat->is_active)
              <span class="badge badge-green"><i class="fa-solid fa-circle" style="font-size:6px"></i>Aktif</span>
            @else
              <span class="badge badge-gray"><i class="fa-solid fa-circle" style="font-size:6px"></i>Nonaktif</span>
            @endif
          </td>
          <td style="text-align:center">
            <div style="display:flex;align-items:center;justify-content:center;gap:6px">
              {{-- Edit --}}
              <button title="Edit"
                onclick="openEdit({{ $cat->id }}, {{ json_encode(['name'=>$cat->name,'icon'=>$cat->icon,'description'=>$cat->description,'sort_order'=>$cat->sort_order,'is_featured'=>$cat->is_featured]) }})"
                style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:transparent;cursor:pointer;color:var(--sub);font-size:12px;transition:all .15s"
                onmouseover="this.style.background='var(--surface2)';this.style.color='var(--text)'"
                onmouseout="this.style.background='transparent';this.style.color='var(--sub)'">
                <i class="fa-solid fa-pen"></i>
              </button>
              {{-- Toggle --}}
              <form method="POST" action="{{ route('outlet-types.categories.toggle-active', [$outletType, $cat]) }}" style="margin:0">
                @csrf
                <button type="submit" title="{{ $cat->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                  style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:transparent;cursor:pointer;font-size:12px;transition:all .15s;color:{{ $cat->is_active ? '#fbbf24' : '#34d399' }}"
                  onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
                  <i class="fa-solid {{ $cat->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                </button>
              </form>
              {{-- Delete --}}
              <button title="Hapus"
                onclick="openDelete({{ $cat->id }}, '{{ addslashes($cat->name) }}', {{ $cat->products_count }})"
                style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:transparent;cursor:pointer;color:#f87171;font-size:12px;transition:all .15s"
                onmouseover="this.style.background='rgba(239,68,68,.1)'" onmouseout="this.style.background='transparent'">
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

{{-- Modal Tambah --}}
<div class="modal-backdrop" id="modal-add" onclick="if(event.target===this)closeModal('modal-add')">
  <div class="modal-box">
    <form method="POST" action="{{ route('outlet-types.categories.store', $outletType) }}">
      @csrf
      <div style="padding:20px 24px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <h3 class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">Tambah Kategori</h3>
        <button type="button" onclick="closeModal('modal-add')"
          style="width:30px;height:30px;border-radius:8px;border:none;background:var(--surface2);cursor:pointer;color:var(--sub);font-size:14px">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <div style="padding:20px 24px;display:flex;flex-direction:column;gap:14px">
        <div>
          <label class="f-label">Nama Kategori <span style="color:var(--ac)">*</span></label>
          <input name="name" class="f-input" required maxlength="100" placeholder="cth: Minuman, Makanan Berat, Snack…" autofocus>
        </div>
        <div>
          <label class="f-label">Icon</label>
          <div style="display:flex;gap:8px;align-items:center;margin-bottom:8px">
            <div id="cr-cat-icon-prev" style="width:38px;height:38px;border-radius:10px;background:var(--ac-lt);color:var(--ac);display:grid;place-items:center;font-size:16px;flex-shrink:0">
              <i class="fa-solid fa-tag" id="cr-cat-icon-prev-i"></i>
            </div>
            <input name="icon" id="cr-cat-icon" type="text" class="f-input" value="fa-tag"
              placeholder="fa-tag" oninput="updateCatIconPrev('cr',this.value)">
          </div>
          <div style="display:flex;flex-wrap:wrap;gap:4px">
            @foreach(['fa-tag','fa-mug-hot','fa-utensils','fa-burger','fa-pizza-slice','fa-bowl-rice','fa-ice-cream','fa-cake-candles','fa-bottle-water','fa-wine-glass','fa-cookie','fa-fish','fa-drumstick-bite','fa-carrot','fa-basket-shopping','fa-shirt','fa-mobile-screen','fa-scissors','fa-spray-can-sparkles','fa-soap','fa-pump-soap','fa-hand-sparkles','fa-book','fa-pen','fa-gift','fa-star','fa-heart','fa-baby','fa-spa'] as $ico)
            <button type="button" onclick="setCatIcon('cr','{{ $ico }}')" title="{{ $ico }}"
              style="width:28px;height:28px;border-radius:7px;border:1px solid var(--border);background:var(--surface2);cursor:pointer;font-size:12px;color:var(--sub);transition:all .12s"
              onmouseover="this.style.background='var(--ac-lt)';this.style.color='var(--ac)'"
              onmouseout="this.style.background='var(--surface2)';this.style.color='var(--sub)'">
              <i class="fa-solid {{ $ico }}"></i>
            </button>
            @endforeach
          </div>
        </div>
        <div>
          <label class="f-label">Deskripsi <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
          <textarea name="description" class="f-input" rows="2" maxlength="300" placeholder="Keterangan singkat kategori ini"></textarea>
        </div>
        <label style="display:flex;align-items:flex-start;gap:10px;padding:12px 14px;border-radius:10px;border:1px solid var(--border);cursor:pointer">
          <input type="checkbox" name="is_featured" value="1" style="accent-color:var(--ac);width:15px;height:15px;margin-top:2px;flex-shrink:0">
          <div>
            <div style="font-size:13px;font-weight:600;color:var(--text)">Kategori Pilihan</div>
            <div style="font-size:11.5px;color:var(--muted)">Tampilkan kategori ini di section "Kategori Pilihan" pada homepage</div>
          </div>
        </label>
        <div>
          <label class="f-label">Urutan Tampil</label>
          <input name="sort_order" type="number" class="f-input" value="0" min="0" max="9999"
            style="width:120px" placeholder="0">
          <p style="font-size:11.5px;color:var(--muted);margin-top:4px">Angka lebih kecil tampil lebih dulu</p>
        </div>
      </div>
      <div style="padding:16px 24px 20px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
        <button type="button" onclick="closeModal('modal-add')"
          style="padding:9px 18px;border-radius:11px;border:1px solid var(--border);background:transparent;cursor:pointer;font-size:13px;font-weight:600;color:var(--sub);font-family:inherit">
          Batal
        </button>
        <button type="submit"
          style="padding:9px 22px;border-radius:11px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit">
          Simpan
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Modal Edit --}}
<div class="modal-backdrop" id="modal-edit" onclick="if(event.target===this)closeModal('modal-edit')">
  <div class="modal-box">
    <form id="form-edit" method="POST" action="">
      @csrf @method('PUT')
      <div style="padding:20px 24px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <h3 class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">Edit Kategori</h3>
        <button type="button" onclick="closeModal('modal-edit')"
          style="width:30px;height:30px;border-radius:8px;border:none;background:var(--surface2);cursor:pointer;color:var(--sub);font-size:14px">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <div style="padding:20px 24px;display:flex;flex-direction:column;gap:14px">
        <div>
          <label class="f-label">Nama Kategori <span style="color:var(--ac)">*</span></label>
          <input name="name" id="edit-name" class="f-input" required maxlength="100">
        </div>
        <div>
          <label class="f-label">Icon</label>
          <div style="display:flex;gap:8px;align-items:center;margin-bottom:8px">
            <div id="ed-cat-icon-prev" style="width:38px;height:38px;border-radius:10px;background:var(--ac-lt);color:var(--ac);display:grid;place-items:center;font-size:16px;flex-shrink:0">
              <i class="fa-solid fa-tag" id="ed-cat-icon-prev-i"></i>
            </div>
            <input name="icon" id="edit-icon" type="text" class="f-input" value="fa-tag"
              placeholder="fa-tag" oninput="updateCatIconPrev('ed',this.value)">
          </div>
          <div style="display:flex;flex-wrap:wrap;gap:4px">
            @foreach(['fa-tag','fa-mug-hot','fa-utensils','fa-burger','fa-pizza-slice','fa-bowl-rice','fa-ice-cream','fa-cake-candles','fa-bottle-water','fa-wine-glass','fa-cookie','fa-fish','fa-drumstick-bite','fa-carrot','fa-basket-shopping','fa-shirt','fa-mobile-screen','fa-scissors','fa-spray-can-sparkles','fa-soap','fa-pump-soap','fa-hand-sparkles','fa-book','fa-pen','fa-gift','fa-star','fa-heart','fa-baby','fa-spa'] as $ico)
            <button type="button" onclick="setCatIcon('ed','{{ $ico }}')" title="{{ $ico }}"
              style="width:28px;height:28px;border-radius:7px;border:1px solid var(--border);background:var(--surface2);cursor:pointer;font-size:12px;color:var(--sub);transition:all .12s"
              onmouseover="this.style.background='var(--ac-lt)';this.style.color='var(--ac)'"
              onmouseout="this.style.background='var(--surface2)';this.style.color='var(--sub)'">
              <i class="fa-solid {{ $ico }}"></i>
            </button>
            @endforeach
          </div>
        </div>
        <div>
          <label class="f-label">Deskripsi <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
          <textarea name="description" id="edit-desc" class="f-input" rows="2" maxlength="300"></textarea>
        </div>
        <label style="display:flex;align-items:flex-start;gap:10px;padding:12px 14px;border-radius:10px;border:1px solid var(--border);cursor:pointer">
          <input type="checkbox" name="is_featured" id="edit-featured" value="1" style="accent-color:var(--ac);width:15px;height:15px;margin-top:2px;flex-shrink:0">
          <div>
            <div style="font-size:13px;font-weight:600;color:var(--text)">Kategori Pilihan</div>
            <div style="font-size:11.5px;color:var(--muted)">Tampilkan kategori ini di section "Kategori Pilihan" pada homepage</div>
          </div>
        </label>
        <div>
          <label class="f-label">Urutan Tampil</label>
          <input name="sort_order" id="edit-sort" type="number" class="f-input" min="0" max="9999" style="width:120px">
          <p style="font-size:11.5px;color:var(--muted);margin-top:4px">Angka lebih kecil tampil lebih dulu</p>
        </div>
      </div>
      <div style="padding:16px 24px 20px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
        <button type="button" onclick="closeModal('modal-edit')"
          style="padding:9px 18px;border-radius:11px;border:1px solid var(--border);background:transparent;cursor:pointer;font-size:13px;font-weight:600;color:var(--sub);font-family:inherit">
          Batal
        </button>
        <button type="submit"
          style="padding:9px 22px;border-radius:11px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit">
          Simpan Perubahan
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Modal Hapus --}}
<div class="modal-backdrop" id="modal-delete" onclick="if(event.target===this)closeModal('modal-delete')">
  <div class="modal-box" style="max-width:380px">
    <form id="form-delete" method="POST" action="">
      @csrf @method('DELETE')
      <div style="padding:28px 24px 20px;text-align:center">
        <div style="width:52px;height:52px;border-radius:14px;background:rgba(239,68,68,.15);display:grid;place-items:center;margin:0 auto 16px;color:#f87171;font-size:20px">
          <i class="fa-solid fa-trash-can"></i>
        </div>
        <h3 class="font-display" style="font-size:17px;font-weight:700;color:var(--text);margin-bottom:6px">Hapus Kategori?</h3>
        <p style="font-size:13.5px;color:var(--muted)">
          Kategori <strong id="delete-name" style="color:var(--text)"></strong> akan dihapus permanen dari
          semua outlet jenis <strong style="color:var(--text)">{{ $outletType->name }}</strong>.
          <span id="delete-warn" style="display:none;color:#f87171"></span>
        </p>
      </div>
      <div style="padding:0 24px 24px;display:flex;gap:10px">
        <button type="button" onclick="closeModal('modal-delete')"
          style="flex:1;padding:10px;border-radius:11px;border:1px solid var(--border);background:transparent;cursor:pointer;font-size:13.5px;font-weight:600;color:var(--sub);font-family:inherit">
          Batal
        </button>
        <button type="submit"
          style="flex:1;padding:10px;border-radius:11px;border:none;background:linear-gradient(135deg,#ef4444,#dc2626);color:#fff;font-size:13.5px;font-weight:700;cursor:pointer;font-family:inherit">
          Hapus
        </button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
const outletTypeId = {{ $outletType->id }};

function updateCatIconPrev(pfx, val) {
  const el = document.getElementById(pfx + '-cat-icon-prev-i');
  if (el) el.className = 'fa-solid ' + (val || 'fa-tag');
}

function setCatIcon(pfx, icon) {
  const input = document.getElementById(pfx === 'cr' ? 'cr-cat-icon' : 'edit-icon');
  if (input) {
    input.value = icon;
    updateCatIconPrev(pfx, icon);
  }
}

function openEdit(id, data) {
  document.getElementById('form-edit').action = `/outlet-types/${outletTypeId}/categories/${id}`;
  document.getElementById('edit-name').value  = data.name || '';
  document.getElementById('edit-desc').value  = data.description || '';
  document.getElementById('edit-sort').value  = data.sort_order ?? 0;
  document.getElementById('edit-featured').checked = !!data.is_featured;
  const icon = data.icon || 'fa-tag';
  document.getElementById('edit-icon').value = icon;
  updateCatIconPrev('ed', icon);
  openModal('modal-edit');
}

function openDelete(id, name, productCount) {
  document.getElementById('form-delete').action = `/outlet-types/${outletTypeId}/categories/${id}`;
  document.getElementById('delete-name').textContent = name;
  const warn = document.getElementById('delete-warn');
  if (productCount > 0) {
    warn.style.display = 'inline';
    warn.textContent = ` ${productCount} produk sedang memakai kategori ini akan jadi tanpa kategori.`;
  } else {
    warn.style.display = 'none';
  }
  openModal('modal-delete');
}

@if($errors->any())
@if(old('_method') === 'PUT')
  openModal('modal-edit');
@else
  openModal('modal-add');
@endif
@endif
</script>
@endpush

</x-app-layout>
