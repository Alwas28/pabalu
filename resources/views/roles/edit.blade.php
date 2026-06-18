<x-app-layout>
<x-slot name="pageTitle">Edit Role</x-slot>
<x-slot name="headerAction">
  <a href="{{ route('roles.index') }}"
    style="display:flex;align-items:center;gap:7px;padding:8px 16px;border-radius:11px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13px;font-weight:600;text-decoration:none">
    <i class="fa-solid fa-arrow-left"></i><span>Kembali</span>
  </a>
</x-slot>

<form method="POST" action="{{ route('roles.update', $role) }}" class="animate-fadeUp">
  @csrf
  @method('PUT')

  @if($role->slug === 'admin')
  <div style="background:rgba(245,158,11,.10);border:1px solid rgba(245,158,11,.3);border-radius:14px;padding:14px 18px;margin-bottom:20px;display:flex;gap:12px;align-items:center">
    <i class="fa-solid fa-triangle-exclamation" style="color:#fbbf24;flex-shrink:0;font-size:16px"></i>
    <div style="font-size:13px;color:var(--sub)">
      Role <strong style="color:var(--text)">Administrator</strong> selalu memiliki <strong style="color:var(--text)">semua akses</strong>. Permission tidak dapat dikurangi untuk menjaga keamanan sistem.
    </div>
  </div>
  @elseif($role->is_system)
  <div style="background:rgba(96,165,250,.10);border:1px solid rgba(96,165,250,.3);border-radius:14px;padding:14px 18px;margin-bottom:20px;display:flex;gap:12px;align-items:center">
    <i class="fa-solid fa-circle-info" style="color:#60a5fa;flex-shrink:0;font-size:16px"></i>
    <div style="font-size:13px;color:var(--sub)">
      Ini adalah <strong style="color:var(--text)">role sistem</strong>. Nama tidak dapat diubah, tetapi permission dapat dikonfigurasi.
    </div>
  </div>
  @endif

  <div style="display:grid;grid-template-columns:1fr 2fr;gap:20px;align-items:start">

    {{-- ── Kolom kiri: Info Role ── --}}
    <div style="display:flex;flex-direction:column;gap:16px">

      <div class="card">
        <div class="card-header">
          <div style="display:flex;align-items:center;gap:10px">
            <div style="width:34px;height:34px;border-radius:10px;{{ $role->is_system ? 'background:var(--ac-lt);color:var(--ac)' : 'background:rgba(167,139,250,.16);color:#a78bfa' }};display:grid;place-items:center;font-size:13px;flex-shrink:0">
              <i class="fa-solid {{ $role->is_system ? 'fa-lock' : 'fa-wand-magic-sparkles' }}"></i>
            </div>
            <div>
              <div class="card-title">{{ $role->name }}</div>
              <div style="font-size:11px;color:var(--muted);margin-top:1px">
                <code style="font-size:10.5px;padding:1px 6px;border-radius:4px;background:var(--surface2)">{{ $role->slug }}</code>
              </div>
            </div>
          </div>
        </div>
        <div class="card-body" style="display:flex;flex-direction:column;gap:16px">

          @if(!$role->is_system)
          <div>
            <label class="f-label" for="name">Nama Role <span style="color:#f87171">*</span></label>
            <input id="name" name="name" type="text" class="f-input"
              value="{{ old('name', $role->name) }}"
              placeholder="Nama role...">
            @error('name')
              <p style="font-size:12px;color:#f87171;margin-top:5px"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
            @enderror
          </div>
          @else
          <input type="hidden" name="name" value="{{ $role->name }}">
          @endif

          <div>
            <label class="f-label" for="description">Deskripsi</label>
            <textarea id="description" name="description" class="f-input"
              rows="3"
              placeholder="Jelaskan tanggung jawab role ini..."
              style="resize:vertical">{{ old('description', $role->description) }}</textarea>
            @error('description')
              <p style="font-size:12px;color:#f87171;margin-top:5px"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
            @enderror
          </div>

        </div>

        <div style="padding:14px 20px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:space-between;align-items:center">
          @if(!$role->is_system)
          <form method="POST" action="{{ route('roles.destroy', $role) }}">
            @csrf
            @method('DELETE')
            <button type="submit"
              style="padding:9px 16px;border-radius:11px;border:1px solid rgba(239,68,68,.4);background:rgba(239,68,68,.10);color:#f87171;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;display:flex;align-items:center;gap:7px"
              onclick="return confirm('Hapus role \'{{ addslashes($role->name) }}\'?')">
              <i class="fa-solid fa-trash"></i> Hapus
            </button>
          </form>
          @else
          <div></div>
          @endif

          <div style="display:flex;gap:10px">
            <a href="{{ route('roles.index') }}"
              style="padding:9px 18px;border-radius:11px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13px;font-weight:600;text-decoration:none">
              Batal
            </a>
            <button type="submit"
              class="a-grad" style="padding:9px 22px;border-radius:11px;border:none;color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;display:flex;align-items:center;gap:7px">
              <i class="fa-solid fa-floppy-disk"></i> Simpan
            </button>
          </div>
        </div>
      </div>

      {{-- Ringkasan terpilih --}}
      <div class="card" style="border-color:var(--ac-lt)">
        <div class="card-body" style="padding:14px 18px">
          <div style="font-size:12px;color:var(--muted);margin-bottom:8px">Permission terpilih</div>
          @if($role->slug === 'admin')
            <div style="font-size:28px;font-weight:700;color:#34d399">∞</div>
            <div style="font-size:12px;color:var(--sub)">Semua akses</div>
          @else
            <div style="font-size:28px;font-weight:700;color:var(--ac)" id="perm-count">{{ count($rolePermIds) }}</div>
            <div style="font-size:12px;color:var(--sub)">dari {{ \App\Models\Permission::count() }} permission</div>
          @endif
        </div>
      </div>

    </div>

    {{-- ── Kolom kanan: Permission Groups ── --}}
    <div class="card">
      <div class="card-header" style="display:flex;align-items:center;justify-content:space-between">
        <div class="card-title">
          <i class="fa-solid fa-key a-text" style="margin-right:8px"></i>Konfigurasi Permission
        </div>
        @if($role->slug !== 'admin')
        <div style="display:flex;gap:8px">
          <button type="button" onclick="toggleAll(true)"
            style="padding:5px 12px;border-radius:8px;border:1px solid rgba(52,211,153,.4);background:rgba(52,211,153,.12);color:#34d399;font-size:11.5px;font-weight:600;cursor:pointer;font-family:inherit">
            <i class="fa-solid fa-check-double"></i> Pilih Semua
          </button>
          <button type="button" onclick="toggleAll(false)"
            style="padding:5px 12px;border-radius:8px;border:1px solid rgba(239,68,68,.35);background:rgba(239,68,68,.10);color:#f87171;font-size:11.5px;font-weight:600;cursor:pointer;font-family:inherit">
            <i class="fa-solid fa-xmark"></i> Reset
          </button>
        </div>
        @endif
      </div>

      <div class="card-body" style="display:flex;flex-direction:column;gap:12px">
        @foreach($permissions as $group => $items)
        <div class="perm-group" style="border:1px solid var(--border);border-radius:12px;overflow:hidden">

          {{-- Group header --}}
          <div style="padding:10px 14px;background:var(--surface2);display:flex;align-items:center;justify-content:space-between;cursor:pointer" onclick="toggleGroup(this)">
            <label style="display:flex;align-items:center;gap:9px;cursor:pointer;font-size:13px;font-weight:600;color:var(--text)" onclick="event.stopPropagation()">
              @if($role->slug !== 'admin')
              <input type="checkbox" class="group-check" data-group="{{ $loop->index }}"
                onchange="onGroupCheck(this)"
                style="width:15px;height:15px;accent-color:var(--ac);cursor:pointer">
              @else
              <input type="checkbox" checked disabled
                style="width:15px;height:15px;accent-color:var(--ac);cursor:not-allowed;opacity:.7">
              @endif
              {{ $group }}
            </label>
            <div style="display:flex;align-items:center;gap:8px">
              @if($role->slug !== 'admin')
              <span class="group-badge" data-group="{{ $loop->index }}"
                style="padding:2px 9px;border-radius:99px;background:var(--ac-lt);color:var(--ac);font-size:11px;font-weight:600">
                0/{{ $items->count() }}
              </span>
              @else
              <span style="padding:2px 9px;border-radius:99px;background:rgba(52,211,153,.14);color:#34d399;font-size:11px;font-weight:600">
                {{ $items->count() }}/{{ $items->count() }}
              </span>
              @endif
              <i class="fa-solid fa-chevron-down" style="font-size:11px;color:var(--muted);transition:transform .2s"></i>
            </div>
          </div>

          {{-- Permission checkboxes --}}
          <div class="perm-list" data-group="{{ $loop->index }}"
            style="display:grid;grid-template-columns:repeat(2,1fr);gap:0;padding:4px 0">
            @foreach($items as $perm)
            <label style="display:flex;align-items:center;gap:9px;padding:8px 14px;cursor:pointer;transition:background .15s"
              onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
              <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                class="perm-check" data-group="{{ $loop->parent->index }}"
                {{ in_array($perm->id, old('permissions', $rolePermIds)) ? 'checked' : '' }}
                {{ $role->slug === 'admin' ? 'checked disabled' : '' }}
                onchange="onPermCheck(this)"
                style="width:14px;height:14px;accent-color:var(--ac);cursor:{{ $role->slug === 'admin' ? 'not-allowed' : 'pointer' }};flex-shrink:0;opacity:{{ $role->slug === 'admin' ? '.7' : '1' }}">
              <span style="font-size:12.5px;color:var(--sub)">{{ $perm->name }}</span>
            </label>
            @endforeach
          </div>

        </div>
        @endforeach
      </div>
    </div>

  </div>
</form>

@push('scripts')
<script>
function countPerms() {
  const total = document.querySelectorAll('.perm-check:not([disabled]):checked').length;
  const el = document.getElementById('perm-count');
  if (el) el.textContent = total;
}

function updateGroupBadge(groupIdx) {
  const checks   = document.querySelectorAll(`.perm-check[data-group="${groupIdx}"]:not([disabled])`);
  const checked  = document.querySelectorAll(`.perm-check[data-group="${groupIdx}"]:not([disabled]):checked`);
  const badge    = document.querySelector(`.group-badge[data-group="${groupIdx}"]`);
  const groupChk = document.querySelector(`.group-check[data-group="${groupIdx}"]`);

  if (badge)    badge.textContent = `${checked.length}/${checks.length}`;
  if (groupChk) groupChk.checked  = checked.length === checks.length && checks.length > 0;
  if (groupChk) groupChk.indeterminate = checked.length > 0 && checked.length < checks.length;
}

function onPermCheck(chk) {
  updateGroupBadge(chk.dataset.group);
  countPerms();
}

function onGroupCheck(chk) {
  const perms = document.querySelectorAll(`.perm-check[data-group="${chk.dataset.group}"]:not([disabled])`);
  perms.forEach(p => p.checked = chk.checked);
  updateGroupBadge(chk.dataset.group);
  countPerms();
}

function toggleAll(state) {
  document.querySelectorAll('.perm-check:not([disabled])').forEach(p => p.checked = state);
  const groupCount = document.querySelectorAll('.group-check').length;
  for (let i = 0; i < groupCount; i++) {
    const g = document.querySelector(`.group-check[data-group="${i}"]`);
    if (g) { g.checked = state; g.indeterminate = false; }
    updateGroupBadge(i);
  }
  countPerms();
}

function toggleGroup(header) {
  if (event.target.type === 'checkbox' || event.target.tagName === 'LABEL') return;
  const list    = header.nextElementSibling;
  const chevron = header.querySelector('.fa-chevron-down');
  const isOpen  = list.style.display !== 'none';
  list.style.display  = isOpen ? 'none' : 'grid';
  chevron.style.transform = isOpen ? 'rotate(-90deg)' : '';
}

document.addEventListener('DOMContentLoaded', () => {
  const groups = document.querySelectorAll('.group-check');
  groups.forEach((_, i) => updateGroupBadge(i));
  countPerms();
});
</script>
@endpush

</x-app-layout>
