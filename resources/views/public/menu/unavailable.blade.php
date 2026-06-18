<x-public-menu-layout :title="$outlet->name">
<x-slot name="styles">
<style>
*{-webkit-tap-highlight-color:transparent}
#app{display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:100vh;max-width:430px;margin:0 auto;background:#f5f5f7;padding:32px 24px;text-align:center}
.ua-icon{width:80px;height:80px;border-radius:50%;background:#fff;display:grid;place-items:center;font-size:32px;color:#94a3b8;margin:0 auto 20px;box-shadow:0 4px 16px rgba(0,0,0,.08)}
.ua-title{font-size:20px;font-weight:800;color:#111;margin-bottom:8px}
.ua-sub{font-size:14px;color:#888;line-height:1.6}
.ua-outlet{display:inline-flex;align-items:center;gap:8px;margin-top:20px;padding:10px 18px;background:#fff;border-radius:12px;font-size:13.5px;font-weight:700;color:#555;box-shadow:0 2px 8px rgba(0,0,0,.07)}
</style>
</x-slot>
<div id="app">
  <div class="ua-icon"><i class="fa-solid fa-ban"></i></div>
  <div class="ua-title">Pemesanan Tidak Tersedia</div>
  <div class="ua-sub">
    Fitur pesan mandiri hanya tersedia untuk outlet F&amp;B (restoran, kafe, dan sejenisnya).<br><br>
    Silakan pesan langsung ke kasir.
  </div>
  <div class="ua-outlet">
    <i class="fa-solid {{ $outlet->outletType?->icon ?? 'fa-shop' }}" style="color:#94a3b8"></i>
    {{ $outlet->name }}
  </div>
</div>
</x-public-menu-layout>
