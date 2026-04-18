{{--
  Partial: permission group checkboxes
  Vars: $groups (array), $allPermissions (Collection), $checked (array of names)
--}}
@php
  $permLabels = [
    'outlet.create'       => 'Buat',
    'outlet.read'         => 'Lihat',
    'outlet.update'       => 'Edit',
    'outlet.delete'       => 'Hapus',
    'role.create'         => 'Buat',
    'role.read'           => 'Lihat',
    'role.update'         => 'Edit',
    'role.delete'         => 'Hapus',
    'permission.create'   => 'Buat',
    'permission.read'     => 'Lihat',
    'permission.update'   => 'Edit',
    'permission.delete'   => 'Hapus',
    'user.create'         => 'Buat',
    'user.read'           => 'Lihat',
    'user.update'         => 'Edit',
    'user.delete'         => 'Hapus',
    'user.assign'         => 'Assign Role',
    'product.create'      => 'Buat',
    'product.read'        => 'Lihat',
    'product.update'      => 'Edit',
    'product.delete'      => 'Hapus',
    'category.create'     => 'Buat',
    'category.read'       => 'Lihat',
    'category.update'     => 'Edit',
    'category.delete'     => 'Hapus',
    'stock.opening'       => 'Opening',
    'stock.in'            => 'Tambah',
    'stock.waste'         => 'Waste',
    'stock.read'          => 'Lihat',
    'transaction.create'  => 'Buat',
    'transaction.read'    => 'Lihat',
    'transaction.void'    => 'Void/Batal',
    'expense.create'      => 'Buat',
    'expense.read'        => 'Lihat',
    'expense.update'      => 'Edit',
    'expense.delete'      => 'Hapus',
    'closing.create'      => 'Closing',
    'closing.read'        => 'Lihat',
    'report.outlet'       => 'Lap. Outlet',
    'report.all'          => 'Semua Outlet',
    'order.read'          => 'Lihat',
    'order.manage'        => 'Proses/Batal',
    'log.read'            => 'Lihat Log',
    'setting.read'        => 'Lihat',
    'setting.update'      => 'Edit',
    'guide.read'          => 'Lihat',
    'guide.update'        => 'Edit Panduan',
  ];
  $existingNames = $allPermissions->pluck('name')->toArray();
@endphp

<div style="display:flex;flex-direction:column;gap:4px;margin-bottom:12px">
  <label style="display:flex;align-items:center;gap:8px;font-size:12.5px;font-weight:600;color:var(--sub);cursor:pointer;user-select:none">
    <input type="checkbox" id="select-all" onchange="toggleSelectAll(this)"
      style="width:15px;height:15px;accent-color:var(--ac);cursor:pointer">
    Pilih Semua Permission
  </label>
</div>

@foreach ($groups as $groupName => $perms)
@php
  $groupPerms = array_filter($perms, fn($p) => in_array($p, $existingNames));
  if (empty($groupPerms)) continue;
@endphp
<div class="perm-group card" style="margin-bottom:12px">
  <div class="card-header" style="padding:12px 16px;cursor:pointer" onclick="toggleGroup(this)">
    <div style="display:flex;align-items:center;gap:10px">
      <input type="checkbox" class="group-check"
        onchange="toggleGroupPerms(this)" onclick="event.stopPropagation()"
        style="width:15px;height:15px;accent-color:var(--ac);cursor:pointer">
      <div class="card-title" style="font-size:13.5px">{{ $groupName }}</div>
      <span class="group-badge" style="font-size:10px;padding:2px 8px;border-radius:99px;background:var(--surface2);color:var(--muted);font-weight:600">
        <span class="group-count">0</span>/{{ count($groupPerms) }}
      </span>
    </div>
    <i class="fa-solid fa-chevron-down group-chevron" style="font-size:11px;color:var(--muted);transition:transform .2s"></i>
  </div>
  <div class="group-body" style="padding:14px 16px;display:flex;flex-wrap:wrap;gap:10px">
    @foreach ($groupPerms as $perm)
    @php $permExists = in_array($perm, $existingNames); @endphp
    @if ($permExists)
    <label style="display:flex;align-items:center;gap:7px;font-size:13px;color:var(--sub);cursor:pointer;user-select:none;padding:6px 12px;border-radius:8px;border:1px solid var(--border);transition:border-color .15s,background .15s;min-width:120px"
      onmouseover="this.style.borderColor='var(--ac)';this.style.background='var(--ac-lt)'"
      onmouseout="if(!this.querySelector('input').checked){this.style.borderColor='var(--border)';this.style.background='transparent'}"
      id="label-{{ str_replace('.','_',$perm) }}">
      <input type="checkbox"
        name="permissions[]"
        value="{{ $perm }}"
        class="perm-check"
        data-group="{{ $groupName }}"
        {{ in_array($perm, $checked ?? []) ? 'checked' : '' }}
        onchange="syncGroupCheck(this)"
        style="width:14px;height:14px;accent-color:var(--ac);cursor:pointer">
      <span style="font-size:12px;font-weight:600">{{ $permLabels[$perm] ?? $perm }}</span>
      <code style="font-size:9.5px;color:var(--muted);margin-left:auto">{{ $perm }}</code>
    </label>
    @endif
    @endforeach
  </div>
</div>
@endforeach

@push('scripts')
<script>
// Sync individual checkbox styling
document.querySelectorAll('.perm-check').forEach(cb => {
  const label = cb.closest('label');
  function updateStyle() {
    if (cb.checked) {
      label.style.borderColor = 'var(--ac)';
      label.style.background  = 'var(--ac-lt)';
      label.style.color       = 'var(--ac)';
    } else {
      label.style.borderColor = 'var(--border)';
      label.style.background  = 'transparent';
      label.style.color       = 'var(--sub)';
    }
  }
  updateStyle();
  cb.addEventListener('change', updateStyle);
});

// Group check state sync
function syncGroupCheck(cb) {
  const group   = cb.dataset.group;
  const all     = document.querySelectorAll(`.perm-check[data-group="${group}"]`);
  const checked = [...all].filter(c => c.checked);
  const groupCb = [...document.querySelectorAll('.group-check')]
    .find(gc => gc.closest('.perm-group').querySelector('.card-title').textContent.trim() === group);
  if (groupCb) {
    groupCb.indeterminate = checked.length > 0 && checked.length < all.length;
    groupCb.checked       = checked.length === all.length;
  }
  updateGroupCount(group);
  syncSelectAll();
}

function updateGroupCount(group) {
  const all     = document.querySelectorAll(`.perm-check[data-group="${group}"]`);
  const checked = [...all].filter(c => c.checked).length;
  const groupEl = [...document.querySelectorAll('.perm-group')]
    .find(g => g.querySelector('.card-title').textContent.trim() === group);
  if (groupEl) groupEl.querySelector('.group-count').textContent = checked;
}

function toggleGroupPerms(groupCb) {
  const group = groupCb.closest('.perm-group').querySelector('.card-title').textContent.trim();
  document.querySelectorAll(`.perm-check[data-group="${group}"]`).forEach(cb => {
    cb.checked = groupCb.checked;
    const label = cb.closest('label');
    if (cb.checked) { label.style.borderColor='var(--ac)';label.style.background='var(--ac-lt)';label.style.color='var(--ac)'; }
    else { label.style.borderColor='var(--border)';label.style.background='transparent';label.style.color='var(--sub)'; }
  });
  groupCb.indeterminate = false;
  updateGroupCount(group);
  syncSelectAll();
}

function toggleSelectAll(cb) {
  document.querySelectorAll('.perm-check').forEach(p => {
    p.checked = cb.checked;
    const label = p.closest('label');
    if (p.checked) { label.style.borderColor='var(--ac)';label.style.background='var(--ac-lt)';label.style.color='var(--ac)'; }
    else { label.style.borderColor='var(--border)';label.style.background='transparent';label.style.color='var(--sub)'; }
  });
  document.querySelectorAll('.group-check').forEach(gc => {
    gc.checked = cb.checked; gc.indeterminate = false;
  });
  document.querySelectorAll('.perm-group').forEach(g => {
    const group   = g.querySelector('.card-title').textContent.trim();
    const checked = [...g.querySelectorAll('.perm-check')].filter(c=>c.checked).length;
    g.querySelector('.group-count').textContent = checked;
  });
}

function syncSelectAll() {
  const all     = document.querySelectorAll('.perm-check');
  const checked = [...all].filter(c => c.checked).length;
  const sa      = document.getElementById('select-all');
  sa.indeterminate = checked > 0 && checked < all.length;
  sa.checked       = checked === all.length;
}

function toggleGroup(header) {
  const group   = header.closest('.perm-group');
  const body    = group.querySelector('.group-body');
  const chevron = group.querySelector('.group-chevron');
  const isOpen  = body.style.display !== 'none';
  body.style.display    = isOpen ? 'none' : 'flex';
  chevron.style.transform = isOpen ? 'rotate(-90deg)' : 'rotate(0deg)';
}

// Init: sync group counts and select-all
document.querySelectorAll('.perm-group').forEach(g => {
  const group   = g.querySelector('.card-title').textContent.trim();
  const all     = g.querySelectorAll('.perm-check');
  const checked = [...all].filter(c => c.checked);
  const groupCb = g.querySelector('.group-check');
  groupCb.indeterminate = checked.length > 0 && checked.length < all.length;
  groupCb.checked       = checked.length === all.length && all.length > 0;
  g.querySelector('.group-count').textContent = checked.length;
});
syncSelectAll();
</script>
@endpush
