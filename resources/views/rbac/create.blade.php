<x-app-layout title="Tambah Role">

  <div style="max-width:860px">

    {{-- Back --}}
    <a href="{{ route('rbac.roles.index') }}"
      style="display:inline-flex;align-items:center;gap:7px;font-size:13px;color:var(--sub);text-decoration:none;margin-bottom:4px;transition:color .15s"
      onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--sub)'">
      <i class="fa-solid fa-arrow-left" style="font-size:11px"></i> Kembali ke Daftar Role
    </a>

    <form method="POST" action="{{ route('rbac.roles.store') }}">
      @csrf

      {{-- Nama Role --}}
      <div class="settings-section animate-fadeUp" style="margin-bottom:20px">
        <div class="settings-title">Identitas Role</div>
        <div class="settings-desc">Nama role bersifat unik dan digunakan sebagai identifier di sistem.</div>

        <div style="max-width:360px">
          <div class="f-group">
            <label for="name" class="f-label">Nama Role <span style="color:#f87171">*</span></label>
            <input id="name" name="name" type="text" class="f-input"
              value="{{ old('name') }}" required placeholder="Contoh: supervisor" autofocus
              oninput="this.value=this.value.toLowerCase().replace(/\s+/g,'_')">
            <div style="font-size:11.5px;color:var(--muted);margin-top:5px">
              Hanya huruf kecil, angka, dan underscore. Contoh: <code style="font-family:monospace">admin_outlet</code>
            </div>
            @error('name')
            <div class="f-error"><i class="fa-solid fa-circle-exclamation" style="font-size:10px"></i>{{ $message }}</div>
            @enderror
          </div>
        </div>
      </div>

      {{-- Permission Groups --}}
      <div class="settings-section animate-fadeUp d1" style="margin-bottom:20px">
        <div class="settings-title">Atur Permission</div>
        <div class="settings-desc">Centang permission yang diizinkan untuk role ini. Permission dikelompokkan berdasarkan modul.</div>

        @include('rbac._permission_groups', [
          'groups'         => $groups,
          'allPermissions' => $allPermissions,
          'checked'        => old('permissions', []),
        ])
      </div>

      {{-- Submit --}}
      <div style="display:flex;align-items:center;gap:12px">
        <button type="submit" class="btn btn-primary">
          <i class="fa-solid fa-floppy-disk"></i> Simpan Role
        </button>
        <a href="{{ route('rbac.roles.index') }}" class="btn" style="text-decoration:none">Batal</a>
      </div>

    </form>
  </div>

</x-app-layout>
