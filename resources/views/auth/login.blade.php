<x-public-layout title="Masuk — Pabalu">

@push('styles')
  :root{--ac:var(--green-600);--ac2:var(--green-700);}
  .a-text{color:var(--ac);}
  .a-grad{background:linear-gradient(135deg,var(--ac),var(--ac2));}

  .auth-wrap{padding:56px 0 72px;background:linear-gradient(180deg,var(--mint-50) 0%,#fff 280px);}
  .auth-card{max-width:440px;margin:0 auto;background:#fff;border:1px solid var(--line);border-radius:22px;box-shadow:0 20px 50px rgba(14,59,42,.08);overflow:hidden;}
  .auth-head{padding:32px 32px 6px;}
  .auth-head h1{font-size:22px;font-weight:700;color:var(--green-900);margin-bottom:8px;}
  .auth-head p{font-size:13.5px;color:var(--ink-soft);line-height:1.6;}
  .auth-status{margin:18px 32px 0;padding:11px 14px;border-radius:12px;background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;font-size:13px;font-weight:600;}
  .auth-form{padding:24px 32px 8px;display:flex;flex-direction:column;gap:16px;}
  .f-label{display:block;font-size:12.5px;font-weight:600;color:#4b5160;margin-bottom:6px;}
  .f-input{width:100%;background:#f7f7f8;border:1px solid #ece9ea;color:#1f2329;border-radius:12px;padding:11px 14px;font-size:13.5px;font-family:inherit;outline:none;transition:border-color .15s,box-shadow .15s;}
  .f-input:focus{border-color:var(--ac);box-shadow:0 0 0 3px rgba(232,22,28,.1);}
  .f-error{font-size:12px;color:#dc2626;margin-top:6px;}
  .f-row{display:flex;align-items:center;justify-content:space-between;font-size:13px;}
  .f-remember{display:flex;align-items:center;gap:8px;color:#4b5160;cursor:pointer;}
  .f-remember input{width:15px;height:15px;accent-color:var(--ac);cursor:pointer;}
  .f-forgot{color:var(--ac);font-weight:600;}
  .auth-submit{width:100%;padding:12px;border-radius:99px;border:none;color:#fff;font-size:14px;font-weight:700;cursor:pointer;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:8px;box-shadow:0 8px 20px rgba(232,22,28,.28);margin-top:4px;}
  .auth-foot{padding:18px 32px 30px;text-align:center;border-top:1px solid #f5f3f3;margin-top:6px;font-size:13px;color:#6b7280;}
  .auth-foot a{color:var(--ac);font-weight:700;}

  @keyframes fadeUp{from{opacity:0;transform:translateY(16px);}to{opacity:1;transform:translateY(0);}}
  .animate-fadeUp{animation:fadeUp .5s ease both;}
@endpush

<section class="auth-wrap">
  <div class="container">
    <div class="auth-card animate-fadeUp">

      <div class="auth-head">
        <h1>Masuk ke Akun Anda</h1>
        <p>Kelola outlet, produk, dan transaksi bisnis Anda di Pabalu.</p>
      </div>

      @if (session('status'))
        <div class="auth-status"><i class="fa-solid fa-circle-check"></i> {{ session('status') }}</div>
      @endif

      <form method="POST" action="{{ route('login') }}" class="auth-form">
        @csrf

        <div>
          <label class="f-label" for="email">Email</label>
          <input id="email" name="email" type="email" class="f-input"
            value="{{ old('email') }}" required autofocus autocomplete="username"
            placeholder="email@domain.com">
          @error('email')
            <p class="f-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
          @enderror
        </div>

        <div>
          <label class="f-label" for="password">Password</label>
          <input id="password" name="password" type="password" class="f-input"
            required autocomplete="current-password" placeholder="Masukkan password">
          @error('password')
            <p class="f-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
          @enderror
        </div>

        <div class="f-row">
          <label class="f-remember" for="remember_me">
            <input id="remember_me" type="checkbox" name="remember">
            Ingat saya
          </label>
          @if (Route::has('password.request'))
            <a class="f-forgot" href="{{ route('password.request') }}">Lupa password?</a>
          @endif
        </div>

        <button type="submit" class="auth-submit a-grad">
          <i class="fa-solid fa-right-to-bracket"></i> Masuk
        </button>
      </form>

      <div class="auth-foot">
        Belum punya akun?
        @if (Route::has('register'))
          <a href="{{ route('register') }}">Daftar di sini</a>
        @endif
      </div>
    </div>
  </div>
</section>

</x-public-layout>
