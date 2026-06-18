@props(['outlet'])
@php
  $isRetail  = $outlet->rp() === 'retail';
  $menuUrl   = $isRetail
    ? route('public.katalog', $outlet->code)
    : route('public.menu', $outlet->code);
  $cardTitle = $isRetail ? 'QR Katalog Produk' : 'QR Menu Pelanggan';
  $btnLabel  = $isRetail ? 'Lihat Produk' : 'Buka Menu';
  $tipText   = $isRetail
    ? 'Cetak QR, laminating, lalu tempel di etalase atau kasir. Pelanggan scan → langsung lihat daftar produk & harga.'
    : 'Cetak QR, laminating, lalu tempel di meja atau pintu masuk. Pelanggan scan → langsung ke menu tanpa instal aplikasi.';
@endphp

<div class="card animate-fadeUp" style="margin-bottom:20px">
  <div class="card-header">
    <div class="card-title">
      <i class="fa-solid fa-qrcode a-text" style="margin-right:8px"></i>{{ $cardTitle }}
    </div>
  </div>
  <div class="card-body">
    <p style="font-size:13px;color:var(--muted);margin:0 0 18px;line-height:1.6">
      @if($isRetail)
        Bagikan QR ini kepada pelanggan untuk melihat daftar produk &amp; harga secara langsung.
      @else
        Bagikan QR code ini kepada pelanggan agar bisa memesan sendiri tanpa perlu login.
        Cocok dicetak dan ditempel di meja.
      @endif
    </p>

    <div style="display:flex;gap:20px;align-items:flex-start;flex-wrap:wrap">
      {{-- QR Code --}}
      <div style="flex-shrink:0;text-align:center">
        <div id="outlet-qr-{{ $outlet->id }}"
          style="width:160px;height:160px;background:#fff;border:1.5px solid var(--border);border-radius:14px;display:flex;align-items:center;justify-content:center;padding:10px">
          <div style="color:var(--muted);font-size:12px;text-align:center">
            <i class="fa-solid fa-spinner fa-spin" style="font-size:20px;display:block;margin-bottom:6px"></i>
            Memuat QR...
          </div>
        </div>
        <div style="margin-top:8px;font-size:11px;color:var(--muted);font-weight:600">
          Kode Outlet: <strong style="color:var(--text);font-size:13px;letter-spacing:2px">{{ $outlet->code }}</strong>
        </div>
      </div>

      {{-- Info & actions --}}
      <div style="flex:1;min-width:200px">
        <div style="margin-bottom:12px">
          <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:6px">{{ $isRetail ? 'Link Katalog' : 'Link Menu' }}</div>
          <div style="display:flex;gap:8px;align-items:center">
            <input type="text" readonly value="{{ $menuUrl }}"
              id="menu-url-{{ $outlet->id }}"
              style="flex:1;padding:8px 12px;border-radius:9px;border:1.5px solid var(--border);background:var(--surface2);color:var(--sub);font-size:12px;min-width:0;outline:none"
              onclick="this.select()">
            <button type="button"
              onclick="copyMenuUrl('{{ $outlet->id }}', '{{ $menuUrl }}')"
              style="flex-shrink:0;padding:8px 12px;border-radius:9px;border:1.5px solid var(--border);background:transparent;color:var(--sub);font-size:12px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:5px"
              id="copy-btn-{{ $outlet->id }}">
              <i class="fa-solid fa-copy" style="font-size:11px"></i> Salin
            </button>
          </div>
        </div>

        <div style="display:flex;gap:8px;flex-wrap:wrap">
          <a href="{{ $menuUrl }}" target="_blank"
            style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:9px;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:700;text-decoration:none">
            <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:11px"></i>{{ $btnLabel }}
          </a>
          <button type="button"
            onclick="printQr('{{ $outlet->id }}', {{ Js::from($outlet->name) }})"
            style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:9px;border:1.5px solid var(--border);background:transparent;color:var(--sub);font-size:13px;font-weight:700;cursor:pointer">
            <i class="fa-solid fa-print" style="font-size:11px"></i>Cetak QR
          </button>
        </div>

        <div style="margin-top:14px;padding:10px 12px;border-radius:10px;background:rgba(var(--ac-rgb),.06);border:1px solid rgba(var(--ac-rgb),.15)">
          <div style="font-size:12px;color:var(--sub);line-height:1.6">
            <i class="fa-solid fa-lightbulb" style="color:var(--ac);margin-right:5px"></i>
            <strong>Tips:</strong> {{ $tipText }}
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@once
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
function generateQr(containerId, url) {
  const el = document.getElementById(containerId);
  if (!el || el.dataset.qrDone) return;
  el.dataset.qrDone = '1';
  el.innerHTML = '';
  new QRCode(el, {
    text: url,
    width: 140,
    height: 140,
    colorDark: '#111827',
    colorLight: '#ffffff',
    correctLevel: QRCode.CorrectLevel.M,
  });
}

function copyMenuUrl(id, url) {
  navigator.clipboard.writeText(url).then(() => {
    const btn = document.getElementById('copy-btn-' + id);
    if (!btn) return;
    const orig = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-check" style="font-size:11px;color:#34d399"></i> Tersalin';
    setTimeout(() => btn.innerHTML = orig, 1800);
  });
}

function printQr(id, name) {
  const qrEl = document.getElementById('outlet-qr-' + id);
  const img   = qrEl?.querySelector('img');
  if (!img) { alert('QR belum dimuat.'); return; }

  const w = window.open('', '_blank', 'width=400,height=500');
  w.document.write(`<!DOCTYPE html><html><head><title>QR ${name}</title>
    <style>body{font-family:sans-serif;text-align:center;padding:40px}
    h2{font-size:18px;margin-bottom:6px}
    p{font-size:13px;color:#666;margin-bottom:20px}
    img{width:200px;height:200px;display:block;margin:0 auto}</style></head><body>
    <h2>${name}</h2>
    <p>Scan untuk memesan</p>
    <img src="${img.src}">
    <script>window.onload=()=>window.print()<\/script>
    </body></html>`);
  w.document.close();
}

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[id^="outlet-qr-"]').forEach(el => {
    const input = document.getElementById('menu-url-' + el.id.replace('outlet-qr-', ''));
    if (input) generateQr(el.id, input.value);
  });
});
</script>
@endpush
@endonce
