<x-app-layout>
<x-slot name="pageTitle">Role &amp; Permission</x-slot>

{{-- ── Stats mini ── --}}
<div class="stat-grid animate-fadeUp" style="grid-template-columns:repeat(4,1fr)">
  @php
    $totalRoles    = $roles->count();
    $systemRoles   = $roles->where('is_system', true)->count();
    $customRoles   = $roles->where('is_system', false)->count();
    $totalPerms    = \App\Models\Permission::count();
  @endphp
  <div class="stat-card">
    <div class="stat-icon" style="background:var(--ac-lt);color:var(--ac)">
      <i class="fa-solid fa-shield-halved"></i>
    </div>
    <div>
      <div class="stat-num">{{ $totalRoles }}</div>
      <div class="stat-label">Total Role</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(96,165,250,.14);color:#60a5fa">
      <i class="fa-solid fa-lock"></i>
    </div>
    <div>
      <div class="stat-num">{{ $systemRoles }}</div>
      <div class="stat-label">Role Sistem</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(167,139,250,.14);color:#a78bfa">
      <i class="fa-solid fa-wand-magic-sparkles"></i>
    </div>
    <div>
      <div class="stat-num">{{ $customRoles }}</div>
      <div class="stat-label">Role Kustom</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(52,211,153,.14);color:#34d399">
      <i class="fa-solid fa-key"></i>
    </div>
    <div>
      <div class="stat-num">{{ $totalPerms }}</div>
      <div class="stat-label">Total Permission</div>
    </div>
  </div>
</div>

{{-- ── Tabel Role ── --}}
<div class="card animate-fadeUp d1">
  <div class="card-header">
    <div class="card-title">
      <i class="fa-solid fa-shield-halved a-text" style="margin-right:8px"></i>Daftar Role
      <span style="font-size:11.5px;color:var(--muted);font-weight:400;margin-left:8px">{{ $totalRoles }} role</span>
    </div>
    <a href="{{ route('roles.create') }}"
      class="a-grad" style="display:flex;align-items:center;gap:7px;padding:7px 14px;border-radius:9px;border:none;cursor:pointer;color:#fff;font-size:12.5px;font-weight:600;text-decoration:none">
      <i class="fa-solid fa-plus"></i><span>Tambah Role</span>
    </a>
  </div>

  <div style="overflow-x:auto">
    <table class="tbl">
      <thead>
        <tr>
          <th>#</th>
          <th>Role</th>
          <th>Slug</th>
          <th>Permission</th>
          <th>Tipe</th>
          <th style="text-align:right">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($roles as $i => $role)
        <tr>
          <td style="font-size:12px;color:var(--muted)">{{ $i + 1 }}</td>

          <td class="td-main">
            <div style="display:flex;align-items:center;gap:10px">
              <div style="width:32px;height:32px;border-radius:9px;{{ $role->is_system ? 'background:var(--ac-lt)' : 'background:rgba(167,139,250,.16)' }};display:grid;place-items:center;flex-shrink:0;font-size:13px;{{ $role->is_system ? 'color:var(--ac)' : 'color:#a78bfa' }}">
                <i class="fa-solid {{ $role->is_system ? 'fa-lock' : 'fa-wand-magic-sparkles' }}"></i>
              </div>
              <div>
                <div style="font-size:13px;font-weight:600;color:var(--text)">{{ $role->name }}</div>
                @if($role->description)
                  <div style="font-size:11.5px;color:var(--muted);margin-top:1px">{{ $role->description }}</div>
                @endif
              </div>
            </div>
          </td>

          <td>
            <code style="font-size:11.5px;padding:2px 8px;border-radius:6px;background:var(--surface2);color:var(--sub);font-family:monospace">{{ $role->slug }}</code>
          </td>

          {{-- Permission count --}}
          <td>
            @if($role->slug === 'admin')
              <span class="badge badge-green">
                <i class="fa-solid fa-infinity" style="font-size:9px"></i>Semua Akses
              </span>
            @else
              <div style="display:flex;align-items:center;gap:6px">
                <div style="height:5px;border-radius:99px;background:var(--border);flex:1;max-width:80px;overflow:hidden">
                  <div style="height:100%;border-radius:99px;background:var(--ac);width:{{ $totalPerms > 0 ? round($role->permissions_count / $totalPerms * 100) : 0 }}%"></div>
                </div>
                <span style="font-size:12px;color:var(--sub);white-space:nowrap">{{ $role->permissions_count }}<span style="color:var(--muted)"> / {{ $totalPerms }}</span></span>
              </div>
            @endif
          </td>

          <td>
            @if($role->is_system)
              <span class="badge badge-blue">
                <i class="fa-solid fa-lock" style="font-size:9px"></i>Sistem
              </span>
            @else
              <span class="badge" style="background:rgba(167,139,250,.14);color:#a78bfa;border-color:rgba(167,139,250,.3)">
                <i class="fa-solid fa-wand-magic-sparkles" style="font-size:9px"></i>Kustom
              </span>
            @endif
          </td>

          <td>
            <div style="display:flex;align-items:center;justify-content:flex-end;gap:6px">
              <a href="{{ route('roles.edit', $role) }}"
                title="Edit"
                style="padding:5px 10px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:11.5px;font-weight:600;text-decoration:none;display:flex;align-items:center;gap:5px">
                <i class="fa-solid fa-pen-to-square"></i> Edit
              </a>

              @if(!$role->is_system)
              <form method="POST" action="{{ route('roles.destroy', $role) }}">
                @csrf
                @method('DELETE')
                <button type="submit"
                  title="Hapus"
                  style="padding:5px 10px;border-radius:8px;border:1px solid rgba(239,68,68,.35);background:rgba(239,68,68,.10);color:#f87171;font-size:11.5px;font-weight:600;cursor:pointer;font-family:inherit"
                  onclick="return confirm('Hapus role \'{{ addslashes($role->name) }}\'? Aksi ini tidak dapat dibatalkan.')">
                  <i class="fa-solid fa-trash"></i>
                </button>
              </form>
              @else
              <span title="Role sistem tidak dapat dihapus"
                style="padding:5px 10px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);color:var(--muted);font-size:11.5px;opacity:.5;cursor:not-allowed">
                <i class="fa-solid fa-trash"></i>
              </span>
              @endif
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="6" style="text-align:center;padding:40px;color:var(--muted)">
            <i class="fa-solid fa-shield-halved" style="font-size:28px;display:block;margin-bottom:10px;opacity:.4"></i>
            Belum ada role
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- ── Info Box ── --}}
<div class="card animate-fadeUp d2" style="border-color:var(--ac-lt)">
  <div class="card-body" style="display:flex;gap:14px;align-items:flex-start">
    <div style="width:34px;height:34px;flex-shrink:0;border-radius:10px;background:var(--ac-lt);display:grid;place-items:center;color:var(--ac);font-size:14px">
      <i class="fa-solid fa-circle-info"></i>
    </div>
    <div style="font-size:13px;color:var(--sub);line-height:1.7">
      <strong style="color:var(--text);font-size:13.5px">Tentang Role &amp; Permission</strong><br>
      Role <strong style="color:var(--text)">Sistem</strong> (Administrator, Pemilik Toko, Admin Outlet, Kasir) tidak dapat dihapus dan namanya tidak dapat diubah.
      Permission dapat dikonfigurasi untuk semua role kecuali <strong style="color:var(--text)">Administrator</strong> yang selalu memiliki semua akses.
      Buat <strong style="color:var(--text)">Role Kustom</strong> untuk kebutuhan khusus bisnis Anda.
    </div>
  </div>
</div>

</x-app-layout>
