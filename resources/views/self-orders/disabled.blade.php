<x-outlet-layout :outlet="$outlet" pageTitle="Pesanan Masuk">

<div style="max-width:520px;margin:60px auto;text-align:center;display:flex;flex-direction:column;align-items:center;gap:18px">
  <div style="width:72px;height:72px;border-radius:20px;background:rgba(var(--ac-rgb),.12);color:var(--ac);display:grid;place-items:center;font-size:30px">
    <i class="fa-solid fa-qrcode"></i>
  </div>
  <div>
    <h1 class="font-display" style="font-size:19px;font-weight:700;color:var(--text);margin-bottom:8px">
      Self-Order Belum Aktif
    </h1>
    <p style="font-size:13.5px;color:var(--muted);line-height:1.6;margin:0">
      Dengan self-order, pelanggan bisa memesan sendiri lewat QR menu, lalu pesanannya masuk ke halaman ini untuk ditinjau.
      @if($settingsUrl)
        Aktifkan fiturnya di Pengaturan outlet.
      @else
        Minta owner mengaktifkannya di Pengaturan outlet.
      @endif
    </p>
  </div>
  <div style="display:flex;gap:10px;flex-wrap:wrap;justify-content:center;margin-top:4px">
    @if($settingsUrl)
    <a href="{{ $settingsUrl }}"
       style="display:inline-flex;align-items:center;gap:7px;padding:10px 20px;border-radius:10px;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:600;text-decoration:none">
      <i class="fa-solid fa-gear"></i>Buka Pengaturan
    </a>
    @endif
    <a href="{{ $outlet->route('show') }}"
       style="display:inline-flex;align-items:center;gap:7px;padding:10px 20px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13px;font-weight:600;text-decoration:none">
      <i class="fa-solid fa-arrow-left"></i>Kembali ke Dashboard Toko
    </a>
  </div>
</div>

</x-outlet-layout>
