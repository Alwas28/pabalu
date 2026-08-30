{{-- Tab: Rate Limiting (keamanan) --}}
<div class="card">
  <div style="padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px">
    <div style="width:34px;height:34px;border-radius:9px;background:var(--ac-lt);color:var(--ac);display:grid;place-items:center;font-size:14px;flex-shrink:0">
      <i class="fa-solid fa-shield-halved"></i>
    </div>
    <div>
      <div style="font-size:15px;font-weight:700;color:var(--text)">Rate Limiting</div>
      <div style="font-size:12px;color:var(--muted)">Batasi jumlah percobaan pada aksi sensitif untuk mencegah brute-force/spam</div>
    </div>
  </div>

  <form method="POST" action="{{ route('system-settings.rate-limit.update') }}">
    @csrf
    @method('PUT')
    <div style="padding:20px 22px;display:flex;flex-direction:column;gap:18px">
      @foreach($rateLimits as $name => $limit)
      <div style="border:1px solid var(--border);border-radius:12px;padding:14px 16px">
        <div style="font-size:13px;font-weight:700;color:var(--text);margin-bottom:10px">{{ $limit['label'] }}</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
          <div>
            <label class="f-label">Maks. Percobaan</label>
            <input type="number" name="{{ $name }}_max" class="f-input" min="1" max="1000"
              value="{{ old($name.'_max', $limit['max']) }}" required>
            @error($name.'_max')
              <p style="font-size:11.5px;color:#f87171;margin-top:5px">{{ $message }}</p>
            @enderror
          </div>
          <div>
            <label class="f-label">Per (menit)</label>
            <input type="number" name="{{ $name }}_minutes" class="f-input" min="1" max="1440"
              value="{{ old($name.'_minutes', $limit['minutes']) }}" required>
            @error($name.'_minutes')
              <p style="font-size:11.5px;color:#f87171;margin-top:5px">{{ $message }}</p>
            @enderror
          </div>
        </div>
      </div>
      @endforeach

      <p style="font-size:11px;color:var(--muted);line-height:1.6;background:var(--surface2);border-radius:10px;padding:10px 12px">
        <i class="fa-solid fa-circle-info" style="margin-right:4px"></i>
        Contoh: "5 percobaan per 1 menit" berarti IP/akun yang sama dibatasi maksimal 5x
        aksi dalam 1 menit — percobaan ke-6 ditolak (HTTP 429) sampai jendela waktunya
        berlalu. Perubahan di sini berlaku langsung tanpa perlu deploy ulang kode.
      </p>
    </div>
    <div style="padding:14px 22px;border-top:1px solid var(--border);display:flex;justify-content:flex-end">
      <button type="submit"
        style="padding:9px 20px;border-radius:10px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
        <i class="fa-solid fa-floppy-disk" style="margin-right:6px"></i>Simpan
      </button>
    </div>
  </form>
</div>
