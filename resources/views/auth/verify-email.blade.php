<x-public-layout title="Verifikasi Akun — Pabalu">

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
  .auth-foot{padding:18px 32px 30px;text-align:center;border-top:1px solid #f5f3f3;margin-top:6px;font-size:13px;color:#6b7280;}
  .auth-foot a{color:var(--ac);font-weight:700;}

  .verify-icon-wrap{
    margin:28px 32px 0;
    width:64px;height:64px;border-radius:18px;
    background:linear-gradient(135deg,var(--ac),var(--ac2));
    display:grid;place-items:center;
    box-shadow:0 10px 28px -8px rgba(232,25,44,.4);
  }
  .verify-icon-wrap i{font-size:26px;color:#fff;}
  .verify-actions{padding:24px 32px 8px;display:flex;flex-direction:column;gap:12px;}
  .auth-submit{
    width:100%;padding:12px;border-radius:99px;border:none;color:#fff;font-size:14px;font-weight:700;
    cursor:pointer;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:8px;
    box-shadow:0 8px 20px rgba(232,25,44,.28);
  }
  .btn-logout{
    width:100%;padding:11px;border-radius:99px;border:1.5px solid var(--line);background:#fff;
    color:var(--ink-soft);font-size:13.5px;font-weight:600;cursor:pointer;font-family:inherit;
    display:flex;align-items:center;justify-content:center;gap:8px;transition:.2s;
  }
  .btn-logout:hover{border-color:#d1d5db;color:var(--ink);}

  @keyframes fadeUp{from{opacity:0;transform:translateY(16px);}to{opacity:1;transform:translateY(0);}}
  .animate-fadeUp{animation:fadeUp .5s ease both;}
@endpush

<section class="auth-wrap">
  <div class="container">
    <div class="auth-card animate-fadeUp">

      <div class="verify-icon-wrap">
        <i class="{{ $waPending ? 'fa-brands fa-whatsapp' : 'fa-solid fa-envelope-circle-check' }}"></i>
      </div>

      @if($waPending)
        <div class="auth-head" style="padding-top:20px">
          <h1>Masukkan Kode dari WhatsApp</h1>
          <p>
            Kami sudah mengirim kode verifikasi 6 digit ke nomor WhatsApp yang Anda
            daftarkan. Masukkan kodenya di bawah ini — berlaku 10 menit.
          </p>
        </div>

        @if (session('status') == 'wa-verification-sent')
          <div class="auth-status">
            <i class="fa-solid fa-circle-check"></i>
            Kode baru telah dikirim ke WhatsApp Anda.
          </div>
        @endif
        @if (session('wa_resend_error'))
          <div class="auth-status" style="background:#fef2f2;border-color:#fecaca;color:#b91c1c">
            <i class="fa-solid fa-triangle-exclamation"></i>
            {{ session('wa_resend_error') }}
          </div>
        @endif

        <div class="verify-actions">
          <form method="POST" action="{{ route('verification.whatsapp.verify') }}">
            @csrf
            <input type="text" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="6"
              placeholder="000000" required
              style="width:100%;padding:14px;border-radius:14px;border:1.5px solid #e5e7eb;font-size:22px;font-weight:700;letter-spacing:8px;text-align:center;margin-bottom:12px;font-family:inherit">
            @error('code')
              <p style="font-size:12.5px;color:#dc2626;margin:-6px 0 12px">{{ $message }}</p>
            @enderror
            <button type="submit" class="auth-submit a-grad">
              <i class="fa-solid fa-circle-check"></i> Verifikasi Kode
            </button>
          </form>

          <form method="POST" action="{{ route('verification.whatsapp.resend') }}">
            @csrf
            <button type="submit" class="btn-logout">
              <i class="fa-brands fa-whatsapp"></i> Kirim Ulang Kode WhatsApp
            </button>
          </form>
        </div>

        <div class="auth-foot" style="border-top:none;padding-top:0">
          Tidak menerima kode WhatsApp?
          <form method="POST" action="{{ route('verification.send') }}" style="display:inline">
            @csrf
            <button type="submit" style="background:none;border:none;padding:0;color:var(--ac);font-weight:700;font-size:13px;cursor:pointer;font-family:inherit">Verifikasi lewat email saja</button>
          </form>
        </div>
      @else
        <div class="auth-head" style="padding-top:20px">
          <h1>Verifikasi Email Anda</h1>
          <p>
            Terima kasih telah mendaftar! Sebelum mulai menggunakan aplikasi,
            silakan verifikasi alamat email Anda dengan mengklik tautan yang
            telah kami kirimkan. Jika belum menerima email, klik tombol di bawah
            untuk mengirim ulang.
          </p>
        </div>

        @if (session('status') == 'verification-link-sent')
          <div class="auth-status">
            <i class="fa-solid fa-circle-check"></i>
            Tautan verifikasi baru telah dikirim ke alamat email Anda.
          </div>
        @endif

        <div class="verify-actions">
          <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="auth-submit a-grad">
              <i class="fa-solid fa-paper-plane"></i> Kirim Ulang Email Verifikasi
            </button>
          </form>
        </div>
      @endif

      <div class="verify-actions" style="padding-top:0">
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="btn-logout">
            <i class="fa-solid fa-right-from-bracket"></i> Keluar dari Akun
          </button>
        </form>
      </div>

      <div class="auth-foot">
        Sudah verifikasi?
        <a href="{{ route('login') }}">Masuk di sini</a>
      </div>

    </div>
  </div>
</section>

</x-public-layout>
