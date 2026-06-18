{{--
  Komponen barcode/QR scanner.
  Mendukung dua mode:
    1. Hardware scanner (USB/Bluetooth keyboard-wedge) — deteksi ketikan cepat + Enter
    2. Kamera (html5-qrcode) — tombol kamera membuka modal viewfinder

  Cara pakai di halaman:
    <x-barcode-scanner />
    <script>
      document.addEventListener('bscan', e => {
        const sku = e.detail; // string SKU / barcode yang discan
        // ... logika halaman
      });
    </script>

  Event yang di-dispatch: CustomEvent('bscan', { detail: sku_string })
--}}

{{-- ── Scan bar (selalu terlihat) ── --}}
<div id="bs-bar"
  style="display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:14px;border:2px solid var(--border);background:var(--surface);transition:border-color .2s"
  id="bs-bar">
  <i class="fa-solid fa-barcode" style="color:var(--muted);font-size:16px;flex-shrink:0"></i>
  <input id="bs-input" type="text" autocomplete="off" spellcheck="false"
    placeholder="Scan barcode / QR di sini, atau ketik manual + Enter…"
    style="flex:1;border:none;background:transparent;outline:none;font-size:13.5px;color:var(--text);font-family:inherit;min-width:0"
    onfocus="document.getElementById('bs-bar').style.borderColor='var(--ac)'"
    onblur="document.getElementById('bs-bar').style.borderColor='var(--border)'">
  <button type="button" id="bs-cam-btn" title="Scan dengan kamera"
    onclick="bsOpenCamera()"
    style="width:34px;height:34px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:14px;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:all .15s"
    onmouseover="this.style.background='var(--ac-lt)';this.style.color='var(--ac)';this.style.borderColor='var(--ac)'"
    onmouseout="this.style.background='var(--surface2)';this.style.color='var(--sub)';this.style.borderColor='var(--border)'">
    <i class="fa-solid fa-camera"></i>
  </button>
</div>

{{-- ── Modal kamera ── --}}
<div id="bs-modal" style="display:none;position:fixed;inset:0;z-index:500;background:rgba(0,0,0,.75);backdrop-filter:blur(4px);align-items:center;justify-content:center;padding:20px"
  onclick="if(event.target===this)bsCloseCamera()">
  <div style="background:var(--surface);border:1px solid var(--border);border-radius:20px;width:100%;max-width:420px;overflow:hidden;box-shadow:0 24px 64px rgba(0,0,0,.5)">

    {{-- Header --}}
    <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
      <div style="display:flex;align-items:center;gap:10px">
        <div style="width:32px;height:32px;border-radius:9px;background:var(--ac-lt);color:var(--ac);display:grid;place-items:center;font-size:14px">
          <i class="fa-solid fa-camera"></i>
        </div>
        <span class="font-display" style="font-size:15px;font-weight:700;color:var(--text)">Scan Barcode / QR</span>
      </div>
      <button type="button" onclick="bsCloseCamera()"
        style="width:30px;height:30px;border-radius:8px;border:none;background:var(--surface2);cursor:pointer;color:var(--sub);font-size:14px;display:flex;align-items:center;justify-content:center">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    {{-- Viewfinder --}}
    <div style="position:relative;background:#000">
      <div id="bs-reader" style="width:100%"></div>
      <div id="bs-cam-status" style="padding:10px 16px;font-size:12.5px;color:var(--muted);text-align:center;background:var(--surface)">
        Memuat kamera…
      </div>
    </div>

    {{-- Hint --}}
    <div style="padding:14px 20px;border-top:1px solid var(--border)">
      <p style="font-size:12px;color:var(--muted);text-align:center;line-height:1.6">
        Arahkan kamera ke barcode / QR produk.<br>
        Mendukung EAN-13, Code128, QR Code, UPC, dll.
      </p>
    </div>
  </div>
</div>

{{-- ── Script ── --}}
@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
(function () {

  /* ── dispatch helper ── */
  function emitScan(sku) {
    sku = (sku || '').trim();
    if (!sku) return;
    document.dispatchEvent(new CustomEvent('bscan', { detail: sku }));
    // Feedback visual di input bar
    const inp = document.getElementById('bs-input');
    inp.value = sku;
    inp.style.color = 'var(--ac)';
    setTimeout(() => { inp.value = ''; inp.style.color = 'var(--text)'; }, 1200);
  }

  /* ────────────────────────────────────────────
     1. Hardware scanner (keyboard-wedge)
     Deteksi: ketikan cepat (< 60 ms antar karakter) + Enter
  ──────────────────────────────────────────── */
  const inp = document.getElementById('bs-input');

  // Selalu auto-fokus ke scan input saat pengguna TIDAK sedang mengetik di field lain
  document.addEventListener('keydown', e => {
    const tag = document.activeElement?.tagName;
    const isEditable = tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT'
      || document.activeElement?.isContentEditable;
    if (!isEditable && e.key.length === 1) {
      inp.focus();
    }
  });

  inp.addEventListener('keydown', e => {
    if (e.key === 'Enter') {
      e.preventDefault();
      const val = inp.value.trim();
      if (val.length >= 2) emitScan(val);
      inp.value = '';
    }
  });

  // Deteksi hardware scanner: ketikan sangat cepat
  let hwBuffer = '', hwLast = 0;
  document.addEventListener('keypress', e => {
    // Abaikan jika input/textarea lain sedang fokus (bukan scan input)
    const active = document.activeElement;
    if (active && active !== inp &&
        (active.tagName === 'INPUT' || active.tagName === 'TEXTAREA' || active.tagName === 'SELECT')) {
      return;
    }

    const now = Date.now();
    if (now - hwLast > 120) hwBuffer = ''; // reset jika jeda terlalu lama
    hwLast = now;

    if (e.key === 'Enter') {
      if (hwBuffer.length >= 2) { emitScan(hwBuffer); }
      hwBuffer = '';
    } else {
      hwBuffer += e.key;
    }
  });

  /* ────────────────────────────────────────────
     2. Kamera scanner (html5-qrcode)
  ──────────────────────────────────────────── */
  let html5Qr = null;
  let scanning = false;

  window.bsOpenCamera = function () {
    const modal = document.getElementById('bs-modal');
    modal.style.display = 'flex';
    document.getElementById('bs-cam-status').textContent = 'Memuat kamera…';

    if (!html5Qr) {
      html5Qr = new Html5Qrcode('bs-reader');
    }

    Html5Qrcode.getCameras()
      .then(cameras => {
        if (!cameras || cameras.length === 0) {
          document.getElementById('bs-cam-status').textContent = 'Kamera tidak ditemukan.';
          return;
        }
        // Pilih kamera belakang jika ada
        const cam = cameras.find(c => /back|rear|environment/i.test(c.label)) || cameras[cameras.length - 1];
        document.getElementById('bs-cam-status').textContent = '';
        return html5Qr.start(
          cam.id,
          { fps: 10, qrbox: { width: 280, height: 180 }, aspectRatio: 1.4 },
          (decodedText) => {
            emitScan(decodedText);
            bsCloseCamera();
          },
          () => {} // scan failure — abaikan
        );
      })
      .catch(err => {
        document.getElementById('bs-cam-status').textContent =
          'Tidak bisa akses kamera: ' + (err.message || err);
      });

    scanning = true;
  };

  window.bsCloseCamera = function () {
    document.getElementById('bs-modal').style.display = 'none';
    if (html5Qr && scanning) {
      html5Qr.stop().catch(() => {});
      scanning = false;
    }
  };

})();
</script>
@endpush
