<x-app-layout>
<x-slot name="pageTitle">Pengaturan Sistem</x-slot>

<style>
  .settings-grid{display:grid;grid-template-columns:220px 1fr;gap:20px;align-items:start}
  .settings-tab{display:flex;align-items:center;gap:10px;padding:11px 14px;border-radius:10px;font-size:13.5px;font-weight:600;margin-bottom:4px;text-decoration:none}
  .settings-tab i{width:16px;text-align:center}
  .settings-tab.active{color:var(--ac);background:var(--ac-lt)}
  .settings-tab:not(.active){color:var(--sub)}
  .settings-tab:not(.active):hover{background:var(--surface2);color:var(--text)}
  @media(max-width:760px){.settings-grid{grid-template-columns:1fr}}
</style>

<div class="settings-grid">

  {{-- Tab vertikal --}}
  <div class="card" style="padding:10px">
    <a href="{{ route('system-settings.edit') }}" class="settings-tab {{ $tab === 'general' ? 'active' : '' }}">
      <i class="fa-solid fa-earth-asia"></i> Umum
    </a>
    <a href="{{ route('system-settings.whatsapp') }}" class="settings-tab {{ $tab === 'whatsapp' ? 'active' : '' }}">
      <i class="fa-brands fa-whatsapp"></i> WhatsApp
    </a>
    <a href="{{ route('system-settings.rate-limit') }}" class="settings-tab {{ $tab === 'rate-limit' ? 'active' : '' }}">
      <i class="fa-solid fa-shield-halved"></i> Rate Limiting
    </a>
  </div>

  {{-- Konten tab --}}
  <div>
    @if($tab === 'general')
      @include('settings.system-tabs.general')
    @elseif($tab === 'whatsapp')
      @include('settings.system-tabs.whatsapp')
    @elseif($tab === 'rate-limit')
      @include('settings.system-tabs.rate-limit')
    @endif
  </div>

</div>

</x-app-layout>
