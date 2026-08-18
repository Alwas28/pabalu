<x-app-layout>
<x-slot name="pageTitle">Tagihan Pro Plan</x-slot>

<style>
  .bill-grid{display:grid;grid-template-columns:220px 1fr;gap:20px;align-items:start}
  .bill-tab{display:flex;align-items:center;gap:10px;padding:11px 14px;border-radius:10px;font-size:13.5px;font-weight:600;margin-bottom:4px;text-decoration:none}
  .bill-tab i{width:16px;text-align:center}
  .bill-tab.active{color:var(--ac);background:var(--ac-lt)}
  .bill-tab:not(.active){color:var(--sub)}
  .bill-tab:not(.active):hover{background:var(--surface2);color:var(--text)}
  @media(max-width:760px){.bill-grid{grid-template-columns:1fr}}
  .stat-card.clickable{cursor:pointer;transition:border-color .2s,transform .15s}
  .stat-card.clickable:hover{border-color:var(--ac);transform:translateY(-1px)}
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

<div class="stat-grid" style="margin-top:16px">
  <a href="{{ route('admin.tagihan.revenue', 'dibayar') }}" class="stat-card clickable" style="text-decoration:none">
    <div class="stat-icon" style="background:rgba(52,211,153,.15);color:#34d399"><i class="fa-solid fa-sack-dollar"></i></div>
    <div>
      <div class="stat-num" style="font-size:19px">Rp {{ number_format($summary['paid_total'], 0, ',', '.') }}</div>
      <div class="stat-label">Total Pendapatan</div>
    </div>
  </a>
  <a href="{{ route('admin.tagihan.revenue', 'dibayar') }}" class="stat-card clickable" style="text-decoration:none">
    <div class="stat-icon" style="background:rgba(129,140,248,.15);color:#818cf8"><i class="fa-solid fa-receipt"></i></div>
    <div>
      <div class="stat-num" style="font-size:19px">{{ $summary['paid_count'] }} tagihan</div>
      <div class="stat-label">Tagihan Dibayar</div>
    </div>
  </a>
  <a href="{{ route('admin.tagihan.revenue', 'gratis') }}" class="stat-card clickable" style="text-decoration:none">
    <div class="stat-icon" style="background:rgba(251,191,36,.15);color:#fbbf24"><i class="fa-solid fa-gift"></i></div>
    <div>
      <div class="stat-num" style="font-size:19px">Rp {{ number_format($summary['free_total'], 0, ',', '.') }}</div>
      <div class="stat-label">Total Digratiskan</div>
    </div>
  </a>
  <a href="{{ route('admin.tagihan.revenue', 'gratis') }}" class="stat-card clickable" style="text-decoration:none">
    <div class="stat-icon" style="background:rgba(148,163,184,.15);color:#94a3b8"><i class="fa-solid fa-receipt"></i></div>
    <div>
      <div class="stat-num" style="font-size:19px">{{ $summary['free_count'] }} tagihan</div>
      <div class="stat-label">Tagihan Digratiskan</div>
    </div>
  </a>
</div>

<div class="bill-grid" style="margin-top:16px">

  {{-- Tab vertikal --}}
  <div class="card" style="padding:10px">
    <a href="{{ route('admin.tagihan.index') }}" class="bill-tab {{ $tab === 'outlet' ? 'active' : '' }}">
      <i class="fa-solid fa-shop"></i> Daftar Outlet
    </a>
    <a href="{{ route('admin.tagihan.codes') }}" class="bill-tab {{ $tab === 'kode' ? 'active' : '' }}">
      <i class="fa-solid fa-key"></i> Kode Pelunasan
    </a>
  </div>

  {{-- Konten tab --}}
  <div>
    @if($tab === 'outlet')
      @include('admin.outlet-billing.tabs.outlets')
    @elseif($tab === 'kode')
      @include('admin.outlet-billing.tabs.codes')
    @endif
  </div>

</div>

</x-app-layout>
