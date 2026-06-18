<x-setup-layout :step="3" pageTitle="Setup Selesai">

<div class="card animate-fadeUp" style="width:100%;max-width:460px;text-align:center">
  <div style="padding:40px 32px;display:flex;flex-direction:column;align-items:center;gap:20px">

    {{-- Animated check circle --}}
    <div class="animate-pop" style="width:80px;height:80px">
      <svg viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="40" cy="40" r="38" fill="rgba(16,185,129,.15)" stroke="#10b981" stroke-width="2"/>
        <path d="M24 40.5L35.5 52L57 30" stroke="#10b981" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"
          stroke-dasharray="45" stroke-dashoffset="45" style="animation:drawCheck .4s .3s ease forwards"/>
      </svg>
    </div>

    <div>
      <h2 class="font-display" style="font-size:22px;font-weight:700;color:var(--text)">Setup Selesai!</h2>
      <p style="font-size:13.5px;color:var(--muted);margin-top:8px;line-height:1.6">
        Akun Anda sudah siap digunakan. Selamat menggunakan <strong style="color:var(--text)">Pabalu</strong> untuk mengelola bisnis Anda.
      </p>
    </div>

    {{-- Summary --}}
    @if($outlet)
    <div style="width:100%;background:var(--surface2);border:1px solid var(--border);border-radius:12px;padding:16px;text-align:left;display:flex;flex-direction:column;gap:10px">
      <div style="font-size:11px;font-weight:600;letter-spacing:1px;text-transform:uppercase;color:var(--muted)">Ringkasan Akun</div>

      <div style="display:flex;align-items:center;gap:10px">
        <i class="fa-solid fa-user-tie" style="width:16px;text-align:center;color:var(--muted);font-size:12px"></i>
        <div>
          <div style="font-size:12px;color:var(--muted)">Pemilik</div>
          <div style="font-size:13px;font-weight:600;color:var(--text)">{{ $user->name }}</div>
        </div>
      </div>

      @if($user->business_name)
      <div style="display:flex;align-items:center;gap:10px">
        <i class="fa-solid fa-briefcase" style="width:16px;text-align:center;color:var(--muted);font-size:12px"></i>
        <div>
          <div style="font-size:12px;color:var(--muted)">Nama Bisnis</div>
          <div style="font-size:13px;font-weight:600;color:var(--text)">{{ $user->business_name }}</div>
        </div>
      </div>
      @endif

      <div style="display:flex;align-items:center;gap:10px">
        <i class="fa-solid fa-store" style="width:16px;text-align:center;color:var(--muted);font-size:12px"></i>
        <div>
          <div style="font-size:12px;color:var(--muted)">Outlet Pertama</div>
          <div style="font-size:13px;font-weight:600;color:var(--text)">{{ $outlet->name }}</div>
          @if($outlet->outletType)
          <div style="font-size:11.5px;color:var(--muted)">{{ $outlet->outletType->name }}</div>
          @endif
        </div>
      </div>
    </div>
    @endif

    <a href="{{ route('dashboard') }}" class="a-grad"
      style="width:100%;padding:12px;border-radius:12px;border:none;color:#fff;font-size:14px;font-weight:600;cursor:pointer;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:9px;text-decoration:none">
      <i class="fa-solid fa-rocket"></i> Mulai Gunakan Pabalu
    </a>

  </div>
</div>

</x-setup-layout>
