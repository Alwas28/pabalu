<x-app-layout title="Role & Permission">

  {{-- Header bar --}}
  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
    <div style="font-size:13px;color:var(--sub)">
      Kelola role dan permission untuk setiap pengguna di sistem.
    </div>
    @can('role.create')
    <a href="{{ route('rbac.roles.create') }}" class="btn btn-primary" style="text-decoration:none">
      <i class="fa-solid fa-plus"></i> Tambah Role
    </a>
    @endcan
  </div>

  {{-- Role cards --}}
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:16px">
    @forelse ($roles as $role)
    @php
      $isAdmin  = $role->name === 'admin';
      $colorMap = ['admin'=>'#f59e0b','owner'=>'#818cf8','admin_outlet'=>'#34d399','kasir'=>'#60a5fa'];
      $iconMap  = ['admin'=>'fa-shield-halved','owner'=>'fa-crown','admin_outlet'=>'fa-store','kasir'=>'fa-cash-register'];
      $color    = $colorMap[$role->name] ?? '#94a3b8';
      $icon     = $iconMap[$role->name] ?? 'fa-user-gear';
      $labelMap = ['admin'=>'Super Admin','owner'=>'Pemilik','admin_outlet'=>'Admin Outlet','kasir'=>'Kasir'];
      $label    = $labelMap[$role->name] ?? ucfirst(str_replace('_',' ',$role->name));
    @endphp

    <div class="card" style="transition:border-color .2s"
      onmouseover="this.style.borderColor='{{ $color }}55'"
      onmouseout="this.style.borderColor='var(--border)'">
      <div class="card-body">

        {{-- Role header --}}
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:16px">
          <div style="display:flex;align-items:center;gap:12px">
            <div style="width:44px;height:44px;border-radius:12px;display:grid;place-items:center;flex-shrink:0;background:{{ $color }}22;color:{{ $color }};font-size:18px">
              <i class="fa-solid {{ $icon }}"></i>
            </div>
            <div>
              <div style="font-family:'Clash Display',sans-serif;font-size:15px;font-weight:700;color:var(--text)">{{ $label }}</div>
              <code style="font-size:10.5px;color:var(--muted);font-family:monospace">{{ $role->name }}</code>
            </div>
          </div>
          @if ($isAdmin)
          <span class="badge badge-amber" style="flex-shrink:0">
            <i class="fa-solid fa-lock" style="font-size:8px"></i> Protected
          </span>
          @endif
        </div>

        {{-- Stats --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px">
          <div style="background:var(--surface2);border-radius:10px;padding:10px;text-align:center">
            <div style="font-family:'Clash Display',sans-serif;font-size:22px;font-weight:700;color:{{ $color }}">{{ $role->permissions_count }}</div>
            <div style="font-size:10.5px;color:var(--muted);margin-top:1px">Permission</div>
          </div>
          <div style="background:var(--surface2);border-radius:10px;padding:10px;text-align:center">
            <div style="font-family:'Clash Display',sans-serif;font-size:22px;font-weight:700;color:var(--text)">{{ $role->users_count }}</div>
            <div style="font-size:10.5px;color:var(--muted);margin-top:1px">User</div>
          </div>
        </div>

        {{-- Permission preview --}}
        <div style="display:flex;flex-wrap:wrap;gap:5px;min-height:26px;margin-bottom:16px">
          @foreach ($role->permissions->take(6) as $perm)
          <span style="background:{{ $color }}18;color:{{ $color }};font-size:10px;font-weight:600;padding:2px 8px;border-radius:6px">{{ $perm->name }}</span>
          @endforeach
          @if ($role->permissions_count > 6)
          <span style="background:var(--surface2);color:var(--muted);font-size:10px;font-weight:600;padding:2px 8px;border-radius:6px">+{{ $role->permissions_count - 6 }} lainnya</span>
          @endif
          @if ($role->permissions_count === 0)
          <span style="color:var(--muted);font-size:12px;font-style:italic">Tidak ada permission</span>
          @endif
        </div>

        {{-- Actions --}}
        <div style="display:flex;gap:8px">
          @can('role.update')
          <a href="{{ route('rbac.roles.edit', $role) }}"
            class="btn" style="flex:1;justify-content:center;text-decoration:none;font-size:12.5px;padding:8px 12px">
            <i class="fa-solid fa-pen-to-square"></i> Edit Permission
          </a>
          @endcan
          @can('role.delete')
          @if (!$isAdmin)
          <button type="button"
            onclick="askDelete({{ $role->id }}, '{{ addslashes($label) }}', {{ $role->users_count }})"
            class="btn btn-danger" style="padding:8px 14px;font-size:12.5px">
            <i class="fa-solid fa-trash"></i>
          </button>
          @endif
          @endcan
        </div>

      </div>
    </div>
    @empty
    <div class="card" style="grid-column:1/-1">
      <div class="card-body" style="text-align:center;padding:56px">
        <i class="fa-solid fa-shield-halved" style="font-size:40px;color:var(--muted);margin-bottom:14px;display:block"></i>
        <div style="font-size:15px;font-weight:600;color:var(--text);margin-bottom:6px">Belum ada role</div>
        <div style="font-size:13px;color:var(--sub)">Mulai dengan menambahkan role pertama.</div>
      </div>
    </div>
    @endforelse
  </div>

  {{-- ══ Delete Confirmation Dialog ══ --}}
  <div id="confirm-backdrop"
    style="display:none;position:fixed;inset:0;z-index:9000;background:rgba(0,0,0,.6);
           backdrop-filter:blur(4px);align-items:center;justify-content:center;
           padding:20px;opacity:0;transition:opacity .2s">

    <div id="confirm-box"
      style="background:var(--surface);border:1px solid var(--border);border-radius:20px;
             width:100%;max-width:400px;box-shadow:0 24px 64px rgba(0,0,0,.5);
             transform:scale(.94) translateY(12px);transition:transform .25s,opacity .25s;opacity:0">

      {{-- Icon --}}
      <div style="padding:28px 28px 0;text-align:center">
        <div style="width:60px;height:60px;border-radius:18px;background:rgba(239,68,68,.15);
                    display:grid;place-items:center;margin:0 auto 16px;font-size:24px;color:#f87171">
          <i class="fa-solid fa-trash-can"></i>
        </div>
        <div id="confirm-title"
          style="font-family:'Clash Display',sans-serif;font-size:18px;font-weight:700;color:var(--text);margin-bottom:8px">
          Hapus Role?
        </div>
        <div id="confirm-body" style="font-size:13px;color:var(--sub);line-height:1.6">
          Anda yakin ingin menghapus role ini?
        </div>
      </div>

      {{-- Warning jika role masih dipakai user -- hidden by default --}}
      <div id="confirm-warning"
        style="display:none;margin:16px 28px 0;padding:10px 14px;border-radius:10px;
               background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.25);
               font-size:12px;color:#fbbf24;gap:8px;align-items:flex-start">
        <i class="fa-solid fa-triangle-exclamation" style="flex-shrink:0;margin-top:1px"></i>
        <span id="confirm-warning-text"></span>
      </div>

      {{-- Buttons --}}
      <div style="padding:20px 28px 24px;display:flex;gap:10px;margin-top:16px">
        <button type="button" onclick="closeConfirm()"
          class="btn" style="flex:1;justify-content:center;font-size:13.5px;padding:10px">
          Batal
        </button>
        <form id="confirm-form" method="POST" style="flex:1">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger"
            style="width:100%;justify-content:center;font-size:13.5px;padding:10px">
            <i class="fa-solid fa-trash-can"></i> Ya, Hapus
          </button>
        </form>
      </div>

    </div>
  </div>

  {{-- ══ Scripts — HARUS di dalam </x-app-layout> ══ --}}
  @push('scripts')
  <script>
  function askDelete(id, label, userCount) {
    const backdrop = document.getElementById('confirm-backdrop');
    const box      = document.getElementById('confirm-box');
    const warning  = document.getElementById('confirm-warning');

    document.getElementById('confirm-title').textContent = 'Hapus Role "' + label + '"?';
    document.getElementById('confirm-body').textContent  =
      'Semua permission role ini akan dihapus. Tindakan ini tidak dapat dibatalkan.';
    document.getElementById('confirm-form').action = '/rbac/roles/' + id;

    if (userCount > 0) {
      document.getElementById('confirm-warning-text').textContent =
        userCount + ' user masih memiliki role ini dan akan kehilangan semua akses.';
      warning.style.display = 'flex';
    } else {
      warning.style.display = 'none';
    }

    // Tampilkan backdrop
    backdrop.style.display = 'flex';
    requestAnimationFrame(function() {
      requestAnimationFrame(function() {
        backdrop.style.opacity = '1';
        box.style.opacity      = '1';
        box.style.transform    = 'scale(1) translateY(0)';
      });
    });
  }

  function closeConfirm() {
    const backdrop = document.getElementById('confirm-backdrop');
    const box      = document.getElementById('confirm-box');
    backdrop.style.opacity = '0';
    box.style.opacity      = '0';
    box.style.transform    = 'scale(.94) translateY(12px)';
    setTimeout(function() { backdrop.style.display = 'none'; }, 220);
  }

  // Klik di luar box = tutup
  document.getElementById('confirm-backdrop').addEventListener('click', function(e) {
    if (e.target === this) closeConfirm();
  });

  // Tekan Escape = tutup
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeConfirm();
  });
  </script>
  @endpush

</x-app-layout>
