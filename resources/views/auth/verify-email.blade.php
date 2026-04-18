<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verifikasi Email — Pabalu</title>
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
    body::before{
      content:'';position:fixed;inset:0;
      background:
        radial-gradient(ellipse 60% 50% at 20% 20%,rgba(245,158,11,.06) 0%,transparent 70%),
        radial-gradient(ellipse 50% 40% at 80% 80%,rgba(239,68,68,.05) 0%,transparent 70%);
      pointer-events:none;
    }
    .wrap{width:100%;max-width:420px;position:relative}

    .logo{display:flex;align-items:center;gap:12px;justify-content:center;margin-bottom:32px}
    .logo-icon{
      width:44px;height:44px;border-radius:12px;
      background:linear-gradient(135deg,#f59e0b,#ef4444);
      display:grid;place-items:center;font-size:18px;color:#fff;
    }
    .logo-text{font-family:'Clash Display',sans-serif;font-size:22px;font-weight:700;color:#e2e8f0}
    .logo-text span{color:#f59e0b}

    .card{
      background:#161b27;border:1px solid #252d42;border-radius:20px;
      padding:36px 32px;box-shadow:0 24px 64px rgba(0,0,0,.5);text-align:center;
    }

    .icon-wrap{
      width:64px;height:64px;border-radius:18px;margin:0 auto 20px;
      background:rgba(245,158,11,.12);border:1px solid rgba(245,158,11,.25);
      display:grid;place-items:center;font-size:26px;color:#f59e0b;
    }

    .card-title{
      font-family:'Clash Display',sans-serif;font-size:20px;font-weight:700;
      color:#e2e8f0;margin-bottom:8px;
    }
    .card-desc{font-size:13.5px;color:#94a3b8;line-height:1.6;margin-bottom:28px}
    .card-desc strong{color:#e2e8f0;font-weight:600}

    .alert-success{
      background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.25);
      border-radius:12px;padding:12px 16px;font-size:13px;color:#34d399;
      margin-bottom:20px;display:flex;align-items:center;gap:8px;
    }

    .btn{
      display:inline-flex;align-items:center;justify-content:center;gap:8px;
      width:100%;padding:11px;border-radius:12px;border:none;cursor:pointer;
      font-size:14px;font-weight:700;font-family:inherit;
      transition:opacity .15s;
    }
    .btn-primary{
      background:linear-gradient(135deg,#f59e0b,#ef4444);color:#fff;margin-bottom:12px;
    }
    .btn-primary:hover{opacity:.9}
    .btn-ghost{
      background:transparent;border:1px solid #252d42;color:#64748b;
      font-size:13px;
    }
    .btn-ghost:hover{color:#94a3b8;border-color:#334155}

    .divider{height:1px;background:#252d42;margin:20px 0}
    .hint{font-size:12px;color:#475569;line-height:1.5}
  </style>
</head>
<body>
<div class="wrap">

  <div class="logo">
    <div class="logo-icon"><i class="fa-solid fa-store"></i></div>
    <div class="logo-text">Pa<span>balu</span></div>
  </div>

  <div class="card">
    <div class="icon-wrap">
      <i class="fa-solid fa-envelope-open-text"></i>
    </div>

    <div class="card-title">Verifikasi Email Anda</div>
    <div class="card-desc">
      Kami telah mengirim link verifikasi ke<br>
      <strong>{{ auth()->user()->email }}</strong><br><br>
      Silakan cek inbox (atau folder spam) dan klik link tersebut untuk mengaktifkan akun Anda.
    </div>

    @if (session('status') == 'verification-link-sent')
    <div class="alert-success">
      <i class="fa-solid fa-circle-check" style="flex-shrink:0"></i>
      <span>Link verifikasi baru telah dikirim ke email Anda.</span>
    </div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}">
      @csrf
      <button type="submit" class="btn btn-primary">
        <i class="fa-solid fa-paper-plane"></i>
        Kirim Ulang Email Verifikasi
      </button>
    </form>

    <div class="divider"></div>

    <div class="hint" style="margin-bottom:14px">
      Sudah verifikasi tapi tetap di halaman ini? Coba refresh halaman.
    </div>

    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="btn btn-ghost">
        <i class="fa-solid fa-arrow-right-from-bracket"></i>
        Keluar
      </button>
    </form>
  </div>

</div>
</body>
</html>
