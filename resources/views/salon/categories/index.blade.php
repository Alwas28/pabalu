<x-outlet-layout :outlet="$outlet" pageTitle="Kategori">

{{-- ── HEADER BAR ── --}}
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px" class="animate-fadeUp">
  <div>
    <h2 class="font-display" style="font-size:20px;font-weight:700;color:var(--text)">Kelola Kategori</h2>
    <p style="font-size:13px;color:var(--muted);margin-top:2px">
      Kategori produk khusus outlet <strong style="color:var(--sub)">{{ $outlet->name }}</strong>
    </p>
  </div>
  @if($user->hasPermission('category.create'))
  <button onclick="openModal('modal-add')"
    style="display:flex;align-items:center;gap:8px;padding:10px 18px;border-radius:12px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13.5px;font-weight:700;cursor:pointer;font-family:inherit;transition:opacity .15s"
    onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
    <i class="fa-solid fa-plus" style="font-size:12px"></i> Tambah Kategori
  </button>
  @endif
</div>

{{-- ── STATS ── --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px" class="animate-fadeUp d1">
  <div class="stat-card">
    <div class="stat-icon" style="background:var(--ac-lt);color:var(--ac)">
      <i class="fa-solid fa-tags"></i>
    </div>
    <div>
      <div class="stat-num">{{ $categories->count() }}</div>
      <div class="stat-label">Total Kategori</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(16,185,129,.15);color:#34d399">
      <i class="fa-solid fa-circle-check"></i>
    </div>
    <div>
      <div class="stat-num">{{ $categories->where('is_active', true)->count() }}</div>
      <div class="stat-label">Aktif</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(148,163,184,.12);color:#94a3b8">
      <i class="fa-solid fa-circle-xmark"></i>
    </div>
    <div>
      <div class="stat-num">{{ $categories->where('is_active', false)->count() }}</div>
      <div class="stat-label">Nonaktif</div>
    </div>
  </div>
</div>

{{-- ── TABLE ── --}}
<div class="card animate-fadeUp d2">
  <div class="card-header">
    <span class="card-title">
      <i class="fa-solid fa-tags" style="color:var(--ac);margin-right:8px;font-size:14px"></i>
      Daftar Kategori
    </span>
    <div style="position:relative">
      <input id="search-input" oninput="filterRows()" placeholder="Cari kategori…"
        style="padding:7px 12px 7px 32px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--text);font-size:13px;font-family:inherit;outline:none;width:200px;transition:border-color .15s"
        onfocus="this.style.borderColor='var(--ac)'" onblur="this.style.borderColor='var(--border)'">
      <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:12px;pointer-events:none"></i>
    </div>
  </div>

  @if($categories->isEmpty())
  <div style="padding:64px 24px;text-align:center">
    <div style="width:60px;height:60px;border-radius:16px;background:var(--ac-lt);display:grid;place-items:center;margin:0 auto 16px;font-size:22px;color:var(--ac)">
      <i class="fa-solid fa-tags"></i>
    </div>
    <h3 class="font-display" style="font-size:17px;font-weight:700;color:var(--text);margin-bottom:6px">Belum Ada Kategori</h3>
    <p style="font-size:13.5px;color:var(--muted);max-width:320px;margin:0 auto 20px">
      Buat kategori untuk mengelompokkan produk di outlet ini.
    </p>
    @if($user->hasPermission('category.create'))
    <button onclick="openModal('modal-add')"
      style="padding:10px 22px;border-radius:12px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13.5px;font-weight:700;cursor:pointer;font-family:inherit">
      <i class="fa-solid fa-plus" style="margin-right:6px;font-size:12px"></i>Tambah Kategori Pertama
    </button>
    @endif
  </div>
  @else
  <div style="overflow-x:auto">
    <table class="tbl" id="cat-table">
      <thead>
        <tr>
          <th style="width:44px">#</th>
          <th>Nama Kategori</th>
          <th>Deskripsi</th>
          <th style="text-align:center;width:80px">Urutan</th>
          <th style="text-align:center;width:90px">Status</th>
          <th style="text-align:center;width:100px">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @foreach($categories as $i => $cat)
        <tr class="cat-row" data-name="{{ strtolower($cat->name) }}">
          <td style="color:var(--muted);font-size:12px">{{ $i + 1 }}</td>
          <td class="td-main">
            <div style="display:flex;align-items:center;gap:9px">
              <div style="width:32px;height:32px;border-radius:9px;background:var(--ac-lt);color:var(--ac);display:grid;place-items:center;font-size:13px;flex-shrink:0">
                <i class="fa-solid fa-tag"></i>
              </div>
              {{ $cat->name }}
            </div>
          </td>
          <td style="max-width:260px">
            <span style="font-size:12.5px;color:var(--muted)">{{ $cat->description ?: '—' }}</span>
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
              @if($user->hasPermission('category.edit'))
              {{-- Edit --}}
              <button title="Edit"
                onclick="openEdit({{ $cat->id }}, {{ json_encode(['name'=>$cat->name,'description'=>$cat->description,'sort_order'=>$cat->sort_order]) }})"
                style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:transparent;cursor:pointer;color:var(--sub);font-size:12px;transition:all .15s"
                onmouseover="this.style.background='var(--surface2)';this.style.color='var(--text)'"
                onmouseout="this.style.background='transparent';this.style.color='var(--sub)'">
                <i class="fa-solid fa-pen"></i>
              </button>
              {{-- Toggle --}}
              <form method="POST" action="{{ $outlet->route('categories.toggle-active', [$cat]) }}" style="margin:0">
                @csrf
                <button type="submit" title="{{ $cat->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                  style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:transparent;cursor:pointer;font-size:12px;transition:all .15s;color:{{ $cat->is_active ? '#fbbf24' : '#34d399' }}"
                  onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
                  <i class="fa-solid {{ $cat->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                </button>
              </form>
              @endif
              @if($user->hasPermission('category.delete'))
              {{-- Delete --}}
              <button title="Hapus"
                onclick="openDelete({{ $cat->id }}, '{{ addslashes($cat->name) }}')"
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
          <td colspan="6" style="text-align:center;padding:32px;color:var(--muted);font-size:13px">
            <i class="fa-solid fa-magnifying-glass" style="margin-right:6px"></i>Tidak ditemukan
          </td>
        </tr>
      </tbody>
    </table>
  </div>
  @endif
</div>

{{-- ══ MODAL TAMBAH ══ --}}
@if($user->hasPermission('category.create'))
<div class="modal-backdrop" id="modal-add" onclick="if(event.target===this)closeModal('modal-add')">
  <div class="modal-box">
    <form method="POST" action="{{ $outlet->route('categories.store') }}">
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
          <input name="name" class="f-input" required maxlength="100" placeholder="cth: Perawatan Rambut, Perawatan Wajah, Treatment…" autofocus>
        </div>
        <div>
          <label class="f-label">Deskripsi <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
          <textarea name="description" class="f-input" rows="2" maxlength="300" placeholder="Keterangan singkat kategori ini"></textarea>
        </div>
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

@endif

{{-- ══ MODAL EDIT ══ --}}
@if($user->hasPermission('category.edit'))
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
          <label class="f-label">Deskripsi <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
          <textarea name="description" id="edit-desc" class="f-input" rows="2" maxlength="300"></textarea>
        </div>
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

@endif

{{-- ══ MODAL HAPUS ══ --}}
@if($user->hasPermission('category.delete'))
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
          Kategori <strong id="delete-name" style="color:var(--text)"></strong> akan dihapus permanen.
          Produk yang menggunakan kategori ini tidak ikut terhapus.
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
@endif

@push('scripts')
<script>
const outletId = {{ $outlet->id }};
const outletRp = '{{ $outlet->rp() }}';

function openEdit(id, data) {
  document.getElementById('form-edit').action = `/${outletRp}/${outletId}/categories/${id}`;
  document.getElementById('edit-name').value  = data.name || '';
  document.getElementById('edit-desc').value  = data.description || '';
  document.getElementById('edit-sort').value  = data.sort_order ?? 0;
  openModal('modal-edit');
}

function openDelete(id, name) {
  document.getElementById('form-delete').action = `/${outletRp}/${outletId}/categories/${id}`;
  document.getElementById('delete-name').textContent = name;
  openModal('modal-delete');
}

function filterRows() {
  const q = document.getElementById('search-input').value.toLowerCase().trim();
  const rows = document.querySelectorAll('.cat-row');
  let visible = 0;
  rows.forEach(r => {
    const match = !q || r.dataset.name.includes(q);
    r.style.display = match ? '' : 'none';
    if (match) visible++;
  });
  const noResult = document.getElementById('no-result');
  if (noResult) noResult.style.display = visible === 0 ? '' : 'none';
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

</x-outlet-layout>
