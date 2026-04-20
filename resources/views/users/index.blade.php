<x-app-layout title="Kelola User">

  {{-- Header bar --}}
  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
    <div style="font-size:13px;color:var(--sub)">
      Kelola akun pengguna dan assign role untuk setiap karyawan.
    </div>
    @can('user.create')
    <a href="{{ route('users.create') }}" class="btn btn-primary" style="text-decoration:none">
      <i class="fa-solid fa-user-plus"></i> Tambah User
    </a>
    @endcan
  </div>

  {{-- Stats --}}
  <div class="stat-grid" style="grid-template-columns:repeat(3,1fr)">
    <div class="stat-card">
      <div class="stat-icon a-bg-lt" style="color:var(--ac)"><i class="fa-solid fa-users"></i></div>
      <div>
        <div class="stat-num">{{ $stats['total'] }}</div>
        <div class="stat-label">Total User</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(96,165,250,.12);color:#60a5fa"><i class="fa-solid fa-user-tag"></i></div>
      <div>
        <div class="stat-num" style="color:#60a5fa">{{ $stats['kasir'] }}</div>
        <div class="stat-label">Kasir</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon" style="background:rgba(129,140,248,.12);color:#818cf8"><i class="fa-solid fa-user-tie"></i></div>
      <div>
        <div class="stat-num" style="color:#818cf8">{{ $stats['owner'] }}</div>
        <div class="stat-label">Pemilik</div>
      </div>
    </div>
  </div>

  @php $roleLabels = ['admin'=>'Super Admin','owner'=>'Pemilik','admin_outlet'=>'Admin Outlet','kasir'=>'Kasir']; @endphp

  {{-- Filter & Search --}}
  <div class="card">
    <div class="card-body" style="padding:14px 20px">
      <form method="GET" action="{{ route('users.index') }}"
        style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
        <div style="position:relative;flex:1;min-width:200px">
          <i class="fa-solid fa-magnifying-glass"
            style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:12px;pointer-events:none"></i>
          <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama, email, jabatan…"
            class="f-input" style="padding-left:34px;padding-top:8px;padding-bottom:8px">
        </div>
        <select name="role" class="f-input" style="width:auto;padding-top:8px;padding-bottom:8px">
          <option value="">Semua Role</option>
          @foreach ($roles as $r)
          <option value="{{ $r->name }}" @selected(request('role') === $r->name)>
            {{ $roleLabels[$r->name] ?? ucfirst(str_replace('_',' ',$r->name)) }}
          </option>
          @endforeach
        </select>
        <button type="submit" class="btn btn-primary" style="padding:8px 16px">
          <i class="fa-solid fa-filter"></i> Filter
        </button>
        @if(request('q') || request('role'))
        <a href="{{ route('users.index') }}" class="btn" style="padding:8px 14px;text-decoration:none">
          <i class="fa-solid fa-xmark"></i>
        </a>
        @endif
      </form>
    </div>
  </div>

  {{-- Table --}}
  <div class="card">
    <div class="card-header">
      <div class="card-title"><i class="fa-solid fa-users-gear" style="color:var(--ac);margin-right:8px"></i>Daftar User</div>
      <div style="font-size:12px;color:var(--muted)">{{ $users->total() }} user ditemukan</div>
    </div>
    <div style="overflow-x:auto">
      <table class="tbl">
        <thead>
          <tr>
            <th style="width:40px">#</th>
            <th>User</th>
            <th>Role</th>
            <th>Jabatan</th>
            <th>No. HP</th>
            <th style="text-align:center">Email</th>
            <th>Bergabung</th>
            <th style="text-align:right">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($users as $user)
          @php
            $role      = $user->roles->first();
            $roleName  = $role?->name ?? null;
            $roleColor = $roleColors[$roleName] ?? '#94a3b8';
            $roleLabel = $roleLabels[$roleName] ?? ($roleName ? ucfirst(str_replace('_',' ',$roleName)) : null);
            $initial   = strtoupper(mb_substr($user->name, 0, 1));
            $isSelf    = auth()->id() === $user->id;
            $isAdmin   = $user->hasRole('admin');
          @endphp
          <tr>
            <td style="color:var(--muted);font-size:12px">{{ $users->firstItem() + $loop->index }}</td>
            <td class="td-main">
              <div style="display:flex;align-items:center;gap:10px">
                <div style="width:36px;height:36px;border-radius:10px;display:grid;place-items:center;
                            flex-shrink:0;font-weight:700;font-size:14px;color:#fff;
                            background:linear-gradient(135deg,{{ $roleColor }},{{ $roleColor }}88)">
                  {{ $initial }}
                </div>
                <div>
                  <div style="font-weight:600;color:var(--text);font-size:13.5px">
                    {{ $user->name }}
                    @if ($isSelf)
                    <span style="font-size:10px;background:var(--ac-lt);color:var(--ac);padding:1px 7px;border-radius:99px;font-weight:600;margin-left:4px">Anda</span>
                    @endif
                  </div>
                  <div style="font-size:12px;color:var(--muted)">{{ $user->email }}</div>
                </div>
              </div>
            </td>
            <td>
              @if ($roleLabel)
              <span class="badge" style="background:{{ $roleColor }}18;color:{{ $roleColor }}">
                {{ $roleLabel }}
              </span>
              @else
              <span style="font-size:12px;color:var(--muted);font-style:italic">—</span>
              @endif
            </td>
            <td style="font-size:13px">{{ $user->profile?->jabatan ?? '—' }}</td>
            <td style="font-size:13px">{{ $user->profile?->no_hp ?? '—' }}</td>
            <td style="text-align:center">
              @if ($user->hasVerifiedEmail())
                <span class="badge badge-green" style="font-size:11px">
                  <i class="fa-solid fa-circle-check"></i> Terverifikasi
                </span>
              @else
                <span class="badge badge-yellow" style="font-size:11px">
                  <i class="fa-solid fa-clock"></i> Belum
                </span>
              @endif
            </td>
            <td style="font-size:12px;color:var(--muted)">{{ $user->created_at?->format('d M Y') ?? '-' }}</td>
            <td>
              <div style="display:flex;gap:6px;justify-content:flex-end;flex-wrap:wrap">
                @if(auth()->user()->isAdmin() && $user->hasRole('owner'))
                <a href="{{ route('users.owner-detail', $user) }}"
                  class="btn" style="padding:6px 12px;font-size:12px;text-decoration:none;
                                     color:#818cf8;border-color:#818cf820;background:rgba(129,140,248,.08)">
                  <i class="fa-solid fa-chart-line"></i> Detail
                </a>
                @endif
                @can('user.update')
                @if (!$user->hasVerifiedEmail() && (auth()->user()->isAdmin() || auth()->user()->isOwner()))
                <form method="POST" action="{{ route('users.verify-email', $user) }}">
                  @csrf
                  <button type="button"
                    onclick="askVerify(this.closest('form'), '{{ addslashes($user->name) }}')"
                    class="btn" style="padding:6px 12px;font-size:12px;color:#34d399;border-color:#34d39930;background:rgba(52,211,153,.08)">
                    <i class="fa-solid fa-envelope-circle-check"></i> Verifikasi
                  </button>
                </form>
                @endif
                <a href="{{ route('users.edit', $user) }}"
                  class="btn" style="padding:6px 12px;font-size:12px;text-decoration:none">
                  <i class="fa-solid fa-pen-to-square"></i> Edit
                </a>
                @endcan
                @can('user.delete')
                @if (!$isSelf && !$isAdmin)
                <button type="button"
                  onclick="askDelete({{ $user->id }}, '{{ addslashes($user->name) }}')"
                  class="btn btn-danger" style="padding:6px 10px;font-size:12px">
                  <i class="fa-solid fa-trash"></i>
                </button>
                @endif
                @endcan
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="7" style="text-align:center;padding:48px;color:var(--muted)">
              <i class="fa-solid fa-users-slash" style="font-size:32px;display:block;margin-bottom:12px"></i>
              @if(request('q') || request('role'))
                Tidak ada user yang cocok dengan filter.
              @else
                Belum ada user terdaftar.
              @endif
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Pagination --}}
    @if ($users->hasPages())
    <div style="padding:14px 20px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">
      <div style="font-size:12px;color:var(--muted)">
        Menampilkan {{ $users->firstItem() }}–{{ $users->lastItem() }} dari {{ $users->total() }} user
      </div>
      <div style="display:flex;gap:4px">
        @if ($users->onFirstPage())
        <span class="btn" style="padding:6px 10px;font-size:12px;opacity:.4;cursor:default">
          <i class="fa-solid fa-chevron-left"></i>
        </span>
        @else
        <a href="{{ $users->previousPageUrl() }}" class="btn" style="padding:6px 10px;font-size:12px;text-decoration:none">
          <i class="fa-solid fa-chevron-left"></i>
        </a>
        @endif
        @foreach ($users->getUrlRange(max(1, $users->currentPage()-2), min($users->lastPage(), $users->currentPage()+2)) as $page => $url)
        <a href="{{ $url }}" class="btn {{ $page == $users->currentPage() ? 'btn-primary' : '' }}"
          style="padding:6px 12px;font-size:12px;text-decoration:none;min-width:34px;justify-content:center">
          {{ $page }}
        </a>
        @endforeach
        @if ($users->hasMorePages())
        <a href="{{ $users->nextPageUrl() }}" class="btn" style="padding:6px 10px;font-size:12px;text-decoration:none">
          <i class="fa-solid fa-chevron-right"></i>
        </a>
        @else
        <span class="btn" style="padding:6px 10px;font-size:12px;opacity:.4;cursor:default">
          <i class="fa-solid fa-chevron-right"></i>
        </span>
        @endif
      </div>
    </div>
    @endif
  </div>

  {{-- Verify Email Dialog (admin only) --}}
  <div id="verify-backdrop"
    style="display:none;position:fixed;inset:0;z-index:9000;background:rgba(0,0,0,.6);
           backdrop-filter:blur(4px);align-items:center;justify-content:center;
           padding:20px;opacity:0;transition:opacity .2s">
    <div id="verify-box"
      style="background:var(--surface);border:1px solid var(--border);border-radius:20px;
             width:100%;max-width:380px;box-shadow:0 24px 64px rgba(0,0,0,.5);
             transform:scale(.94) translateY(12px);transition:transform .25s,opacity .25s;opacity:0">
      <div style="padding:28px 28px 0;text-align:center">
        <div style="width:56px;height:56px;border-radius:16px;background:rgba(52,211,153,.15);
                    display:grid;place-items:center;margin:0 auto 14px;font-size:22px;color:#34d399">
          <i class="fa-solid fa-envelope-circle-check"></i>
        </div>
        <div style="font-family:'Clash Display',sans-serif;font-size:17px;font-weight:700;color:var(--text);margin-bottom:6px">
          Verifikasi Email?
        </div>
        <div style="font-size:13px;color:var(--sub);line-height:1.6">
          Akun <strong id="verify-name"></strong> akan ditandai sebagai terverifikasi dan bisa langsung login tanpa konfirmasi email.
        </div>
      </div>
      <div style="padding:20px 28px 24px;display:flex;gap:10px;margin-top:16px">
        <button type="button" onclick="closeVerify()"
          class="btn" style="flex:1;justify-content:center;font-size:13px;padding:10px">
          Batal
        </button>
        <button type="button" onclick="confirmVerify()"
          class="btn" style="flex:1;justify-content:center;font-size:13px;padding:10px;
                             background:rgba(52,211,153,.12);color:#34d399;border-color:#34d39940">
          <i class="fa-solid fa-check"></i> Ya, Verifikasi
        </button>
      </div>
    </div>
  </div>

  {{-- Delete Confirmation Dialog --}}
  <div id="confirm-backdrop"
    style="display:none;position:fixed;inset:0;z-index:9000;background:rgba(0,0,0,.6);
           backdrop-filter:blur(4px);align-items:center;justify-content:center;
           padding:20px;opacity:0;transition:opacity .2s">
    <div id="confirm-box"
      style="background:var(--surface);border:1px solid var(--border);border-radius:20px;
             width:100%;max-width:380px;box-shadow:0 24px 64px rgba(0,0,0,.5);
             transform:scale(.94) translateY(12px);transition:transform .25s,opacity .25s;opacity:0">
      <div style="padding:28px 28px 0;text-align:center">
        <div style="width:56px;height:56px;border-radius:16px;background:rgba(239,68,68,.15);
                    display:grid;place-items:center;margin:0 auto 14px;font-size:22px;color:#f87171">
          <i class="fa-solid fa-user-slash"></i>
        </div>
        <div id="confirm-title"
          style="font-family:'Clash Display',sans-serif;font-size:17px;font-weight:700;color:var(--text);margin-bottom:6px">
          Hapus User?
        </div>
        <div id="confirm-body" style="font-size:13px;color:var(--sub);line-height:1.6">
          Akun user ini dan semua datanya akan dihapus permanen.
        </div>
      </div>
      <div style="padding:20px 28px 24px;display:flex;gap:10px;margin-top:16px">
        <button type="button" onclick="closeConfirm()"
          class="btn" style="flex:1;justify-content:center;font-size:13px;padding:10px">
          Batal
        </button>
        <form id="confirm-form" method="POST" style="flex:1">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger"
            style="width:100%;justify-content:center;font-size:13px;padding:10px">
            <i class="fa-solid fa-trash"></i> Ya, Hapus
          </button>
        </form>
      </div>
    </div>
  </div>

  @push('styles')
  <style>
    .badge-yellow { background: rgba(245,158,11,.15); color: #f59e0b; }
  </style>
  @endpush

  @push('scripts')
  <script>
  var pendingVerifyForm = null;
  function askVerify(form, name) {
    pendingVerifyForm = form;
    var backdrop = document.getElementById('verify-backdrop');
    var box      = document.getElementById('verify-box');
    document.getElementById('verify-name').textContent = name;
    backdrop.style.display = 'flex';
    requestAnimationFrame(function() { requestAnimationFrame(function() {
      backdrop.style.opacity = '1';
      box.style.opacity      = '1';
      box.style.transform    = 'scale(1) translateY(0)';
    }); });
  }
  function closeVerify() {
    var backdrop = document.getElementById('verify-backdrop');
    var box      = document.getElementById('verify-box');
    backdrop.style.opacity = '0';
    box.style.opacity      = '0';
    box.style.transform    = 'scale(.94) translateY(12px)';
    setTimeout(function() { backdrop.style.display = 'none'; pendingVerifyForm = null; }, 220);
  }
  function confirmVerify() {
    if (pendingVerifyForm) pendingVerifyForm.submit();
  }

  function askDelete(id, name) {
    var backdrop = document.getElementById('confirm-backdrop');
    var box      = document.getElementById('confirm-box');
    document.getElementById('confirm-title').textContent = 'Hapus "' + name + '"?';
    document.getElementById('confirm-body').textContent  = 'Akun user ini dan semua datanya akan dihapus secara permanen.';
    document.getElementById('confirm-form').action = '/users/' + id;
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
    var backdrop = document.getElementById('confirm-backdrop');
    var box      = document.getElementById('confirm-box');
    backdrop.style.opacity = '0';
    box.style.opacity      = '0';
    box.style.transform    = 'scale(.94) translateY(12px)';
    setTimeout(function() { backdrop.style.display = 'none'; }, 220);
  }
  document.getElementById('verify-backdrop').addEventListener('click', function(e) {
    if (e.target === this) closeVerify();
  });
  document.getElementById('confirm-backdrop').addEventListener('click', function(e) {
    if (e.target === this) closeConfirm();
  });
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') { closeConfirm(); closeVerify(); }
  });
  </script>
  @endpush

</x-app-layout>
