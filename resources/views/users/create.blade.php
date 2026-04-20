<x-app-layout title="Tambah User">

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
    <span style="color:var(--text)">Tambah User Baru</span>
  </div>

  <form method="POST" action="{{ route('users.store') }}">
    @csrf

    <div class="form-grid-user">

      {{-- Left column --}}
      <div style="display:flex;flex-direction:column;gap:20px">

        {{-- Informasi Akun --}}
        <div class="card animate-fadeUp">
          <div class="card-header">
            <div class="card-title"><i class="fa-solid fa-key" style="color:var(--ac);margin-right:8px"></i>Informasi Akun</div>
          </div>
          <div class="card-body">

            <div class="f-row">
              <div class="f-group">
                <label for="name" class="f-label">Nama Tampilan <span style="color:#f87171">*</span></label>
                <input id="name" name="name" type="text" class="f-input"
                  value="{{ old('name') }}" required placeholder="Nama lengkap atau username">
                @error('name')<div class="f-error"><i class="fa-solid fa-circle-exclamation" style="font-size:10px"></i>{{ $message }}</div>@enderror
              </div>
              <div class="f-group">
                <label for="email" class="f-label">Email <span style="color:#f87171">*</span></label>
                <input id="email" name="email" type="email" class="f-input"
                  value="{{ old('email') }}" required placeholder="nama@email.com">
                @error('email')<div class="f-error"><i class="fa-solid fa-circle-exclamation" style="font-size:10px"></i>{{ $message }}</div>@enderror
              </div>
            </div>

            <div class="f-row">
              <div class="f-group">
                <label for="password" class="f-label">Password <span style="color:#f87171">*</span></label>
                <div style="position:relative">
                  <input id="password" name="password" type="password" class="f-input"
                    required placeholder="Min. 8 karakter" autocomplete="new-password"
                    style="padding-right:40px">
                  <button type="button" onclick="togglePwd('password','eye1')"
                    style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--muted);font-size:13px;padding:0">
                    <i id="eye1" class="fa-solid fa-eye"></i>
                  </button>
                </div>
                @error('password')<div class="f-error"><i class="fa-solid fa-circle-exclamation" style="font-size:10px"></i>{{ $message }}</div>@enderror
              </div>
              <div class="f-group">
                <label for="password_confirmation" class="f-label">Konfirmasi Password <span style="color:#f87171">*</span></label>
                <div style="position:relative">
                  <input id="password_confirmation" name="password_confirmation" type="password" class="f-input"
                    required placeholder="Ulangi password" autocomplete="new-password"
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

        {{-- Profil Identitas --}}
        <div class="card animate-fadeUp d1">
          <div class="card-header">
            <div class="card-title"><i class="fa-solid fa-id-card" style="color:var(--ac);margin-right:8px"></i>Profil Identitas</div>
            <span style="font-size:11px;color:var(--muted)">Opsional</span>
          </div>
          <div class="card-body">

            <div class="f-row">
              <div class="f-group">
                <label for="nama_lengkap" class="f-label">Nama Lengkap</label>
                <input id="nama_lengkap" name="nama_lengkap" type="text" class="f-input"
                  value="{{ old('nama_lengkap') }}" placeholder="Nama sesuai KTP">
                @error('nama_lengkap')<div class="f-error"><i class="fa-solid fa-circle-exclamation" style="font-size:10px"></i>{{ $message }}</div>@enderror
              </div>
              <div class="f-group">
                <label for="no_hp" class="f-label">No. HP / WA</label>
                <input id="no_hp" name="no_hp" type="text" class="f-input"
                  value="{{ old('no_hp') }}" placeholder="08xx-xxxx-xxxx">
                @error('no_hp')<div class="f-error"><i class="fa-solid fa-circle-exclamation" style="font-size:10px"></i>{{ $message }}</div>@enderror
              </div>
            </div>

            <div class="f-row">
              <div class="f-group">
                <label for="jenis_kelamin" class="f-label">Jenis Kelamin</label>
                <select id="jenis_kelamin" name="jenis_kelamin" class="f-input">
                  <option value="">— Pilih —</option>
                  <option value="L" @selected(old('jenis_kelamin') === 'L')>Laki-laki</option>
                  <option value="P" @selected(old('jenis_kelamin') === 'P')>Perempuan</option>
                </select>
              </div>
              <div class="f-group">
                <label for="tanggal_lahir" class="f-label">Tanggal Lahir</label>
                <input id="tanggal_lahir" name="tanggal_lahir" type="date" class="f-input"
                  value="{{ old('tanggal_lahir') }}">
              </div>
            </div>

            <div class="f-group">
              <label for="alamat" class="f-label">Alamat</label>
              <textarea id="alamat" name="alamat" class="f-input" rows="2"
                placeholder="Alamat lengkap">{{ old('alamat') }}</textarea>
            </div>

          </div>
        </div>

      </div>

      {{-- Right column --}}
      <div class="col-aside-user" style="display:flex;flex-direction:column;gap:20px">

        {{-- Assign Role --}}
        @canany(['user.assign', 'role.read'])
        <div class="card animate-fadeUp d2">
          <div class="card-header">
            <div class="card-title"><i class="fa-solid fa-shield-halved" style="color:var(--ac);margin-right:8px"></i>Role Akses</div>
          </div>
          <div class="card-body">
            <p style="font-size:12.5px;color:var(--sub);margin-bottom:14px">Pilih role yang akan diberikan kepada user ini.</p>
            @php
              $roleColors2 = ['admin'=>'#f59e0b','owner'=>'#818cf8','admin_outlet'=>'#34d399','kasir'=>'#60a5fa'];
              $roleIcons   = ['admin'=>'fa-shield-halved','owner'=>'fa-crown','admin_outlet'=>'fa-store','kasir'=>'fa-cash-register'];
              $roleDescs   = ['admin'=>'Akses penuh ke semua fitur sistem','owner'=>'Kelola outlet, user, dan laporan','admin_outlet'=>'Operasional harian outlet','kasir'=>'Hanya transaksi POS'];
              $roleLabels2 = ['admin'=>'Super Admin','owner'=>'Pemilik','admin_outlet'=>'Admin Outlet','kasir'=>'Kasir'];
            @endphp

            <div style="display:flex;flex-direction:column;gap:8px">
              <label style="display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:12px;
                            background:var(--surface2);border:1px solid var(--border);cursor:pointer;transition:border-color .15s"
                id="role-none-wrap">
                <input type="radio" name="role" value="" id="role-none"
                  @checked(old('role') === null || old('role') === '')
                  style="accent-color:var(--ac)" onchange="highlightRole()">
                <div>
                  <div style="font-size:13px;font-weight:600;color:var(--text)">Tanpa Role</div>
                  <div style="font-size:11.5px;color:var(--muted)">User tidak memiliki akses khusus</div>
                </div>
              </label>

              @foreach ($roles as $r)
              @php $rc2 = $roleColors2[$r->name] ?? '#94a3b8'; @endphp
              <label style="display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:12px;
                            background:var(--surface2);border:1px solid var(--border);cursor:pointer;transition:border-color .15s"
                id="role-wrap-{{ $r->name }}">
                <input type="radio" name="role" value="{{ $r->name }}" id="role-{{ $r->name }}"
                  @checked(old('role') === $r->name)
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
              @endforeach
            </div>
            @error('role')<div class="f-error" style="margin-top:8px"><i class="fa-solid fa-circle-exclamation" style="font-size:10px"></i>{{ $message }}</div>@enderror
          </div>
        </div>
        @endcanany

        {{-- Jabatan --}}
        <div class="card animate-fadeUp d3">
          <div class="card-header">
            <div class="card-title"><i class="fa-solid fa-briefcase" style="color:var(--ac);margin-right:8px"></i>Jabatan</div>
          </div>
          <div class="card-body">
            <div class="f-group" style="margin-bottom:0">
              <label for="jabatan" class="f-label">Jabatan / Posisi</label>
              <input id="jabatan" name="jabatan" type="text" class="f-input"
                value="{{ old('jabatan') }}" placeholder="cth. Manajer, Staf Kasir…">
            </div>
          </div>
        </div>

        {{-- Outlet Terikat --}}
        <div class="card animate-fadeUp d4" id="outlet-card">
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
                <option value="{{ $ol->id }}" @selected(old('outlet_id') == $ol->id)>{{ $ol->nama }}</option>
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
            <i class="fa-solid fa-user-plus"></i> Simpan User
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

  // init on load
  highlightRole();
  </script>
  @endpush

</x-app-layout>
