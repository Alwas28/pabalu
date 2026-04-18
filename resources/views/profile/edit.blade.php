<x-app-layout title="Akun Saya">

  <div style="max-width:720px">

    {{-- Flash via toast (handled by layout) --}}

    {{-- ── Informasi Profil ── --}}
    <div class="settings-section animate-fadeUp">
      <div class="settings-title">Informasi Profil</div>
      <div class="settings-desc">Perbarui nama dan alamat email akun Anda.</div>

      <form method="POST" action="{{ route('profile.update') }}">
        @csrf
        @method('patch')

        <div class="f-row">
          <div class="f-group">
            <label for="name" class="f-label">Nama</label>
            <input id="name" name="name" type="text" class="f-input"
              value="{{ old('name', $user->name) }}" required autocomplete="name" placeholder="Nama lengkap">
            @error('name')
            <div class="f-error"><i class="fa-solid fa-circle-exclamation" style="font-size:10px"></i>{{ $message }}</div>
            @enderror
          </div>
          <div class="f-group">
            <label for="email" class="f-label">Email</label>
            <input id="email" name="email" type="email" class="f-input"
              value="{{ old('email', $user->email) }}" required autocomplete="username" placeholder="nama@email.com">
            @error('email')
            <div class="f-error"><i class="fa-solid fa-circle-exclamation" style="font-size:10px"></i>{{ $message }}</div>
            @enderror
          </div>
        </div>

        <div style="display:flex;align-items:center;gap:12px;margin-top:8px">
          <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
          </button>
        </div>
      </form>
    </div>

    {{-- ── Ubah Password ── --}}
    <div class="settings-section animate-fadeUp d1">
      <div class="settings-title">Ubah Password</div>
      <div class="settings-desc">Gunakan password yang kuat dan unik untuk keamanan akun.</div>

      <form method="POST" action="{{ route('password.update') }}">
        @csrf
        @method('put')

        <div class="f-group">
          <label for="current_password" class="f-label">Password Saat Ini</label>
          <div style="position:relative">
            <i class="fa-solid fa-lock" style="position:absolute;left:13px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:13px;pointer-events:none"></i>
            <input id="current_password" name="current_password" type="password" class="f-input"
              style="padding-left:38px" autocomplete="current-password" placeholder="••••••••">
          </div>
          @error('current_password', 'updatePassword')
          <div class="f-error"><i class="fa-solid fa-circle-exclamation" style="font-size:10px"></i>{{ $message }}</div>
          @enderror
        </div>

        <div class="f-row">
          <div class="f-group">
            <label for="password" class="f-label">Password Baru</label>
            <input id="password" name="password" type="password" class="f-input"
              autocomplete="new-password" placeholder="••••••••">
            @error('password', 'updatePassword')
            <div class="f-error"><i class="fa-solid fa-circle-exclamation" style="font-size:10px"></i>{{ $message }}</div>
            @enderror
          </div>
          <div class="f-group">
            <label for="password_confirmation" class="f-label">Konfirmasi Password Baru</label>
            <input id="password_confirmation" name="password_confirmation" type="password" class="f-input"
              autocomplete="new-password" placeholder="••••••••">
            @error('password_confirmation', 'updatePassword')
            <div class="f-error"><i class="fa-solid fa-circle-exclamation" style="font-size:10px"></i>{{ $message }}</div>
            @enderror
          </div>
        </div>

        <button type="submit" class="btn btn-primary">
          <i class="fa-solid fa-key"></i> Perbarui Password
        </button>
      </form>
    </div>

  </div>{{-- /max-width --}}

</x-app-layout>
