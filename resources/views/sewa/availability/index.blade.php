<x-outlet-layout :outlet="$outlet" pageTitle="Ketersediaan">

@php
  $statusBadge = [
    'tersedia'    => 'badge-green',
    'disewa'      => 'badge-blue',
    'maintenance' => 'badge-amber',
    'rusak'       => 'badge-red',
  ];
  $allUnits = $items->flatMap->units;
@endphp

<style>
  .avail-unit-select{padding:5px 9px;border-radius:8px;border:1px solid var(--border);background:var(--surface2);color:var(--sub);font-size:11.5px;font-family:inherit;outline:none;cursor:pointer}
</style>

{{-- ── HEADER ── --}}
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px" class="animate-fadeUp">
  <div>
    <h2 class="font-display" style="font-size:20px;font-weight:700;color:var(--text)">Ketersediaan</h2>
    <p style="font-size:13px;color:var(--muted);margin-top:2px">
      Status setiap unit barang sewa outlet <strong style="color:var(--sub)">{{ $outlet->name }}</strong> saat ini
    </p>
  </div>
</div>

@if($items->isEmpty())
<div class="card animate-fadeUp" style="text-align:center;padding:52px 24px">
  <div style="width:64px;height:64px;border-radius:50%;background:var(--ac-lt);color:var(--ac);display:grid;place-items:center;margin:0 auto 18px;font-size:24px">
    <i class="fa-solid fa-box"></i>
  </div>
  <div style="font-size:16px;font-weight:800;color:var(--text);margin-bottom:8px;font-family:'Clash Display',sans-serif">Belum Ada Barang Sewa</div>
  <div style="font-size:13px;color:var(--muted);line-height:1.7;max-width:380px;margin:0 auto">
    Tambahkan Barang Sewa dan Unit terlebih dahulu untuk melihat ketersediaannya di sini.
  </div>
  <div style="margin-top:20px">
    <a href="{{ $outlet->route('items.index') }}"
      style="padding:10px 20px;border-radius:10px;background:var(--ac);color:#fff;text-decoration:none;font-size:13px;font-weight:700;display:inline-flex;align-items:center;gap:7px">
      <i class="fa-solid fa-box"></i> Ke Barang Sewa
    </a>
  </div>
</div>
@else

{{-- ── STATS ── --}}
@php
  $total       = $allUnits->count();
  $tersedia    = $allUnits->where('status', 'tersedia')->count();
  $disewa      = $allUnits->where('status', 'disewa')->count();
  $maintenance = $allUnits->whereIn('status', ['maintenance', 'rusak'])->count();
@endphp
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px" class="animate-fadeUp d1">
  <div class="stat-card">
    <div class="stat-icon" style="background:var(--ac-lt);color:var(--ac)"><i class="fa-solid fa-cubes"></i></div>
    <div><div class="stat-num">{{ $total }}</div><div class="stat-label">Total Unit</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(16,185,129,.15);color:#34d399"><i class="fa-solid fa-circle-check"></i></div>
    <div><div class="stat-num">{{ $tersedia }}</div><div class="stat-label">Tersedia</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(96,165,250,.15);color:#60a5fa"><i class="fa-solid fa-key"></i></div>
    <div><div class="stat-num">{{ $disewa }}</div><div class="stat-label">Disewa</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(251,191,36,.15);color:#fbbf24"><i class="fa-solid fa-screwdriver-wrench"></i></div>
    <div><div class="stat-num">{{ $maintenance }}</div><div class="stat-label">Maintenance / Rusak</div></div>
  </div>
</div>

{{-- ── BOARD PER BARANG ── --}}
<div style="display:flex;flex-direction:column;gap:16px" class="animate-fadeUp d2">
  @foreach($items as $item)
  <div class="card">
    <div class="card-header">
      <div style="display:flex;align-items:center;gap:10px">
        <div style="width:36px;height:36px;border-radius:10px;background:var(--surface2);overflow:hidden;display:flex;align-items:center;justify-content:center;flex-shrink:0">
          @if($item->images->isNotEmpty())
          <img src="{{ Storage::url($item->images->first()->path) }}" alt="" style="width:100%;height:100%;object-fit:cover">
          @else
          <i class="fa-solid fa-box" style="color:var(--muted);font-size:14px"></i>
          @endif
        </div>
        <span class="card-title">{{ $item->name }}</span>
      </div>
      <span style="font-size:12px;color:var(--muted)">{{ $item->units->count() }} unit</span>
    </div>
    <div class="card-body">
      @if($item->units->isEmpty())
      <div style="font-size:12.5px;color:var(--muted);text-align:center;padding:12px">
        Belum ada unit untuk barang ini. <a href="{{ $outlet->route('units.index') }}" style="color:var(--ac);font-weight:600">Tambah unit</a>
      </div>
      @else
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px">
        @foreach($item->units as $unit)
        <div style="border:1px solid var(--border);border-radius:12px;padding:11px 13px;display:flex;flex-direction:column;gap:8px">
          <div style="display:flex;align-items:center;justify-content:space-between;gap:8px">
            <span style="font-size:13px;font-weight:700;color:var(--text)">{{ $unit->code }}</span>
            <span class="badge {{ $statusBadge[$unit->status] ?? 'badge-gray' }}" style="font-size:10px">
              {{ \App\Models\RentalUnit::STATUSES[$unit->status] ?? $unit->status }}
            </span>
          </div>
          @if($unit->condition)
          <div style="font-size:11px;color:var(--muted)">Kondisi: {{ $unit->condition }}</div>
          @endif
          <form method="POST" action="{{ $outlet->route('availability.update-status', [$unit]) }}" onchange="this.submit()">
            @csrf @method('PATCH')
            <select name="status" class="avail-unit-select" style="width:100%">
              @foreach(\App\Models\RentalUnit::STATUSES as $val => $label)
              <option value="{{ $val }}" {{ $unit->status === $val ? 'selected' : '' }}>{{ $label }}</option>
              @endforeach
            </select>
          </form>
        </div>
        @endforeach
      </div>
      @endif
    </div>
  </div>
  @endforeach
</div>
@endif

</x-outlet-layout>
