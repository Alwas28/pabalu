<!DOCTYPE html>
<html lang="id" id="html-root">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Akun Tidak Aktif — Pabalu</title>
<link href="https://fonts.googleapis.com/css2?family=Clash+Display:wght@400;600;700&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Plus Jakarta Sans',sans-serif;background:#0f1117;color:#e2e8f0;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px}
.box{background:#161b27;border:1px solid #252d42;border-radius:24px;padding:48px 40px;max-width:500px;width:100%;text-align:center}
.icon{width:80px;height:80px;border-radius:24px;display:grid;place-items:center;font-size:34px;margin:0 auto 28px}
h1{font-family:'Clash Display',sans-serif;font-size:24px;font-weight:700;margin-bottom:12px}
p{font-size:14px;color:#94a3b8;line-height:1.7;margin-bottom:8px}
.divider{height:1px;background:#252d42;margin:28px 0}
.contact-label{font-size:11px;font-weight:600;letter-spacing:1px;text-transform:uppercase;color:#64748b;margin-bottom:14px}
.contact-item{display:flex;align-items:center;justify-content:center;gap:10px;font-size:14px;color:#e2e8f0;font-weight:500;margin-bottom:8px}
.contact-item i{color:#f59e0b;width:16px;text-align:center}
.btn-logout{margin-top:28px;display:inline-flex;align-items:center;gap:8px;padding:10px 24px;border-radius:11px;border:1px solid #252d42;background:#1c2336;color:#94a3b8;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;text-decoration:none;transition:color .15s}
.btn-logout:hover{color:#e2e8f0}
.badge-exp{display:inline-flex;align-items:center;gap:6px;padding:4px 14px;border-radius:99px;font-size:12px;font-weight:600;margin-bottom:20px}
</style>
</head>
<body>
@php $reason = request('reason', 'expired'); @endphp
@php $user = auth()->user(); @endphp
@php $owner = $user->ownerAccount(); @endphp

<div class="box">

  @if($reason === 'inactive')
  {{-- NONAKTIF --}}
  <div class="icon" style="background:rgba(239,68,68,.15);color:#f87171">
    <i class="fa-solid fa-ban"></i>
  </div>
  <div class="badge-exp" style="background:rgba(239,68,68,.12);color:#f87171">
    <i class="fa-solid fa-circle-xmark"></i> Akun Dinonaktifkan
  </div>
  <h1 style="color:#f87171">Akun Anda Dinonaktifkan</h1>
  <p>Akun <strong style="color:#e2e8f0">{{ $owner?->name ?? $user->name }}</strong> telah dinonaktifkan oleh administrator Pabalu.</p>
  <p>Jika Anda merasa ini adalah kesalahan, silakan hubungi admin untuk informasi lebih lanjut.</p>

  @else
  {{-- TRIAL EXPIRED --}}
  <div class="icon" style="background:rgba(245,158,11,.12);color:#f59e0b">
    <i class="fa-solid fa-clock"></i>
  </div>
  <div class="badge-exp" style="background:rgba(245,158,11,.1);color:#f59e0b">
    <i class="fa-solid fa-hourglass-end"></i> Trial Berakhir
  </div>
  <h1 style="color:#f59e0b">Masa Trial Telah Berakhir</h1>
  @if($owner?->trial_ends_at)
  <p>Trial Anda berakhir pada <strong style="color:#e2e8f0">{{ $owner->trial_ends_at->translatedFormat('d F Y') }}</strong>.</p>
  @endif
  <p>Untuk melanjutkan akses ke semua fitur Pabalu, tingkatkan akun Anda ke <strong style="color:#e2e8f0">Premium</strong>.</p>
  @endif

  <div class="divider"></div>

  <div class="contact-label">Hubungi Admin Pabalu</div>
  <div class="contact-item"><i class="fa-solid fa-envelope"></i> admin@pabalu.com</div>
  <div class="contact-item"><i class="fa-brands fa-whatsapp"></i> +62 812-3456-7890</div>
  <div class="contact-item"><i class="fa-solid fa-instagram"></i> @pabalu.id</div>

  <div style="margin-top:8px;font-size:12.5px;color:#64748b">
    Sebutkan nama akun dan email Anda saat menghubungi admin.
  </div>

  <form method="POST" action="{{ route('logout') }}" style="display:inline">
    @csrf
    <button type="submit" class="btn-logout">
      <i class="fa-solid fa-right-from-bracket"></i> Keluar dari Akun
    </button>
  </form>

</div>
</body>
</html>
