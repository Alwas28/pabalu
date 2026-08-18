@php
  $pageLabel  = $outlet->isRetailFlow() ? 'Pengaturan Toko' : 'Pengaturan Outlet';
@endphp
<x-outlet-layout :outlet="$outlet" :pageTitle="$pageLabel">

@php
  $isOwner       = Auth::user()->role === 'owner' || Auth::user()->role === 'admin';
  $isSystemAdmin = Auth::user()->role === 'admin';
  $adminContact  = $isSystemAdmin ? null : \App\Models\User::whereHas('roleRelation', fn($q) => $q->where('slug', 'admin'))->first();
  $adminWaPhone  = $adminContact?->phone ? preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $adminContact->phone)) : null;
@endphp

<x-outlet-menu-qr :outlet="$outlet" />

<form method="POST" action="{{ $outlet->route('settings.update') }}">
@csrf
@method('PATCH')

{{-- Laundry selalu Quick Pay — server memaksa order_mode='quick' di SettingController --}}
<div class="card animate-fadeUp" style="margin-bottom:20px">
  <div class="card-header">
    <div class="card-title">
      <i class="fa-solid fa-bolt a-text" style="margin-right:8px"></i>Mode Transaksi
    </div>
  </div>
  <div class="card-body">
    <div style="display:flex;align-items:flex-start;gap:12px;padding:14px 16px;border-radius:12px;border:2px solid var(--ac);background:var(--ac-lt);max-width:520px">
      <i class="fa-solid fa-circle-check a-text" style="margin-top:2px;flex-shrink:0"></i>
      <div>
        <div style="font-size:13.5px;font-weight:700;color:var(--text)">Quick Pay</div>
        <div style="font-size:12px;color:var(--muted);margin-top:3px;line-height:1.5">
          Pembayaran dilakukan langsung di kasir saat pesanan dibuat atau saat diambil.
          Progres cucian (masuk, diproses, selesai, diambil) dilacak lewat halaman <strong>Pesanan Laundry</strong>, bukan lewat mode transaksi ini.
        </div>
      </div>
    </div>
  </div>
</div>

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
    <p style="font-size:13px;color:var(--muted);margin:0 0 16px;line-height:1.6">
      Pilih metode pembayaran yang tersedia selain <strong>Tunai</strong>. Tunai selalu aktif dan tidak bisa dinonaktifkan.
    </p>

    @php
      $pmList = [
        ['enable_qris_transfer', 'qris-transfer', 'QRIS Transfer', 'fa-qrcode',           'Pembayaran QRIS dengan konfirmasi transfer (scan & transfer manual).'],
        ['enable_qris_pay',      'qris-pay',      'QRIS Pay',      'fa-bolt',              'Pembayaran QRIS digital terintegrasi (memerlukan konfigurasi Midtrans).'],
        ['enable_transfer',      'transfer',      'Transfer Bank', 'fa-building-columns',  'Transfer antar bank / virtual account.'],
        ['enable_card',          'card',          'Kartu Debit/Kredit', 'fa-credit-card',  'Pembayaran dengan mesin EDC / kartu gesek.'],
      ];
    @endphp

    @foreach($pmList as [$field, $slug, $label, $icon, $desc])
    @php $isQrisPay = $field === 'enable_qris_pay'; $canToggle = !$isQrisPay || $isSystemAdmin; @endphp
    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 0;border-bottom:1px solid var(--border){{ !$canToggle ? ';opacity:.75' : '' }}">
      <div style="flex:1;padding-right:24px;display:flex;align-items:flex-start;gap:12px">
        <div style="width:36px;height:36px;border-radius:10px;background:var(--ac-lt);color:{{ $canToggle ? 'var(--ac)' : 'var(--muted)' }};display:grid;place-items:center;font-size:14px;flex-shrink:0;margin-top:2px">
          <i class="fa-solid {{ $icon }}"></i>
        </div>
        <div>
          <div style="font-size:14px;font-weight:700;color:var(--text);display:flex;align-items:center;gap:8px">
            {{ $label }}
            @if($isQrisPay && !$isSystemAdmin)
            <span style="font-size:10px;font-weight:700;padding:2px 7px;border-radius:99px;background:rgba(239,68,68,.12);color:#f87171">
              <i class="fa-solid fa-lock" style="font-size:9px;margin-right:2px"></i>Admin Only
            </span>
            @endif
          </div>
          <div style="font-size:12.5px;color:var(--muted);margin-top:3px;line-height:1.5">{{ $desc }}</div>
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
          <div style="margin-top:6px">
            <span id="pm-status-{{ $slug }}" style="font-size:11px;font-weight:700;padding:2px 10px;border-radius:99px;
              background:{{ $outlet->$field ? 'var(--ac-lt)' : 'var(--surface2)' }};
              color:{{ $outlet->$field ? 'var(--ac)' : 'var(--muted)' }}">
              {{ $outlet->$field ? 'Aktif' : 'Nonaktif' }}
            </span>
          </div>
        </div>
      </div>
      <label style="position:relative;display:inline-block;width:48px;height:26px;flex-shrink:0;cursor:{{ $canToggle ? 'pointer' : 'not-allowed' }}">
        <input type="checkbox" name="{{ $field }}" value="1" id="toggle-pm-{{ $slug }}"
          {{ $outlet->$field ? 'checked' : '' }}
          {{ !$canToggle ? 'disabled' : '' }}
          style="opacity:0;width:0;height:0;position:absolute"
          @if($canToggle) onchange="
            document.getElementById('pm-status-{{ $slug }}').textContent = this.checked ? 'Aktif' : 'Nonaktif';
            document.getElementById('pm-status-{{ $slug }}').style.color = this.checked ? 'var(--ac)' : 'var(--muted)';
            document.getElementById('pm-status-{{ $slug }}').style.background = this.checked ? 'var(--ac-lt)' : 'var(--surface2)';
            {{ $field === 'enable_qris_pay' ? "toggleMidtransCard(this.checked);" : '' }}" @endif>
        <span id="pm-track-{{ $slug }}" style="position:absolute;inset:0;border-radius:99px;transition:background .2s;
          background:{{ $outlet->$field ? 'var(--ac)' : 'var(--border)' }}"></span>
        <span id="pm-thumb-{{ $slug }}" style="position:absolute;top:3px;
          left:{{ $outlet->$field ? '25px' : '3px' }};width:20px;height:20px;border-radius:50%;
          background:#fff;transition:left .2s;box-shadow:0 1px 4px rgba(0,0,0,.25)"></span>
      </label>
    </div>
    @endforeach

    {{-- Tunai — selalu aktif --}}
    <div style="display:flex;align-items:center;gap:12px;padding:14px 0 4px">
      <div style="width:36px;height:36px;border-radius:10px;background:rgba(16,185,129,.12);color:#34d399;display:grid;place-items:center;font-size:14px;flex-shrink:0">
        <i class="fa-solid fa-money-bill-wave"></i>
      </div>
      <div>
        <div style="font-size:13.5px;font-weight:700;color:var(--text)">Tunai <span style="font-size:11px;font-weight:600;color:#34d399;background:rgba(16,185,129,.12);padding:2px 10px;border-radius:99px;margin-left:4px">Selalu Aktif</span></div>
        <div style="font-size:12px;color:var(--muted);margin-top:2px">Pembayaran cash langsung ke kasir.</div>
      </div>
    </div>

  </div>
</div>

{{-- Koneksi Midtrans — khusus admin sistem, owner tidak boleh lihat/isi secret key.
     Aktivasi Midtrans sepenuhnya di tangan admin walau paket owner sudah termasuk fiturnya. --}}
@if($isSystemAdmin)
<div class="card animate-fadeUp d1" id="midtrans-card" style="margin-bottom:20px;{{ $outlet->enable_qris_pay ? '' : 'display:none' }}">
  <div class="card-header">
    <div class="card-title">
      <i class="fa-solid fa-plug a-text" style="margin-right:8px"></i>Koneksi Midtrans
    </div>
  </div>
  <div class="card-body">
    <p style="font-size:13px;color:var(--muted);margin:0 0 16px">
      Dibutuhkan agar metode <strong>QRIS Pay</strong> bisa membuat QRIS dinamis.
      Ambil Server Key &amp; Client Key dari dashboard Midtrans (Settings &rarr; Access Keys).
    </p>
    @error('midtrans_server_key')
    <div style="padding:10px 14px;border-radius:10px;background:rgba(239,68,68,.12);color:#f87171;font-size:13px;margin-bottom:14px;border:1px solid rgba(239,68,68,.2)">
      <i class="fa-solid fa-circle-exclamation" style="margin-right:6px"></i>{{ $message }}
    </div>
    @enderror
    <div style="display:flex;flex-direction:column;gap:14px;max-width:480px">
      <div>
        <label style="font-size:12.5px;font-weight:600;color:var(--sub);display:block;margin-bottom:6px">Server Key</label>
        <input type="text" name="midtrans_server_key" value="{{ old('midtrans_server_key', $outlet->midtrans_server_key) }}"
          placeholder="SB-Mid-server-xxxxxxxxxxxxxxxxxxxxxxxx"
          style="width:100%;padding:10px 12px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--text);font-size:13px;font-family:monospace;outline:none">
      </div>
      <div>
        <label style="font-size:12.5px;font-weight:600;color:var(--sub);display:block;margin-bottom:6px">Client Key</label>
        <input type="text" name="midtrans_client_key" value="{{ old('midtrans_client_key', $outlet->midtrans_client_key) }}"
          placeholder="SB-Mid-client-xxxxxxxxxxxxxxxxxxxxxxxx"
          style="width:100%;padding:10px 12px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--text);font-size:13px;font-family:monospace;outline:none">
      </div>
      <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
        <input type="checkbox" name="midtrans_is_production" value="1"
          {{ $outlet->midtrans_is_production ? 'checked' : '' }}
          style="accent-color:var(--ac);width:16px;height:16px">
        <span style="font-size:13px;color:var(--text)">Mode Produksi (nonaktifkan untuk Sandbox/uji coba)</span>
      </label>
    </div>
  </div>
</div>
@endif

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

wireToggle('toggle-shift',      'toggle-track',      'toggle-thumb');
@if($outlet->isRetailFlow())
wireToggle('toggle-barcode',    'barcode-track',     'barcode-thumb');
@endif
// Payment method toggles
['qris-transfer','qris-pay','transfer','card'].forEach(slug => {
  wireToggle('toggle-pm-'+slug, 'pm-track-'+slug, 'pm-thumb-'+slug);
});

function toggleMidtransCard(show) {
  document.getElementById('midtrans-card').style.display = show ? 'block' : 'none';
}
</script>
@endpush

</x-outlet-layout>
