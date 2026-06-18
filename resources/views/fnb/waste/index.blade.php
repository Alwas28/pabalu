<x-outlet-layout :outlet="$outlet" pageTitle="Waste / Rusak">

<style>
.reason-badge{display:inline-flex;align-items:center;gap:4px;padding:2px 9px;border-radius:99px;font-size:11px;font-weight:600}
</style>

{{-- Header --}}
<div style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap">
  <div>
    <h2 class="font-display" style="font-size:20px;font-weight:700;color:var(--text)">Waste / Rusak</h2>
    <p style="font-size:13px;color:var(--muted);margin-top:2px">Pencatatan bahan/produk yang terbuang, rusak, atau kedaluwarsa</p>
  </div>
  @if($user->hasPermission('stock.waste'))
  <a href="{{ $outlet->route('waste.create') }}"
     style="display:inline-flex;align-items:center;gap:8px;padding:9px 18px;border-radius:12px;background:var(--ac);color:#fff;font-size:13px;font-weight:600;text-decoration:none;transition:opacity .15s"
     onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
    <i class="fa-solid fa-plus"></i> Buat Laporan Waste
  </a>
  @endif
</div>

{{-- Filter --}}
<form method="GET" style="display:flex;align-items:flex-end;gap:10px;flex-wrap:wrap">
  <div>
    <label class="f-label">Dari Tanggal</label>
    <input type="date" name="from" value="{{ request('from') }}" class="f-input" style="width:160px">
  </div>
  <div>
    <label class="f-label">Sampai</label>
    <input type="date" name="to" value="{{ request('to') }}" class="f-input" style="width:160px">
  </div>
  <button type="submit"
    style="padding:9px 16px;border-radius:12px;background:var(--ac-lt);color:var(--ac);border:1px solid rgba(var(--ac-rgb),.25);font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;white-space:nowrap">
    <i class="fa-solid fa-filter"></i> Filter
  </button>
  @if(request('from') || request('to'))
  <a href="{{ $outlet->route('waste.index') }}"
     style="padding:9px 16px;border-radius:12px;background:var(--surface2);color:var(--muted);border:1px solid var(--border);font-size:13px;font-weight:600;text-decoration:none">
    Reset
  </a>
  @endif
</form>

{{-- Table --}}
<div class="card">
  <div class="card-header">
    <span class="card-title"><i class="fa-solid fa-trash-can" style="color:var(--ac);margin-right:6px"></i>Riwayat Waste</span>
    <span style="font-size:12px;color:var(--muted)">{{ $wastes->total() }} catatan</span>
  </div>
  <div style="overflow-x:auto">
    <table class="tbl">
      <thead>
        <tr>
          <th>Tanggal</th>
          <th>Total Item</th>
          <th>Total Qty</th>
          <th>Dicatat Oleh</th>
          <th>Catatan</th>
          <th style="text-align:right">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($wastes as $waste)
        <tr>
          <td class="td-main">{{ $waste->date->translatedFormat('d M Y') }}</td>
          <td>{{ $waste->items->count() }} produk</td>
          <td>{{ $waste->items->sum('qty') }} pcs</td>
          <td>{{ $waste->user?->name ?? '-' }}</td>
          <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
            {{ $waste->notes ?? '-' }}
          </td>
          <td style="text-align:right">
            <a href="{{ $outlet->route('waste.show', [$waste]) }}"
               style="display:inline-flex;align-items:center;gap:5px;padding:5px 12px;border-radius:8px;background:var(--ac-lt);color:var(--ac);font-size:12px;font-weight:600;text-decoration:none;border:1px solid rgba(var(--ac-rgb),.2)">
              <i class="fa-solid fa-eye"></i> Detail
            </a>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="6" style="text-align:center;padding:40px 0;color:var(--muted)">
            <i class="fa-solid fa-box-open" style="font-size:28px;margin-bottom:8px;display:block;opacity:.4"></i>
            Belum ada catatan waste
            @if($user->hasPermission('stock.waste'))
            — <a href="{{ $outlet->route('waste.create') }}" style="color:var(--ac)">Buat Laporan</a>
            @endif
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if($wastes->hasPages())
  <div style="padding:16px 20px;border-top:1px solid var(--border)">
    {{ $wastes->links() }}
  </div>
  @endif
</div>

</x-outlet-layout>
