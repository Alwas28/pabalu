<x-app-layout>
<x-slot name="pageTitle">Paket Pro</x-slot>

@if(session('success'))
<div style="padding:12px 16px;border-radius:12px;background:rgba(52,211,153,.12);border:1px solid rgba(52,211,153,.3);
     display:flex;align-items:center;gap:10px;font-size:12.5px;color:#34d399;font-weight:600">
  <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div style="padding:12px 16px;border-radius:12px;background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.3);
     display:flex;align-items:center;gap:10px;font-size:12.5px;color:#f87171;font-weight:600">
  <i class="fa-solid fa-circle-xmark"></i> {{ session('error') }}
</div>
@endif

@if(!$subscription['is_free'] && $subscription['days_left'] !== null && $subscription['days_left'] <= 7)
<div style="margin-top:14px;padding:16px 20px;border-radius:14px;background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.3);
     display:flex;align-items:center;gap:14px">
  <div style="width:40px;height:40px;border-radius:11px;background:rgba(248,113,113,.15);display:grid;place-items:center;color:#f87171;font-size:17px;flex-shrink:0">
    <i class="fa-solid fa-triangle-exclamation"></i>
  </div>
  <div style="flex:1">
    <div style="font-size:13.5px;font-weight:700;color:var(--text)">
      Paket {{ $subscription['plan_name'] }} akan berakhir {{ $subscription['days_left'] }} hari lagi
    </div>
    <div style="font-size:12px;color:var(--muted);margin-top:2px">
      Berakhir pada {{ \Carbon\Carbon::parse($subscription['expires_at'])->translatedFormat('d F Y') }} — setelah itu akun akan otomatis kembali ke paket Free.
      Masukkan kode baru di bawah supaya tidak terputus.
    </div>
  </div>
</div>
@endif

<div class="stat-grid" style="margin-top:16px">
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(251,191,36,.15);color:#fbbf24"><i class="fa-solid fa-crown"></i></div>
    <div>
      <div class="stat-num" style="font-size:19px">{{ $subscription['plan_name'] }}</div>
      <div class="stat-label">Paket Aktif</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(129,140,248,.15);color:#818cf8"><i class="fa-solid fa-shop"></i></div>
    <div>
      <div class="stat-num" style="font-size:19px">{{ $subscription['max_outlet_types'] === null ? 'Tanpa batas' : $subscription['max_outlet_types'] . ' outlet' }}</div>
      <div class="stat-label">Batas Jumlah Outlet</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(236,72,153,.15);color:#ec4899"><i class="fa-solid fa-user-tag"></i></div>
    <div>
      <div class="stat-num" style="font-size:19px">{{ $subscription['max_kasir'] === null ? 'Tanpa batas' : $subscription['max_kasir'] . ' akun' }}</div>
      <div class="stat-label">Batas Akun Kasir</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(52,211,153,.15);color:#34d399"><i class="fa-solid fa-calendar-check"></i></div>
    <div>
      <div class="stat-num" style="font-size:19px">{{ $subscription['expires_at'] ? \Carbon\Carbon::parse($subscription['expires_at'])->format('d/m/Y') : 'Tanpa batas' }}</div>
      <div class="stat-label">Berlaku Sampai</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(96,165,250,.15);color:#60a5fa"><i class="fa-solid fa-hourglass-half"></i></div>
    <div>
      <div class="stat-num" style="font-size:19px">{{ $subscription['days_left'] !== null ? $subscription['days_left'] . ' hari' : '—' }}</div>
      <div class="stat-label">Sisa Waktu</div>
    </div>
  </div>
</div>

{{-- ── TAB BAR ── --}}
<div style="display:flex;gap:4px;background:var(--surface2);border-radius:12px;padding:4px;width:fit-content;margin-top:20px">
  <button id="tab-btn-upgrade" onclick="switchTab('upgrade')"
    style="padding:8px 20px;border-radius:9px;border:none;font-size:13.5px;font-weight:600;cursor:pointer;transition:all .18s">
    <i class="fa-solid fa-crown" style="margin-right:6px;font-size:12px"></i>Upgrade Paket
  </button>
  <button id="tab-btn-aktivasi" onclick="switchTab('aktivasi')"
    style="padding:8px 20px;border-radius:9px;border:none;font-size:13.5px;font-weight:600;cursor:pointer;transition:all .18s">
    <i class="fa-solid fa-key" style="margin-right:6px;font-size:12px"></i>Aktivasi / Perpanjang
  </button>
  <button id="tab-btn-riwayat" onclick="switchTab('riwayat')"
    style="padding:8px 20px;border-radius:9px;border:none;font-size:13.5px;font-weight:600;cursor:pointer;transition:all .18s">
    <i class="fa-solid fa-clock-rotate-left" style="margin-right:6px;font-size:12px"></i>Riwayat
    <span style="margin-left:4px;background:var(--ac);color:#fff;font-size:10px;font-weight:800;padding:1px 6px;border-radius:99px">{{ $history->count() }}</span>
  </button>
</div>

{{-- ════════ TAB: UPGRADE PAKET ════════ --}}
@php
  $adminContact = \App\Models\User::whereHas('roleRelation', fn($q) => $q->where('slug', 'admin'))->first();
  $adminWaPhone = $adminContact?->phone ? preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $adminContact->phone)) : null;
@endphp
<div id="tab-upgrade" style="margin-top:16px">
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:14px;max-width:900px">
    @foreach($plans as $p)
    @php $isCurrent = $p->id === $currentPlanId; @endphp
    <div class="card" style="{{ $isCurrent ? 'border-color:var(--ac)' : '' }}">
      <div class="card-header" style="display:flex;align-items:center;justify-content:space-between">
        <div class="card-title">{{ $p->name }}</div>
        @if($isCurrent)
        <span style="font-size:10px;font-weight:800;padding:2px 8px;border-radius:99px;background:rgba(52,211,153,.15);color:#34d399">AKTIF</span>
        @endif
      </div>
      <div style="padding:18px 20px">
        <div style="font-size:22px;font-weight:800;color:var(--text)">
          {{ $p->price > 0 ? 'Rp ' . number_format($p->price, 0, ',', '.') : 'Gratis' }}
        </div>
        @if($p->description)
        <div style="font-size:12px;color:var(--muted);margin-top:6px;line-height:1.6">{{ $p->description }}</div>
        @endif
        <div style="margin-top:14px;display:flex;flex-direction:column;gap:6px;font-size:12px;color:var(--text)">
          <div><i class="fa-solid fa-shop" style="width:16px;color:var(--ac)"></i> {{ $p->max_outlet_types === null ? 'Outlet tanpa batas' : $p->max_outlet_types . ' outlet' }}</div>
          <div><i class="fa-solid fa-user-tag" style="width:16px;color:var(--ac)"></i> {{ $p->max_kasir === null ? 'Akun kasir tanpa batas' : $p->max_kasir . ' akun kasir' }}</div>
        </div>

        @if($p->allowedOutletTypes->isNotEmpty())
        <div style="margin-top:8px;font-size:11px;color:var(--muted)">Jenis outlet: {{ $p->allowedOutletTypes->pluck('name')->join(', ') }}</div>
        @endif

        @if($isCurrent)
        <div style="margin-top:14px;text-align:center;font-size:12px;color:var(--muted);font-weight:600">Ini paket Anda saat ini</div>
        @elseif($p->is_default)
        <div style="margin-top:14px;text-align:center;font-size:11.5px;color:var(--muted)">Paket bawaan, otomatis aktif tanpa kode.</div>
        @elseif($p->is_self_activatable)
        <form method="POST" action="{{ route('pro.subscription.activate', $p) }}" style="margin-top:14px">
          @csrf
          <button type="submit"
            style="width:100%;padding:10px;border-radius:10px;border:none;background:linear-gradient(135deg,#10b981,#059669);color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
            <i class="fa-solid fa-bolt" style="margin-right:6px"></i>Aktifkan Sekarang
          </button>
          <p style="font-size:10px;color:var(--muted);margin-top:6px;text-align:center">Tanpa kode — tagihan dihitung belakangan sesuai pemakaian per outlet.</p>
        </form>
        @elseif($adminWaPhone)
        <a href="https://wa.me/{{ $adminWaPhone }}?text={{ rawurlencode("Halo Admin Pabalu, saya {$owner->name} ingin upgrade ke paket {$p->name}. Mohon info cara pembayaran & kode aktivasinya.") }}"
           target="_blank" rel="noopener"
           style="margin-top:14px;display:block;text-align:center;padding:10px;border-radius:10px;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:600;text-decoration:none">
          <i class="fa-brands fa-whatsapp" style="margin-right:6px"></i>Upgrade via WhatsApp
        </a>
        @else
        <div style="margin-top:14px;text-align:center;font-size:11.5px;color:var(--muted)">Hubungi admin Pabalu untuk upgrade ke paket ini.</div>
        @endif
      </div>
    </div>
    @endforeach
  </div>
  <p style="font-size:11px;color:var(--muted);margin-top:14px;line-height:1.6;max-width:900px">
    Harga & masa aktif final ditentukan admin saat memproses upgrade. Setelah pembayaran dikonfirmasi, admin akan
    memberikan kode aktivasi — masukkan di tab <b>"Aktivasi / Perpanjang"</b> untuk mengaktifkan paket.
  </p>
</div>

{{-- ════════ TAB: AKTIVASI / PERPANJANG ════════ --}}
<div id="tab-aktivasi" style="margin-top:16px">
  <div class="card" style="max-width:420px">
    <div class="card-header">
      <div class="card-title"><i class="fa-solid fa-key a-text" style="margin-right:8px"></i>Aktivasi / Perpanjang</div>
    </div>
    <form method="POST" action="{{ route('pro.subscription.redeem') }}" style="padding:18px 20px">
      @csrf
      <label class="f-label">Masukkan Kode/PIN</label>
      <input name="code" class="f-input" placeholder="cth: PABALU-XXXX-XXXX" style="text-transform:uppercase;letter-spacing:1px" maxlength="40" required
             value="{{ old('code') }}">
      <button type="submit"
        style="width:100%;margin-top:12px;padding:10px;border-radius:10px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
        Aktifkan Kode
      </button>
      <p style="font-size:11px;color:var(--muted);margin-top:10px;line-height:1.6">
        Belum punya kode? Hubungi admin Pabalu untuk membeli paket Pro.
      </p>
    </form>
  </div>
</div>

{{-- ════════ TAB: RIWAYAT ════════ --}}
<div id="tab-riwayat" style="margin-top:16px">
  <div class="card" style="max-width:420px">
    <div class="card-header">
      <div class="card-title"><i class="fa-solid fa-clock-rotate-left a-text" style="margin-right:8px"></i>Riwayat Kode</div>
    </div>
    <div style="padding:6px 20px 16px">
      @forelse($history as $h)
      <div style="padding:10px 0;border-bottom:1px solid var(--border)">
        <div style="font-size:12.5px;font-weight:600;color:var(--text)"><code style="font-size:11px">{{ $h['code'] }}</code></div>
        <div style="font-size:11.5px;color:var(--muted);margin-top:2px">
          {{ $h['plan'] }} · dipakai {{ \Carbon\Carbon::parse($h['redeemed_at'])->translatedFormat('d M Y') }}
        </div>
      </div>
      @empty
      <div style="padding:16px 0;font-size:12.5px;color:var(--muted);text-align:center">Belum ada kode yang pernah diaktifkan.</div>
      @endforelse
    </div>
  </div>
</div>

@push('scripts')
<script>
function switchTab(tab) {
  const tabs = ['upgrade','aktivasi','riwayat'];
  tabs.forEach(t => {
    document.getElementById('tab-' + t).style.display = t === tab ? '' : 'none';
    const btn = document.getElementById('tab-btn-' + t);
    if (t === tab) {
      btn.style.background = 'var(--surface)';
      btn.style.color      = 'var(--ac)';
      btn.style.boxShadow  = '0 1px 4px rgba(0,0,0,.18)';
    } else {
      btn.style.background = 'transparent';
      btn.style.color      = 'var(--sub)';
      btn.style.boxShadow  = 'none';
    }
  });
}

document.addEventListener('DOMContentLoaded', () => {
  switchTab('upgrade');
});
</script>
@endpush

</x-app-layout>
