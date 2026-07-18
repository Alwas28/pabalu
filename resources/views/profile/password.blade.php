@php
  /** @var \App\Models\User $user */
  $isAdmin = $user->role === 'admin';
@endphp

<x-app-layout>
<x-slot name="pageTitle">Ganti Password</x-slot>
<x-slot name="headerAction">
  <a href="{{ route('profile.edit') }}"
    style="display:flex;align-items:center;gap:7px;padding:8px 16px;border-radius:11px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13px;font-weight:600;text-decoration:none">
    <i class="fa-solid fa-arrow-left"></i><span>Kembali ke Profil</span>
  </a>
</x-slot>

<div style="max-width:540px" class="animate-fadeUp">
  <div class="card">
    <div class="card-header">
      <div class="card-title">
        <i class="fa-solid fa-lock a-text" style="margin-right:8px"></i>Ganti Password
      </div>
    </div>

    <form method="POST" action="{{ route('password.update') }}">
      @csrf
      @method('PUT')

      <div class="card-body" style="display:flex;flex-direction:column;gap:18px">

        @if(session('status') === 'password-updated')
        <div style="padding:12px 16px;background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.3);border-radius:11px;display:flex;align-items:center;gap:10px;font-size:13px;color:#34d399;font-weight:600">
          <i class="fa-solid fa-circle-check"></i>Password berhasil diperbarui.
        </div>
        @endif

        @if($isAdmin)
        <div style="padding:11px 14px;background:rgba(167,139,250,.08);border:1px solid rgba(167,139,250,.2);border-radius:11px;display:flex;align-items:center;gap:10px;font-size:12.5px;color:#a78bfa">
          <i class="fa-solid fa-shield-halved"></i>
          <span>Sebagai Administrator, password lama bersifat opsional.</span>
        </div>
        @endif

        <div>
          <label class="f-label" for="current_password">
            Password Saat Ini
            @if(!$isAdmin)<span style="color:#f87171">*</span>@else<span style="font-size:11px;color:var(--muted);font-weight:400"> (opsional)</span>@endif
          </label>
          <div style="position:relative">
            <input id="current_password" name="current_password" type="password" class="f-input"
              placeholder="{{ $isAdmin ? 'Kosongkan jika tidak ingin verifikasi' : 'Masukkan password saat ini' }}"
              autocomplete="current-password"
              style="padding-right:40px">
            <button type="button" onclick="togglePw('current_password','eye-cur')"
              style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--muted);font-size:14px;line-height:1">
              <i class="fa-solid fa-eye" id="eye-cur"></i>
            </button>
          </div>
          @if($errors->updatePassword->has('current_password'))
            <p style="font-size:12px;color:#f87171;margin-top:5px"><i class="fa-solid fa-circle-exclamation"></i> {{ $errors->updatePassword->first('current_password') }}</p>
          @endif
        </div>

        <div>
          <label class="f-label" for="new_password">Password Baru <span style="color:#f87171">*</span></label>
          <div style="position:relative">
            <input id="new_password" name="password" type="password" class="f-input"
              placeholder="Minimal 8 karakter" autocomplete="new-password"
              style="padding-right:40px">
            <button type="button" onclick="togglePw('new_password','eye-new')"
              style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--muted);font-size:14px;line-height:1">
              <i class="fa-solid fa-eye" id="eye-new"></i>
            </button>
          </div>
          @if($errors->updatePassword->has('password'))
            <p style="font-size:12px;color:#f87171;margin-top:5px"><i class="fa-solid fa-circle-exclamation"></i> {{ $errors->updatePassword->first('password') }}</p>
          @endif
        </div>

        <div>
          <label class="f-label" for="password_confirmation">Konfirmasi Password Baru <span style="color:#f87171">*</span></label>
          <div style="position:relative">
            <input id="password_confirmation" name="password_confirmation" type="password" class="f-input"
              placeholder="Ulangi password baru" autocomplete="new-password"
              style="padding-right:40px">
            <button type="button" onclick="togglePw('password_confirmation','eye-cnf')"
              style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--muted);font-size:14px;line-height:1">
              <i class="fa-solid fa-eye" id="eye-cnf"></i>
            </button>
          </div>
        </div>

      </div>

      <div style="padding:14px 20px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
        <a href="{{ route('profile.edit') }}"
          style="padding:9px 18px;border-radius:11px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13px;font-weight:600;text-decoration:none">
          Batal
        </a>
        <button type="submit"
          style="padding:9px 22px;border-radius:11px;border:none;background:linear-gradient(135deg,#7c3aed,#6d28d9);color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;display:flex;align-items:center;gap:7px">
          <i class="fa-solid fa-key"></i>Perbarui Password
        </button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
function togglePw(fieldId, iconId) {
  const f = document.getElementById(fieldId);
  const i = document.getElementById(iconId);
  if (f.type === 'password') { f.type = 'text'; i.className = 'fa-solid fa-eye-slash'; }
  else { f.type = 'password'; i.className = 'fa-solid fa-eye'; }
}
</script>
@endpush

</x-app-layout>
