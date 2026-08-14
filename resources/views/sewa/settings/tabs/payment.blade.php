@php
  $isSystemAdmin = Auth::user()->role === 'admin';
  $adminContact  = $isSystemAdmin ? null : \App\Models\User::whereHas('roleRelation', fn($q) => $q->where('slug', 'admin'))->first();
  $adminWaPhone  = $adminContact?->phone ? preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $adminContact->phone)) : null;
@endphp

<div id="payment-settings-form">

{{-- Metode Pembayaran --}}
<div class="card animate-fadeUp" style="margin-bottom:20px">
  <div class="card-header">
    <span class="card-title"><i class="fa-solid fa-credit-card" style="color:var(--ac);margin-right:8px"></i>Metode Pembayaran</span>
  </div>
  <div class="card-body" style="display:flex;flex-direction:column;gap:0">
    <p style="font-size:13px;color:var(--muted);margin:0 0 16px;line-height:1.6">
      Pilih metode pembayaran yang tersedia untuk transaksi sewa selain <strong>Tunai</strong>. Tunai selalu aktif dan tidak bisa dinonaktifkan.
      Perubahan tersimpan otomatis begitu Anda mengaktifkan/menonaktifkan.
    </p>

    @php
      $pmList = [
        ['enable_qris_transfer', 'qris-transfer', 'QRIS Transfer', 'fa-qrcode',          'Pembayaran QRIS dengan konfirmasi transfer (scan & transfer manual).'],
        ['enable_qris_pay',      'qris-pay',      'QRIS Pay',      'fa-bolt',             'Pembayaran QRIS digital terintegrasi (memerlukan konfigurasi Midtrans).'],
        ['enable_transfer',      'transfer',      'Transfer Bank', 'fa-building-columns', 'Transfer antar bank / virtual account.'],
        ['enable_card',          'card',          'Kartu Debit/Kredit', 'fa-credit-card', 'Pembayaran dengan mesin EDC / kartu gesek.'],
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
        <input type="checkbox" value="1" id="toggle-pm-{{ $slug }}" data-field="{{ $field }}"
          {{ $outlet->$field ? 'checked' : '' }}
          {{ !$canToggle ? 'disabled' : '' }}
          style="opacity:0;width:0;height:0;position:absolute"
          @if($canToggle) onchange="
            syncSwitchVisual('{{ $slug }}', this.checked);
            {{ $field === 'enable_qris_pay' ? "toggleMidtransCard(this.checked);" : '' }}
            autoSavePayment('{{ $slug }}');" @endif>
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

{{-- Koneksi Midtrans --}}
<div class="card animate-fadeUp d1" id="midtrans-card" style="margin-bottom:20px;{{ $outlet->enable_qris_pay ? '' : 'display:none' }}">
  <div class="card-header">
    <span class="card-title"><i class="fa-solid fa-plug" style="color:var(--ac);margin-right:8px"></i>Koneksi Midtrans</span>
  </div>
  <div class="card-body">
    <p style="font-size:13px;color:var(--muted);margin:0 0 16px">
      Dibutuhkan agar metode <strong>QRIS Pay</strong> bisa membuat QRIS dinamis.
      Ambil Server Key &amp; Client Key dari dashboard Midtrans (Settings &rarr; Access Keys).
      Tersimpan otomatis saat Anda pindah dari kolom (klik di luar kolom).
    </p>
    <div id="midtrans-error" style="display:none;padding:10px 14px;border-radius:10px;background:rgba(239,68,68,.12);color:#f87171;font-size:13px;margin-bottom:14px;border:1px solid rgba(239,68,68,.2)">
      <i class="fa-solid fa-circle-exclamation" style="margin-right:6px"></i><span id="midtrans-error-text"></span>
    </div>
    <div style="display:flex;flex-direction:column;gap:14px;max-width:480px">
      <div>
        <label class="f-label">Server Key</label>
        <input type="text" id="midtrans-server-key" value="{{ $outlet->midtrans_server_key }}"
          placeholder="SB-Mid-server-xxxxxxxxxxxxxxxxxxxxxxxx" class="f-input" style="font-family:monospace"
          onblur="autoSavePayment()">
      </div>
      <div>
        <label class="f-label">Client Key</label>
        <input type="text" id="midtrans-client-key" value="{{ $outlet->midtrans_client_key }}"
          placeholder="SB-Mid-client-xxxxxxxxxxxxxxxxxxxxxxxx" class="f-input" style="font-family:monospace"
          onblur="autoSavePayment()">
      </div>
      <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
        <input type="checkbox" id="midtrans-is-production"
          {{ $outlet->midtrans_is_production ? 'checked' : '' }}
          style="accent-color:var(--ac);width:16px;height:16px" onchange="autoSavePayment()">
        <span style="font-size:13px;color:var(--text)">Mode Produksi (nonaktifkan untuk Sandbox/uji coba)</span>
      </label>
    </div>
  </div>
</div>

</div>

@push('scripts')
<script>
const PAYMENT_UPDATE_URL = '{{ $outlet->route('settings.payment.update') }}';

function toggleMidtransCard(show) {
  document.getElementById('midtrans-card').style.display = show ? 'block' : 'none';
}

/** Perbarui tampilan switch (track + lingkaran) dan badge status — checkbox punya opacity:0 jadi tidak otomatis terlihat. */
function syncSwitchVisual(slug, checked) {
  const track  = document.getElementById('pm-track-' + slug);
  const thumb  = document.getElementById('pm-thumb-' + slug);
  const status = document.getElementById('pm-status-' + slug);
  if (track)  track.style.background = checked ? 'var(--ac)' : 'var(--border)';
  if (thumb)  thumb.style.left = checked ? '25px' : '3px';
  if (status) {
    status.textContent = checked ? 'Aktif' : 'Nonaktif';
    status.style.color = checked ? 'var(--ac)' : 'var(--muted)';
    status.style.background = checked ? 'var(--ac-lt)' : 'var(--surface2)';
  }
}

async function autoSavePayment(toggledSlug) {
  const errBox  = document.getElementById('midtrans-error');
  errBox.style.display = 'none';

  const payload = {
    enable_qris_transfer:   document.getElementById('toggle-pm-qris-transfer')?.checked ? 1 : 0,
    enable_qris_pay:        document.getElementById('toggle-pm-qris-pay')?.checked ? 1 : 0,
    enable_transfer:        document.getElementById('toggle-pm-transfer')?.checked ? 1 : 0,
    enable_card:            document.getElementById('toggle-pm-card')?.checked ? 1 : 0,
    midtrans_server_key:    document.getElementById('midtrans-server-key')?.value || '',
    midtrans_client_key:    document.getElementById('midtrans-client-key')?.value || '',
    midtrans_is_production: document.getElementById('midtrans-is-production')?.checked ? 1 : 0,
  };

  try {
    const res = await fetch(PAYMENT_UPDATE_URL, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify(payload),
    });
    const data = await res.json();

    if (!res.ok) {
      if (data.errors?.midtrans_server_key) {
        document.getElementById('midtrans-error-text').textContent = data.errors.midtrans_server_key[0];
        errBox.style.display = 'block';
      }
      // Gagal disimpan (mis. QRIS Pay tanpa Server Key) — kembalikan switch yang barusan diklik ke posisi semula.
      if (toggledSlug === 'qris-pay') {
        const cb = document.getElementById('toggle-pm-qris-pay');
        if (cb) {
          cb.checked = false;
          syncSwitchVisual('qris-pay', false);
          toggleMidtransCard(false);
        }
      }
      showToast('error', data.message || 'Gagal menyimpan.');
      return;
    }

    showToast('success', data.message || 'Tersimpan.');
  } catch (e) {
    showToast('error', 'Gagal menyimpan metode pembayaran. Periksa koneksi Anda.');
  }
}
</script>
@endpush
