<x-public-layout title="Daftar — Pabalu">

@push('styles')
  :root{--ac:var(--green-600);--ac2:var(--green-700);}
  .a-text{color:var(--ac);}
  .a-grad{background:linear-gradient(135deg,var(--ac),var(--ac2));}

  .auth-wrap{padding:48px 0 72px;background:linear-gradient(180deg,var(--mint-50) 0%,#fff 280px);}
  .auth-card{max-width:480px;margin:0 auto;background:#fff;border:1px solid var(--line);border-radius:22px;box-shadow:0 20px 50px rgba(14,59,42,.08);overflow:hidden;}
  .auth-head{padding:30px 32px 4px;}
  .auth-head h1{font-size:21px;font-weight:700;color:var(--green-900);margin-bottom:8px;}
  .auth-head p{font-size:13.5px;color:var(--ink-soft);line-height:1.6;}
  .auth-form{padding:22px 32px 8px;display:flex;flex-direction:column;gap:15px;}
  .f-label{display:block;font-size:12.5px;font-weight:600;color:#4b5160;margin-bottom:6px;}
  .f-input{width:100%;background:#f7f7f8;border:1px solid #ece9ea;color:#1f2329;border-radius:12px;padding:11px 14px;font-size:13.5px;font-family:inherit;outline:none;transition:border-color .15s,box-shadow .15s;}
  .f-input:focus{border-color:var(--ac);box-shadow:0 0 0 3px rgba(232,22,28,.1);}
  .f-input.f-input-error{border-color:#dc2626;background:#fff8f8;}
  .f-hint{font-size:11.5px;color:#9aa0ab;margin-top:5px;}
  .f-error{font-size:12px;color:#dc2626;margin-top:6px;}
  .f-icon-wrap{position:relative;}
  .f-icon-wrap i.f-ico{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:#9aa0ab;font-size:13px;pointer-events:none;}
  .f-icon-wrap .f-input{padding-left:38px;}
  .f-eye{position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9aa0ab;font-size:14px;line-height:1;}
  .terms-check{display:flex;align-items:flex-start;gap:10px;cursor:pointer;padding:12px 14px;border-radius:12px;border:1px solid #ece9ea;background:#f7f7f8;transition:border-color .2s,background .2s;}
  .terms-check:has(input:checked){border-color:var(--ac);background:rgba(232,22,28,.05);}
  .auth-submit{width:100%;padding:12px;border-radius:99px;border:none;color:#fff;font-size:14px;font-weight:700;cursor:pointer;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:8px;box-shadow:0 8px 20px rgba(232,22,28,.28);margin-top:2px;transition:.2s;}
  .auth-submit:disabled{opacity:.5;cursor:not-allowed;box-shadow:none;}
  .auth-foot{padding:18px 32px 28px;text-align:center;border-top:1px solid #f5f3f3;margin-top:6px;font-size:13px;color:#6b7280;}
  .auth-foot a{color:var(--ac);font-weight:700;}
  .trust-row{margin-top:18px;text-align:center;display:flex;align-items:center;justify-content:center;gap:18px;flex-wrap:wrap;}
  .trust-row span{font-size:12px;color:#9aa0ab;}

  /* ════ ERROR MODAL ════ */
  .modal-backdrop{position:fixed;inset:0;background:rgba(20,16,15,.55);backdrop-filter:blur(4px);z-index:500;display:flex;align-items:center;justify-content:center;padding:20px;opacity:0;visibility:hidden;transition:opacity .25s,visibility .25s;}
  .modal-backdrop.show{opacity:1;visibility:visible;}
  .modal-box{background:#fff;border-radius:22px;box-shadow:0 32px 80px rgba(0,0,0,.18);max-width:380px;width:100%;padding:32px;text-align:center;transform:scale(.92) translateY(16px);transition:transform .28s ease;}
  .modal-backdrop.show .modal-box{transform:scale(1) translateY(0);}
  .modal-icon{width:68px;height:68px;border-radius:18px;margin:0 auto 18px;background:linear-gradient(135deg,var(--green-500),var(--green-700));display:grid;place-items:center;box-shadow:0 10px 28px -8px rgba(184,15,20,.45);}
  .modal-icon i{font-size:28px;color:#fff;}
  .modal-box h2{font-size:18px;font-weight:700;color:#15181d;margin-bottom:8px;}
  .modal-box p{font-size:13.5px;color:#6b7280;line-height:1.65;margin-bottom:22px;}
  .modal-email-badge{display:inline-flex;align-items:center;gap:7px;background:#fff0f0;border:1px solid #fecaca;color:#dc2626;border-radius:99px;padding:6px 14px;font-size:13px;font-weight:600;margin-bottom:22px;word-break:break-all;}
  .modal-btn{width:100%;padding:12px;border-radius:99px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:14px;font-weight:700;cursor:pointer;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:8px;box-shadow:0 8px 20px rgba(232,22,28,.3);transition:.2s;}
  .modal-btn:hover{transform:translateY(-1px);}

  @keyframes fadeUp{from{opacity:0;transform:translateY(16px);}to{opacity:1;transform:translateY(0);}}
  .animate-fadeUp{animation:fadeUp .5s ease both;}
@endpush

<section class="auth-wrap">
  <div class="container">
    <div class="auth-card animate-fadeUp">

      <div class="auth-head">
        <h1>Daftar sebagai Pemilik Toko</h1>
        <p>Kelola outlet, produk, stok, dan laporan bisnis Anda dalam satu platform.</p>
      </div>

      <form method="POST" action="{{ route('register') }}" class="auth-form">
        @csrf

        <div>
          <label class="f-label" for="name">Nama Lengkap <span style="color:var(--ac)">*</span></label>
          <input id="name" name="name" type="text" class="f-input"
            value="{{ old('name') }}" placeholder="Nama lengkap Anda..."
            autofocus autocomplete="name">
          @error('name')
            <p class="f-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
          @enderror
        </div>

        <div>
          <label class="f-label" for="email">Alamat Email <span style="color:var(--ac)">*</span></label>
          <div class="f-icon-wrap">
            <i class="fa-solid fa-envelope f-ico"></i>
            <input id="email" name="email" type="email" class="f-input"
              value="{{ old('email') }}" placeholder="email@domain.com" autocomplete="username">
          </div>
          <p class="f-hint"><i class="fa-solid fa-circle-info"></i> Link verifikasi dikirim ke email ini.</p>
          @error('email')
            <p class="f-error" id="email-error-inline">
              <i class="fa-solid fa-circle-exclamation"></i>
              Email ini sudah terdaftar — silakan gunakan email lain.
            </p>
          @enderror
        </div>

        <div>
          <label class="f-label" for="phone">Nomor WhatsApp Aktif <span style="color:var(--ac)">*</span></label>
          <div class="f-icon-wrap">
            <i class="fa-brands fa-whatsapp f-ico" style="color:#25d366"></i>
            <input id="phone" name="phone" type="tel" class="f-input"
              value="{{ old('phone') }}" placeholder="628xx-xxxx-xxxx" autocomplete="tel">
          </div>
          <p class="f-hint"><i class="fa-solid fa-circle-info"></i> Untuk notifikasi penting dan konfirmasi akun.</p>
          @error('phone')
            <p class="f-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
          @enderror
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
          <div>
            <label class="f-label" for="password">Password <span style="color:var(--ac)">*</span></label>
            <div class="f-icon-wrap">
              <input id="password" name="password" type="password" class="f-input"
                placeholder="Min. 8 karakter" autocomplete="new-password" style="padding-left:14px;padding-right:40px">
              <button type="button" onclick="togglePass('password','eye-pw')" class="f-eye">
                <i class="fa-solid fa-eye" id="eye-pw"></i>
              </button>
            </div>
            @error('password')
              <p class="f-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
            @enderror
          </div>
          <div>
            <label class="f-label" for="password_confirmation">Konfirmasi <span style="color:var(--ac)">*</span></label>
            <div class="f-icon-wrap">
              <input id="password_confirmation" name="password_confirmation" type="password" class="f-input"
                placeholder="Ulangi password" autocomplete="new-password" style="padding-left:14px;padding-right:40px">
              <button type="button" onclick="togglePass('password_confirmation','eye-pc')" class="f-eye">
                <i class="fa-solid fa-eye" id="eye-pc"></i>
              </button>
            </div>
          </div>
        </div>

        <div>
          <label class="terms-check" for="terms">
            <input type="checkbox" name="terms" id="terms"
              {{ old('terms') ? 'checked' : '' }}
              style="width:16px;height:16px;accent-color:var(--ac);margin-top:2px;flex-shrink:0;cursor:pointer">
            <span style="font-size:13px;color:#4b5160;line-height:1.6">
              Saya telah membaca dan menyetujui
              <a href="{{ route('pages.show', 'syarat-dan-ketentuan') }}" target="_blank" rel="noopener" style="color:var(--ac);font-weight:600">Syarat &amp; Ketentuan</a>
              serta
              <a href="{{ route('pages.show', 'kebijakan-privasi') }}" target="_blank" rel="noopener" style="color:var(--ac);font-weight:600">Kebijakan Privasi</a>
              Pabalu.
            </span>
          </label>
          @error('terms')
            <p class="f-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
          @enderror
        </div>

        @if(config('services.cloudflare.turnstile.site_key'))
        <div>
          <div class="cf-turnstile" data-sitekey="{{ config('services.cloudflare.turnstile.site_key') }}"></div>
          @error('cf-turnstile-response')
            <p class="f-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
          @enderror
        </div>
        @endif

        <button type="submit" id="btn-submit" class="auth-submit a-grad"
          @if($errors->has('email')) disabled @endif>
          <i class="fa-solid fa-user-plus"></i> Buat Akun Gratis
        </button>
      </form>

      <div class="auth-foot">
        Sudah punya akun?
        <a href="{{ route('login') }}">Masuk di sini</a>
      </div>
    </div>

    <div class="trust-row">
      <span><i class="fa-solid fa-gift" style="color:var(--ac)"></i> Paket Free selamanya</span>
      <span><i class="fa-solid fa-shield-halved" style="color:var(--ac)"></i> Data aman & terenkripsi</span>
      <span><i class="fa-solid fa-credit-card-slash" style="color:var(--ac)"></i> Tanpa kartu kredit</span>
    </div>
  </div>
</section>

{{-- Modal: email sudah terdaftar --}}
@if($errors->has('email'))
<div class="modal-backdrop" id="emailModal">
  <div class="modal-box">
    <div class="modal-icon"><i class="fa-solid fa-envelope-circle-check" style="font-size:26px"></i></div>
    <h2>Email Sudah Terdaftar</h2>
    <p>Alamat email berikut sudah digunakan oleh akun lain. Silakan gunakan email yang berbeda untuk mendaftar.</p>
    <div class="modal-email-badge">
      <i class="fa-solid fa-envelope"></i>
      <span>{{ old('email') }}</span>
    </div>
    <button class="modal-btn" onclick="closeEmailModal()">
      <i class="fa-solid fa-pen-to-square"></i> Ganti Email
    </button>
  </div>
</div>
@endif

@push('scripts')
<script>
function togglePass(fieldId, iconId) {
  const f=document.getElementById(fieldId), i=document.getElementById(iconId);
  f.type=f.type==='password'?'text':'password';
  i.className=f.type==='password'?'fa-solid fa-eye':'fa-solid fa-eye-slash';
}

// ── Email modal & submit guard ──────────────────────────────────
const takenEmail = '{{ old('email') }}';
const hasEmailError = {{ $errors->has('email') ? 'true' : 'false' }};
const emailInput  = document.getElementById('email');
const submitBtn   = document.getElementById('btn-submit');

function closeEmailModal() {
  const m = document.getElementById('emailModal');
  if (m) { m.classList.remove('show'); setTimeout(()=>m.remove(), 280); }
  emailInput.focus();
  emailInput.select();
}

function syncSubmitState() {
  if (!hasEmailError) return;
  const changed = emailInput.value.trim().toLowerCase() !== takenEmail.trim().toLowerCase();
  submitBtn.disabled = !changed;
  emailInput.classList.toggle('f-input-error', !changed);
  const inlineErr = document.getElementById('email-error-inline');
  if (inlineErr) inlineErr.style.display = changed ? 'none' : '';
}

if (hasEmailError) {
  setTimeout(() => {
    const m = document.getElementById('emailModal');
    if (m) m.classList.add('show');
  }, 400);

  emailInput.addEventListener('input', syncSubmitState);
  syncSubmitState();

  document.getElementById('emailModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeEmailModal();
  });
}
</script>
@if(config('services.cloudflare.turnstile.site_key'))
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
@endif
@endpush

</x-public-layout>
