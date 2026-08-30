@php
  $pageLabel  = $outlet->isRetailFlow() ? 'Pengaturan Toko' : 'Pengaturan Outlet';
@endphp
<x-outlet-layout :outlet="$outlet" :pageTitle="$pageLabel">

@php
  $isOwner       = Auth::user()->role === 'owner' || Auth::user()->role === 'admin';
  $isRetail      = $outlet->isRetailFlow();
  $isSystemAdmin = Auth::user()->role === 'admin';
  $adminContact  = $isSystemAdmin ? null : \App\Models\User::whereHas('roleRelation', fn($q) => $q->where('slug', 'admin'))->first();
  $adminWaPhone  = $adminContact?->phone ? preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $adminContact->phone)) : null;
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

    @if(!$isRetail)
    {{-- Self Order Toggle (hanya F&B) --}}
    <div style="display:flex;align-items:center;justify-content:space-between;padding:20px 0 16px;border-top:1px solid var(--border)">
      <div style="flex:1;padding-right:24px">
        <div style="font-size:14px;font-weight:700;color:var(--text);display:flex;align-items:center;gap:8px">
          <i class="fa-solid fa-mobile-screen-button" style="color:var(--ac);font-size:13px"></i>
          Self Order (Pesan Mandiri)
        </div>
        <div style="font-size:12.5px;color:var(--muted);margin-top:4px;line-height:1.6">
          Pelanggan dapat memindai QR code meja dan <strong>memesan langsung</strong> dari ponsel mereka tanpa bantuan kasir.
          Jika dinonaktifkan, halaman menu tetap bisa diakses namun pelanggan hanya bisa <strong>melihat menu</strong> (tidak bisa memesan).
        </div>
        <div style="margin-top:8px;display:flex;gap:6px;flex-wrap:wrap">
          <span style="font-size:11px;padding:2px 8px;border-radius:99px;background:var(--ac-lt);color:var(--ac);font-weight:600">
            <i class="fa-solid fa-check" style="margin-right:3px"></i>Scan QR meja
          </span>
          <span style="font-size:11px;padding:2px 8px;border-radius:99px;background:var(--ac-lt);color:var(--ac);font-weight:600">
            <i class="fa-solid fa-check" style="margin-right:3px"></i>Pesanan masuk ke antrian
          </span>
          <span style="font-size:11px;padding:2px 8px;border-radius:99px;background:var(--ac-lt);color:var(--ac);font-weight:600">
            <i class="fa-solid fa-check" style="margin-right:3px"></i>Konfirmasi oleh kasir
          </span>
        </div>
      </div>
      <label style="position:relative;display:inline-block;width:48px;height:26px;flex-shrink:0;cursor:pointer">
        <input type="checkbox" name="enable_self_order" value="1" id="toggle-self-order"
          {{ $outlet->enable_self_order ? 'checked' : '' }}
          style="opacity:0;width:0;height:0;position:absolute">
        <span id="self-order-track" style="position:absolute;inset:0;border-radius:99px;transition:background .2s;background:{{ $outlet->enable_self_order ? 'var(--ac)' : 'var(--border)' }}"></span>
        <span id="self-order-thumb" style="position:absolute;top:3px;left:{{ $outlet->enable_self_order ? '25px' : '3px' }};width:20px;height:20px;border-radius:50%;background:#fff;transition:left .2s;box-shadow:0 1px 4px rgba(0,0,0,.25)"></span>
      </label>
    </div>
    <div style="padding:0 0 4px;display:flex;align-items:center;gap:6px">
      <span style="font-size:12px;color:var(--muted)">Status:</span>
      <span id="self-order-status" style="font-size:12px;font-weight:700;color:{{ $outlet->enable_self_order ? 'var(--ac)' : 'var(--muted)' }}">
        {{ $outlet->enable_self_order ? 'Aktif' : 'Nonaktif' }}
      </span>
    </div>

    {{-- Opening Stok Toggle (hanya F&B non-retail) --}}
    <div style="display:flex;align-items:center;justify-content:space-between;padding:20px 0 16px;border-top:1px solid var(--border)">
      <div style="flex:1;padding-right:24px">
        <div style="font-size:14px;font-weight:700;color:var(--text);display:flex;align-items:center;gap:8px">
          <i class="fa-solid fa-clipboard-list" style="color:var(--ac);font-size:13px"></i>
          Opening Stok Harian
        </div>
        <div style="font-size:12.5px;color:var(--muted);margin-top:4px;line-height:1.6">
          Kasir wajib mencatat <strong>stok awal</strong> setiap hari sebelum bisa transaksi.
          Jika dinonaktifkan, kasir bisa langsung transaksi tanpa opening stok terlebih dahulu.
        </div>
        <div style="margin-top:8px;display:flex;gap:6px;flex-wrap:wrap">
          <span style="font-size:11px;padding:2px 8px;border-radius:99px;background:var(--ac-lt);color:var(--ac);font-weight:600">
            <i class="fa-solid fa-check" style="margin-right:3px"></i>Stok awal harian
          </span>
          <span style="font-size:11px;padding:2px 8px;border-radius:99px;background:var(--ac-lt);color:var(--ac);font-weight:600">
            <i class="fa-solid fa-check" style="margin-right:3px"></i>Tutup hari otomatis rekap
          </span>
        </div>
      </div>
      <label style="position:relative;display:inline-block;width:48px;height:26px;flex-shrink:0;cursor:pointer">
        <input type="checkbox" name="requires_opening_stock" value="1" id="toggle-opening-stock"
          {{ $outlet->requiresOpeningStock() ? 'checked' : '' }}
          style="opacity:0;width:0;height:0;position:absolute">
        <span id="opening-stock-track" style="position:absolute;inset:0;border-radius:99px;transition:background .2s;background:{{ $outlet->requiresOpeningStock() ? 'var(--ac)' : 'var(--border)' }}"></span>
        <span id="opening-stock-thumb" style="position:absolute;top:3px;left:{{ $outlet->requiresOpeningStock() ? '25px' : '3px' }};width:20px;height:20px;border-radius:50%;background:#fff;transition:left .2s;box-shadow:0 1px 4px rgba(0,0,0,.25)"></span>
      </label>
    </div>
    <div style="padding:0 0 4px;display:flex;align-items:center;gap:6px">
      <span style="font-size:12px;color:var(--muted)">Status:</span>
      <span id="opening-stock-status" style="font-size:12px;font-weight:700;color:{{ $outlet->requiresOpeningStock() ? 'var(--ac)' : 'var(--muted)' }}">
        {{ $outlet->requiresOpeningStock() ? 'Aktif' : 'Nonaktif' }}
      </span>
    </div>
    @endif

    @if($outlet->isRetailFlow())
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
    @endif

  </div>
</div>

{{-- Metode Pembayaran --}}
<div class="card animate-fadeUp d1" style="margin-bottom:20px">
  <div class="card-header">
    <div class="card-title">
      <i class="fa-solid fa-credit-card a-text" style="margin-right:8px"></i>Metode Pembayaran
    </div>
  </div>
  <div class="card-body" style="display:flex;flex-direction:column;gap:0">
    <p style="font-size:13px;color:var(--muted);margin:0 0 16px">
      Tunai selalu aktif. Aktifkan metode tambahan di bawah agar muncul sebagai pilihan saat kasir memproses pembayaran.
    </p>

    @foreach([
      ['enable_qris_transfer', 'QRIS Transfer', 'fa-qrcode', 'Pelanggan scan QRIS statis milik outlet, kasir input nomor referensi & upload foto bukti bayar.'],
      ['enable_qris_pay', 'QRIS Pay', 'fa-bolt', 'Sistem membuat QRIS dinamis via Midtrans. Pembayaran terverifikasi otomatis tanpa input manual.'],
      ['enable_transfer', 'Transfer Bank', 'fa-building-columns', 'Pelanggan transfer ke rekening outlet, kasir input nomor referensi & upload foto bukti transfer.'],
      ['enable_card', 'Kartu (EDC)', 'fa-credit-card', 'Pembayaran via mesin EDC, kasir input nomor referensi & upload foto struk EDC.'],
    ] as $i => [$field, $label, $icon, $desc])
    @php $isQrisPay = $field === 'enable_qris_pay'; $canToggle = !$isQrisPay || $isSystemAdmin; @endphp
    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 0;{{ $i < 3 ? 'border-bottom:1px solid var(--border)' : '' }}{{ !$canToggle ? ';opacity:.75' : '' }}">
      <div style="flex:1;padding-right:24px">
        <div style="font-size:14px;font-weight:700;color:var(--text);display:flex;align-items:center;gap:8px">
          <i class="fa-solid {{ $icon }}" style="color:{{ $canToggle ? 'var(--ac)' : 'var(--muted)' }};font-size:13px"></i>{{ $label }}
          @if($isQrisPay && !$isSystemAdmin)
          <span style="font-size:10px;font-weight:700;padding:2px 7px;border-radius:99px;background:rgba(239,68,68,.12);color:#f87171">
            <i class="fa-solid fa-lock" style="font-size:9px;margin-right:2px"></i>Admin Only
          </span>
          @endif
        </div>
        <div style="font-size:12.5px;color:var(--muted);margin-top:4px;line-height:1.6">{{ $desc }}</div>
        @if($isQrisPay && !$isSystemAdmin)
        <div style="font-size:12px;color:#f87171;margin-top:5px;display:flex;align-items:center;gap:5px">
          <i class="fa-solid fa-circle-info" style="font-size:11px"></i>
          Hanya Administrator yang dapat mengaktifkan fitur ini.
        </div>
        @if($adminContact)
        <div style="margin-top:8px;padding:10px 12px;border-radius:10px;background:var(--surface2);border:1px solid var(--border);display:flex;align-items:center;gap:10px">
          <div style="width:32px;height:32px;border-radius:50%;background:var(--ac-lt);color:var(--ac);display:grid;place-items:center;flex-shrink:0">
            <i class="fa-solid fa-user-shield" style="font-size:13px"></i>
          </div>
          <div>
            <div style="font-size:11px;color:var(--muted);margin-bottom:2px">Hubungi Administrator:</div>
            <div style="font-size:12.5px;font-weight:700;color:var(--text)">{{ $adminContact->name }}</div>
            <div style="display:flex;gap:10px;margin-top:4px;flex-wrap:wrap">
              @if($adminContact->email)
              <a href="mailto:{{ $adminContact->email }}" style="font-size:11px;color:var(--ac);text-decoration:none;display:flex;align-items:center;gap:4px">
                <i class="fa-solid fa-envelope" style="font-size:10px"></i>{{ $adminContact->email }}
              </a>
              @endif
              @if($adminWaPhone)
              <a href="https://wa.me/{{ $adminWaPhone }}" target="_blank" rel="noopener" style="font-size:11px;color:#25d366;text-decoration:none;display:flex;align-items:center;gap:4px">
                <i class="fa-brands fa-whatsapp" style="font-size:12px"></i>{{ $adminContact->phone }}
              </a>
              @endif
            </div>
          </div>
        </div>
        @endif
        @endif
      </div>
      <label style="position:relative;display:inline-block;width:48px;height:26px;flex-shrink:0;cursor:{{ $canToggle ? 'pointer' : 'not-allowed' }}">
        <input type="checkbox" name="{{ $field }}" value="1" id="toggle-{{ $field }}"
          {{ $outlet->{$field} ? 'checked' : '' }}
          {{ !$canToggle ? 'disabled' : '' }}
          style="opacity:0;width:0;height:0;position:absolute"
          @if($field === 'enable_qris_pay' && $isSystemAdmin) onchange="toggleMidtransCard(this.checked)" @endif>
        <span id="track-{{ $field }}" style="position:absolute;inset:0;border-radius:99px;transition:background .2s;background:{{ $outlet->{$field} ? 'var(--ac)' : 'var(--border)' }}"></span>
        <span id="thumb-{{ $field }}" style="position:absolute;top:3px;left:{{ $outlet->{$field} ? '25px' : '3px' }};width:20px;height:20px;border-radius:50%;background:#fff;transition:left .2s;box-shadow:0 1px 4px rgba(0,0,0,.25)"></span>
      </label>
    </div>
    @endforeach

  </div>
</div>

{{-- Koneksi Midtrans --}}
<div class="card animate-fadeUp d1" id="midtrans-card" style="margin-bottom:20px;{{ $outlet->enable_qris_pay ? '' : 'display:none' }}">
  <div class="card-header">
    <div class="card-title">
      <i class="fa-solid fa-plug a-text" style="margin-right:8px"></i>Koneksi Midtrans
    </div>
  </div>
  <div class="card-body">
    <p style="font-size:13px;color:var(--muted);margin:0 0 16px">
      Dibutuhkan agar metode <strong>QRIS Pay</strong> bisa membuat QRIS dinamis. Ambil Server Key &amp; Client Key dari dashboard Midtrans (Settings &rarr; Access Keys).
    </p>
    <div style="display:flex;flex-direction:column;gap:14px;max-width:480px">
      <div>
        <label style="font-size:12.5px;font-weight:600;color:var(--sub);display:block;margin-bottom:6px">Server Key</label>
        <input type="text" name="midtrans_server_key" value="{{ old('midtrans_server_key', $outlet->midtrans_server_key) }}"
          placeholder="SB-Mid-server-xxxxxxxxxxxxxxxxxxxxxxxx"
          style="width:100%;padding:10px 12px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--text);font-size:13px;font-family:monospace">
      </div>
      <div>
        <label style="font-size:12.5px;font-weight:600;color:var(--sub);display:block;margin-bottom:6px">Client Key</label>
        <input type="text" name="midtrans_client_key" value="{{ old('midtrans_client_key', $outlet->midtrans_client_key) }}"
          placeholder="SB-Mid-client-xxxxxxxxxxxxxxxxxxxxxxxx"
          style="width:100%;padding:10px 12px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--text);font-size:13px;font-family:monospace">
      </div>
      <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
        <input type="checkbox" name="midtrans_is_production" value="1" {{ $outlet->midtrans_is_production ? 'checked' : '' }} style="accent-color:var(--ac);width:16px;height:16px">
        <span style="font-size:13px;color:var(--text)">Mode Produksi (nonaktifkan untuk Sandbox/uji coba)</span>
      </label>
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
@if(!$isRetail)
wireToggle('toggle-self-order', 'self-order-track', 'self-order-thumb');
const selfOrderStatusEl = document.getElementById('self-order-status');
document.getElementById('toggle-self-order')?.addEventListener('change', function() {
  if (selfOrderStatusEl) {
    selfOrderStatusEl.textContent = this.checked ? 'Aktif' : 'Nonaktif';
    selfOrderStatusEl.style.color = this.checked ? 'var(--ac)' : 'var(--muted)';
  }
});

wireToggle('toggle-opening-stock', 'opening-stock-track', 'opening-stock-thumb');
const openingStockStatusEl = document.getElementById('opening-stock-status');
document.getElementById('toggle-opening-stock')?.addEventListener('change', function() {
  if (openingStockStatusEl) {
    openingStockStatusEl.textContent = this.checked ? 'Aktif' : 'Nonaktif';
    openingStockStatusEl.style.color = this.checked ? 'var(--ac)' : 'var(--muted)';
  }
});
@endif
@if($outlet->isRetailFlow())
wireToggle('toggle-barcode', 'barcode-track', 'barcode-thumb');
@endif

wireToggle('toggle-enable_qris_transfer', 'track-enable_qris_transfer', 'thumb-enable_qris_transfer');
wireToggle('toggle-enable_qris_pay',      'track-enable_qris_pay',      'thumb-enable_qris_pay');
wireToggle('toggle-enable_transfer',      'track-enable_transfer',      'thumb-enable_transfer');
wireToggle('toggle-enable_card',          'track-enable_card',          'thumb-enable_card');

function toggleMidtransCard(show) {
  document.getElementById('midtrans-card').style.display = show ? 'block' : 'none';
}
</script>
@endpush

</x-outlet-layout>
