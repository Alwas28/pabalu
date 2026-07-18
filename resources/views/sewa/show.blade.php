<x-app-layout>
  <x-slot name="header">
    <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap">
      <div style="width:40px;height:40px;border-radius:12px;background:var(--ac-lt);color:var(--ac);
                  display:grid;place-items:center;flex-shrink:0">
        <i class="fa-solid fa-key" style="font-size:16px"></i>
      </div>
      <div>
        <h2 class="font-display" style="font-size:22px;color:var(--text);margin:0">
          {{ $outlet->name }}
        </h2>
        <p style="font-size:13px;color:var(--muted);margin:3px 0 0">
          Jasa Sewa
          @if($outlet->code)
          · <span style="font-family:monospace">#{{ $outlet->code }}</span>
          @endif
        </p>
      </div>
    </div>
  </x-slot>

  <div class="py-6">
  <div style="max-width:760px;margin:0 auto;padding:0 20px">

    {{-- Info Outlet --}}
    <div class="card animate-fadeUp" style="margin-bottom:20px">
      <div style="padding:24px">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:18px">
          <i class="fa-solid fa-circle-info a-text" style="font-size:15px"></i>
          <span style="font-size:15px;font-weight:700;color:var(--text)">Informasi Outlet</span>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:14px">

          @if($outlet->address)
          <div style="display:flex;align-items:flex-start;gap:10px;font-size:13px">
            <i class="fa-solid fa-location-dot" style="color:var(--ac);margin-top:2px;flex-shrink:0;width:14px;text-align:center"></i>
            <span style="color:var(--text);line-height:1.5">{{ $outlet->address }}</span>
          </div>
          @endif

          @if($outlet->phone)
          <div style="display:flex;align-items:center;gap:10px;font-size:13px">
            <i class="fa-solid fa-phone" style="color:var(--ac);flex-shrink:0;width:14px;text-align:center"></i>
            <span style="color:var(--text)">{{ $outlet->phone }}</span>
          </div>
          @endif

          <div style="display:flex;align-items:center;gap:10px;font-size:13px">
            <i class="fa-solid fa-circle" style="font-size:9px;flex-shrink:0;width:14px;text-align:center;
               color:{{ $outlet->is_active ? '#34d399' : '#f87171' }}"></i>
            <span style="color:var(--text)">{{ $outlet->is_active ? 'Outlet Aktif' : 'Outlet Nonaktif' }}</span>
          </div>

          <div style="display:flex;align-items:center;gap:10px;font-size:13px">
            <i class="fa-regular fa-calendar" style="color:var(--ac);flex-shrink:0;width:14px;text-align:center"></i>
            <span style="color:var(--text)">Dibuat {{ $outlet->created_at->translatedFormat('d F Y') }}</span>
          </div>
        </div>
      </div>
    </div>

    {{-- Fitur belum tersedia --}}
    <div class="card animate-fadeUp" style="text-align:center;padding:52px 24px">
      <div style="width:72px;height:72px;border-radius:50%;background:var(--ac-lt);color:var(--ac);
                  display:grid;place-items:center;margin:0 auto 20px;font-size:28px">
        <i class="fa-solid fa-hammer"></i>
      </div>
      <div style="font-size:18px;font-weight:800;color:var(--text);margin-bottom:8px;font-family:'Clash Display',sans-serif">
        Fitur Sedang Dikembangkan
      </div>
      <div style="font-size:13.5px;color:var(--muted);line-height:1.7;max-width:400px;margin:0 auto">
        Dashboard lengkap untuk outlet <strong style="color:var(--text)">Jasa Sewa</strong> sedang dalam tahap pembangunan.
        Fitur transaksi, manajemen produk, dan laporan akan segera tersedia.
      </div>
      <div style="margin-top:24px;display:flex;gap:10px;justify-content:center;flex-wrap:wrap">
        <a href="{{ route('dashboard') }}"
           style="padding:10px 20px;border-radius:10px;background:var(--ac);color:#fff;
                  text-decoration:none;font-size:13px;font-weight:700;display:flex;align-items:center;gap:7px">
          <i class="fa-solid fa-house"></i> Kembali ke Dashboard
        </a>
        <a href="{{ route('profile.edit') }}"
           style="padding:10px 20px;border-radius:10px;background:var(--surface2);color:var(--text);
                  text-decoration:none;font-size:13px;font-weight:600;display:flex;align-items:center;gap:7px">
          <i class="fa-solid fa-user-gear"></i> Pengaturan Akun
        </a>
      </div>
    </div>

  </div>
  </div>
</x-app-layout>
