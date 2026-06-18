<x-outlet-layout :outlet="$outlet" pageTitle="Detail Opening Stok">

@php
  $habis    = $stockOpname->items->where('actual_qty', 0)->count();
  $tersedia = $stockOpname->items->where('actual_qty', '>', 0)->count();
  $isToday  = $stockOpname->date->isToday();
@endphp

{{-- ── HEADER ── --}}
<div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px" class="animate-fadeUp">
  <div style="display:flex;align-items:center;gap:14px">
    <a href="{{ $outlet->route('opening.index') }}"
      style="width:38px;height:38px;border-radius:10px;border:1px solid var(--border);background:var(--surface);display:grid;place-items:center;color:var(--sub);text-decoration:none;font-size:14px;flex-shrink:0;transition:all .15s"
      onmouseover="this.style.borderColor='var(--ac)';this.style.color='var(--ac)'"
      onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--sub)'">
      <i class="fa-solid fa-arrow-left"></i>
    </a>
    <div>
      <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
        <h2 class="font-display" style="font-size:20px;font-weight:700;color:var(--text)">Detail Opening Stok</h2>
        @if($isToday)
          <span class="badge" style="background:var(--ac-lt2);color:var(--ac)">Hari Ini</span>
        @endif
      </div>
      <p style="font-size:13px;color:var(--muted);margin-top:3px">
        {{ $stockOpname->date->translatedFormat('l, d F Y') }}
        · {{ $stockOpname->submitted_at?->format('H:i') }}
        · Dicatat oleh {{ $stockOpname->user->name }}
      </p>
    </div>
  </div>

  @if($canCancel)
  <button onclick="document.getElementById('modal-batal').classList.add('open')"
    style="display:inline-flex;align-items:center;gap:8px;padding:9px 16px;border-radius:12px;border:1px solid rgba(245,158,11,.3);background:transparent;color:#f59e0b;font-size:13px;font-weight:600;cursor:pointer;transition:all .15s"
    onmouseover="this.style.background='rgba(245,158,11,.1)'" onmouseout="this.style.background='transparent'">
    <i class="fa-solid fa-rotate-left" style="font-size:11px"></i> Batalkan &amp; Isi Ulang
  </button>
  @endif
</div>

{{-- ── STAT CARDS ── --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px" class="animate-fadeUp d1">
  <div class="stat-card">
    <div class="stat-icon" style="background:var(--ac-lt);color:var(--ac)"><i class="fa-solid fa-boxes-stacking"></i></div>
    <div>
      <div class="stat-num">{{ $stockOpname->items->count() }}</div>
      <div class="stat-label">Total Produk</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(16,185,129,.15);color:#34d399"><i class="fa-solid fa-circle-check"></i></div>
    <div>
      <div class="stat-num">{{ $tersedia }}</div>
      <div class="stat-label">Produk Tersedia</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(248,113,113,.15);color:#f87171"><i class="fa-solid fa-circle-xmark"></i></div>
    <div>
      <div class="stat-num" style="{{ $habis > 0 ? 'color:#f87171' : '' }}">{{ $habis }}</div>
      <div class="stat-label">Stok Habis</div>
    </div>
  </div>
</div>

{{-- ── TWO-COL ── --}}
<div class="two-col animate-fadeUp d2">

{{-- LEFT: Tabel produk --}}
<div class="card">
  <div class="card-header">
    <span class="card-title">
      <i class="fa-solid fa-clipboard-list" style="color:var(--ac);margin-right:8px;font-size:13px"></i>
      Hasil Hitungan Stok
    </span>
    <span style="font-size:12px;color:var(--muted)">{{ $stockOpname->items->count() }} produk</span>
  </div>
  <div style="overflow-x:auto">
    <table class="tbl">
      <thead>
        <tr>
          <th style="width:40px">#</th>
          <th>Produk</th>
          <th>Kategori</th>
          <th style="text-align:center;width:110px">Porsi / Stok</th>
          <th>Catatan</th>
        </tr>
      </thead>
      <tbody>
        @foreach($stockOpname->items->sortBy(fn($i) => $i->product->name ?? '') as $idx => $item)
        <tr style="{{ $item->actual_qty === 0 ? 'opacity:.7' : '' }}">
          <td style="color:var(--muted);font-size:12px">{{ $idx + 1 }}</td>
          <td class="td-main">
            <div style="display:flex;align-items:center;gap:9px">
              @if($item->product?->image)
                <img src="{{ Storage::url($item->product->image) }}"
                  style="width:32px;height:32px;border-radius:8px;object-fit:cover;flex-shrink:0">
              @else
                <div style="width:32px;height:32px;border-radius:8px;background:var(--ac-lt);color:var(--ac);display:grid;place-items:center;flex-shrink:0;font-size:13px">
                  <i class="fa-solid fa-box"></i>
                </div>
              @endif
              <div>
                <div style="font-weight:600;color:var(--text)">{{ $item->product->name ?? '(produk dihapus)' }}</div>
                <div style="font-size:11.5px;color:var(--muted)">{{ $item->product?->unit }}</div>
              </div>
            </div>
          </td>
          <td>
            @if($item->product?->category)
              <span class="badge" style="background:var(--ac-lt2);color:var(--ac)">{{ $item->product->category->name }}</span>
            @else
              <span style="color:var(--muted);font-size:12px">—</span>
            @endif
          </td>
          <td style="text-align:center">
            <span style="font-size:16px;font-weight:800;color:{{ $item->actual_qty > 0 ? 'var(--text)' : '#f87171' }}">
              {{ $item->actual_qty }}
            </span>
            <div style="font-size:11px;color:var(--muted)">{{ $item->product?->unit }}</div>
          </td>
          <td style="font-size:12px;color:var(--muted)">{{ $item->notes ?: '—' }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

{{-- RIGHT: Info sidebar --}}
<div style="display:flex;flex-direction:column;gap:16px">

  {{-- Ringkasan --}}
  <div class="card">
    <div class="card-header">
      <span class="card-title"><i class="fa-solid fa-circle-info" style="color:var(--ac);margin-right:8px;font-size:13px"></i>Informasi</span>
    </div>
    <div class="card-body" style="display:flex;flex-direction:column;gap:10px">
      @foreach([
        ['Tanggal',    $stockOpname->date->translatedFormat('d F Y')],
        ['Waktu',      $stockOpname->submitted_at?->format('H:i:s') ?? '—'],
        ['Dicatat oleh', $stockOpname->user->name],
      ] as [$lbl, $val])
      <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 12px;background:var(--surface2);border-radius:10px">
        <span style="font-size:12px;color:var(--muted)">{{ $lbl }}</span>
        <span style="font-size:13px;font-weight:600;color:var(--text)">{{ $val }}</span>
      </div>
      @endforeach

      @if($stockOpname->notes)
      <div style="padding:10px 12px;background:var(--surface2);border-radius:10px">
        <div style="font-size:12px;color:var(--muted);margin-bottom:4px">Catatan</div>
        <div style="font-size:13px;color:var(--text)">{{ $stockOpname->notes }}</div>
      </div>
      @endif
    </div>
  </div>


</div>{{-- end right --}}
</div>{{-- end two-col --}}

{{-- ── MODAL BATALKAN ── --}}
@if($canCancel)
<div id="modal-batal" class="modal-backdrop" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-box" style="max-width:420px">
    <div style="padding:28px 24px;text-align:center">
      <div style="width:52px;height:52px;border-radius:14px;background:rgba(251,191,36,.15);display:grid;place-items:center;margin:0 auto 14px;font-size:20px;color:#fbbf24">
        <i class="fa-solid fa-rotate-left"></i>
      </div>
      <h3 class="font-display" style="font-size:17px;font-weight:700;color:var(--text);margin-bottom:8px">Batalkan Opening Stok?</h3>
      <p style="font-size:13.5px;color:var(--muted);max-width:320px;margin:0 auto;line-height:1.6">
        Stok produk akan dikembalikan ke kondisi <strong style="color:var(--text)">sebelum opening</strong> dilakukan, lalu kamu bisa isi ulang.
      </p>
      <p style="font-size:12px;color:#f87171;margin-top:8px;font-weight:600">
        <i class="fa-solid fa-triangle-exclamation" style="margin-right:4px"></i>
        Pembatalan hanya bisa dilakukan jika belum ada penjualan hari ini.
      </p>
    </div>
    <form action="{{ $outlet->route('opening.destroy', [$stockOpname]) }}" method="POST">
    @csrf @method('DELETE')
    <div style="padding:0 20px 20px;display:flex;gap:10px">
      <button type="button" onclick="document.getElementById('modal-batal').classList.remove('open')"
        style="flex:1;padding:10px;border-radius:12px;border:1px solid var(--border);background:transparent;color:var(--sub);font-size:13.5px;font-weight:600;cursor:pointer">
        Batal
      </button>
      <button type="submit"
        style="flex:1;padding:10px;border-radius:12px;border:none;background:#f59e0b;color:#fff;font-size:13.5px;font-weight:700;cursor:pointer">
        <i class="fa-solid fa-rotate-left" style="margin-right:6px;font-size:12px"></i>Ya, Batalkan Opening
      </button>
    </div>
    </form>
  </div>
</div>
@endif

</x-outlet-layout>
