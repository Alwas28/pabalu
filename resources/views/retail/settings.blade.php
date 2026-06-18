@php
  $pageLabel  = $outlet->isRetailFlow() ? 'Pengaturan Toko' : 'Pengaturan Outlet';
@endphp
<x-outlet-layout :outlet="$outlet" :pageTitle="$pageLabel">

@php
  $isOwner    = Auth::user()->role === 'owner' || Auth::user()->role === 'admin';
  $isRetail   = $outlet->isRetailFlow();
@endphp

<x-outlet-menu-qr :outlet="$outlet" />

<form method="POST" action="{{ $outlet->route('settings.update') }}">
@csrf
@method('PATCH')

@if($isRetail)
{{-- Retail selalu Quick Pay — kirim hidden agar validasi controller tidak gagal --}}
<input type="hidden" name="order_mode" value="quick">
@else
{{-- Mode Transaksi (hanya untuk outlet non-retail) --}}
<div class="card animate-fadeUp" style="margin-bottom:20px">
  <div class="card-header">
    <div class="card-title">
      <i class="fa-solid fa-bolt a-text" style="margin-right:8px"></i>Mode Transaksi
    </div>
  </div>
  <div class="card-body">
    <p style="font-size:13px;color:var(--muted);margin:0 0 16px">
      Pilih cara kasir memproses pesanan di outlet ini.
    </p>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;max-width:520px">

      <label id="lbl-quick"
        style="display:flex;align-items:flex-start;gap:12px;padding:14px 16px;border-radius:12px;border:2px solid {{ $outlet->order_mode !== 'kitchen' ? 'var(--ac)' : 'var(--border)' }};cursor:pointer;transition:border-color .15s;background:{{ $outlet->order_mode !== 'kitchen' ? 'var(--ac-lt)' : 'transparent' }}">
        <input type="radio" name="order_mode" value="quick"
          {{ $outlet->order_mode !== 'kitchen' ? 'checked' : '' }}
          style="accent-color:var(--ac);margin-top:2px;flex-shrink:0"
          onchange="highlightMode('quick')">
        <div>
          <div style="font-size:13.5px;font-weight:700;color:var(--text)">Quick Pay</div>
          <div style="font-size:12px;color:var(--muted);margin-top:3px;line-height:1.5">
            Pelanggan langsung bayar di kasir. Cocok untuk warung, kafe.
          </div>
        </div>
      </label>

      <label id="lbl-kitchen"
        style="display:flex;align-items:flex-start;gap:12px;padding:14px 16px;border-radius:12px;border:2px solid {{ $outlet->order_mode === 'kitchen' ? 'var(--ac)' : 'var(--border)' }};cursor:pointer;transition:border-color .15s;background:{{ $outlet->order_mode === 'kitchen' ? 'var(--ac-lt)' : 'transparent' }}">
        <input type="radio" name="order_mode" value="kitchen"
          {{ $outlet->order_mode === 'kitchen' ? 'checked' : '' }}
          style="accent-color:var(--ac);margin-top:2px;flex-shrink:0"
          onchange="highlightMode('kitchen')">
        <div>
          <div style="font-size:13.5px;font-weight:700;color:var(--text)">Kitchen Order</div>
          <div style="font-size:12px;color:var(--muted);margin-top:3px;line-height:1.5">
            Pesanan dikirim ke dapur dulu, bayar setelah selesai.
          </div>
        </div>
      </label>

    </div>
  </div>
</div>
@endif

{{-- Fitur Tambahan --}}
<div class="card animate-fadeUp d1" style="margin-bottom:20px">
  <div class="card-header">
    <div class="card-title">
      <i class="fa-solid fa-puzzle-piece a-text" style="margin-right:8px"></i>Fitur Tambahan
    </div>
  </div>
  <div class="card-body" style="display:flex;flex-direction:column;gap:0">

    {{-- Opening Shift Toggle --}}
    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 0;border-bottom:1px solid var(--border)">
      <div style="flex:1;padding-right:24px">
        <div style="font-size:14px;font-weight:700;color:var(--text);display:flex;align-items:center;gap:8px">
          <i class="fa-solid fa-clock-rotate-left" style="color:var(--ac);font-size:13px"></i>
          Opening Shift
        </div>
        <div style="font-size:12.5px;color:var(--muted);margin-top:4px;line-height:1.6">
          Kasir wajib membuka shift dengan mencatat <strong>kas awal</strong> sebelum mulai beroperasi,
          dan menutup shift dengan <strong>kas akhir</strong> di akhir jam kerja.
          Sistem otomatis menghitung selisih kas.
        </div>
        <div style="margin-top:8px;display:flex;gap:6px;flex-wrap:wrap">
          <span style="font-size:11px;padding:2px 8px;border-radius:99px;background:var(--ac-lt);color:var(--ac);font-weight:600">
            <i class="fa-solid fa-check" style="margin-right:3px"></i>Kontrol kas harian
          </span>
          <span style="font-size:11px;padding:2px 8px;border-radius:99px;background:var(--ac-lt);color:var(--ac);font-weight:600">
            <i class="fa-solid fa-check" style="margin-right:3px"></i>Deteksi selisih kas
          </span>
          <span style="font-size:11px;padding:2px 8px;border-radius:99px;background:var(--ac-lt);color:var(--ac);font-weight:600">
            <i class="fa-solid fa-check" style="margin-right:3px"></i>Riwayat shift per kasir
          </span>
        </div>
      </div>
      <label style="position:relative;display:inline-block;width:48px;height:26px;flex-shrink:0;cursor:pointer">
        <input type="checkbox" name="enable_opening_shift" value="1" id="toggle-shift"
          {{ $outlet->enable_opening_shift ? 'checked' : '' }}
          style="opacity:0;width:0;height:0;position:absolute"
          onchange="document.getElementById('shift-status').textContent = this.checked ? 'Aktif' : 'Nonaktif';
                    document.getElementById('shift-status').style.color = this.checked ? 'var(--ac)' : 'var(--muted)'">
        <span id="toggle-track" style="position:absolute;inset:0;border-radius:99px;transition:background .2s;background:{{ $outlet->enable_opening_shift ? 'var(--ac)' : 'var(--border)' }}"></span>
        <span id="toggle-thumb" style="position:absolute;top:3px;left:{{ $outlet->enable_opening_shift ? '25px' : '3px' }};width:20px;height:20px;border-radius:50%;background:#fff;transition:left .2s;box-shadow:0 1px 4px rgba(0,0,0,.25)"></span>
      </label>
    </div>

    {{-- Status badge bawah toggle --}}
    <div style="padding:10px 0 4px;display:flex;align-items:center;gap:6px">
      <span style="font-size:12px;color:var(--muted)">Status:</span>
      <span id="shift-status" style="font-size:12px;font-weight:700;color:{{ $outlet->enable_opening_shift ? 'var(--ac)' : 'var(--muted)' }}">
        {{ $outlet->enable_opening_shift ? 'Aktif' : 'Nonaktif' }}
      </span>
    </div>

    {{-- Barcode Scanner Toggle --}}
    <div style="display:flex;align-items:center;justify-content:space-between;padding:20px 0 16px;border-top:1px solid var(--border)">
      <div style="flex:1;padding-right:24px">
        <div style="font-size:14px;font-weight:700;color:var(--text);display:flex;align-items:center;gap:8px">
          <i class="fa-solid fa-barcode" style="color:var(--ac);font-size:13px"></i>
          Scanner Barcode / QR
        </div>
        <div style="font-size:12.5px;color:var(--muted);margin-top:4px;line-height:1.6">
          Aktifkan input barcode / QR code di halaman <strong>POS</strong> dan <strong>Tambah Stok</strong>.
          Mendukung scanner hardware (USB/Bluetooth) maupun kamera perangkat.
          Produk harus memiliki <strong>SKU / Barcode</strong> yang terisi.
        </div>
        <div style="margin-top:8px;display:flex;gap:6px;flex-wrap:wrap">
          <span style="font-size:11px;padding:2px 8px;border-radius:99px;background:var(--ac-lt);color:var(--ac);font-weight:600">
            <i class="fa-solid fa-check" style="margin-right:3px"></i>Hardware scanner
          </span>
          <span style="font-size:11px;padding:2px 8px;border-radius:99px;background:var(--ac-lt);color:var(--ac);font-weight:600">
            <i class="fa-solid fa-check" style="margin-right:3px"></i>Kamera QR / barcode
          </span>
          <span style="font-size:11px;padding:2px 8px;border-radius:99px;background:var(--ac-lt);color:var(--ac);font-weight:600">
            <i class="fa-solid fa-check" style="margin-right:3px"></i>EAN-13 · Code128 · QR
          </span>
        </div>
      </div>
      <label style="position:relative;display:inline-block;width:48px;height:26px;flex-shrink:0;cursor:pointer">
        <input type="checkbox" name="enable_barcode_scanner" value="1" id="toggle-barcode"
          {{ $outlet->enable_barcode_scanner ? 'checked' : '' }}
          style="opacity:0;width:0;height:0;position:absolute"
          onchange="document.getElementById('barcode-status').textContent = this.checked ? 'Aktif' : 'Nonaktif';
                    document.getElementById('barcode-status').style.color = this.checked ? 'var(--ac)' : 'var(--muted)'">
        <span id="barcode-track" style="position:absolute;inset:0;border-radius:99px;transition:background .2s;background:{{ $outlet->enable_barcode_scanner ? 'var(--ac)' : 'var(--border)' }}"></span>
        <span id="barcode-thumb" style="position:absolute;top:3px;left:{{ $outlet->enable_barcode_scanner ? '25px' : '3px' }};width:20px;height:20px;border-radius:50%;background:#fff;transition:left .2s;box-shadow:0 1px 4px rgba(0,0,0,.25)"></span>
      </label>
    </div>
    <div style="padding:0 0 4px;display:flex;align-items:center;gap:6px">
      <span style="font-size:12px;color:var(--muted)">Status:</span>
      <span id="barcode-status" style="font-size:12px;font-weight:700;color:{{ $outlet->enable_barcode_scanner ? 'var(--ac)' : 'var(--muted)' }}">
        {{ $outlet->enable_barcode_scanner ? 'Aktif' : 'Nonaktif' }}
      </span>
    </div>

  </div>
</div>

{{-- Save button --}}
<div class="animate-fadeUp d2" style="display:flex;justify-content:flex-end">
  <button type="submit"
    style="display:flex;align-items:center;gap:8px;padding:10px 24px;border-radius:11px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13.5px;font-weight:700;cursor:pointer;font-family:inherit;box-shadow:0 2px 12px rgba(var(--ac-rgb),.35)">
    <i class="fa-solid fa-floppy-disk"></i>Simpan Pengaturan
  </button>
</div>

</form>

@push('scripts')
<script>
@unless($isRetail)
function highlightMode(mode) {
  const ac      = getComputedStyle(document.documentElement).getPropertyValue('--ac').trim();
  const acLt    = getComputedStyle(document.documentElement).getPropertyValue('--ac-lt').trim();
  const border  = getComputedStyle(document.documentElement).getPropertyValue('--border').trim();
  document.getElementById('lbl-quick').style.borderColor   = mode === 'quick'   ? ac : border;
  document.getElementById('lbl-quick').style.background    = mode === 'quick'   ? acLt : 'transparent';
  document.getElementById('lbl-kitchen').style.borderColor = mode === 'kitchen' ? ac : border;
  document.getElementById('lbl-kitchen').style.background  = mode === 'kitchen' ? acLt : 'transparent';
}
@endunless

// Toggle track & thumb animation — helper
function wireToggle(checkboxId, trackId, thumbId) {
  const el = document.getElementById(checkboxId);
  if (!el) return;
  el.addEventListener('change', function() {
    const ac    = getComputedStyle(document.documentElement).getPropertyValue('--ac').trim();
    const border= getComputedStyle(document.documentElement).getPropertyValue('--border').trim();
    document.getElementById(trackId).style.background = this.checked ? ac : border;
    document.getElementById(thumbId).style.left       = this.checked ? '25px' : '3px';
  });
}

wireToggle('toggle-shift',   'toggle-track',  'toggle-thumb');
wireToggle('toggle-barcode', 'barcode-track', 'barcode-thumb');
</script>
@endpush

</x-outlet-layout>
