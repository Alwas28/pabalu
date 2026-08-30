{{-- Tab: Gateway WhatsApp (Verifikasi/OTP) --}}
<div class="card">
  <div style="padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px">
    <div style="width:34px;height:34px;border-radius:9px;background:var(--ac-lt);color:var(--ac);display:grid;place-items:center;font-size:14px;flex-shrink:0">
      <i class="fa-brands fa-whatsapp"></i>
    </div>
    <div>
      <div style="font-size:15px;font-weight:700;color:var(--text)">Gateway WhatsApp (Verifikasi/OTP)</div>
      <div style="font-size:12px;color:var(--muted)">API tidak resmi — pakai nomor WA khusus (bukan nomor pribadi), lihat catatan di bawah</div>
    </div>
  </div>

  <form method="POST" action="{{ route('system-settings.whatsapp.update') }}">
    @csrf
    @method('PUT')
    <div style="padding:20px 22px;display:flex;flex-direction:column;gap:14px">

      <label style="display:flex;align-items:center;gap:10px;cursor:pointer">
        <input type="checkbox" name="wa_gateway_enabled" value="1" {{ $waEnabled ? 'checked' : '' }}
          style="width:16px;height:16px;accent-color:var(--ac);cursor:pointer">
        <span style="font-size:13px;font-weight:600;color:var(--text)">Aktifkan pengiriman kode verifikasi lewat WhatsApp</span>
      </label>

      <div>
        <label class="f-label">Provider</label>
        <select name="wa_gateway_provider" class="f-input">
          @foreach($waProviders as $val => $label)
          <option value="{{ $val }}" {{ old('wa_gateway_provider', $waProvider) === $val ? 'selected' : '' }}>{{ $label }}</option>
          @endforeach
        </select>
        @error('wa_gateway_provider')
          <p style="font-size:11.5px;color:#f87171;margin-top:5px">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label class="f-label">API Key / Token</label>
        <input type="password" name="wa_gateway_api_key" class="f-input" autocomplete="off"
          placeholder="{{ $waHasKey ? 'Sudah diisi — kosongkan kalau tidak ganti' : 'Token dari dashboard provider' }}">
        @error('wa_gateway_api_key')
          <p style="font-size:11.5px;color:#f87171;margin-top:5px">{{ $message }}</p>
        @enderror
      </div>

      @php
        $waNeedsDomain = in_array(old('wa_gateway_provider', $waProvider), \App\Services\WhatsApp\WhatsAppGatewayManager::PROVIDERS_NEEDING_DOMAIN, true);
        $isSelfHost    = old('wa_gateway_provider', $waProvider) === 'selfhost';
      @endphp
      <div id="wa-domain-field" style="{{ $waNeedsDomain ? '' : 'display:none' }}">
        <label class="f-label" id="wa-domain-label">{{ $isSelfHost ? 'URL Service Self-Host' : 'Domain Server Wablas' }}</label>
        <input name="wa_gateway_api_domain" type="text" class="f-input" id="wa-domain-input"
          value="{{ old('wa_gateway_api_domain', $waDomain) }}"
          placeholder="{{ $isSelfHost ? 'http://127.0.0.1:3001' : 'https://xxx.wablas.com' }}">
        <p style="font-size:11px;color:var(--muted);margin-top:5px" id="wa-domain-help">
          {{ $isSelfHost
            ? 'Alamat service Node.js self-host Anda (lihat folder /whatsapp-gateway di repo, README-nya).'
            : 'Khusus Wablas — domain server ini beda per akun, lihat dashboard Wablas Anda.' }}
        </p>
        @error('wa_gateway_api_domain')
          <p style="font-size:11.5px;color:#f87171;margin-top:5px">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label class="f-label">Nomor Pengirim <span style="font-weight:400;text-transform:none;font-size:11px;color:var(--muted)">(catatan saja, tidak dipakai sistem)</span></label>
        <input name="wa_gateway_sender_number" type="text" class="f-input"
          value="{{ old('wa_gateway_sender_number', $waSender) }}"
          placeholder="08xxxxxxxxxx">
      </div>

      <p style="font-size:11px;color:var(--muted);line-height:1.6;background:var(--surface2);border-radius:10px;padding:10px 12px">
        <i class="fa-solid fa-circle-info" style="margin-right:4px"></i>
        Nomor WA yang dihubungkan ke provider harus SIM/perangkat terpisah dari nomor pribadi —
        otomasi tidak resmi berisiko kena banned sewaktu-waktu. Kode verifikasi tetap dikirim ke
        email kalau pengiriman WhatsApp gagal.
      </p>
    </div>
    <div style="padding:14px 22px;border-top:1px solid var(--border);display:flex;justify-content:flex-end">
      <button type="submit"
        style="padding:9px 20px;border-radius:10px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
        <i class="fa-solid fa-floppy-disk" style="margin-right:6px"></i>Simpan
      </button>
    </div>
  </form>

  <div style="padding:16px 22px;border-top:1px dashed var(--border)">
    <form method="POST" action="{{ route('system-settings.whatsapp.test') }}" style="display:flex;gap:10px;align-items:flex-end">
      @csrf
      <div style="flex:1">
        <label class="f-label">Tes Kirim ke Nomor</label>
        <input name="test_phone" type="text" class="f-input" placeholder="08xxxxxxxxxx" required>
      </div>
      <button type="submit"
        style="padding:9px 18px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--text);font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;white-space:nowrap">
        <i class="fa-solid fa-paper-plane" style="margin-right:6px"></i>Kirim Tes
      </button>
    </form>
    <p style="font-size:11px;color:var(--muted);margin-top:8px">Simpan pengaturan di atas dulu sebelum tes kirim.</p>
  </div>
</div>

<script>
document.querySelector('select[name="wa_gateway_provider"]')?.addEventListener('change', function () {
  const needsDomain = ['wablas', 'selfhost'].includes(this.value);
  const isSelfHost  = this.value === 'selfhost';

  document.getElementById('wa-domain-field').style.display = needsDomain ? '' : 'none';
  document.getElementById('wa-domain-label').textContent = isSelfHost ? 'URL Service Self-Host' : 'Domain Server Wablas';
  document.getElementById('wa-domain-input').placeholder = isSelfHost ? 'http://127.0.0.1:3001' : 'https://xxx.wablas.com';
  document.getElementById('wa-domain-help').textContent = isSelfHost
    ? 'Alamat service Node.js self-host Anda (lihat folder /whatsapp-gateway di repo, README-nya).'
    : 'Khusus Wablas — domain server ini beda per akun, lihat dashboard Wablas Anda.';
});
</script>
