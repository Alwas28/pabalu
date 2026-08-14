<x-outlet-layout :outlet="$outlet" pageTitle="Unit">

@php
  $statusBadge = [
    'tersedia'    => 'badge-green',
    'disewa'      => 'badge-blue',
    'maintenance' => 'badge-amber',
    'rusak'       => 'badge-red',
  ];
@endphp

{{-- ── HEADER ── --}}
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px" class="animate-fadeUp">
  <div>
    <h2 class="font-display" style="font-size:20px;font-weight:700;color:var(--text)">Unit</h2>
    <p style="font-size:13px;color:var(--muted);margin-top:2px">
      Unit fisik per barang sewa outlet <strong style="color:var(--sub)">{{ $outlet->name }}</strong>
    </p>
  </div>
  @if($items->isNotEmpty())
  <button onclick="openModal('modal-add')"
    style="display:flex;align-items:center;gap:8px;padding:10px 18px;border-radius:12px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13.5px;font-weight:700;cursor:pointer;font-family:inherit">
    <i class="fa-solid fa-plus" style="font-size:12px"></i> Tambah Unit
  </button>
  @endif
</div>

@if($items->isEmpty())
<div class="card animate-fadeUp" style="text-align:center;padding:52px 24px">
  <div style="width:64px;height:64px;border-radius:50%;background:var(--ac-lt);color:var(--ac);display:grid;place-items:center;margin:0 auto 18px;font-size:24px">
    <i class="fa-solid fa-box"></i>
  </div>
  <div style="font-size:16px;font-weight:800;color:var(--text);margin-bottom:8px;font-family:'Clash Display',sans-serif">Belum Ada Barang Sewa</div>
  <div style="font-size:13px;color:var(--muted);line-height:1.7;max-width:380px;margin:0 auto">
    Unit adalah unit fisik dari sebuah barang sewa (mis. 3 unit kamera dari model yang sama).
    Tambahkan Barang Sewa terlebih dahulu sebelum menambahkan unit.
  </div>
  <div style="margin-top:20px">
    <a href="{{ $outlet->route('items.index') }}"
      style="padding:10px 20px;border-radius:10px;background:var(--ac);color:#fff;text-decoration:none;font-size:13px;font-weight:700;display:inline-flex;align-items:center;gap:7px">
      <i class="fa-solid fa-box"></i> Ke Barang Sewa
    </a>
  </div>
</div>
@else

{{-- ── STATS ── --}}
@php
  $total       = $units->count();
  $tersedia    = $units->where('status', 'tersedia')->count();
  $disewa      = $units->where('status', 'disewa')->count();
  $maintenance = $units->whereIn('status', ['maintenance', 'rusak'])->count();
@endphp
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px" class="animate-fadeUp d1">
  <div class="stat-card">
    <div class="stat-icon" style="background:var(--ac-lt);color:var(--ac)"><i class="fa-solid fa-cubes"></i></div>
    <div><div class="stat-num">{{ $total }}</div><div class="stat-label">Total Unit</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(16,185,129,.15);color:#34d399"><i class="fa-solid fa-circle-check"></i></div>
    <div><div class="stat-num">{{ $tersedia }}</div><div class="stat-label">Tersedia</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(96,165,250,.15);color:#60a5fa"><i class="fa-solid fa-key"></i></div>
    <div><div class="stat-num">{{ $disewa }}</div><div class="stat-label">Disewa</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(251,191,36,.15);color:#fbbf24"><i class="fa-solid fa-screwdriver-wrench"></i></div>
    <div><div class="stat-num">{{ $maintenance }}</div><div class="stat-label">Maintenance / Rusak</div></div>
  </div>
</div>

{{-- ── TABLE ── --}}
<div class="card animate-fadeUp d2">
  <div class="card-header" style="flex-wrap:wrap;gap:10px">
    <span class="card-title"><i class="fa-solid fa-cubes" style="color:var(--ac);margin-right:8px;font-size:14px"></i>Daftar Unit</span>
    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-left:auto">
      <select id="filter-item" onchange="applyFilters()"
        style="padding:7px 12px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13px;font-family:inherit;outline:none;cursor:pointer">
        <option value="">Semua Barang</option>
        @foreach($items as $it)
        <option value="{{ $it->id }}">{{ $it->name }}</option>
        @endforeach
      </select>
      <select id="filter-status" onchange="applyFilters()"
        style="padding:7px 12px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13px;font-family:inherit;outline:none;cursor:pointer">
        <option value="">Semua Status</option>
        @foreach(\App\Models\RentalUnit::STATUSES as $val => $label)
        <option value="{{ $val }}">{{ $label }}</option>
        @endforeach
      </select>
      <div style="position:relative">
        <input id="search-input" oninput="applyFilters()" placeholder="Cari kode unit..."
          style="padding:7px 12px 7px 32px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--text);font-size:13px;font-family:inherit;outline:none;width:170px"
          onfocus="this.style.borderColor='var(--ac)'" onblur="this.style.borderColor='var(--border)'">
        <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:12px;pointer-events:none"></i>
      </div>
    </div>
  </div>

  @if($units->isEmpty())
  <div style="padding:56px 24px;text-align:center">
    <i class="fa-solid fa-cubes" style="font-size:26px;opacity:.4;display:block;margin-bottom:10px;color:var(--muted)"></i>
    <p style="font-size:13.5px;color:var(--muted)">Belum ada unit. Klik "Tambah Unit" untuk menambahkan.</p>
  </div>
  @else
  <div style="overflow-x:auto">
  <table class="tbl" id="unit-table">
    <thead>
      <tr>
        <th>Barang</th>
        <th>Kode Unit</th>
        <th>Kondisi</th>
        <th style="text-align:center">Status</th>
        <th style="text-align:right">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @foreach($units as $unit)
      <tr class="unit-row" data-item="{{ $unit->rental_item_id }}" data-status="{{ $unit->status }}" data-code="{{ strtolower($unit->code) }}">
        <td class="td-main">{{ $unit->rentalItem->name }}</td>
        <td>{{ $unit->code }}</td>
        <td>{{ $unit->condition ?: '—' }}</td>
        <td style="text-align:center">
          <span class="badge {{ $statusBadge[$unit->status] ?? 'badge-gray' }}">{{ \App\Models\RentalUnit::STATUSES[$unit->status] ?? $unit->status }}</span>
        </td>
        <td style="text-align:right;white-space:nowrap">
          <button title="Edit"
            onclick='openEdit({{ $unit->id }}, {{ json_encode([
              "rental_item_id" => $unit->rental_item_id,
              "code"           => $unit->code,
              "condition"      => $unit->condition,
              "status"         => $unit->status,
              "notes"          => $unit->notes,
            ]) }})'
            style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);cursor:pointer;color:var(--sub);font-size:12px">
            <i class="fa-solid fa-pen-to-square"></i>
          </button>
          <button title="Hapus" onclick="openDelete({{ $unit->id }}, {{ json_encode($unit->code) }})"
            style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);cursor:pointer;color:#f87171;font-size:12px;margin-left:4px">
            <i class="fa-solid fa-trash-can"></i>
          </button>
        </td>
      </tr>
      @endforeach
      <tr id="no-result" style="display:none">
        <td colspan="5" style="text-align:center;padding:32px;color:var(--muted);font-size:13px">
          <i class="fa-solid fa-magnifying-glass" style="margin-right:6px"></i>Tidak ditemukan
        </td>
      </tr>
    </tbody>
  </table>
  </div>
  @endif
</div>
@endif

{{-- ══ MODAL TAMBAH ══ --}}
<div class="modal-backdrop" id="modal-add" onclick="if(event.target===this)closeModal('modal-add')">
  <div class="modal-box" style="max-width:460px">
    <form method="POST" action="{{ $outlet->route('units.store') }}">
      @csrf
      <div style="padding:20px 24px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <h3 class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">Tambah Unit</h3>
        <button type="button" onclick="closeModal('modal-add')" style="width:30px;height:30px;border-radius:8px;border:none;background:var(--surface2);cursor:pointer;color:var(--sub);font-size:14px">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <div style="padding:20px 24px;display:flex;flex-direction:column;gap:14px">
        <div>
          <label class="f-label">Barang <span style="color:var(--ac)">*</span></label>
          <select name="rental_item_id" class="f-input" required>
            <option value="">— Pilih barang —</option>
            @foreach($items as $it)
            <option value="{{ $it->id }}">{{ $it->name }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="f-label">Kode Unit <span style="color:var(--ac)">*</span></label>
          <input name="code" class="f-input" required maxlength="100" placeholder="cth: Unit 1, SN-00123">
        </div>
        <div>
          <label class="f-label">Kondisi <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
          <input name="condition" class="f-input" maxlength="100" placeholder="cth: Baik, Rusak Ringan">
        </div>
        <div>
          <label class="f-label">Status</label>
          <select name="status" class="f-input">
            @foreach(\App\Models\RentalUnit::STATUSES as $val => $label)
            <option value="{{ $val }}" {{ $val === 'tersedia' ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="f-label">Catatan <span style="color:var(--muted);font-weight:400">(opsional)</span></label>
          <textarea name="notes" class="f-input" rows="2" maxlength="1000"></textarea>
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
  <div class="modal-box" style="max-width:460px">
    <form id="form-edit" method="POST" action="">
      @csrf @method('PUT')
      <div style="padding:20px 24px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <h3 class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">Edit Unit</h3>
        <button type="button" onclick="closeModal('modal-edit')" style="width:30px;height:30px;border-radius:8px;border:none;background:var(--surface2);cursor:pointer;color:var(--sub);font-size:14px">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <div style="padding:20px 24px;display:flex;flex-direction:column;gap:14px">
        <div>
          <label class="f-label">Barang <span style="color:var(--ac)">*</span></label>
          <select name="rental_item_id" id="e-item" class="f-input" required>
            @foreach($items as $it)
            <option value="{{ $it->id }}">{{ $it->name }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="f-label">Kode Unit <span style="color:var(--ac)">*</span></label>
          <input name="code" id="e-code" class="f-input" required maxlength="100">
        </div>
        <div>
          <label class="f-label">Kondisi</label>
          <input name="condition" id="e-condition" class="f-input" maxlength="100">
        </div>
        <div>
          <label class="f-label">Status</label>
          <select name="status" id="e-status" class="f-input">
            @foreach(\App\Models\RentalUnit::STATUSES as $val => $label)
            <option value="{{ $val }}">{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="f-label">Catatan</label>
          <textarea name="notes" id="e-notes" class="f-input" rows="2" maxlength="1000"></textarea>
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
        <h3 class="font-display" style="font-size:17px;font-weight:700;color:var(--text);margin-bottom:6px">Hapus Unit?</h3>
        <p style="font-size:13.5px;color:var(--muted)">
          Unit <strong id="delete-name" style="color:var(--text)"></strong> akan dihapus permanen.
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

function openEdit(id, d) {
  document.getElementById('form-edit').action = `/${outletRp}/${outletId}/units/${id}`;
  document.getElementById('e-item').value      = d.rental_item_id ?? '';
  document.getElementById('e-code').value      = d.code || '';
  document.getElementById('e-condition').value = d.condition || '';
  document.getElementById('e-status').value    = d.status || 'tersedia';
  document.getElementById('e-notes').value     = d.notes || '';
  openModal('modal-edit');
}

function openDelete(id, name) {
  document.getElementById('form-delete').action = `/${outletRp}/${outletId}/units/${id}`;
  document.getElementById('delete-name').textContent = name;
  openModal('modal-delete');
}

function applyFilters() {
  const q      = (document.getElementById('search-input')?.value || '').toLowerCase().trim();
  const itemId = document.getElementById('filter-item')?.value || '';
  const status = document.getElementById('filter-status')?.value || '';
  let visible  = 0;

  document.querySelectorAll('.unit-row').forEach(r => {
    const ok = (!q || r.dataset.code.includes(q))
            && (!itemId || r.dataset.item === itemId)
            && (!status || r.dataset.status === status);
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
