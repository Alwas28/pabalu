<x-outlet-layout :outlet="$outlet" pageTitle="Dashboard Toko">

{{-- Ringkasan --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:16px">

  <div class="stat-card animate-fadeUp">
    <div class="stat-icon" style="background:rgba(167,139,250,.14);color:#a78bfa">
      <i class="fa-solid fa-users"></i>
    </div>
    <div>
      <div class="stat-num">{{ $totalCustomers }}</div>
      <div class="stat-label">Total Pelanggan</div>
    </div>
  </div>

  <div class="stat-card animate-fadeUp d1">
    <div class="stat-icon" style="background:rgba(96,165,250,.14);color:#60a5fa">
      <i class="fa-solid fa-calendar-day"></i>
    </div>
    <div>
      <div class="stat-num">0</div>
      <div class="stat-label">Booking Hari Ini</div>
    </div>
  </div>

  <div class="stat-card animate-fadeUp d2">
    <div class="stat-icon" style="background:rgba(251,191,36,.14);color:#fbbf24">
      <i class="fa-solid fa-key"></i>
    </div>
    <div>
      <div class="stat-num">0</div>
      <div class="stat-label">Barang Sedang Disewa</div>
    </div>
  </div>

  <div class="stat-card animate-fadeUp d3">
    <div class="stat-icon" style="background:rgba(52,211,153,.14);color:#34d399">
      <i class="fa-solid fa-hourglass-half"></i>
    </div>
    <div>
      <div class="stat-num">0</div>
      <div class="stat-label">Jatuh Tempo</div>
    </div>
  </div>

  <div class="stat-card animate-fadeUp d4">
    <div class="stat-icon" style="background:rgba(248,113,113,.14);color:#f87171">
      <i class="fa-solid fa-triangle-exclamation"></i>
    </div>
    <div>
      <div class="stat-num">0</div>
      <div class="stat-label">Terlambat</div>
    </div>
  </div>

</div>

<div style="font-size:11.5px;color:var(--muted);display:flex;align-items:center;gap:6px;margin-top:-8px">
  <i class="fa-solid fa-circle-info"></i>
  Modul Booking, Sewa, dan Barang belum dibangun — angka di atas akan otomatis terisi setelah modul terkait tersedia.
</div>

{{-- Aksi Cepat --}}
<div class="card animate-fadeUp d1">
  <div class="card-header">
    <span class="card-title">Aksi Cepat</span>
  </div>
  <div class="card-body">
    <div class="action-grid">
      <a href="{{ $outlet->route('customers.create') }}" class="action-card">
        <div class="action-card-icon" style="background:rgba(167,139,250,.14);color:#a78bfa"><i class="fa-solid fa-user-plus"></i></div>
        <div class="action-card-label">Tambah Pelanggan</div>
        <div class="action-card-sub">Daftarkan pelanggan baru</div>
      </a>
      @if($outlet->enable_booking)
      <a href="{{ $outlet->route('bookings.create') }}" class="action-card">
        <div class="action-card-icon" style="background:rgba(96,165,250,.14);color:#60a5fa"><i class="fa-solid fa-calendar-plus"></i></div>
        <div class="action-card-label">Booking Baru</div>
        <div class="action-card-sub">Booking barang untuk pelanggan</div>
      </a>
      @endif
      <a href="{{ $outlet->route('rentals.create') }}" class="action-card">
        <div class="action-card-icon" style="background:rgba(251,191,36,.14);color:#fbbf24"><i class="fa-solid fa-key"></i></div>
        <div class="action-card-label">Sewa Baru</div>
        <div class="action-card-sub">Proses transaksi sewa</div>
      </a>
    </div>
  </div>
</div>

{{-- Info Outlet --}}
<div class="card animate-fadeUp d2">
  <div class="card-header">
    <span class="card-title">Informasi Outlet</span>
  </div>
  <div class="card-body" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:14px">

    @if($outlet->address)
    <div style="display:flex;align-items:flex-start;gap:10px;font-size:13px">
      <i class="fa-solid fa-location-dot" style="color:var(--ac);margin-top:2px;flex-shrink:0;width:14px;text-align:center"></i>
      <span style="color:var(--text);line-height:1.5">{{ $outlet->address }}</span>
    </div>
    @endif

    @if($outlet->phone)
    <div style="display:flex;align-items:center;gap:10px;font-size:13px">
      <i class="fa-solid fa-phone" style="color:var(--ac);flex-shrink:0;width:14px;text-align:center"></i>
      <span style="color:var(--text)">{{ $outlet->phone }}</span>
    </div>
    @endif

    <div style="display:flex;align-items:center;gap:10px;font-size:13px">
      <i class="fa-solid fa-circle" style="font-size:9px;flex-shrink:0;width:14px;text-align:center;
         color:{{ $outlet->is_active ? '#34d399' : '#f87171' }}"></i>
      <span style="color:var(--text)">{{ $outlet->is_active ? 'Outlet Aktif' : 'Outlet Nonaktif' }}</span>
    </div>

    <div style="display:flex;align-items:center;gap:10px;font-size:13px">
      <i class="fa-regular fa-calendar" style="color:var(--ac);flex-shrink:0;width:14px;text-align:center"></i>
      <span style="color:var(--text)">Dibuat {{ $outlet->created_at->translatedFormat('d F Y') }}</span>
    </div>
  </div>
</div>

</x-outlet-layout>
