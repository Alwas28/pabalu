<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — Pabalu</title>
<x-seo
    title="Login — Pabalu | Sistem Manajemen UMKM"
    description="Masuk ke akun Pabalu dan kelola bisnis UMKM Anda."
    url="{{ url('/login') }}"
    :noindex="true"
/>

<!-- Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Clash+Display:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
*{box-sizing:border-box;margin:0;padding:0}
body{
  font-family:'Plus Jakarta Sans',sans-serif;
  background:#0f1117;
  color:#e2e8f0;
  min-height:100vh;
  display:flex;
  align-items:center;
  justify-content:center;
  padding:24px;
}
.font-display,h1,h2,h3{font-family:'Clash Display',sans-serif}

:root{
  --ac:#E8000D;--ac2:#C0000A;
  --ac-lt:rgba(232,0,13,.14);
  --bg:#0f1117;
  --surface:#161b27;
  --surface2:#1c2336;
  --border:#252d42;
  --text:#e2e8f0;
  --muted:#64748b;
  --sub:#94a3b8;
}

/* Card */
.login-card{
  background:var(--surface);
  border:1px solid var(--border);
  border-radius:24px;
  padding:40px 36px;
  width:100%;
  max-width:420px;
  box-shadow:0 24px 64px rgba(0,0,0,.5);
  animation:fadeUp .4s ease both;
}
@media(max-width:480px){.login-card{padding:32px 24px}}

/* Logo */
.logo-wrap{
  display:flex;align-items:center;gap:12px;
  margin-bottom:32px;
}
.logo-icon{
  width:44px;height:44px;border-radius:13px;
  background:linear-gradient(135deg,var(--ac),var(--ac2));
  display:grid;place-items:center;flex-shrink:0;
}
.logo-icon i{color:#fff;font-size:18px}
.logo-name{font-size:20px;font-weight:700;color:var(--text);line-height:1.1}
.logo-name span{color:var(--ac)}
.logo-sub{font-size:10px;color:var(--muted);letter-spacing:.3px;margin-top:2px}

/* Heading */
.login-title{font-size:22px;font-weight:700;color:var(--text);margin-bottom:4px}
.login-sub{font-size:13px;color:var(--sub);margin-bottom:28px}

/* Form */
.f-group{margin-bottom:18px}
.f-label{
  display:block;font-size:12px;font-weight:600;
  color:var(--sub);margin-bottom:6px;letter-spacing:.3px;
}
.f-wrap{position:relative}
.f-icon{
  position:absolute;left:13px;top:50%;transform:translateY(-50%);
  color:var(--muted);font-size:13px;pointer-events:none;
}
.f-input{
  width:100%;background:var(--surface2);border:1px solid var(--border);
  color:var(--text);border-radius:12px;padding:10px 13px 10px 38px;
  font-size:13.5px;font-family:inherit;
  outline:none;transition:border-color .15s,box-shadow .15s;
}
.f-input:focus{border-color:var(--ac);box-shadow:0 0 0 3px var(--ac-lt)}
.f-input::placeholder{color:var(--muted)}

/* Password toggle */
.pw-toggle{
  position:absolute;right:13px;top:50%;transform:translateY(-50%);
  background:none;border:none;cursor:pointer;color:var(--muted);
  font-size:13px;padding:0;transition:color .15s;
}
.pw-toggle:hover{color:var(--sub)}

/* Error */
.f-error{font-size:11.5px;color:#f87171;margin-top:5px;display:flex;align-items:center;gap:4px}

/* Checkbox */
.check-wrap{display:flex;align-items:center;gap:8px;margin-bottom:22px}
.check-wrap input[type=checkbox]{
  width:15px;height:15px;accent-color:var(--ac);cursor:pointer;
}
.check-wrap label{font-size:13px;color:var(--sub);cursor:pointer}

/* Submit button */
.btn-submit{
  width:100%;padding:11px;border-radius:12px;border:none;cursor:pointer;
  background:linear-gradient(135deg,var(--ac),var(--ac2));
  color:#fff;font-size:14px;font-weight:700;font-family:inherit;
  letter-spacing:.3px;transition:opacity .2s,transform .15s;
  display:flex;align-items:center;justify-content:center;gap:8px;
}
.btn-submit:hover{opacity:.92;transform:translateY(-1px)}
.btn-submit:active{transform:translateY(0)}

/* Forgot */
.forgot-link{
  display:block;text-align:center;margin-top:16px;
  font-size:12.5px;color:var(--muted);text-decoration:none;
  transition:color .15s;
}
.forgot-link:hover{color:var(--ac)}

/* Divider */
.divider{height:1px;background:var(--border);margin:24px 0}

/* Alert */
.alert{
  padding:10px 14px;border-radius:10px;font-size:12.5px;
  margin-bottom:20px;display:flex;align-items:flex-start;gap:8px;
}
.alert-success{background:rgba(16,185,129,.1);color:#34d399;border:1px solid rgba(16,185,129,.2)}
.alert-error{background:rgba(239,68,68,.1);color:#f87171;border:1px solid rgba(239,68,68,.2)}

/* Animation */
@keyframes fadeUp{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}

/* Background decoration */
body::before{
  content:'';position:fixed;inset:0;
  background:
    radial-gradient(ellipse 60% 50% at 20% 20%,rgba(245,158,11,.06) 0%,transparent 70%),
    radial-gradient(ellipse 50% 40% at 80% 80%,rgba(239,68,68,.05) 0%,transparent 70%);
  pointer-events:none;
}
</style>
</head>
<body>

<div class="login-card">

  <!-- Logo -->
  <div class="logo-wrap">
    <div class="logo-icon" style="background:none;padding:0;overflow:hidden"><img src="{{ asset('img/Logo Pabalu.png') }}" alt="Pabalu" style="width:100%;height:100%;object-fit:cover;border-radius:13px"></div>
    <div>
      <div class="font-display logo-name">Pa<span>balu</span></div>
      <div class="logo-sub">Sistem Manajemen UMKM</div>
    </div>
  </div>

  <h2 class="font-display login-title">Selamat Datang</h2>
  <p class="login-sub">Masuk ke akun Anda untuk melanjutkan</p>

  <!-- Session Status -->
  @if (session('status'))
    <div class="alert alert-success">
      <i class="fa-solid fa-circle-check" style="margin-top:1px;flex-shrink:0"></i>
      <span>{{ session('status') }}</span>
    </div>
  @endif

  <!-- Validation Errors -->
  @if ($errors->any())
    <div class="alert alert-error">
      <i class="fa-solid fa-circle-exclamation" style="margin-top:1px;flex-shrink:0"></i>
      <div>
        @foreach ($errors->all() as $error)
          <div>{{ $error }}</div>
        @endforeach
      </div>
    </div>
  @endif

  <form method="POST" action="{{ route('login') }}">
    @csrf

    <!-- Email -->
    <div class="f-group">
      <label for="email" class="f-label">Email</label>
      <div class="f-wrap">
        <i class="fa-solid fa-envelope f-icon"></i>
        <input
          id="email"
          type="email"
          name="email"
          class="f-input"
          placeholder="nama@email.com"
          value="{{ old('email') }}"
          required
          autofocus
          autocomplete="username"
        >
      </div>
    </div>

    <!-- Password -->
    <div class="f-group">
      <label for="password" class="f-label">Password</label>
      <div class="f-wrap">
        <i class="fa-solid fa-lock f-icon"></i>
        <input
          id="password"
          type="password"
          name="password"
          class="f-input"
          placeholder="••••••••"
          required
          autocomplete="current-password"
        >
        <button type="button" class="pw-toggle" onclick="togglePw()" id="pw-btn" aria-label="Tampilkan password">
          <i class="fa-solid fa-eye" id="pw-icon"></i>
        </button>
      </div>
    </div>

    <!-- Remember Me -->
    <div class="check-wrap">
      <input type="checkbox" id="remember_me" name="remember">
      <label for="remember_me">Ingat saya</label>
    </div>

    <!-- Submit -->
    <button type="submit" class="btn-submit">
      <i class="fa-solid fa-right-to-bracket"></i>
      Masuk
    </button>

    <!-- Forgot Password -->
    @if (Route::has('password.request'))
      <a href="{{ route('password.request') }}" class="forgot-link">
        Lupa password?
      </a>
    @endif

  </form>

  <div class="divider"></div>

  <div style="text-align:center;font-size:13px;color:var(--muted)">
    Punya usaha? <a href="{{ route('owner.register') }}" style="color:var(--ac);font-weight:600;text-decoration:none">Daftar sebagai Owner</a>
  </div>

</div>

<script>
function togglePw() {
  const input = document.getElementById('password');
  const icon  = document.getElementById('pw-icon');
  if (input.type === 'password') {
    input.type = 'text';
    icon.classList.replace('fa-eye', 'fa-eye-slash');
  } else {
    input.type = 'password';
    icon.classList.replace('fa-eye-slash', 'fa-eye');
  }
}
</script>
</body>
</html>
