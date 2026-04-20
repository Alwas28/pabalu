<x-app-layout title="Pengaturan Sistem">

<style>
.toggle-wrap{display:flex;align-items:center;gap:12px}
.toggle{position:relative;display:inline-block;width:44px;height:24px;flex-shrink:0}
.toggle input{opacity:0;width:0;height:0;position:absolute}
.toggle-slider{
  position:absolute;inset:0;border-radius:99px;
  background:var(--border);cursor:pointer;transition:background .2s;
}
.toggle-slider:before{
  content:'';position:absolute;height:18px;width:18px;left:3px;bottom:3px;
  background:#fff;border-radius:50%;transition:transform .2s;
}
.toggle input:checked + .toggle-slider{background:var(--ac)}
.toggle input:checked + .toggle-slider:before{transform:translateX(20px)}
.toggle-label{font-size:13.5px;font-weight:600;color:var(--text);cursor:pointer}

.outlet-toggle-row{
  display:flex;align-items:center;justify-content:space-between;
  padding:10px 14px;border-radius:12px;border:1px solid var(--border);
  background:var(--surface2);transition:border-color .15s;
}
.outlet-toggle-row:has(input:checked){border-color:var(--ac);background:var(--ac-lt)}

.setup-step{display:flex;gap:14px;padding:14px 0;border-bottom:1px solid var(--border)}
.setup-step:last-child{border-bottom:none;padding-bottom:0}
.step-num{
  width:28px;height:28px;border-radius:50%;flex-shrink:0;
  background:var(--ac-lt);color:var(--ac);font-size:12px;font-weight:700;
  display:grid;place-items:center;
}
.code-block{
  background:var(--surface);border:1px solid var(--border);border-radius:8px;
  padding:8px 12px;font-family:monospace;font-size:12px;color:#34d399;
  margin-top:6px;word-break:break-all;
}
</style>

  <form method="POST" action="{{ route('settings.update') }}">
    @csrf @method('PUT')

    {{-- ── Generic groups (aplikasi, stok, keuangan) ── --}}
    @foreach($grouped as $groupKey => $settings)
    @if(in_array($groupKey, ['payment','panduan','billing'])) @continue @endif
    @php $label = $groupLabels[$groupKey] ?? ucfirst($groupKey); @endphp
    <div class="card animate-fadeUp" style="margin-bottom:0">
      <div class="card-header">
        <div class="card-title">
          @if($groupKey === 'aplikasi') <i class="fa-solid fa-gear" style="color:var(--ac);margin-right:8px"></i>
          @elseif($groupKey === 'stok') <i class="fa-solid fa-boxes-stacking" style="color:var(--ac);margin-right:8px"></i>
          @elseif($groupKey === 'keuangan') <i class="fa-solid fa-coins" style="color:var(--ac);margin-right:8px"></i>
          @else <i class="fa-solid fa-sliders" style="color:var(--ac);margin-right:8px"></i>
          @endif
          {{ $label }}
        </div>
      </div>
      <div class="card-body" style="display:flex;flex-direction:column;gap:20px">
        @foreach($settings as $s)
        <div style="display:grid;grid-template-columns:280px 1fr;gap:24px;align-items:start">
          <div>
            <div style="font-size:13.5px;font-weight:600;color:var(--text)">{{ $s['label'] }}</div>
            @if(!empty($s['description']))
            <div style="font-size:12px;color:var(--muted);margin-top:3px;line-height:1.5">{{ $s['description'] }}</div>
            @endif
          </div>
          <div>
            @if($s['type'] === 'textarea')
            <textarea name="{{ $s['key'] }}" class="f-input" rows="3">{{ old($s['key'], $s['value']) }}</textarea>
            @elseif($s['type'] === 'number')
            <input type="number" name="{{ $s['key'] }}" class="f-input" style="max-width:160px"
              value="{{ old($s['key'], $s['value']) }}" min="0" step="1">
            @else
            <input type="text" name="{{ $s['key'] }}" class="f-input"
              value="{{ old($s['key'], $s['value']) }}">
            @endif
            @error($s['key'])
            <div class="f-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>
            @enderror
          </div>
        </div>
        @if(!$loop->last)
        <div style="height:1px;background:var(--border)"></div>
        @endif
        @endforeach
      </div>
    </div>
    @endforeach

    {{-- ══════════ PAYMENT GATEWAY ══════════ --}}
    @php
      $pg = $grouped['payment'] ?? collect();
      $pgMap = $pg->keyBy('key');
      $midtransEnabled    = old('midtrans_enabled',       $pgMap->get('midtrans_enabled')['value']       ?? '0');
      $midtransServerKey  = old('midtrans_server_key',    $pgMap->get('midtrans_server_key')['value']    ?? '');
      $midtransClientKey  = old('midtrans_client_key',    $pgMap->get('midtrans_client_key')['value']    ?? '');
      $midtransProduction = old('midtrans_is_production', $pgMap->get('midtrans_is_production')['value'] ?? '0');
    @endphp

    <div class="card animate-fadeUp d2" style="margin-bottom:0">
      <div class="card-header" style="justify-content:space-between;align-items:center">
        <div class="card-title">
          <i class="fa-solid fa-credit-card" style="color:var(--ac);margin-right:8px"></i>
          Payment Gateway (Midtrans)
        </div>
        @if($midtransEnabled === '1')
          <span class="badge badge-green"><i class="fa-solid fa-circle" style="font-size:7px"></i> Aktif</span>
        @else
          <span class="badge badge-gray">Nonaktif</span>
        @endif
      </div>
      <div class="card-body" style="display:flex;flex-direction:column;gap:24px">

        {{-- Master toggle --}}
        <div style="display:grid;grid-template-columns:280px 1fr;gap:24px;align-items:center">
          <div>
            <div style="font-size:13.5px;font-weight:600;color:var(--text)">Aktifkan Midtrans</div>
            <div style="font-size:12px;color:var(--muted);margin-top:3px">Aktifkan integrasi Midtrans Snap untuk pembayaran online</div>
          </div>
          <div class="toggle-wrap">
            <label class="toggle">
              <input type="checkbox" name="midtrans_enabled" value="1" id="midtrans-master"
                {{ $midtransEnabled === '1' ? 'checked' : '' }}
                onchange="toggleMidtransFields(this.checked)">
              <span class="toggle-slider"></span>
            </label>
            <span class="toggle-label" for="midtrans-master">{{ $midtransEnabled === '1' ? 'Aktif' : 'Nonaktif' }}</span>
          </div>
        </div>

        <div id="midtrans-fields" style="{{ $midtransEnabled !== '1' ? 'opacity:.45;pointer-events:none' : '' }};display:flex;flex-direction:column;gap:20px;transition:opacity .2s">

          <div style="height:1px;background:var(--border)"></div>

          {{-- Mode produksi --}}
          <div style="display:grid;grid-template-columns:280px 1fr;gap:24px;align-items:center">
            <div>
              <div style="font-size:13.5px;font-weight:600;color:var(--text)">Mode Produksi</div>
              <div style="font-size:12px;color:var(--muted);margin-top:3px">Nonaktif = Sandbox (testing). Aktif = transaksi sungguhan</div>
            </div>
            <div class="toggle-wrap">
              <label class="toggle">
                <input type="checkbox" name="midtrans_is_production" value="1" id="midtrans-prod"
                  {{ $midtransProduction === '1' ? 'checked' : '' }}>
                <span class="toggle-slider"></span>
              </label>
              <label class="toggle-label" for="midtrans-prod" id="prod-label">
                {{ $midtransProduction === '1' ? 'Production' : 'Sandbox' }}
              </label>
            </div>
          </div>

          <div style="height:1px;background:var(--border)"></div>

          {{-- Server Key --}}
          <div style="display:grid;grid-template-columns:280px 1fr;gap:24px;align-items:start">
            <div>
              <div style="font-size:13.5px;font-weight:600;color:var(--text)">Server Key</div>
              <div style="font-size:12px;color:var(--muted);margin-top:3px;line-height:1.5">
                Digunakan di <b>backend</b> saja. Jangan bagikan.<br>
                Sandbox: <code style="color:var(--ac);font-size:11px">SB-Mid-<b>server</b>-xxxx</code><br>
                Production: <code style="color:#34d399;font-size:11px">Mid-<b>server</b>-xxxx</code>
              </div>
            </div>
            <div>
              <div style="position:relative">
                <input type="password" name="midtrans_server_key" id="server-key-input" class="f-input"
                  value="{{ $midtransServerKey }}" placeholder="SB-Mid-server-... atau Mid-server-..."
                  oninput="validateKeyFormat(this,'server')">
                <button type="button" onclick="toggleVisibility('server-key-input','eye-server')"
                  style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--muted);font-size:13px">
                  <i class="fa-solid fa-eye" id="eye-server"></i>
                </button>
              </div>
              <div id="hint-server" style="font-size:11px;margin-top:5px;display:none"></div>
            </div>
          </div>

          {{-- Client Key --}}
          <div style="display:grid;grid-template-columns:280px 1fr;gap:24px;align-items:start">
            <div>
              <div style="font-size:13.5px;font-weight:600;color:var(--text)">Client Key</div>
              <div style="font-size:12px;color:var(--muted);margin-top:3px;line-height:1.5">
                Digunakan di <b>frontend</b> (browser).<br>
                Sandbox: <code style="color:var(--ac);font-size:11px">SB-Mid-<b>client</b>-xxxx</code><br>
                Production: <code style="color:#34d399;font-size:11px">Mid-<b>client</b>-xxxx</code>
              </div>
            </div>
            <div>
              <input type="text" name="midtrans_client_key" class="f-input"
                value="{{ $midtransClientKey }}" placeholder="SB-Mid-client-... atau Mid-client-..."
                oninput="validateKeyFormat(this,'client')">
              <div id="hint-client" style="font-size:11px;margin-top:5px;display:none"></div>
            </div>
          </div>

        </div>{{-- /midtrans-fields --}}

        {{-- ── Panduan Setup ── --}}
        <div style="height:1px;background:var(--border)"></div>
        <div>
          <div style="font-size:13px;font-weight:700;color:var(--text);margin-bottom:14px;display:flex;align-items:center;gap:8px">
            <i class="fa-solid fa-book-open" style="color:var(--ac)"></i> Panduan Setup Midtrans
          </div>
          <div class="setup-step">
            <div class="step-num">1</div>
            <div>
              <div style="font-size:13px;font-weight:600;color:var(--text)">Daftar akun Midtrans</div>
              <div style="font-size:12px;color:var(--muted);margin-top:3px">Buka <span style="color:var(--ac)">dashboard.midtrans.com</span> → daftar akun merchant → verifikasi email</div>
            </div>
          </div>
          <div class="setup-step">
            <div class="step-num">2</div>
            <div>
              <div style="font-size:13px;font-weight:600;color:var(--text)">Ambil API Keys</div>
              <div style="font-size:12px;color:var(--muted);margin-top:3px">Login → <b>Settings → Access Keys</b><br>
              Gunakan <span style="color:var(--ac)">Sandbox</span> dulu untuk testing, <span style="color:#34d399">Production</span> setelah siap live</div>
              <div class="code-block">Settings → Access Keys → Sandbox Client Key & Server Key</div>
            </div>
          </div>
          <div class="setup-step">
            <div class="step-num">3</div>
            <div>
              <div style="font-size:13px;font-weight:600;color:var(--text)">Install package PHP</div>
              <div style="font-size:12px;color:var(--muted);margin-top:3px">Jalankan di terminal project:</div>
              <div class="code-block">composer require midtrans/midtrans-php</div>
            </div>
          </div>
          <div class="setup-step">
            <div class="step-num">4</div>
            <div>
              <div style="font-size:13px;font-weight:600;color:var(--text)">Set Notification URL di Midtrans</div>
              <div style="font-size:12px;color:var(--muted);margin-top:3px">Settings → <b>Configuration</b> → Payment Notification URL:</div>
              <div class="code-block">https://domain-kamu.com/payment/callback</div>
              <div style="font-size:12px;color:var(--muted);margin-top:6px">Untuk testing lokal gunakan <span style="color:var(--ac)">ngrok</span> atau <span style="color:var(--ac)">Expose</span></div>
            </div>
          </div>
          <div class="setup-step">
            <div class="step-num">5</div>
            <div>
              <div style="font-size:13px;font-weight:600;color:var(--text)">Aktifkan & Simpan</div>
              <div style="font-size:12px;color:var(--muted);margin-top:3px">
                Isi Server Key & Client Key di form di atas → klik <b>Simpan</b>. Untuk mengaktifkan payment gateway per outlet, kelola melalui menu <b>Akun Owner</b>.
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

    {{-- ── Billing Settings ── --}}
    @php
      $bg = $grouped['billing'] ?? collect();
      $bgMap = $bg->keyBy('key');
      $billingGrace   = old('billing_grace_period', $bgMap->get('billing_grace_period')['value'] ?? '7');
      $billingMethodsRaw = old('billing_payment_methods', $bgMap->get('billing_payment_methods')['value'] ?? '');
      $activeBillingMethods = array_filter(array_map('trim', explode(',', $billingMethodsRaw)));
      $allBillingMethods = [
        'qris'        => ['label' => 'QRIS',         'icon' => 'fa-qrcode',      'color' => '#34d399'],
        'bca_va'      => ['label' => 'VA BCA',        'icon' => 'fa-building-columns', 'color' => '#60a5fa'],
        'bni_va'      => ['label' => 'VA BNI',        'icon' => 'fa-building-columns', 'color' => '#60a5fa'],
        'bri_va'      => ['label' => 'VA BRI',        'icon' => 'fa-building-columns', 'color' => '#60a5fa'],
        'permata_va'  => ['label' => 'VA Permata',    'icon' => 'fa-building-columns', 'color' => '#60a5fa'],
        'cimb_va'     => ['label' => 'VA CIMB',       'icon' => 'fa-building-columns', 'color' => '#60a5fa'],
        'danamon_va'  => ['label' => 'VA Danamon',    'icon' => 'fa-building-columns', 'color' => '#60a5fa'],
        'echannel'    => ['label' => 'Mandiri Bill',  'icon' => 'fa-building-columns', 'color' => '#f59e0b'],
      ];
    @endphp
    <div class="card animate-fadeUp" style="margin-bottom:0">
      <div class="card-header">
        <div class="card-title">
          <i class="fa-solid fa-file-invoice-dollar" style="color:var(--ac);margin-right:8px"></i>
          Billing Tagihan Aplikasi
        </div>
      </div>
      <div class="card-body" style="display:flex;flex-direction:column;gap:20px">

        {{-- Grace Period --}}
        <div style="display:grid;grid-template-columns:280px 1fr;gap:24px;align-items:start">
          <div>
            <div style="font-size:13.5px;font-weight:600;color:var(--text)">Grace Period (hari)</div>
            <div style="font-size:12px;color:var(--muted);margin-top:3px">Jumlah hari setelah due date sebelum akun owner disuspend otomatis</div>
          </div>
          <input type="number" name="billing_grace_period" class="f-input" min="0" max="30" value="{{ $billingGrace }}">
        </div>

        <div style="height:1px;background:var(--border)"></div>

        {{-- Metode Pembayaran Toggle --}}
        <div style="display:grid;grid-template-columns:280px 1fr;gap:24px;align-items:start">
          <div>
            <div style="font-size:13.5px;font-weight:600;color:var(--text)">Metode Pembayaran Tagihan</div>
            <div style="font-size:12px;color:var(--muted);margin-top:3px;line-height:1.6">
              Pilih metode yang tersedia untuk pembayaran tagihan aplikasi.<br>
              <i class="fa-solid fa-circle-info" style="color:var(--ac)"></i>
              Harus diaktifkan di <b>dashboard.midtrans.com</b> terlebih dahulu.
            </div>
          </div>
          <div style="display:flex;flex-direction:column;gap:8px">
            {{-- Hidden input agar value kosong juga terkirim --}}
            <input type="hidden" name="billing_payment_methods" id="billing-methods-hidden" value="{{ $billingMethodsRaw }}">
            @foreach($allBillingMethods as $key => $meta)
            @php $isActive = in_array($key, $activeBillingMethods); @endphp
            <div class="outlet-toggle-row" id="billing-row-{{ $key }}">
              <div style="display:flex;align-items:center;gap:10px">
                <div style="width:34px;height:34px;border-radius:9px;flex-shrink:0;display:grid;place-items:center;font-size:14px;
                            background:{{ $isActive ? 'var(--ac-lt)' : 'var(--surface)' }};
                            color:{{ $isActive ? 'var(--ac)' : 'var(--muted)' }};
                            border:1px solid {{ $isActive ? 'var(--ac)' : 'var(--border)' }};
                            transition:all .15s" id="billing-icon-{{ $key }}">
                  <i class="fa-solid {{ $meta['icon'] }}"></i>
                </div>
                <div>
                  <div style="font-size:13px;font-weight:600;color:var(--text)">{{ $meta['label'] }}</div>
                  <div style="font-size:11px;color:var(--muted);font-family:monospace">{{ $key }}</div>
                </div>
              </div>
              <label class="toggle" style="cursor:pointer">
                <input type="checkbox" class="billing-method-cb" value="{{ $key }}"
                  onchange="syncBillingMethods()"
                  {{ $isActive ? 'checked' : '' }}>
                <span class="toggle-slider"></span>
              </label>
            </div>
            @endforeach
          </div>
        </div>

      </div>
    </div>

    {{-- Save button --}}
    <div style="display:flex;justify-content:flex-end;gap:10px">
      <button type="reset" class="btn" style="padding:10px 22px">
        <i class="fa-solid fa-rotate-left"></i> Reset
      </button>
      @can('setting.update')
      <button type="submit" class="btn btn-primary" style="padding:10px 24px;font-size:14px">
        <i class="fa-solid fa-floppy-disk"></i> Simpan Pengaturan
      </button>
      @endcan
    </div>

  </form>

@push('scripts')
<script>
function toggleMidtransFields(enabled) {
  var fields = document.getElementById('midtrans-fields');
  fields.style.opacity         = enabled ? '1' : '.45';
  fields.style.pointerEvents   = enabled ? 'auto' : 'none';
}

function toggleVisibility(inputId, iconId) {
  var input = document.getElementById(inputId);
  var icon  = document.getElementById(iconId);
  if (input.type === 'password') {
    input.type   = 'text';
    icon.className = 'fa-solid fa-eye-slash';
  } else {
    input.type   = 'password';
    icon.className = 'fa-solid fa-eye';
  }
}

// Update Sandbox/Production label live
document.getElementById('midtrans-prod').addEventListener('change', function() {
  document.getElementById('prod-label').textContent = this.checked ? 'Production' : 'Sandbox';
});

// Validasi format key saat mengetik
function validateKeyFormat(input, type) {
  var val  = input.value.trim();
  var hint = document.getElementById('hint-' + type);
  if (!val) { hint.style.display = 'none'; return; }

  var isServer = type === 'server';
  var correct  = isServer
    ? (val.includes('-server-'))
    : (val.includes('-client-'));
  var wrong = isServer
    ? (val.includes('-client-'))
    : (val.includes('-server-'));

  hint.style.display = 'block';
  if (wrong) {
    hint.style.color = '#f87171';
    hint.innerHTML   = '<i class="fa-solid fa-triangle-exclamation"></i> Sepertinya ini ' + (isServer ? 'Client' : 'Server') + ' Key — pastikan tidak tertukar!';
  } else if (correct) {
    hint.style.color = '#34d399';
    hint.innerHTML   = '<i class="fa-solid fa-circle-check"></i> Format ' + (isServer ? 'Server' : 'Client') + ' Key terdeteksi.';
  } else {
    hint.style.display = 'none';
  }
}

// Sync toggle switches → hidden input billing_payment_methods
function syncBillingMethods() {
  var checked = Array.from(document.querySelectorAll('.billing-method-cb:checked'))
    .map(function(cb) { return cb.value; });
  document.getElementById('billing-methods-hidden').value = checked.join(',');

  // Update ikon visual setiap baris
  document.querySelectorAll('.billing-method-cb').forEach(function(cb) {
    var key  = cb.value;
    var icon = document.getElementById('billing-icon-' + key);
    if (!icon) return;
    if (cb.checked) {
      icon.style.background   = 'var(--ac-lt)';
      icon.style.color        = 'var(--ac)';
      icon.style.borderColor  = 'var(--ac)';
    } else {
      icon.style.background   = 'var(--surface)';
      icon.style.color        = 'var(--muted)';
      icon.style.borderColor  = 'var(--border)';
    }
  });
}
</script>
@endpush

</x-app-layout>
