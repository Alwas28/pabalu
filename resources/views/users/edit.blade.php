@php
  $authRole    = Auth::user()->role;
  $isAdmin     = $authRole === 'admin';
  $roleOptions = [
    'admin'        => ['label' => 'Administrator',  'icon' => 'fa-shield-halved', 'desc' => 'Akses penuh sistem — email otomatis terverifikasi'],
    'owner'        => ['label' => 'Pemilik Toko',   'icon' => 'fa-store',         'desc' => 'Kelola outlet, produk, user, dan laporan'],
    'admin_outlet' => ['label' => 'Admin Outlet',   'icon' => 'fa-user-tie',      'desc' => 'Operasional harian + laporan outlet sendiri'],
    'kasir'        => ['label' => 'Kasir',           'icon' => 'fa-cash-register', 'desc' => 'POS, stok harian, pengeluaran, antrian order'],
  ];
  $currentRole = old('role', $user->role);
@endphp

<x-app-layout>
<x-slot name="pageTitle">Edit User</x-slot>
<x-slot name="headerAction">
  <a href="{{ route('users.index') }}"
    style="display:flex;align-items:center;gap:7px;padding:8px 16px;border-radius:11px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13px;font-weight:600;text-decoration:none">
    <i class="fa-solid fa-arrow-left"></i><span>Kembali</span>
  </a>
</x-slot>

<div style="max-width:680px" class="animate-fadeUp">
  <form method="POST" action="{{ route('users.update', $user) }}">
    @csrf
    @method('PUT')

    {{-- Status verifikasi banner --}}
    @if($user->hasVerifiedEmail())
    <div style="background:rgba(16,185,129,.10);border:1px solid rgba(16,185,129,.3);border-radius:14px;padding:14px 18px;margin-bottom:20px;display:flex;gap:12px;align-items:center">
      <i class="fa-solid fa-circle-check" style="color:#34d399;flex-shrink:0;font-size:16px"></i>
      <div style="font-size:13px;color:var(--sub)">
        Email <strong style="color:var(--text)">{{ $user->email }}</strong> sudah terverifikasi pada
        <strong style="color:var(--text)">{{ $user->email_verified_at->format('d M Y, H:i') }}</strong>.
      </div>
    </div>
    @else
    <div style="background:rgba(245,158,11,.10);border:1px solid rgba(245,158,11,.3);border-radius:14px;padding:14px 18px;margin-bottom:20px;display:flex;gap:12px;align-items:center;justify-content:space-between">
      <div style="display:flex;gap:12px;align-items:center">
        <i class="fa-solid fa-triangle-exclamation" style="color:#fbbf24;flex-shrink:0;font-size:16px"></i>
        <div style="font-size:13px;color:var(--sub)">
          Email <strong style="color:var(--text)">{{ $user->email }}</strong> belum terverifikasi.
        </div>
      </div>
      @if($isAdmin)
      <form method="POST" action="{{ route('users.verify', $user) }}" style="flex-shrink:0">
        @csrf
        <button type="submit"
          style="padding:6px 14px;border-radius:9px;border:1px solid rgba(16,185,129,.4);background:rgba(16,185,129,.14);color:#34d399;font-size:12px;font-weight:600;cursor:pointer;font-family:inherit;display:flex;align-items:center;gap:6px;white-space:nowrap">
          <i class="fa-solid fa-circle-check"></i> Verifikasi Sekarang
        </button>
      </form>
      @endif
    </div>
    @endif

    {{-- Form Card --}}
    <div class="card">
      <div class="card-header">
        <div style="display:flex;align-items:center;gap:12px">
          <div class="a-grad" style="width:36px;height:36px;border-radius:10px;display:grid;place-items:center;color:#fff;font-size:14px;font-weight:700;flex-shrink:0">
            {{ strtoupper(substr($user->name, 0, 1)) }}
          </div>
          <div>
            <div class="card-title">Edit: {{ $user->name }}</div>
            <div style="font-size:11.5px;color:var(--muted);margin-top:1px">ID #{{ $user->id }} · Bergabung {{ $user->created_at->format('d M Y') }}</div>
          </div>
        </div>
      </div>

      <div class="card-body" style="display:flex;flex-direction:column;gap:18px">

        {{-- Nama --}}
        <div>
          <label class="f-label" for="name">Nama Lengkap <span style="color:#f87171">*</span></label>
          <input id="name" name="name" type="text" class="f-input"
            value="{{ old('name', $user->name) }}"
            placeholder="Nama lengkap user...">
          @error('name')
            <p style="font-size:12px;color:#f87171;margin-top:5px"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
          @enderror
        </div>

        {{-- Email --}}
        <div>
          <label class="f-label" for="email">Alamat Email <span style="color:#f87171">*</span></label>
          <input id="email" name="email" type="email" class="f-input"
            value="{{ old('email', $user->email) }}"
            placeholder="user@domain.com">
          @if(!$user->hasVerifiedEmail())
            <p style="font-size:11.5px;color:var(--muted);margin-top:5px">
              <i class="fa-solid fa-circle-info"></i> Mengubah email akan mereset status verifikasi.
            </p>
          @endif
          @error('email')
            <p style="font-size:12px;color:#f87171;margin-top:5px"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
          @enderror
        </div>

        {{-- Role --}}
        <div>
          <label class="f-label">Role <span style="color:#f87171">*</span></label>
          <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-top:6px">
            @foreach($allowedRoles as $roleKey)
            @if(isset($roleOptions[$roleKey]))
            @php $opt = $roleOptions[$roleKey]; @endphp
            <label style="cursor:pointer">
              <input type="radio" name="role" value="{{ $roleKey }}"
                {{ $currentRole === $roleKey ? 'checked' : '' }}
                onchange="onRoleChange(this)"
                style="display:none">
              <div class="role-card{{ $currentRole === $roleKey ? ' role-card-active' : '' }}"
                data-role="{{ $roleKey }}"
                style="border:1.5px solid {{ $currentRole === $roleKey ? 'var(--ac)' : 'var(--border)' }};background:{{ $currentRole === $roleKey ? 'var(--ac-lt)' : 'transparent' }};border-radius:12px;padding:14px;display:flex;align-items:flex-start;gap:10px;transition:border-color .2s,background .2s">
                <div style="width:34px;height:34px;border-radius:9px;background:{{ $currentRole === $roleKey ? 'var(--ac-lt)' : 'var(--surface2)' }};display:grid;place-items:center;flex-shrink:0;font-size:14px;color:{{ $currentRole === $roleKey ? 'var(--ac)' : 'var(--sub)' }};transition:background .2s,color .2s" class="role-icon">
                  <i class="fa-solid {{ $opt['icon'] }}"></i>
                </div>
                <div>
                  <div style="font-size:13px;font-weight:600;color:var(--text)">{{ $opt['label'] }}</div>
                  <div style="font-size:11.5px;color:var(--muted);margin-top:2px;line-height:1.4">{{ $opt['desc'] }}</div>
                  @if($roleKey === 'admin')
                  <div style="margin-top:6px;display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:99px;background:rgba(16,185,129,.14);color:#34d399;font-size:10.5px;font-weight:600">
                    <i class="fa-solid fa-circle-check" style="font-size:9px"></i> Auto-verified
                  </div>
                  @endif
                </div>
              </div>
            </label>
            @endif
            @endforeach
          </div>
          @error('role')
            <p style="font-size:12px;color:#f87171;margin-top:5px"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
          @enderror
        </div>

        {{-- Password (opsional) --}}
        <div>
          <label class="f-label">Ubah Password</label>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:6px">
            <div>
              <div style="position:relative">
                <input id="password" name="password" type="password" class="f-input"
                  placeholder="Kosongkan jika tidak diubah"
                  style="padding-right:40px">
                <button type="button" onclick="togglePass('password','eye-pw')"
                  style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--muted);font-size:14px">
                  <i class="fa-solid fa-eye" id="eye-pw"></i>
                </button>
              </div>
              @error('password')
                <p style="font-size:12px;color:#f87171;margin-top:5px"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
              @enderror
            </div>
            <div>
              <div style="position:relative">
                <input id="password_confirmation" name="password_confirmation" type="password" class="f-input"
                  placeholder="Konfirmasi password baru"
                  style="padding-right:40px">
                <button type="button" onclick="togglePass('password_confirmation','eye-pc')"
                  style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--muted);font-size:14px">
                  <i class="fa-solid fa-eye" id="eye-pc"></i>
                </button>
              </div>
            </div>
          </div>
        </div>

      </div>

      {{-- Footer --}}
      <div style="padding:16px 20px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        {{-- Hapus (bukan diri sendiri, bukan admin-only target jika owner) --}}
        @if($user->id !== Auth::id() && ($isAdmin || !in_array($user->role, ['admin','owner'])))
        <form method="POST" action="{{ route('users.destroy', $user) }}">
          @csrf
          @method('DELETE')
          <button type="submit"
            style="padding:9px 16px;border-radius:11px;border:1px solid rgba(239,68,68,.4);background:rgba(239,68,68,.10);color:#f87171;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;display:flex;align-items:center;gap:7px"
            onclick="return confirm('Hapus user {{ addslashes($user->name) }}? Aksi ini tidak dapat dibatalkan.')">
            <i class="fa-solid fa-trash"></i> Hapus User
          </button>
        </form>
        @else
        <div></div>
        @endif

        <div style="display:flex;gap:10px">
          <a href="{{ route('users.index') }}"
            style="padding:9px 18px;border-radius:11px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13px;font-weight:600;text-decoration:none">
            Batal
          </a>
          <button type="submit"
            class="a-grad" style="padding:9px 22px;border-radius:11px;border:none;color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;display:flex;align-items:center;gap:7px">
            <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
          </button>
        </div>
      </div>
    </div>

  </form>
</div>

@push('scripts')
<script>
function onRoleChange(input) {
  document.querySelectorAll('.role-card').forEach(card => {
    card.style.borderColor = 'var(--border)';
    card.style.background  = 'transparent';
    card.querySelector('.role-icon').style.background = 'var(--surface2)';
    card.querySelector('.role-icon').style.color = 'var(--sub)';
  });
  const active = input.closest('label').querySelector('.role-card');
  active.style.borderColor = 'var(--ac)';
  active.style.background  = 'var(--ac-lt)';
  active.querySelector('.role-icon').style.background = 'var(--ac-lt)';
  active.querySelector('.role-icon').style.color = 'var(--ac)';
}

function togglePass(fieldId, iconId) {
  const f = document.getElementById(fieldId);
  const i = document.getElementById(iconId);
  if (f.type === 'password') {
    f.type = 'text';
    i.className = 'fa-solid fa-eye-slash';
  } else {
    f.type = 'password';
    i.className = 'fa-solid fa-eye';
  }
}
</script>
@endpush

</x-app-layout>
