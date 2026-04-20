<x-app-layout title="Edit User">

@push('styles')
<style>
.form-grid-user {
  display: grid;
  grid-template-columns: 1fr 340px;
  gap: 20px;
  align-items: start;
}
@media (max-width: 768px) {
  .form-grid-user {
    grid-template-columns: 1fr;
  }
}
</style>
@endpush

  {{-- Breadcrumb --}}
  <div style="display:flex;align-items:center;gap:8px;font-size:12.5px;color:var(--muted)">
    <a href="{{ route('users.index') }}" style="color:var(--muted);text-decoration:none;transition:color .15s"
      onmouseover="this.style.color='var(--ac)'" onmouseout="this.style.color='var(--muted)'">
      <i class="fa-solid fa-users-gear"></i> Kelola User
    </a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px"></i>
    <span style="color:var(--text)">Edit: {{ $user->name }}</span>
  </div>

  @php
    $isSelf    = auth()->id() === $user->id;
    $isAdmin   = $user->hasRole('admin');
    $userRole  = $user->roles->first()?->name;
    $roleColors2 = ['admin'=>'#f59e0b','owner'=>'#818cf8','admin_outlet'=>'#34d399','kasir'=>'#60a5fa'];
    $roleIcons   = ['admin'=>'fa-shield-halved','owner'=>'fa-crown','admin_outlet'=>'fa-store','kasir'=>'fa-cash-register'];
    $roleDescs   = ['admin'=>'Akses penuh ke semua fitur sistem','owner'=>'Kelola outlet, user, dan laporan','admin_outlet'=>'Operasional harian outlet','kasir'=>'Hanya transaksi POS'];
    $roleLabels2 = ['admin'=>'Super Admin','owner'=>'Pemilik','admin_outlet'=>'Admin Outlet','kasir'=>'Kasir'];
  @endphp

  <form method="POST" action="{{ route('users.update', $user) }}">
    @csrf
    @method('PUT')

    <div class="form-grid-user">

      {{-- Left column --}}
      <div style="display:flex;flex-direction:column;gap:20px">

        {{-- Info Akun --}}
        <div class="card animate-fadeUp">
          <div class="card-header">
            <div class="card-title"><i class="fa-solid fa-key" style="color:var(--ac);margin-right:8px"></i>Informasi Akun</div>
            @if ($isSelf)
            <span class="badge badge-amber"><i class="fa-solid fa-user" style="font-size:9px"></i> Akun Anda</span>
            @endif
          </div>
          <div class="card-body">

            <div class="f-row">
              <div class="f-group">
                <label for="name" class="f-label">Nama Tampilan <span style="color:#f87171">*</span></label>
                <input id="name" name="name" type="text" class="f-input"
                  value="{{ old('name', $user->name) }}" required placeholder="Nama lengkap atau username">
                @error('name')<div class="f-error"><i class="fa-solid fa-circle-exclamation" style="font-size:10px"></i>{{ $message }}</div>@enderror
              </div>
              <div class="f-group">
                <label for="email" class="f-label">Email <span style="color:#f87171">*</span></label>
                <input id="email" name="email" type="email" class="f-input"
                  value="{{ old('email', $user->email) }}" required placeholder="nama@email.com">
                @error('email')<div class="f-error"><i class="fa-solid fa-circle-exclamation" style="font-size:10px"></i>{{ $message }}</div>@enderror
              </div>
            </div>

            {{-- Password section --}}
            <div style="background:var(--surface2);border-radius:12px;padding:14px;margin-bottom:18px">
              <div style="font-size:12px;font-weight:600;color:var(--sub);margin-bottom:10px">
                <i class="fa-solid fa-lock" style="margin-right:5px"></i>Ubah Password
                <span style="font-weight:400;color:var(--muted);margin-left:6px">— Kosongkan jika tidak ingin mengubah</span>
              </div>
              <div class="f-row">
                <div class="f-group" style="margin-bottom:0">
                  <label for="password" class="f-label">Password Baru</label>
                  <div style="position:relative">
                    <input id="password" name="password" type="password" class="f-input"
                      placeholder="Min. 8 karakter" autocomplete="new-password"
                      style="padding-right:40px">
                    <button type="button" onclick="togglePwd('password','eye1')"
                      style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--muted);font-size:13px;padding:0">
                      <i id="eye1" class="fa-solid fa-eye"></i>
                    </button>
                  </div>
                  @error('password')<div class="f-error"><i class="fa-solid fa-circle-exclamation" style="font-size:10px"></i>{{ $message }}</div>@enderror
                </div>
                <div class="f-group" style="margin-bottom:0">
                  <label for="password_confirmation" class="f-label">Konfirmasi Password</label>
                  <div style="position:relative">
                    <input id="password_confirmation" name="password_confirmation" type="password" class="f-input"
                      placeholder="Ulangi password baru" autocomplete="new-password"
                      style="padding-right:40px">
                    <button type="button" onclick="togglePwd('password_confirmation','eye2')"
                      style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--muted);font-size:13px;padding:0">
                      <i id="eye2" class="fa-solid fa-eye"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>

        {{-- Profil Identitas --}}
        <div class="card animate-fadeUp d1">
          <div class="card-header">
            <div class="card-title"><i class="fa-solid fa-id-card" style="color:var(--ac);margin-right:8px"></i>Profil Identitas</div>
          </div>
          <div class="card-body">

            <div class="f-row">
              <div class="f-group">
                <label for="nama_lengkap" class="f-label">Nama Lengkap</label>
                <input id="nama_lengkap" name="nama_lengkap" type="text" class="f-input"
                  value="{{ old('nama_lengkap', $user->profile?->nama_lengkap) }}" placeholder="Nama sesuai KTP">
                @error('nama_lengkap')<div class="f-error"><i class="fa-solid fa-circle-exclamation" style="font-size:10px"></i>{{ $message }}</div>@enderror
              </div>
              <div class="f-group">
                <label for="no_hp" class="f-label">No. HP / WA</label>
                <input id="no_hp" name="no_hp" type="text" class="f-input"
                  value="{{ old('no_hp', $user->profile?->no_hp) }}" placeholder="08xx-xxxx-xxxx">
                @error('no_hp')<div class="f-error"><i class="fa-solid fa-circle-exclamation" style="font-size:10px"></i>{{ $message }}</div>@enderror
              </div>
            </div>

            <div class="f-row">
              <div class="f-group">
                <label for="jenis_kelamin" class="f-label">Jenis Kelamin</label>
                <select id="jenis_kelamin" name="jenis_kelamin" class="f-input">
                  <option value="">— Pilih —</option>
                  <option value="L" @selected(old('jenis_kelamin', $user->profile?->jenis_kelamin) === 'L')>Laki-laki</option>
                  <option value="P" @selected(old('jenis_kelamin', $user->profile?->jenis_kelamin) === 'P')>Perempuan</option>
                </select>
              </div>
              <div class="f-group">
                <label for="tanggal_lahir" class="f-label">Tanggal Lahir</label>
                <input id="tanggal_lahir" name="tanggal_lahir" type="date" class="f-input"
                  value="{{ old('tanggal_lahir', $user->profile?->tanggal_lahir?->format('Y-m-d')) }}">
              </div>
            </div>

            <div class="f-group" style="margin-bottom:0">
              <label for="alamat" class="f-label">Alamat</label>
              <textarea id="alamat" name="alamat" class="f-input" rows="2"
                placeholder="Alamat lengkap">{{ old('alamat', $user->profile?->alamat) }}</textarea>
            </div>

          </div>
        </div>

      </div>

      {{-- Right column --}}
      <div class="col-aside-user" style="display:flex;flex-direction:column;gap:20px">

        {{-- User Info Card --}}
        <div class="card animate-fadeUp" style="border-color:{{ $roleColors2[$userRole] ?? 'var(--border)' }}44">
          <div class="card-body" style="text-align:center;padding:24px">
            <div style="width:60px;height:60px;border-radius:16px;display:grid;place-items:center;margin:0 auto 12px;
                        font-weight:700;font-size:22px;color:#fff;
                        background:linear-gradient(135deg,{{ $roleColors2[$userRole] ?? '#94a3b8' }},{{ $roleColors2[$userRole] ?? '#94a3b8' }}88)">
              {{ strtoupper(mb_substr($user->name, 0, 1)) }}
            </div>
            <div style="font-family:'Clash Display',sans-serif;font-size:15px;font-weight:700;color:var(--text)">{{ $user->name }}</div>
            <div style="font-size:12px;color:var(--muted);margin-top:2px">{{ $user->email }}</div>
            @if ($userRole)
            <span class="badge" style="margin-top:10px;background:{{ $roleColors2[$userRole] ?? '#94a3b8' }}22;color:{{ $roleColors2[$userRole] ?? '#94a3b8' }}">
              {{ $roleLabels2[$userRole] ?? ucfirst(str_replace('_',' ',$userRole)) }}
            </span>
            @endif
            <div style="font-size:11px;color:var(--muted);margin-top:10px">
              Terdaftar: {{ $user->created_at->format('d M Y') }}
            </div>
          </div>
        </div>

        {{-- Assign Role --}}
        @if (!$isSelf)
        @can('user.assign')
        <div class="card animate-fadeUp d1">
          <div class="card-header">
            <div class="card-title"><i class="fa-solid fa-shield-halved" style="color:var(--ac);margin-right:8px"></i>Role Akses</div>
          </div>
          <div class="card-body">
            @if ($isAdmin)
            <div style="background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.25);border-radius:10px;
                        padding:10px 14px;font-size:12.5px;color:#fbbf24;display:flex;align-items:center;gap:8px">
              <i class="fa-solid fa-lock"></i>
              Role Admin tidak dapat diubah.
            </div>
            <input type="hidden" name="role" value="admin">
            @else
            <div style="display:flex;flex-direction:column;gap:8px">
              <label style="display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:12px;
                            background:var(--surface2);border:1px solid var(--border);cursor:pointer;transition:border-color .15s"
                id="role-none-wrap">
                <input type="radio" name="role" value="" id="role-none"
                  @checked(old('role', $userRole) === null || old('role', $userRole) === '')
                  style="accent-color:var(--ac)" onchange="highlightRole()">
                <div>
                  <div style="font-size:13px;font-weight:600;color:var(--text)">Tanpa Role</div>
                  <div style="font-size:11.5px;color:var(--muted)">User tidak memiliki akses khusus</div>
                </div>
              </label>

              @foreach ($roles as $r)
              @if ($r->name !== 'admin')
              @php $rc2 = $roleColors2[$r->name] ?? '#94a3b8'; @endphp
              <label style="display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:12px;
                            background:var(--surface2);border:1px solid var(--border);cursor:pointer;transition:border-color .15s"
                id="role-wrap-{{ $r->name }}">
                <input type="radio" name="role" value="{{ $r->name }}" id="role-{{ $r->name }}"
                  @checked(old('role', $userRole) === $r->name)
                  style="accent-color:{{ $rc2 }}" onchange="highlightRole()">
                <div style="width:32px;height:32px;border-radius:9px;display:grid;place-items:center;
                            flex-shrink:0;background:{{ $rc2 }}22;color:{{ $rc2 }};font-size:13px">
                  <i class="fa-solid {{ $roleIcons[$r->name] ?? 'fa-user-gear' }}"></i>
                </div>
                <div style="flex:1;min-width:0">
                  <div style="font-size:13px;font-weight:600;color:var(--text)">
                    {{ $roleLabels2[$r->name] ?? ucfirst(str_replace('_',' ',$r->name)) }}
                  </div>
                  <div style="font-size:11.5px;color:var(--muted)">
                    {{ $roleDescs[$r->name] ?? $r->name }}
                  </div>
                </div>
              </label>
              @endif
              @endforeach
            </div>
            @error('role')<div class="f-error" style="margin-top:8px"><i class="fa-solid fa-circle-exclamation" style="font-size:10px"></i>{{ $message }}</div>@enderror
            @endif
          </div>
        </div>
        @endcan
        @else
        {{-- Editing own account — no role change --}}
        @if ($userRole)
        <input type="hidden" name="role" value="{{ $userRole }}">
        @endif
        <div class="card animate-fadeUp d1">
          <div class="card-body" style="padding:14px">
            <div style="font-size:12px;color:var(--muted);display:flex;align-items:center;gap:8px">
              <i class="fa-solid fa-circle-info" style="color:var(--ac)"></i>
              Anda tidak dapat mengubah role akun Anda sendiri.
            </div>
          </div>
        </div>
        @endif

        {{-- Jabatan --}}
        <div class="card animate-fadeUp d2">
          <div class="card-header">
            <div class="card-title"><i class="fa-solid fa-briefcase" style="color:var(--ac);margin-right:8px"></i>Jabatan</div>
          </div>
          <div class="card-body">
            <div class="f-group" style="margin-bottom:0">
              <label for="jabatan" class="f-label">Jabatan / Posisi</label>
              <input id="jabatan" name="jabatan" type="text" class="f-input"
                value="{{ old('jabatan', $user->profile?->jabatan) }}" placeholder="cth. Manajer, Staf Kasir…">
            </div>
          </div>
        </div>

        {{-- Outlet Terikat --}}
        <div class="card animate-fadeUp d3">
          <div class="card-header">
            <div class="card-title"><i class="fa-solid fa-store" style="color:var(--ac);margin-right:8px"></i>Outlet Terikat</div>
            <span style="font-size:11px;color:var(--muted)">Wajib untuk Kasir</span>
          </div>
          <div class="card-body">
            <div class="f-group" style="margin-bottom:0">
              <label for="outlet_id" class="f-label">Outlet</label>
              <select id="outlet_id" name="outlet_id" class="f-input">
                <option value="">— Tidak Terikat —</option>
                @foreach ($outlets as $ol)
                <option value="{{ $ol->id }}" @selected(old('outlet_id', $user->outlet_id) == $ol->id)>{{ $ol->nama }}</option>
                @endforeach
              </select>
              <div style="font-size:11.5px;color:var(--muted);margin-top:6px">
                <i class="fa-solid fa-circle-info" style="font-size:10px"></i>
                Kasir hanya bisa mengakses outlet yang ditentukan di sini.
              </div>
              @error('outlet_id')<div class="f-error"><i class="fa-solid fa-circle-exclamation" style="font-size:10px"></i>{{ $message }}</div>@enderror
            </div>
          </div>
        </div>

        {{-- Actions --}}
        <div style="display:flex;flex-direction:column;gap:8px">
          <button type="submit" class="btn btn-primary" style="justify-content:center;padding:11px">
            <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
          </button>
          <a href="{{ route('users.index') }}" class="btn" style="justify-content:center;padding:11px;text-decoration:none">
            <i class="fa-solid fa-arrow-left"></i> Kembali
          </a>
        </div>

      </div>
    </div>
  </form>

  @push('scripts')
  <script>
  function togglePwd(id, eyeId) {
    var el  = document.getElementById(id);
    var eye = document.getElementById(eyeId);
    if (el.type === 'password') {
      el.type = 'text';
      eye.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
      el.type = 'password';
      eye.classList.replace('fa-eye-slash', 'fa-eye');
    }
  }

  function highlightRole() {
    var radios = document.querySelectorAll('input[name="role"]');
    radios.forEach(function(r) {
      var wrap = r.closest('label');
      if (!wrap) return;
      wrap.style.borderColor = r.checked ? 'var(--ac)' : 'var(--border)';
    });
  }

  highlightRole();
  </script>
  @endpush

</x-app-layout>
