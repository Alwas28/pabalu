@php
  $authRole = Auth::user()->role;
  $isAdmin  = $authRole === 'admin';

  $roleBadge = [
    'admin'        => ['class' => 'badge-red',   'icon' => 'fa-shield-halved', 'label' => 'Administrator'],
    'owner'        => ['class' => 'badge-amber',  'icon' => 'fa-store',         'label' => 'Pemilik Toko'],
    'admin_outlet' => ['class' => 'badge-blue',   'icon' => 'fa-user-tie',      'label' => 'Admin Outlet'],
    'kasir'        => ['class' => 'badge-green',  'icon' => 'fa-cash-register', 'label' => 'Kasir'],
  ];
@endphp

<x-app-layout>
<x-slot name="pageTitle">Kelola User</x-slot>

{{-- ── Stats mini ── --}}
<div class="stat-grid animate-fadeUp" style="grid-template-columns:repeat(4,1fr)">
  @php
    $total     = $users->total();
    $verified  = \App\Models\User::whereNotNull('email_verified_at')->count();
    $unverified= \App\Models\User::whereNull('email_verified_at')->count();
    $active    = \App\Models\User::where('is_active', true)->count();
  @endphp
  <div class="stat-card">
    <div class="stat-icon" style="background:var(--ac-lt);color:var(--ac)">
      <i class="fa-solid fa-users"></i>
    </div>
    <div>
      <div class="stat-num">{{ \App\Models\User::count() }}</div>
      <div class="stat-label">Total User</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(16,185,129,.14);color:#34d399">
      <i class="fa-solid fa-circle-check"></i>
    </div>
    <div>
      <div class="stat-num">{{ $verified }}</div>
      <div class="stat-label">Email Terverifikasi</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(245,158,11,.14);color:#fbbf24">
      <i class="fa-solid fa-clock"></i>
    </div>
    <div>
      <div class="stat-num">{{ $unverified }}</div>
      <div class="stat-label">Belum Verifikasi</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(96,165,250,.14);color:#60a5fa">
      <i class="fa-solid fa-user-check"></i>
    </div>
    <div>
      <div class="stat-num">{{ $active }}</div>
      <div class="stat-label">User Aktif</div>
    </div>
  </div>
</div>

{{-- ── Tabel User ── --}}
<div class="card animate-fadeUp d1">
  <div class="card-header">
    <div class="card-title">
      <i class="fa-solid fa-users a-text" style="margin-right:8px"></i>Daftar User
      <span style="font-size:11.5px;color:var(--muted);font-weight:400;margin-left:8px">{{ $users->total() }} user</span>
    </div>
    <a href="{{ route('users.create') }}"
      class="a-grad" style="display:flex;align-items:center;gap:7px;padding:7px 14px;border-radius:9px;border:none;cursor:pointer;color:#fff;font-size:12.5px;font-weight:600;text-decoration:none">
      <i class="fa-solid fa-user-plus"></i><span>Tambah User</span>
    </a>
  </div>

  <div style="overflow-x:auto">
    <table class="tbl">
      <thead>
        <tr>
          <th>#</th>
          <th>Nama</th>
          <th>Email</th>
          <th>Role</th>
          <th>Status Email</th>
          <th>Status Akun</th>
          <th>Bergabung</th>
          <th style="text-align:right">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($users as $i => $user)
        <tr>
          <td style="font-size:12px;color:var(--muted)">
            {{ ($users->currentPage() - 1) * $users->perPage() + $i + 1 }}
          </td>

          {{-- Nama + initial avatar --}}
          <td class="td-main">
            <div style="display:flex;align-items:center;gap:10px">
              <div class="a-grad" style="width:30px;height:30px;border-radius:8px;display:grid;place-items:center;color:#fff;font-size:11px;font-weight:700;flex-shrink:0">
                {{ strtoupper(substr($user->name, 0, 1)) }}
              </div>
              <div>
                <div style="font-size:13px;font-weight:600;color:var(--text)">
                  {{ $user->name }}
                  @if($user->id === Auth::id())
                    <span style="font-size:10px;color:var(--muted);font-weight:400">(Anda)</span>
                  @endif
                </div>
              </div>
            </div>
          </td>

          <td style="font-size:12.5px">{{ $user->email }}</td>

          {{-- Role badge --}}
          <td>
            @if(isset($roleBadge[$user->role]))
            <span class="badge {{ $roleBadge[$user->role]['class'] }}">
              <i class="fa-solid {{ $roleBadge[$user->role]['icon'] }}" style="font-size:9px"></i>
              {{ $roleBadge[$user->role]['label'] }}
            </span>
            @else
            <span class="badge badge-gray">
              <i class="fa-solid fa-circle" style="font-size:9px"></i>
              Belum diatur
            </span>
            @endif
          </td>

          {{-- Status Email --}}
          <td>
            @if($user->hasVerifiedEmail())
              <span class="badge badge-green">
                <i class="fa-solid fa-circle-check" style="font-size:9px"></i>Terverifikasi
              </span>
            @else
              <span class="badge badge-amber">
                <i class="fa-solid fa-clock" style="font-size:9px"></i>Menunggu
              </span>
            @endif
          </td>

          {{-- Status Akun --}}
          <td>
            @if($user->is_active)
              <span class="badge badge-green">
                <i class="fa-solid fa-circle" style="font-size:7px"></i>Aktif
              </span>
            @else
              <span class="badge badge-red">
                <i class="fa-solid fa-circle" style="font-size:7px"></i>Nonaktif
              </span>
            @endif
          </td>

          <td style="font-size:12px">
            {{ $user->created_at->format('d M Y') }}
          </td>

          {{-- Aksi --}}
          <td>
            <div style="display:flex;align-items:center;justify-content:flex-end;gap:6px">

              {{-- Verifikasi Email (admin only, belum terverifikasi) --}}
              @if($isAdmin && !$user->hasVerifiedEmail())
              <form method="POST" action="{{ route('users.verify', $user) }}">
                @csrf
                <button type="submit"
                  title="Verifikasi Email"
                  style="padding:5px 10px;border-radius:8px;border:1px solid rgba(16,185,129,.35);background:rgba(16,185,129,.12);color:#34d399;font-size:11.5px;font-weight:600;cursor:pointer;font-family:inherit;display:flex;align-items:center;gap:5px"
                  onclick="return confirm('Verifikasi email {{ addslashes($user->name) }}?')">
                  <i class="fa-solid fa-circle-check"></i> Verifikasi
                </button>
              </form>
              @endif

              {{-- Toggle Aktif (admin only, bukan diri sendiri) --}}
              @if($isAdmin && $user->id !== Auth::id())
              <form method="POST" action="{{ route('users.toggle-active', $user) }}">
                @csrf
                <button type="submit"
                  title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                  style="padding:5px 10px;border-radius:8px;border:1px solid {{ $user->is_active ? 'rgba(239,68,68,.35)' : 'rgba(16,185,129,.35)' }};background:{{ $user->is_active ? 'rgba(239,68,68,.12)' : 'rgba(16,185,129,.12)' }};color:{{ $user->is_active ? '#f87171' : '#34d399' }};font-size:11.5px;font-weight:600;cursor:pointer;font-family:inherit"
                  onclick="return confirm('{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }} user {{ addslashes($user->name) }}?')">
                  <i class="fa-solid {{ $user->is_active ? 'fa-ban' : 'fa-circle-play' }}"></i>
                </button>
              </form>
              @endif

              {{-- Edit --}}
              @if($isAdmin || !in_array($user->role, ['admin','owner']))
              <a href="{{ route('users.edit', $user) }}"
                title="Edit"
                style="padding:5px 10px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:11.5px;font-weight:600;text-decoration:none;display:flex;align-items:center;gap:5px">
                <i class="fa-solid fa-pen-to-square"></i> Edit
              </a>
              @endif

              {{-- Hapus (bukan diri sendiri) --}}
              @if($user->id !== Auth::id() && ($isAdmin || !in_array($user->role, ['admin','owner'])))
              <form method="POST" action="{{ route('users.destroy', $user) }}">
                @csrf
                @method('DELETE')
                <button type="submit"
                  title="Hapus"
                  style="padding:5px 10px;border-radius:8px;border:1px solid rgba(239,68,68,.35);background:rgba(239,68,68,.10);color:#f87171;font-size:11.5px;font-weight:600;cursor:pointer;font-family:inherit"
                  onclick="return confirm('Hapus user {{ addslashes($user->name) }}? Aksi ini tidak dapat dibatalkan.')">
                  <i class="fa-solid fa-trash"></i>
                </button>
              </form>
              @endif

            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="8" style="text-align:center;padding:40px;color:var(--muted)">
            <i class="fa-solid fa-users-slash" style="font-size:28px;display:block;margin-bottom:10px;opacity:.4"></i>
            Belum ada user
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Pagination --}}
  @if($users->hasPages())
  <div style="padding:16px 20px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
    <span style="font-size:12.5px;color:var(--muted)">
      Menampilkan {{ $users->firstItem() }}–{{ $users->lastItem() }} dari {{ $users->total() }} user
    </span>
    <div style="display:flex;gap:6px">
      @if($users->onFirstPage())
        <span style="padding:5px 12px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);color:var(--muted);font-size:12px">
          <i class="fa-solid fa-chevron-left"></i>
        </span>
      @else
        <a href="{{ $users->previousPageUrl() }}"
          style="padding:5px 12px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:12px;text-decoration:none">
          <i class="fa-solid fa-chevron-left"></i>
        </a>
      @endif

      @if($users->hasMorePages())
        <a href="{{ $users->nextPageUrl() }}"
          style="padding:5px 12px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:12px;text-decoration:none">
          <i class="fa-solid fa-chevron-right"></i>
        </a>
      @else
        <span style="padding:5px 12px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);color:var(--muted);font-size:12px">
          <i class="fa-solid fa-chevron-right"></i>
        </span>
      @endif
    </div>
  </div>
  @endif
</div>

</x-app-layout>
