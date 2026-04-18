<x-app-layout title="Edit Role: {{ $role->name }}">

  <div style="max-width:860px">

    {{-- Back --}}
    <a href="{{ route('rbac.roles.index') }}"
      style="display:inline-flex;align-items:center;gap:7px;font-size:13px;color:var(--sub);text-decoration:none;margin-bottom:4px;transition:color .15s"
      onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--sub)'">
      <i class="fa-solid fa-arrow-left" style="font-size:11px"></i> Kembali ke Daftar Role
    </a>

    {{-- Flash --}}
    @if (session('success'))
    <div style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:12px;background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.2);color:#34d399;font-size:13px;margin-bottom:4px">
      <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
    </div>
    @endif

    <form method="POST" action="{{ route('rbac.roles.update', $role) }}">
      @csrf @method('PUT')

      {{-- Identitas --}}
      <div class="settings-section animate-fadeUp" style="margin-bottom:20px">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap">
          <div>
            <div class="settings-title">Identitas Role</div>
            <div class="settings-desc">Ubah nama role. Perubahan nama akan mempengaruhi semua user yang memiliki role ini.</div>
          </div>
          {{-- Stats badge --}}
          <div style="display:flex;gap:10px;flex-shrink:0">
            <div style="background:var(--surface2);border-radius:10px;padding:8px 16px;text-align:center">
              <div style="font-family:'Clash Display',sans-serif;font-size:18px;font-weight:700;color:var(--ac)">{{ $role->permissions->count() }}</div>
              <div style="font-size:10px;color:var(--muted)">Permission</div>
            </div>
            <div style="background:var(--surface2);border-radius:10px;padding:8px 16px;text-align:center">
              <div style="font-family:'Clash Display',sans-serif;font-size:18px;font-weight:700;color:var(--text)">{{ $role->users()->count() }}</div>
              <div style="font-size:10px;color:var(--muted)">User</div>
            </div>
          </div>
        </div>

        <div style="max-width:360px;margin-top:4px">
          <div class="f-group">
            <label for="name" class="f-label">Nama Role <span style="color:#f87171">*</span></label>
            @if ($role->name === 'admin')
            <input id="name" name="name" type="text" class="f-input"
              value="{{ $role->name }}" readonly
              style="opacity:.6;cursor:not-allowed">
            <div style="font-size:11.5px;color:var(--muted);margin-top:5px">
              <i class="fa-solid fa-lock" style="font-size:10px"></i> Role admin tidak dapat diubah namanya.
            </div>
            @else
            <input id="name" name="name" type="text" class="f-input"
              value="{{ old('name', $role->name) }}" required
              oninput="this.value=this.value.toLowerCase().replace(/\s+/g,'_')">
            @error('name')
            <div class="f-error"><i class="fa-solid fa-circle-exclamation" style="font-size:10px"></i>{{ $message }}</div>
            @enderror
            @endif
          </div>
        </div>
      </div>

      {{-- Permission Groups --}}
      <div class="settings-section animate-fadeUp d1" style="margin-bottom:20px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
          <div>
            <div class="settings-title">Permission</div>
            <div class="settings-desc">Centang permission yang diizinkan untuk role <strong>{{ $role->name }}</strong>.</div>
          </div>
          @if ($role->name === 'admin')
          <span class="badge badge-amber"><i class="fa-solid fa-lock" style="font-size:9px"></i> Semua Aktif</span>
          @endif
        </div>

        @if ($role->name === 'admin')
        {{-- Admin: tampilkan tapi readonly --}}
        <div style="padding:16px;background:var(--surface2);border-radius:12px;font-size:13px;color:var(--sub)">
          <i class="fa-solid fa-shield-halved" style="color:var(--ac);margin-right:6px"></i>
          Role <strong style="color:var(--text)">admin</strong> otomatis memiliki semua permission yang ada di sistem.
          Permission tidak dapat diubah untuk role ini.
        </div>
        {{-- Hidden inputs to keep permissions on submit --}}
        @foreach ($allPermissions as $perm)
        <input type="hidden" name="permissions[]" value="{{ $perm->name }}">
        @endforeach
        @else
        @include('rbac._permission_groups', [
          'groups'         => $groups,
          'allPermissions' => $allPermissions,
          'checked'        => old('permissions', $rolePermissions),
        ])
        @endif
      </div>

      {{-- Submit --}}
      <div style="display:flex;align-items:center;gap:12px">
        <button type="submit" class="btn btn-primary">
          <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
        </button>
        <a href="{{ route('rbac.roles.index') }}" class="btn" style="text-decoration:none">Batal</a>
      </div>

    </form>
  </div>

</x-app-layout>
