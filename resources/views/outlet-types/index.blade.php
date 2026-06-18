<x-app-layout>
<x-slot name="pageTitle">Jenis Outlet</x-slot>

@php
  $total    = $types->count();
  $aktif    = $types->where('is_active', true)->count();
  $nonaktif = $types->where('is_active', false)->count();
@endphp

{{-- Stats --}}
<div class="stat-grid animate-fadeUp" style="grid-template-columns:repeat(3,1fr)">
  <div class="stat-card">
    <div class="stat-icon" style="background:var(--ac-lt);color:var(--ac)">
      <i class="fa-solid fa-tags"></i>
    </div>
    <div>
      <div class="stat-num">{{ $total }}</div>
      <div class="stat-label">Total Jenis</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(16,185,129,.14);color:#34d399">
      <i class="fa-solid fa-circle-check"></i>
    </div>
    <div>
      <div class="stat-num">{{ $aktif }}</div>
      <div class="stat-label">Aktif</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(148,163,184,.12);color:var(--muted)">
      <i class="fa-solid fa-circle-xmark"></i>
    </div>
    <div>
      <div class="stat-num">{{ $nonaktif }}</div>
      <div class="stat-label">Tidak Aktif</div>
    </div>
  </div>
</div>

{{-- Table card --}}
<div class="card animate-fadeUp d1">
  <div class="card-header">
    <div class="card-title"><i class="fa-solid fa-tags a-text" style="margin-right:8px"></i>Daftar Jenis Outlet</div>
    <button onclick="openModal('modal-create')"
      style="display:flex;align-items:center;gap:7px;padding:8px 16px;border-radius:10px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
      <i class="fa-solid fa-plus"></i> Tambah Jenis
    </button>
  </div>

  @if($types->isEmpty())
  <div style="padding:60px 24px;text-align:center">
    <div style="width:64px;height:64px;border-radius:18px;background:var(--surface2);display:grid;place-items:center;margin:0 auto 16px;font-size:26px;color:var(--muted)">
      <i class="fa-solid fa-tags"></i>
    </div>
    <div style="font-size:15px;font-weight:600;color:var(--text);margin-bottom:6px">Belum ada jenis outlet</div>
    <p style="font-size:13px;color:var(--muted)">Klik <strong>Tambah Jenis</strong> untuk menambahkan jenis outlet pertama.</p>
  </div>
  @else
  <div style="overflow-x:auto">
    <table class="tbl">
      <thead>
        <tr>
          <th style="width:40px">#</th>
          <th>Nama Jenis</th>
          <th style="width:70px">Kode</th>
          <th>Fitur</th>
          <th style="width:80px;text-align:center">Outlet</th>
          <th style="width:80px;text-align:center">Urutan</th>
          <th style="width:90px">Status</th>
          <th style="text-align:right">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @foreach($types as $i => $type)
        <tr>
          <td style="color:var(--muted);font-size:12px">{{ $i + 1 }}</td>
          <td>
            <div style="display:flex;align-items:center;gap:10px">
              <div style="width:36px;height:36px;border-radius:10px;background:var(--ac-lt);color:var(--ac);display:grid;place-items:center;flex-shrink:0;font-size:15px">
                <i class="fa-solid {{ $type->icon ?? 'fa-store' }}"></i>
              </div>
              <div>
                <div style="font-size:13.5px;font-weight:600;color:var(--text)">{{ $type->name }}</div>
                @if($type->description)
                <div style="font-size:11px;color:var(--muted);margin-top:1px">{{ $type->description }}</div>
                @endif
              </div>
            </div>
          </td>
          <td>
            @if($type->type_code)
            <span style="font-size:12px;font-family:monospace;font-weight:700;letter-spacing:.04em;padding:3px 10px;border-radius:8px;background:var(--surface2);color:var(--text)">
              {{ $type->type_code }}
            </span>
            @else
            <span style="color:var(--muted);font-size:12px">—</span>
            @endif
          </td>
          <td>
            <div style="display:flex;gap:5px;flex-wrap:wrap">
              @if($type->requires_opening_stock)
              <span class="badge badge-blue"><i class="fa-solid fa-boxes-stacking" style="font-size:9px"></i>Opening Stock</span>
              @endif
              @if($type->track_cogs)
              <span class="badge badge-amber"><i class="fa-solid fa-chart-line" style="font-size:9px"></i>Track COGS</span>
              @endif
              @if(!$type->requires_opening_stock && !$type->track_cogs)
              <span style="font-size:12px;color:var(--muted)">—</span>
              @endif
            </div>
          </td>
          <td style="text-align:center">
            <span style="font-size:13px;font-weight:700;color:{{ $type->outlets_count > 0 ? 'var(--ac)' : 'var(--muted)' }}">
              {{ $type->outlets_count }}
            </span>
          </td>
          <td style="text-align:center">
            <span style="font-size:12px;color:var(--sub)">{{ $type->sort_order }}</span>
          </td>
          <td>
            @if($type->is_active)
            <span class="badge badge-green"><i class="fa-solid fa-circle" style="font-size:7px"></i>Aktif</span>
            @else
            <span class="badge badge-gray"><i class="fa-solid fa-circle" style="font-size:7px"></i>Nonaktif</span>
            @endif
          </td>
          <td>
            <div style="display:flex;align-items:center;justify-content:flex-end;gap:6px">
              {{-- Toggle Active --}}
              <form method="POST" action="{{ route('outlet-types.toggle-active', $type) }}" style="display:inline">
                @csrf
                <button type="submit" title="{{ $type->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                  style="width:32px;height:32px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);cursor:pointer;font-size:13px;color:{{ $type->is_active ? '#fbbf24' : '#34d399' }};transition:color .15s">
                  <i class="fa-solid {{ $type->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                </button>
              </form>
              {{-- Edit --}}
              <button
                data-id="{{ $type->id }}"
                data-name="{{ $type->name }}"
                data-type-code="{{ $type->type_code }}"
                data-icon="{{ $type->icon }}"
                data-description="{{ $type->description }}"
                data-sort="{{ $type->sort_order }}"
                data-ros="{{ $type->requires_opening_stock ? '1' : '0' }}"
                data-cogs="{{ $type->track_cogs ? '1' : '0' }}"
                onclick="openEdit(this)"
                style="width:32px;height:32px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);cursor:pointer;font-size:13px;color:var(--sub);transition:color .15s"
                onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--sub)'" title="Edit">
                <i class="fa-solid fa-pen-to-square"></i>
              </button>
              {{-- Hapus --}}
              <button
                data-id="{{ $type->id }}"
                data-name="{{ $type->name }}"
                data-count="{{ $type->outlets_count }}"
                onclick="confirmDelete(this)"
                style="width:32px;height:32px;border-radius:8px;border:1px solid rgba(239,68,68,.3);background:rgba(239,68,68,.08);cursor:pointer;font-size:13px;color:#f87171;transition:background .15s"
                onmouseover="this.style.background='rgba(239,68,68,.18)'" onmouseout="this.style.background='rgba(239,68,68,.08)'" title="Hapus">
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

{{-- ── Modal Tambah ── --}}
<div class="modal-backdrop" id="modal-create" onclick="if(event.target===this)closeModal('modal-create')">
  <div class="modal-box" style="max-width:520px">
    <div style="padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
      <div>
        <div class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">Tambah Jenis Outlet</div>
        <div style="font-size:12px;color:var(--muted);margin-top:1px">Jenis usaha yang dapat dipilih saat membuat outlet</div>
      </div>
      <button onclick="closeModal('modal-create')"
        style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);cursor:pointer;color:var(--muted);font-size:14px">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <form method="POST" action="{{ route('outlet-types.store') }}">
      @csrf
      <div style="padding:20px 22px;display:flex;flex-direction:column;gap:14px">

        <div style="display:grid;grid-template-columns:1fr 80px;gap:12px">
          <div>
            <label class="f-label">Nama Jenis <span style="color:#f87171">*</span></label>
            <input name="name" type="text" class="f-input" placeholder="Contoh: Warung Makan" required
              value="{{ old('name') }}">
          </div>
          <div>
            <label class="f-label">Kode</label>
            <input name="type_code" id="cr-type-code" type="text" class="f-input" placeholder="01"
              maxlength="2" style="text-transform:uppercase;letter-spacing:.1em;text-align:center"
              value="{{ old('type_code') }}"
              oninput="this.value=this.value.toUpperCase()">
          </div>
        </div>

        <div>
          <label class="f-label">Icon <span style="color:#f87171">*</span></label>
          <div style="display:flex;gap:8px;align-items:center;margin-bottom:8px">
            <div id="cr-icon-prev" style="width:38px;height:38px;border-radius:10px;background:var(--ac-lt);color:var(--ac);display:grid;place-items:center;font-size:16px;flex-shrink:0">
              <i class="fa-solid fa-store" id="cr-icon-prev-i"></i>
            </div>
            <input name="icon" id="cr-icon" type="text" class="f-input" value="{{ old('icon', 'fa-store') }}"
              placeholder="fa-store" oninput="updateIconPrev('cr',this.value)" required>
          </div>
          <div style="display:flex;flex-wrap:wrap;gap:4px">
            @foreach(['fa-store','fa-shop','fa-utensils','fa-mug-hot','fa-basket-shopping','fa-prescription-bottle','fa-shirt','fa-mobile-screen','fa-scissors','fa-soap','fa-book','fa-wheat-awn','fa-fish','fa-pizza-slice','fa-burger','fa-bowl-rice','fa-ice-cream','fa-cake-candles','fa-truck','fa-wrench','fa-paintbrush','fa-computer','fa-gamepad','fa-bicycle','fa-baby','fa-glasses','fa-gem','fa-music','fa-dumbbell','fa-stethoscope','fa-leaf','fa-seedling','fa-hammer','fa-toolbox','fa-palette','fa-graduation-cap'] as $ico)
            <button type="button" onclick="setIcon('cr','{{ $ico }}')" title="{{ $ico }}"
              style="width:28px;height:28px;border-radius:7px;border:1px solid var(--border);background:var(--surface2);cursor:pointer;font-size:12px;color:var(--sub);transition:all .12s"
              onmouseover="this.style.background='var(--ac-lt)';this.style.color='var(--ac)'"
              onmouseout="this.style.background='var(--surface2)';this.style.color='var(--sub)'">
              <i class="fa-solid {{ $ico }}"></i>
            </button>
            @endforeach
          </div>
        </div>

        <div>
          <label class="f-label">Deskripsi</label>
          <input name="description" type="text" class="f-input" placeholder="Contoh: Warung nasi, warteg, rumah makan"
            value="{{ old('description') }}" maxlength="255">
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
          <label style="display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;border:1px solid var(--border);cursor:pointer">
            <input type="checkbox" name="requires_opening_stock" value="1" style="accent-color:var(--ac);width:15px;height:15px">
            <div>
              <div style="font-size:13px;font-weight:600;color:var(--text)">Opening Stock</div>
              <div style="font-size:11px;color:var(--muted)">Wajib input stok awal</div>
            </div>
          </label>
          <label style="display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;border:1px solid var(--border);cursor:pointer">
            <input type="checkbox" name="track_cogs" value="1" style="accent-color:var(--ac);width:15px;height:15px">
            <div>
              <div style="font-size:13px;font-weight:600;color:var(--text)">Track COGS</div>
              <div style="font-size:11px;color:var(--muted)">Lacak harga pokok</div>
            </div>
          </label>
        </div>

        <div style="width:120px">
          <label class="f-label">Urutan Tampil</label>
          <input name="sort_order" type="number" class="f-input" placeholder="0" min="0" value="{{ old('sort_order', 0) }}">
        </div>

      </div>
      <div style="padding:14px 22px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
        <button type="button" onclick="closeModal('modal-create')"
          style="padding:9px 18px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
          Batal
        </button>
        <button type="submit"
          style="padding:9px 20px;border-radius:10px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
          <i class="fa-solid fa-plus" style="margin-right:6px"></i>Tambah Jenis
        </button>
      </div>
    </form>
  </div>
</div>

{{-- ── Modal Edit ── --}}
<div class="modal-backdrop" id="modal-edit" onclick="if(event.target===this)closeModal('modal-edit')">
  <div class="modal-box" style="max-width:520px">
    <div style="padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
      <div>
        <div class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">Edit Jenis Outlet</div>
        <div id="ed-subtitle" style="font-size:12px;color:var(--muted);margin-top:1px">Perbarui data jenis outlet</div>
      </div>
      <button onclick="closeModal('modal-edit')"
        style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);cursor:pointer;color:var(--muted);font-size:14px">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <form method="POST" id="ed-form" action="">
      @csrf
      @method('PUT')
      <div style="padding:20px 22px;display:flex;flex-direction:column;gap:14px">

        <div style="display:grid;grid-template-columns:1fr 80px;gap:12px">
          <div>
            <label class="f-label">Nama Jenis <span style="color:#f87171">*</span></label>
            <input id="ed-name" name="name" type="text" class="f-input" placeholder="Nama jenis outlet" required>
          </div>
          <div>
            <label class="f-label">Kode</label>
            <input id="ed-type-code" name="type_code" type="text" class="f-input" placeholder="01"
              maxlength="2" style="text-transform:uppercase;letter-spacing:.1em;text-align:center"
              oninput="this.value=this.value.toUpperCase()">
          </div>
        </div>

        <div>
          <label class="f-label">Icon <span style="color:#f87171">*</span></label>
          <div style="display:flex;gap:8px;align-items:center;margin-bottom:8px">
            <div id="ed-icon-prev" style="width:38px;height:38px;border-radius:10px;background:var(--ac-lt);color:var(--ac);display:grid;place-items:center;font-size:16px;flex-shrink:0">
              <i class="fa-solid fa-store" id="ed-icon-prev-i"></i>
            </div>
            <input id="ed-icon" name="icon" type="text" class="f-input" placeholder="fa-store"
              oninput="updateIconPrev('ed',this.value)" required>
          </div>
          <div style="display:flex;flex-wrap:wrap;gap:4px">
            @foreach(['fa-store','fa-shop','fa-utensils','fa-mug-hot','fa-basket-shopping','fa-prescription-bottle','fa-shirt','fa-mobile-screen','fa-scissors','fa-soap','fa-book','fa-wheat-awn','fa-fish','fa-pizza-slice','fa-burger','fa-bowl-rice','fa-ice-cream','fa-cake-candles','fa-truck','fa-wrench','fa-paintbrush','fa-computer','fa-gamepad','fa-bicycle','fa-baby','fa-glasses','fa-gem','fa-music','fa-dumbbell','fa-stethoscope','fa-leaf','fa-seedling','fa-hammer','fa-toolbox','fa-palette','fa-graduation-cap'] as $ico)
            <button type="button" onclick="setIcon('ed','{{ $ico }}')" title="{{ $ico }}"
              style="width:28px;height:28px;border-radius:7px;border:1px solid var(--border);background:var(--surface2);cursor:pointer;font-size:12px;color:var(--sub);transition:all .12s"
              onmouseover="this.style.background='var(--ac-lt)';this.style.color='var(--ac)'"
              onmouseout="this.style.background='var(--surface2)';this.style.color='var(--sub)'">
              <i class="fa-solid {{ $ico }}"></i>
            </button>
            @endforeach
          </div>
        </div>

        <div>
          <label class="f-label">Deskripsi</label>
          <input id="ed-desc" name="description" type="text" class="f-input" placeholder="Deskripsi singkat" maxlength="255">
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
          <label style="display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;border:1px solid var(--border);cursor:pointer">
            <input type="checkbox" id="ed-ros" name="requires_opening_stock" value="1" style="accent-color:var(--ac);width:15px;height:15px">
            <div>
              <div style="font-size:13px;font-weight:600;color:var(--text)">Opening Stock</div>
              <div style="font-size:11px;color:var(--muted)">Wajib input stok awal</div>
            </div>
          </label>
          <label style="display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;border:1px solid var(--border);cursor:pointer">
            <input type="checkbox" id="ed-cogs" name="track_cogs" value="1" style="accent-color:var(--ac);width:15px;height:15px">
            <div>
              <div style="font-size:13px;font-weight:600;color:var(--text)">Track COGS</div>
              <div style="font-size:11px;color:var(--muted)">Lacak harga pokok</div>
            </div>
          </label>
        </div>

        <div style="width:120px">
          <label class="f-label">Urutan Tampil</label>
          <input id="ed-sort" name="sort_order" type="number" class="f-input" placeholder="0" min="0">
        </div>

      </div>
      <div style="padding:14px 22px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
        <button type="button" onclick="closeModal('modal-edit')"
          style="padding:9px 18px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
          Batal
        </button>
        <button type="submit"
          style="padding:9px 20px;border-radius:10px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
          <i class="fa-solid fa-floppy-disk" style="margin-right:6px"></i>Simpan Perubahan
        </button>
      </div>
    </form>
  </div>
</div>

{{-- ── Modal Konfirmasi Hapus ── --}}
<div class="modal-backdrop" id="modal-delete" onclick="if(event.target===this)closeModal('modal-delete')">
  <div class="modal-box" style="max-width:400px">
    <div style="padding:24px 24px 20px;text-align:center">
      <div style="width:54px;height:54px;border-radius:14px;background:rgba(239,68,68,.12);color:#f87171;display:grid;place-items:center;font-size:22px;margin:0 auto 14px">
        <i class="fa-solid fa-trash-can"></i>
      </div>
      <div class="font-display" style="font-size:17px;font-weight:700;color:var(--text);margin-bottom:7px">Hapus Jenis Outlet?</div>
      <p style="font-size:13px;color:var(--muted);line-height:1.65">
        Jenis <strong id="del-name" style="color:var(--text)"></strong> akan dihapus permanen.<br>
        <span id="del-warn" style="display:none;color:#f87171;font-size:12px;margin-top:4px;display:block"></span>
        Tindakan ini tidak dapat dibatalkan.
      </p>
    </div>
    <form method="POST" id="del-form" action="">
      @csrf
      @method('DELETE')
      <div style="padding:0 24px 20px;display:flex;gap:10px">
        <button type="button" onclick="closeModal('modal-delete')"
          style="flex:1;padding:10px;border-radius:11px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
          Batal
        </button>
        <button id="del-submit" type="submit"
          style="flex:1;padding:10px;border-radius:11px;border:none;background:linear-gradient(135deg,#ef4444,#dc2626);color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
          <i class="fa-solid fa-trash-can" style="margin-right:6px"></i>Ya, Hapus
        </button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
function updateIconPrev(pfx, val) {
  const el = document.getElementById(pfx + '-icon-prev-i');
  if (el) el.className = 'fa-solid ' + (val || 'fa-store');
}

function setIcon(pfx, icon) {
  const input = document.getElementById(pfx + '-icon');
  if (input) {
    input.value = icon;
    updateIconPrev(pfx, icon);
  }
}

function openEdit(btn) {
  const d = btn.dataset;
  document.getElementById('ed-form').action              = '/outlet-types/' + d.id;
  document.getElementById('ed-subtitle').textContent     = 'Edit: ' + d.name;
  document.getElementById('ed-name').value               = d.name        || '';
  document.getElementById('ed-type-code').value          = d.typeCode    || '';
  document.getElementById('ed-desc').value               = d.description || '';
  document.getElementById('ed-sort').value               = d.sort        || 0;
  document.getElementById('ed-ros').checked              = d.ros  === '1';
  document.getElementById('ed-cogs').checked             = d.cogs === '1';

  const icon = d.icon || 'fa-store';
  document.getElementById('ed-icon').value = icon;
  updateIconPrev('ed', icon);

  openModal('modal-edit');
}

function confirmDelete(btn) {
  const d = btn.dataset;
  document.getElementById('del-form').action      = '/outlet-types/' + d.id;
  document.getElementById('del-name').textContent = d.name;
  const warn  = document.getElementById('del-warn');
  const count = parseInt(d.count) || 0;
  if (count > 0) {
    warn.style.display = 'block';
    warn.textContent   = 'Masih digunakan oleh ' + count + ' outlet — tidak dapat dihapus.';
    document.getElementById('del-submit').disabled     = true;
    document.getElementById('del-submit').style.opacity = '.5';
  } else {
    warn.style.display = 'none';
    document.getElementById('del-submit').disabled     = false;
    document.getElementById('del-submit').style.opacity = '1';
  }
  openModal('modal-delete');
}

// Sinkronkan preview ikon pada modal create saat pertama kali dibuka
document.addEventListener('DOMContentLoaded', () => {
  const crIcon = document.getElementById('cr-icon');
  if (crIcon) updateIconPrev('cr', crIcon.value);
});
</script>
@endpush

</x-app-layout>
