<x-app-layout>
<x-slot name="pageTitle">Pengaturan Sistem</x-slot>

<div style="max-width:560px">

  <div class="card animate-fadeUp">
    <div style="padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px">
      <div style="width:34px;height:34px;border-radius:9px;background:var(--ac-lt);color:var(--ac);display:grid;place-items:center;font-size:14px;flex-shrink:0">
        <i class="fa-solid fa-earth-asia"></i>
      </div>
      <div>
        <div style="font-size:15px;font-weight:700;color:var(--text)">Zona Waktu</div>
        <div style="font-size:12px;color:var(--muted)">Menentukan waktu "sekarang" yang dipakai di seluruh aplikasi</div>
      </div>
    </div>

    <form method="POST" action="{{ route('system-settings.update') }}">
      @csrf
      @method('PUT')
      <div style="padding:20px 22px;display:flex;flex-direction:column;gap:14px">
        <div>
          <label class="f-label">Zona Waktu Aplikasi</label>
          <select name="timezone" class="f-input">
            @foreach($timezones as $val => $label)
            <option value="{{ $val }}" {{ $timezone === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
          </select>
          <p style="font-size:11.5px;color:var(--muted);margin-top:6px;line-height:1.6">
            Dipakai untuk menentukan tanggal &amp; jam "sekarang" pada seluruh outlet (mis. default waktu mulai sewa, laporan harian, dsb).
            Default: <strong>WITA</strong> (Waktu Indonesia Tengah).
          </p>
        </div>
      </div>
      <div style="padding:14px 22px;border-top:1px solid var(--border);display:flex;justify-content:flex-end">
        <button type="submit"
          style="padding:9px 20px;border-radius:10px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
          <i class="fa-solid fa-floppy-disk" style="margin-right:6px"></i>Simpan
        </button>
      </div>
    </form>
  </div>

</div>

</x-app-layout>
