<x-app-layout>
<x-slot name="pageTitle">Pengaturan Homepage</x-slot>

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
    <a href="{{ route('settings.header') }}" class="settings-tab {{ $tab === 'header' ? 'active' : '' }}">
      <i class="fa-solid fa-images"></i> Header (Slider)
    </a>
    <a href="{{ route('settings.menu') }}" class="settings-tab {{ $tab === 'menu' ? 'active' : '' }}">
      <i class="fa-solid fa-bars"></i> Menu
    </a>
    <a href="{{ route('settings.categories') }}" class="settings-tab {{ $tab === 'categories' ? 'active' : '' }}">
      <i class="fa-solid fa-tags"></i> Kategori
    </a>
  </div>

  {{-- Konten tab --}}
  <div>
    @if($tab === 'header')
      @include('settings.tabs.header')
    @elseif($tab === 'menu')
      @include('settings.tabs.menu')
    @elseif($tab === 'categories')
      @include('settings.tabs.categories')
    @endif
  </div>

</div>

</x-app-layout>
