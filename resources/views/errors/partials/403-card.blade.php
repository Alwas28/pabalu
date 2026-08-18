<div style="max-width:520px;margin:60px auto;text-align:center;display:flex;flex-direction:column;align-items:center;gap:18px">
  <div style="width:72px;height:72px;border-radius:20px;background:{{ $isPlanLock ? 'rgba(251,191,36,.12)' : 'rgba(239,68,68,.12)' }};color:{{ $isPlanLock ? '#fbbf24' : '#f87171' }};display:grid;place-items:center;font-size:30px">
    <i class="fa-solid {{ $isPlanLock ? 'fa-crown' : 'fa-lock' }}"></i>
  </div>
  <div>
    <h1 class="font-display" style="font-size:19px;font-weight:700;color:var(--text);margin-bottom:8px">
      {{ $isPlanLock ? 'Fitur Belum Termasuk Paket Anda' : 'Akses Ditolak' }}
    </h1>
    <p style="font-size:13.5px;color:var(--muted);line-height:1.6;margin:0">{{ $message }}</p>
  </div>
  <div style="display:flex;gap:10px;flex-wrap:wrap;justify-content:center;margin-top:4px">
    @if($isPlanLock)
    <a href="{{ route('pro.subscription') }}"
       style="display:inline-flex;align-items:center;gap:7px;padding:10px 20px;border-radius:10px;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:600;text-decoration:none">
      <i class="fa-solid fa-crown"></i>Upgrade Paket
    </a>
    @endif
    <a href="{{ $backUrl }}"
       style="display:inline-flex;align-items:center;gap:7px;padding:10px 20px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:13px;font-weight:600;text-decoration:none">
      <i class="fa-solid fa-arrow-left"></i>{{ $backLabel }}
    </a>
  </div>
</div>
