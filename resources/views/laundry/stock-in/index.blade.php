<x-outlet-layout :outlet="$outlet" pageTitle="Tambah Stok">

{{-- ── HEADER ── --}}
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px" class="animate-fadeUp">
  <div>
    <h2 class="font-display" style="font-size:20px;font-weight:700;color:var(--text)">Riwayat Tambah Stok</h2>
    <p style="font-size:13px;color:var(--muted);margin-top:2px">
      Catatan penerimaan stok masuk · {{ now()->translatedFormat('F Y') }}
    </p>
  </div>
  @if($user->hasPermission('stock.in'))
  <a href="{{ $outlet->route('stock-in.create') }}"
    style="display:inline-flex;align-items:center;gap:8px;padding:10px 18px;border-radius:12px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13.5px;font-weight:700;cursor:pointer;text-decoration:none;transition:opacity .15s"
    onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
    <i class="fa-solid fa-plus" style="font-size:12px"></i> Tambah Stok Masuk
  </a>
  @endif
</div>

{{-- ── STATS BULAN INI ── --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px" class="animate-fadeUp d1">
  <div class="stat-card">
    <div class="stat-icon" style="background:var(--ac-lt);color:var(--ac)"><i class="fa-solid fa-truck-ramp-box"></i></div>
    <div>
      <div class="stat-num">{{ $statTrx }}</div>
      <div class="stat-label">Transaksi Bulan Ini</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(16,185,129,.15);color:#34d399"><i class="fa-solid fa-boxes-stacking"></i></div>
    <div>
      <div class="stat-num">{{ number_format($statQty) }}</div>
      <div class="stat-label">Total Qty Masuk</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(79,110,247,.15);color:#818cf8"><i class="fa-solid fa-cubes"></i></div>
    <div>
      <div class="stat-num">{{ $statProducts }}</div>
      <div class="stat-label">Jenis Produk</div>
    </div>
  </div>
</div>

{{-- ── FILTER ── --}}
<form method="GET" action="{{ $outlet->route('stock-in.index') }}"
  class="animate-fadeUp d2"
  style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:14px 18px;display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end">

  <div style="min-width:130px">
    <label class="f-label">Dari Tanggal</label>
    <input name="date_from" type="date" value="{{ request('date_from') }}" class="f-input">
  </div>

  <div style="min-width:130px">
    <label class="f-label">Sampai Tanggal</label>
    <input name="date_to" type="date" value="{{ request('date_to') }}" class="f-input">
  </div>

  <div style="display:flex;gap:8px;align-items:flex-end">
    <button type="submit"
      style="padding:9px 18px;border-radius:12px;border:none;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;font-size:13px;font-weight:700;cursor:pointer;white-space:nowrap">
      <i class="fa-solid fa-filter" style="font-size:11px;margin-right:5px"></i>Filter
    </button>
    @if($hasFilter)
    <a href="{{ $outlet->route('stock-in.index') }}"
      style="padding:9px 14px;border-radius:12px;border:1px solid var(--border);background:transparent;color:var(--sub);font-size:13px;font-weight:600;text-decoration:none;white-space:nowrap">
      Reset
    </a>
    @endif
  </div>
</form>

{{-- ── TABLE ── --}}
<div class="card animate-fadeUp d3">
  <div class="card-header">
    <span class="card-title">
      <i class="fa-solid fa-clock-rotate-left" style="color:var(--ac);margin-right:8px;font-size:14px"></i>
      Riwayat Penerimaan Stok
    </span>
    <span style="font-size:12px;color:var(--muted)">
      {{ $stockIns->total() }} catatan
      @if($hasFilter)<span style="color:var(--ac);font-weight:600"> (difilter)</span>@endif
    </span>
  </div>

  @if($stockIns->isEmpty())
  <div style="padding:60px 24px;text-align:center">
    <div style="width:60px;height:60px;border-radius:16px;background:var(--ac-lt);display:grid;place-items:center;margin:0 auto 16px;font-size:22px;color:var(--ac)">
      <i class="fa-solid fa-truck-ramp-box"></i>
    </div>
    <h3 class="font-display" style="font-size:17px;font-weight:700;color:var(--text);margin-bottom:6px">Belum Ada Data</h3>
    <p style="font-size:13.5px;color:var(--muted);max-width:320px;margin:0 auto 20px">
      Catat penerimaan stok pertama kali dengan menekan tombol di atas.
    </p>
    @if($user->hasPermission('stock.in'))
    <a href="{{ $outlet->route('stock-in.create') }}"
      style="display:inline-flex;align-items:center;gap:8px;padding:10px 20px;border-radius:12px;background:linear-gradient(135deg,var(--ac),var(--ac2));color:#fff;text-decoration:none;font-size:13.5px;font-weight:700">
      <i class="fa-solid fa-plus" style="font-size:12px"></i>Tambah Stok Pertama
    </a>
    @endif
  </div>
  @else
  <div style="overflow-x:auto">
    <table class="tbl">
      <thead>
        <tr>
          <th style="width:130px">Tanggal</th>
          <th style="text-align:center;width:80px">Produk</th>
          <th style="text-align:center;width:80px">Total Qty</th>
          <th>Dicatat oleh</th>
          <th style="text-align:center;width:120px">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @foreach($stockIns as $si)
        <tr>
          <td class="td-main">
            <div style="font-weight:600;color:var(--text)">{{ $si->date->translatedFormat('d M Y') }}</div>
            <div style="font-size:11px;color:var(--muted)">{{ $si->created_at->format('H:i') }}</div>
          </td>
          <td style="text-align:center;font-weight:600;color:var(--text)">{{ $si->items->count() }}</td>
          <td style="text-align:center;font-weight:700;color:var(--text)">{{ number_format($si->items->sum('qty')) }}</td>
          <td style="font-size:12.5px;color:var(--sub)">{{ $si->user->name }}</td>
          <td style="text-align:center">
            <div style="display:flex;align-items:center;justify-content:center;gap:6px">
              <a href="{{ $outlet->route('stock-in.show', [$si]) }}"
                title="Lihat Detail"
                style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);display:inline-flex;align-items:center;justify-content:center;font-size:12px;color:var(--sub);text-decoration:none;transition:all .15s"
                onmouseover="this.style.background='var(--surface2)';this.style.color='var(--text)'"
                onmouseout="this.style.background='transparent';this.style.color='var(--sub)'">
                <i class="fa-solid fa-eye"></i>
              </a>
              @if($user->hasPermission('stock.in'))
              <a href="{{ $outlet->route('stock-in.edit', [$si]) }}"
                title="Edit"
                style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);display:inline-flex;align-items:center;justify-content:center;font-size:12px;color:var(--sub);text-decoration:none;transition:all .15s"
                onmouseover="this.style.background='var(--surface2)';this.style.color='var(--text)'"
                onmouseout="this.style.background='transparent';this.style.color='var(--sub)'">
                <i class="fa-solid fa-pen-to-square"></i>
              </a>
              <button onclick="confirmHapus({{ $si->id }}, '{{ $si->date->format('d/m/Y') }}')"
                title="Hapus"
                style="width:30px;height:30px;border-radius:8px;border:1px solid rgba(239,68,68,.3);background:transparent;color:#f87171;font-size:12px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .15s"
                onmouseover="this.style.background='rgba(239,68,68,.1)'"
                onmouseout="this.style.background='transparent'">
                <i class="fa-solid fa-trash-can"></i>
              </button>
              @endif
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  @if($stockIns->hasPages())
  <div style="padding:14px 20px;border-top:1px solid var(--border);display:flex;justify-content:center">
    {{ $stockIns->links() }}
  </div>
  @endif
  @endif
</div>

{{-- ── MODAL HAPUS ── --}}
<div id="modal-hapus" class="modal-backdrop" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-box" style="max-width:400px">
    <div style="padding:28px 24px;text-align:center">
      <div style="width:52px;height:52px;border-radius:14px;background:rgba(239,68,68,.15);display:grid;place-items:center;margin:0 auto 14px;font-size:20px;color:#f87171">
        <i class="fa-solid fa-trash-can"></i>
      </div>
      <h3 class="font-display" style="font-size:17px;font-weight:700;color:var(--text);margin-bottom:8px">Hapus Data Tambah Stok?</h3>
      <p style="font-size:13.5px;color:var(--muted);line-height:1.5;max-width:280px;margin:0 auto">
        Penerimaan tanggal <strong id="hapus-tanggal" style="color:var(--text)"></strong> akan dihapus dan stok produk akan dikembalikan.
      </p>
    </div>
    <form id="hapus-form" method="POST">
    @csrf @method('DELETE')
    <div style="padding:0 20px 20px;display:flex;gap:10px">
      <button type="button" onclick="document.getElementById('modal-hapus').classList.remove('open')"
        style="flex:1;padding:10px;border-radius:12px;border:1px solid var(--border);background:transparent;color:var(--sub);font-size:13.5px;font-weight:600;cursor:pointer">
        Batal
      </button>
      <button type="submit"
        style="flex:1;padding:10px;border-radius:12px;border:none;background:#ef4444;color:#fff;font-size:13.5px;font-weight:700;cursor:pointer">
        <i class="fa-solid fa-trash-can" style="margin-right:6px;font-size:12px"></i>Hapus &amp; Kembalikan Stok
      </button>
    </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
function confirmHapus(id, tanggal) {
  document.getElementById('hapus-tanggal').textContent = tanggal;
  document.getElementById('hapus-form').action = '{{ url("outlets/".$outlet->id."/stock-in") }}/' + id;
  document.getElementById('modal-hapus').classList.add('open');
}
</script>
@endpush

</x-outlet-layout>
