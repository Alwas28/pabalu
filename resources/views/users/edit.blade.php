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

<div style="max-width:680px;display:flex;flex-direction:column;gap:20px" class="animate-fadeUp">

  {{-- Status verifikasi banner --}}
  @if($user->hasVerifiedEmail())
  <div style="background:rgba(16,185,129,.10);border:1px solid rgba(16,185,129,.3);border-radius:14px;padding:14px 18px;display:flex;gap:12px;align-items:center">
    <i class="fa-solid fa-circle-check" style="color:#34d399;flex-shrink:0;font-size:16px"></i>
    <div style="font-size:13px;color:var(--sub)">
      Email <strong style="color:var(--text)">{{ $user->email }}</strong> sudah terverifikasi pada
      <strong style="color:var(--text)">{{ $user->email_verified_at->format('d M Y, H:i') }}</strong>.
    </div>
  </div>
  @else
  <div style="background:rgba(245,158,11,.10);border:1px solid rgba(245,158,11,.3);border-radius:14px;padding:14px 18px;display:flex;gap:12px;align-items:center;justify-content:space-between">
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

  {{-- Edit Data + Role --}}
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

    <form method="POST" action="{{ route('users.update', $user) }}">
      @csrf
      @method('PUT')

      <div class="card-body" style="display:flex;flex-direction:column;gap:18px">

        @if(session('success'))
        <div style="padding:12px 16px;background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.3);border-radius:11px;display:flex;align-items:center;gap:10px;font-size:13px;color:#34d399;font-weight:600">
          <i class="fa-solid fa-circle-check"></i>{{ session('success') }}
        </div>
        @endif

        <div>
          <label class="f-label" for="name">Nama Lengkap <span style="color:#f87171">*</span></label>
          <input id="name" name="name" type="text" class="f-input"
            value="{{ old('name', $user->name) }}" placeholder="Nama lengkap user...">
          @error('name')
            <p style="font-size:12px;color:#f87171;margin-top:5px"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
          @enderror
        </div>

        <div>
          <label class="f-label" for="email">Alamat Email <span style="color:#f87171">*</span></label>
          <input id="email" name="email" type="email" class="f-input"
            value="{{ old('email', $user->email) }}" placeholder="user@domain.com">
          @if(!$user->hasVerifiedEmail())
            <p style="font-size:11.5px;color:var(--muted);margin-top:5px">
              <i class="fa-solid fa-circle-info"></i> Mengubah email akan mereset status verifikasi.
            </p>
          @endif
          @error('email')
            <p style="font-size:12px;color:#f87171;margin-top:5px"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
          @enderror
        </div>

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

      </div>

      <div style="padding:16px 20px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
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
            <i class="fa-solid fa-floppy-disk"></i> Simpan
          </button>
        </div>
      </div>
    </form>
  </div>

  {{-- Ubah Password --}}
  <div class="card animate-fadeUp d1">
    <div class="card-header" style="display:flex;align-items:center;justify-content:space-between">
      <div class="card-title">
        <i class="fa-solid fa-lock a-text" style="margin-right:8px"></i>Ubah Password
      </div>
      <button type="button" onclick="togglePwForm()"
        style="display:flex;align-items:center;gap:6px;padding:7px 14px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:12.5px;font-weight:600;cursor:pointer;font-family:inherit">
        <i class="fa-solid fa-key" style="font-size:11px"></i>
        <span id="pw-toggle-label">Atur Password</span>
      </button>
    </div>

    <div id="pw-form" style="display:none">
      <form method="POST" action="{{ route('users.password', $user) }}">
        @csrf
        @method('PATCH')

        <div class="card-body" style="display:flex;flex-direction:column;gap:18px">

          @if($errors->has('password'))
          <div style="padding:12px 16px;background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);border-radius:11px;font-size:13px;color:#f87171">
            <i class="fa-solid fa-circle-exclamation" style="margin-right:6px"></i>{{ $errors->first('password') }}
          </div>
          @endif

          <div style="padding:11px 14px;background:rgba(167,139,250,.08);border:1px solid rgba(167,139,250,.2);border-radius:11px;display:flex;align-items:center;gap:10px;font-size:12.5px;color:#a78bfa">
            <i class="fa-solid fa-shield-halved"></i>
            <span>Administrator dapat mengatur ulang password tanpa perlu password lama.</span>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
            <div>
              <label class="f-label" for="new_pw">Password Baru <span style="color:#f87171">*</span></label>
              <div style="position:relative">
                <input id="new_pw" name="password" type="password" class="f-input"
                  placeholder="Min. 8 karakter"
                  style="padding-right:40px">
                <button type="button" onclick="togglePw('new_pw','eye-np')"
                  style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--muted);font-size:14px;line-height:1">
                  <i class="fa-solid fa-eye" id="eye-np"></i>
                </button>
              </div>
            </div>
            <div>
              <label class="f-label" for="new_pw_confirmation">Konfirmasi <span style="color:#f87171">*</span></label>
              <div style="position:relative">
                <input id="new_pw_confirmation" name="password_confirmation" type="password" class="f-input"
                  placeholder="Ulangi password baru"
                  style="padding-right:40px">
                <button type="button" onclick="togglePw('new_pw_confirmation','eye-nc')"
                  style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--muted);font-size:14px;line-height:1">
                  <i class="fa-solid fa-eye" id="eye-nc"></i>
                </button>
              </div>
            </div>
          </div>
        </div>

        <div style="padding:14px 20px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:10px">
          <button type="button" onclick="togglePwForm()"
            style="padding:9px 18px;border-radius:11px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
            Batal
          </button>
          <button type="submit"
            style="padding:9px 22px;border-radius:11px;border:none;background:linear-gradient(135deg,#7c3aed,#6d28d9);color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;display:flex;align-items:center;gap:7px">
            <i class="fa-solid fa-key"></i>Simpan Password
          </button>
        </div>
      </form>
    </div>

    {{-- tampil otomatis jika ada error password --}}
    @if($errors->has('password'))
    <script>document.addEventListener('DOMContentLoaded',()=>document.getElementById('pw-form').style.display='block')</script>
    @endif
  </div>

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

function togglePwForm() {
  const form  = document.getElementById('pw-form');
  const label = document.getElementById('pw-toggle-label');
  const show  = form.style.display === 'none';
  form.style.display = show ? 'block' : 'none';
  label.textContent  = show ? 'Tutup' : 'Atur Password';
}

function togglePw(fieldId, iconId) {
  const f = document.getElementById(fieldId);
  const i = document.getElementById(iconId);
  if (f.type === 'password') { f.type = 'text'; i.className = 'fa-solid fa-eye-slash'; }
  else { f.type = 'password'; i.className = 'fa-solid fa-eye'; }
}
</script>
@endpush

</x-app-layout>
