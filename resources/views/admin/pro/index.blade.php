<x-app-layout>
<x-slot name="pageTitle">Kelola Paket Pro</x-slot>

<style>
  .pro-grid{display:grid;grid-template-columns:220px 1fr;gap:20px;align-items:start}
  .pro-tab{display:flex;align-items:center;gap:10px;padding:11px 14px;border-radius:10px;font-size:13.5px;font-weight:600;margin-bottom:4px;text-decoration:none}
  .pro-tab i{width:16px;text-align:center}
  .pro-tab.active{color:var(--ac);background:var(--ac-lt)}
  .pro-tab:not(.active){color:var(--sub)}
  .pro-tab:not(.active):hover{background:var(--surface2);color:var(--text)}
  @media(max-width:760px){.pro-grid{grid-template-columns:1fr}}
</style>

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
@if($errors->any())
<div style="padding:12px 16px;border-radius:12px;background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.3);
     font-size:12.5px;color:#f87171;font-weight:600">
  <i class="fa-solid fa-circle-xmark"></i> {{ $errors->first() }}
</div>
@endif

<div class="pro-grid" style="margin-top:16px">

  {{-- Tab vertikal --}}
  <div class="card" style="padding:10px">
    <a href="{{ route('admin.pro.features') }}" class="pro-tab {{ $tab === 'fitur' ? 'active' : '' }}">
      <i class="fa-solid fa-puzzle-piece"></i> Fitur
    </a>
    <a href="{{ route('admin.pro.plans') }}" class="pro-tab {{ $tab === 'paket' ? 'active' : '' }}">
      <i class="fa-solid fa-crown"></i> Paket
    </a>
    <a href="{{ route('admin.pro.codes') }}" class="pro-tab {{ $tab === 'kode' ? 'active' : '' }}">
      <i class="fa-solid fa-key"></i> Kode Aktivasi
    </a>
  </div>

  {{-- Konten tab --}}
  <div>
    @if($tab === 'fitur')
      @include('admin.pro.tabs.features')
    @elseif($tab === 'paket')
      @include('admin.pro.tabs.plans')
    @elseif($tab === 'kode')
      @include('admin.pro.tabs.codes')
    @endif
  </div>

</div>

</x-app-layout>
