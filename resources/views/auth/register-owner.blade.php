<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar Sebagai Owner — Pabalu</title>
  <link href="https://fonts.googleapis.com/css2?family=Clash+Display:wght@400;600;700&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{
      font-family:'Plus Jakarta Sans',sans-serif;
      background:#0f1117;color:#e2e8f0;
      min-height:100vh;display:flex;align-items:center;justify-content:center;
      padding:24px 16px;
    }
    .wrap{width:100%;max-width:440px}

    /* Logo */
    .logo{display:flex;align-items:center;gap:12px;justify-content:center;margin-bottom:32px}
    .logo-icon{
      width:44px;height:44px;border-radius:12px;overflow:hidden;flex-shrink:0;
    }
    .logo-icon img{width:100%;height:100%;object-fit:cover}
    .logo-text{font-family:'Clash Display',sans-serif;font-size:22px;font-weight:700;color:#e2e8f0}
    .logo-text span{color:#E8000D}

    /* Card */
    .card{
      background:#161b27;border:1px solid #252d42;border-radius:20px;
      padding:32px;box-shadow:0 24px 64px rgba(0,0,0,.5);
    }
    .card-title{
      font-family:'Clash Display',sans-serif;font-size:20px;font-weight:700;
      color:#e2e8f0;margin-bottom:4px;
    }
    .card-sub{font-size:13px;color:#64748b;margin-bottom:28px}

    /* Form */
    .f-group{margin-bottom:18px}
    .f-label{display:block;font-size:12px;font-weight:600;color:#94a3b8;margin-bottom:6px;letter-spacing:.3px}
    .f-input{
      width:100%;background:#1c2336;border:1px solid #252d42;color:#e2e8f0;
      border-radius:12px;padding:10px 14px;font-size:13.5px;font-family:inherit;
      outline:none;transition:border-color .15s,box-shadow .15s;
    }
    .f-input:focus{border-color:#f59e0b;box-shadow:0 0 0 3px rgba(245,158,11,.14)}
    .f-input::placeholder{color:#475569}
    .f-error{font-size:11.5px;color:#f87171;margin-top:5px;display:flex;align-items:center;gap:4px}

    /* Button */
    .btn-submit{
      width:100%;padding:11px;border-radius:12px;border:none;
      background:linear-gradient(135deg,#f59e0b,#ef4444);color:#fff;
      font-size:14px;font-weight:700;cursor:pointer;font-family:inherit;
      transition:opacity .15s;margin-top:8px;
    }
    .btn-submit:hover{opacity:.9}

    /* Divider */
    .divider{display:flex;align-items:center;gap:12px;margin:20px 0;color:#334155;font-size:12px}
    .divider::before,.divider::after{content:'';flex:1;height:1px;background:#252d42}

    /* Features */
    .features{display:flex;flex-direction:column;gap:10px;margin-bottom:24px}
    .feat{display:flex;align-items:center;gap:10px;font-size:13px;color:#94a3b8}
    .feat-icon{
      width:28px;height:28px;border-radius:8px;
      background:rgba(245,158,11,.12);color:#f59e0b;
      display:grid;place-items:center;font-size:11px;flex-shrink:0;
    }

    /* Login link */
    .login-link{text-align:center;margin-top:20px;font-size:13px;color:#64748b}
    .login-link a{color:#f59e0b;text-decoration:none;font-weight:600}
    .login-link a:hover{text-decoration:underline}

    /* Alert */
    .alert-error{
      background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);
      border-radius:12px;padding:12px 16px;font-size:13px;color:#f87171;
      margin-bottom:20px;display:flex;align-items:flex-start;gap:8px;
    }
  </style>
</head>
<body>
<div class="wrap">

  <div class="logo">
    <div class="logo-icon"><img src="{{ asset('img/Logo Pabalu.png') }}" alt="Pabalu"></div>
    <div class="logo-text">Pa<span>balu</span></div>
  </div>

  <div class="card">
    <div class="card-title">Daftar sebagai Owner</div>
    <div class="card-sub">Kelola outlet dan tim Anda dalam satu platform</div>

    {{-- Fitur singkat --}}
    <div class="features">
      <div class="feat">
        <div class="feat-icon"><i class="fa-solid fa-shop"></i></div>
        <span>Buat dan kelola outlet sendiri</span>
      </div>
      <div class="feat">
        <div class="feat-icon"><i class="fa-solid fa-cash-register"></i></div>
        <span>Kasir POS & order online per outlet</span>
      </div>
      <div class="feat">
        <div class="feat-icon"><i class="fa-solid fa-chart-line"></i></div>
        <span>Laporan penjualan & stok real-time</span>
      </div>
    </div>

    @if($errors->any())
    <div class="alert-error">
      <i class="fa-solid fa-circle-exclamation" style="margin-top:2px;flex-shrink:0"></i>
      <div>{{ $errors->first() }}</div>
    </div>
    @endif

    <form method="POST" action="{{ route('owner.register.store') }}">
      @csrf

      <div class="f-group">
        <label class="f-label" for="name">Nama Lengkap</label>
        <input id="name" type="text" name="name" class="f-input"
          value="{{ old('name') }}" placeholder="Nama Anda" required autofocus>
        @error('name')<div class="f-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>@enderror
      </div>

      <div class="f-group">
        <label class="f-label" for="email">Email</label>
        <input id="email" type="email" name="email" class="f-input"
          value="{{ old('email') }}" placeholder="email@usaha.com" required>
        @error('email')<div class="f-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>@enderror
      </div>

      <div class="f-group">
        <label class="f-label" for="password">Password</label>
        <input id="password" type="password" name="password" class="f-input"
          placeholder="Minimal 8 karakter" required>
        @error('password')<div class="f-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>@enderror
      </div>

      <div class="f-group">
        <label class="f-label" for="password_confirmation">Konfirmasi Password</label>
        <input id="password_confirmation" type="password" name="password_confirmation"
          class="f-input" placeholder="Ulangi password" required>
      </div>

      <button type="submit" class="btn-submit">
        <i class="fa-solid fa-user-plus" style="margin-right:6px"></i>Buat Akun
      </button>
    </form>

    <div class="login-link">
      Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a>
    </div>

  </div>

</div>
</body>
</html>
