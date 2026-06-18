<x-outlet-layout :outlet="$outlet" pageTitle="Detail Waste #{{ $waste->id }}">

@php
$reasonColors = [
    'sisa'    => ['bg' => 'rgba(245,158,11,.12)', 'color' => '#fbbf24'],
    'rusak'   => ['bg' => 'rgba(239,68,68,.12)',  'color' => '#f87171'],
    'expired' => ['bg' => 'rgba(239,68,68,.18)',  'color' => '#f43f5e'],
    'lainnya' => ['bg' => 'rgba(148,163,184,.12)','color' => '#94a3b8'],
];
@endphp

{{-- Back --}}
<div>
  <a href="{{ $outlet->route('waste.index') }}"
     style="display:inline-flex;align-items:center;gap:6px;font-size:13px;color:var(--muted);text-decoration:none;transition:color .15s"
     onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--muted)'">
    <i class="fa-solid fa-arrow-left" style="font-size:11px"></i> Kembali ke Waste / Rusak
  </a>
</div>

<div class="two-col">

  {{-- Left: item table --}}
  <div class="card">
    <div class="card-header">
      <span class="card-title">
        <i class="fa-solid fa-trash-can" style="color:var(--ac);margin-right:6px"></i>
        Detail Item Waste
      </span>
      <span class="badge badge-amber">{{ $waste->items->count() }} produk</span>
    </div>
    <div style="overflow-x:auto">
      <table class="tbl">
        <thead>
          <tr>
            <th>#</th>
            <th>Produk</th>
            <th style="text-align:center">Qty</th>
            <th>Alasan</th>
            <th>Catatan</th>
          </tr>
        </thead>
        <tbody>
          @foreach($waste->items as $i => $item)
          @php
            $rc = $reasonColors[$item->reason] ?? $reasonColors['lainnya'];
          @endphp
          <tr>
            <td style="color:var(--muted);font-size:12px">{{ $i + 1 }}</td>
            <td class="td-main">
              {{ $item->product_name }}
              @if(!$item->product)
                <span style="font-size:11px;color:var(--muted)">(produk dihapus)</span>
              @endif
            </td>
            <td style="text-align:center;font-weight:700;color:var(--text);font-size:15px">
              {{ $item->qty }}
            </td>
            <td>
              <span style="display:inline-flex;align-items:center;padding:3px 10px;border-radius:99px;font-size:11px;font-weight:600;background:{{ $rc['bg'] }};color:{{ $rc['color'] }}">
                {{ $reasons[$item->reason] ?? $item->reason }}
              </span>
            </td>
            <td style="font-size:12px;color:var(--muted)">{{ $item->notes ?? '—' }}</td>
          </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr style="border-top:2px solid var(--border)">
            <td colspan="2" style="padding:12px 14px;font-weight:600;color:var(--text);font-size:13px">Total</td>
            <td style="text-align:center;padding:12px 14px;font-weight:700;color:var(--text);font-size:16px">
              {{ $waste->items->sum('qty') }} pcs
            </td>
            <td colspan="2"></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

  {{-- Right: info + actions --}}
  <div style="display:flex;flex-direction:column;gap:16px">

    {{-- Info card --}}
    <div class="card">
      <div class="card-header">
        <span class="card-title">Informasi</span>
      </div>
      <div class="card-body" style="display:flex;flex-direction:column;gap:14px">

        <div style="display:flex;flex-direction:column;gap:4px">
          <span style="font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.5px">Tanggal</span>
          <span style="font-size:14px;font-weight:600;color:var(--text)">{{ $waste->date->translatedFormat('d F Y') }}</span>
        </div>
        <div style="height:1px;background:var(--border)"></div>

        <div style="display:flex;flex-direction:column;gap:4px">
          <span style="font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.5px">Dicatat Oleh</span>
          <span style="font-size:14px;font-weight:600;color:var(--text)">{{ $waste->user?->name ?? '-' }}</span>
        </div>
        <div style="height:1px;background:var(--border)"></div>

        <div style="display:flex;flex-direction:column;gap:4px">
          <span style="font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.5px">Waktu Dicatat</span>
          <span style="font-size:13px;color:var(--sub)">{{ $waste->submitted_at?->translatedFormat('d M Y, H:i') ?? '-' }}</span>
        </div>

        @if($waste->notes)
        <div style="height:1px;background:var(--border)"></div>
        <div style="display:flex;flex-direction:column;gap:4px">
          <span style="font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.5px">Catatan</span>
          <span style="font-size:13px;color:var(--sub);line-height:1.5">{{ $waste->notes }}</span>
        </div>
        @endif

      </div>
    </div>

    {{-- Breakdown alasan --}}
    <div class="card">
      <div class="card-header">
        <span class="card-title">Breakdown Alasan</span>
      </div>
      <div class="card-body" style="display:flex;flex-direction:column;gap:8px">
        @foreach($reasons as $key => $label)
        @php
          $groupQty = $waste->items->where('reason', $key)->sum('qty');
          $groupCount = $waste->items->where('reason', $key)->count();
          $rc = $reasonColors[$key] ?? $reasonColors['lainnya'];
        @endphp
        @if($groupCount > 0)
        <div style="display:flex;align-items:center;gap:10px">
          <span style="display:inline-flex;padding:3px 10px;border-radius:99px;font-size:11px;font-weight:600;background:{{ $rc['bg'] }};color:{{ $rc['color'] }};min-width:110px;justify-content:center">
            {{ $label }}
          </span>
          <span style="font-size:13px;color:var(--sub)">{{ $groupCount }} item · {{ $groupQty }} pcs</span>
        </div>
        @endif
        @endforeach
      </div>
    </div>

    {{-- Delete action --}}
    @if($user->hasPermission('stock.waste'))
    <div class="card" style="border-color:rgba(239,68,68,.2)">
      <div class="card-body">
        <p style="font-size:12.5px;color:var(--muted);margin-bottom:12px;line-height:1.5">
          Menghapus laporan ini akan <strong style="color:#f87171">mengembalikan stok</strong> produk ke kondisi sebelum waste dicatat.
        </p>
        <button onclick="openModal('modal-hapus')"
          style="width:100%;padding:10px;border-radius:10px;background:rgba(239,68,68,.1);color:#f87171;border:1px solid rgba(239,68,68,.25);font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:7px;transition:background .15s"
          onmouseover="this.style.background='rgba(239,68,68,.18)'" onmouseout="this.style.background='rgba(239,68,68,.1)'">
          <i class="fa-solid fa-trash"></i> Hapus Laporan Waste
        </button>
      </div>
    </div>
    @endif

  </div>

</div>

{{-- Modal hapus --}}
@if($user->hasPermission('stock.waste'))
<div class="modal-backdrop" id="modal-hapus">
  <div class="modal-box" style="max-width:400px">
    <div style="padding:24px">
      <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px">
        <div style="width:44px;height:44px;border-radius:12px;background:rgba(239,68,68,.12);display:grid;place-items:center;font-size:18px;color:#f87171;flex-shrink:0">
          <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <div>
          <h3 class="font-display" style="font-size:16px;font-weight:700;color:var(--text)">Hapus Laporan Waste?</h3>
          <p style="font-size:12px;color:var(--muted)">Tindakan ini tidak dapat dibatalkan</p>
        </div>
      </div>
      <p style="font-size:13px;color:var(--sub);line-height:1.6;margin-bottom:20px">
        Laporan waste tanggal <strong style="color:var(--text)">{{ $waste->date->translatedFormat('d M Y') }}</strong>
        akan dihapus dan stok <strong style="color:#34d399">{{ $waste->items->sum('qty') }} pcs</strong> dari
        <strong style="color:#34d399">{{ $waste->items->count() }} produk</strong> akan dikembalikan.
      </p>
      <div style="display:flex;gap:10px">
        <button onclick="closeModal('modal-hapus')"
          style="flex:1;padding:10px;border-radius:10px;background:var(--surface2);color:var(--sub);border:1px solid var(--border);font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
          Batal
        </button>
        <form method="POST" action="{{ $outlet->route('waste.destroy', [$waste]) }}" style="flex:1">
          @csrf @method('DELETE')
          <button type="submit"
            style="width:100%;padding:10px;border-radius:10px;background:rgba(239,68,68,.9);color:#fff;border:none;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit">
            Ya, Hapus
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
@endif

</x-outlet-layout>
