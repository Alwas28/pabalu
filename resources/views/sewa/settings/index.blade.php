<x-outlet-layout :outlet="$outlet" pageTitle="Pengaturan Rental">

<style>
  .settings-grid{display:grid;grid-template-columns:220px 1fr;gap:20px;align-items:start}
  .settings-tab{display:flex;align-items:center;gap:10px;padding:11px 14px;border-radius:10px;font-size:13.5px;font-weight:600;margin-bottom:4px;text-decoration:none}
  .settings-tab i{width:16px;text-align:center}
  .settings-tab.active{color:var(--ac);background:var(--ac-lt)}
  .settings-tab:not(.active){color:var(--sub)}
  .settings-tab:not(.active):hover{background:var(--surface2);color:var(--text)}
  @media(max-width:760px){.settings-grid{grid-template-columns:1fr}}

  .sw-wrap{position:relative;display:inline-block;width:46px;height:25px;flex-shrink:0;cursor:pointer}
  .sw-wrap input{opacity:0;width:0;height:0;position:absolute}
  .sw-track{position:absolute;inset:0;border-radius:99px;background:var(--border);transition:background .2s}
  .sw-thumb{position:absolute;top:3px;left:3px;width:19px;height:19px;border-radius:50%;background:#fff;box-shadow:0 1px 4px rgba(0,0,0,.25);transition:transform .2s}
  .sw-wrap input:checked~.sw-track{background:var(--ac)}
  .sw-wrap input:checked~.sw-thumb{transform:translateX(21px)}

  .req-chip{padding:6px 12px;border-radius:8px;border:1.5px solid var(--border);font-size:12px;font-weight:600;color:var(--sub);cursor:pointer;transition:.15s;user-select:none;display:inline-block}
  .req-chip:has(input:checked){border-color:var(--ac);background:var(--ac-lt);color:var(--ac)}
  .req-chip input{display:none}

  .btn-save{padding:10px 20px;border-radius:10px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit}
</style>

<div class="settings-grid">

  {{-- Tab vertikal --}}
  <div class="card" style="padding:10px">
    <a href="{{ $outlet->route('settings.edit', ['tab' => 'booking']) }}" class="settings-tab {{ $tab === 'booking' ? 'active' : '' }}">
      <i class="fa-solid fa-calendar-check"></i> Booking
    </a>
    <a href="{{ $outlet->route('settings.edit', ['tab' => 'requirements']) }}" class="settings-tab {{ $tab === 'requirements' ? 'active' : '' }}">
      <i class="fa-solid fa-list-check"></i> Persyaratan Dokumen
    </a>
    <a href="{{ $outlet->route('settings.edit', ['tab' => 'payment']) }}" class="settings-tab {{ $tab === 'payment' ? 'active' : '' }}">
      <i class="fa-solid fa-credit-card"></i> Metode Pembayaran
    </a>
    <a href="{{ $outlet->route('settings.edit', ['tab' => 'receipt']) }}" class="settings-tab {{ $tab === 'receipt' ? 'active' : '' }}">
      <i class="fa-solid fa-receipt"></i> Jenis Struk
    </a>
  </div>

  {{-- Konten tab --}}
  <div>
    @if($tab === 'requirements')
      @include('sewa.settings.tabs.requirements')
    @elseif($tab === 'payment')
      @include('sewa.settings.tabs.payment')
    @elseif($tab === 'receipt')
      @include('sewa.settings.tabs.receipt')
    @else
      @include('sewa.settings.tabs.booking')
    @endif
  </div>

</div>

</x-outlet-layout>
