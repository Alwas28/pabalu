<x-outlet-layout :outlet="$outlet" pageTitle="Kategori">

{{-- ── HEADER BAR ── --}}
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px" class="animate-fadeUp">
  <div>
    <h2 class="font-display" style="font-size:20px;font-weight:700;color:var(--text)">Kategori Produk</h2>
    <p style="font-size:13px;color:var(--muted);margin-top:2px">
      Kategori untuk jenis outlet <strong style="color:var(--sub)">{{ $outlet->outletType?->name }}</strong>
    </p>
  </div>
</div>

{{-- ── INFO BANNER ── --}}
<div class="animate-fadeUp d1" style="display:flex;align-items:flex-start;gap:12px;padding:14px 16px;border-radius:12px;background:var(--ac-lt2);border:1px solid rgba(var(--ac-rgb),.2);margin-top:14px">
  <i class="fa-solid fa-circle-info" style="color:var(--ac);font-size:14px;margin-top:1px"></i>
  <div style="font-size:12.5px;color:var(--sub);line-height:1.6">
    Kategori ditentukan oleh admin untuk semua outlet dengan jenis yang sama, jadi tidak bisa ditambah,
    diubah, atau dihapus dari sini. Hubungi admin jika perlu kategori baru.
  </div>
</div>

{{-- ── STATS ── --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-top:16px" class="animate-fadeUp d1">
  <div class="stat-card">
    <div class="stat-icon" style="background:var(--ac-lt);color:var(--ac)">
      <i class="fa-solid fa-tags"></i>
    </div>
    <div>
      <div class="stat-num">{{ $categories->count() }}</div>
      <div class="stat-label">Total Kategori</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(16,185,129,.15);color:#34d399">
      <i class="fa-solid fa-circle-check"></i>
    </div>
    <div>
      <div class="stat-num">{{ $categories->where('is_active', true)->count() }}</div>
      <div class="stat-label">Aktif</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(148,163,184,.12);color:#94a3b8">
      <i class="fa-solid fa-circle-xmark"></i>
    </div>
    <div>
      <div class="stat-num">{{ $categories->where('is_active', false)->count() }}</div>
      <div class="stat-label">Nonaktif</div>
    </div>
  </div>
</div>

{{-- ── TABLE ── --}}
<div class="card animate-fadeUp d2">
  <div class="card-header">
    <span class="card-title">
      <i class="fa-solid fa-tags" style="color:var(--ac);margin-right:8px;font-size:14px"></i>
      Daftar Kategori
    </span>
    <div style="position:relative">
      <input id="search-input" oninput="filterRows()" placeholder="Cari kategori…"
        style="padding:7px 12px 7px 32px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--text);font-size:13px;font-family:inherit;outline:none;width:200px;transition:border-color .15s"
        onfocus="this.style.borderColor='var(--ac)'" onblur="this.style.borderColor='var(--border)'">
      <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:12px;pointer-events:none"></i>
    </div>
  </div>

  @if($categories->isEmpty())
  <div style="padding:64px 24px;text-align:center">
    <div style="width:60px;height:60px;border-radius:16px;background:var(--ac-lt);display:grid;place-items:center;margin:0 auto 16px;font-size:22px;color:var(--ac)">
      <i class="fa-solid fa-tags"></i>
    </div>
    <h3 class="font-display" style="font-size:17px;font-weight:700;color:var(--text);margin-bottom:6px">Belum Ada Kategori</h3>
    <p style="font-size:13.5px;color:var(--muted);max-width:340px;margin:0 auto">
      Admin belum menambahkan kategori untuk jenis outlet ini.
    </p>
  </div>
  @else
  <div style="overflow-x:auto">
    <table class="tbl" id="cat-table">
      <thead>
        <tr>
          <th style="width:44px">#</th>
          <th>Nama Kategori</th>
          <th>Deskripsi</th>
          <th style="text-align:center;width:80px">Urutan</th>
          <th style="text-align:center;width:90px">Status</th>
        </tr>
      </thead>
      <tbody>
        @foreach($categories as $i => $cat)
        <tr class="cat-row" data-name="{{ strtolower($cat->name) }}">
          <td style="color:var(--muted);font-size:12px">{{ $i + 1 }}</td>
          <td class="td-main">
            <div style="display:flex;align-items:center;gap:9px">
              <div style="width:32px;height:32px;border-radius:9px;background:var(--ac-lt);color:var(--ac);display:grid;place-items:center;font-size:13px;flex-shrink:0">
                <i class="fa-solid fa-tag"></i>
              </div>
              {{ $cat->name }}
            </div>
          </td>
          <td style="max-width:260px">
            <span style="font-size:12.5px;color:var(--muted)">{{ $cat->description ?: '—' }}</span>
          </td>
          <td style="text-align:center">
            <span style="font-size:13px;font-weight:600;color:var(--sub)">{{ $cat->sort_order }}</span>
          </td>
          <td style="text-align:center">
            @if($cat->is_active)
              <span class="badge badge-green"><i class="fa-solid fa-circle" style="font-size:6px"></i>Aktif</span>
            @else
              <span class="badge badge-gray"><i class="fa-solid fa-circle" style="font-size:6px"></i>Nonaktif</span>
            @endif
          </td>
        </tr>
        @endforeach
        <tr id="no-result" style="display:none">
          <td colspan="5" style="text-align:center;padding:32px;color:var(--muted);font-size:13px">
            <i class="fa-solid fa-magnifying-glass" style="margin-right:6px"></i>Tidak ditemukan
          </td>
        </tr>
      </tbody>
    </table>
  </div>
  @endif
</div>

@push('scripts')
<script>
function filterRows() {
  const q = document.getElementById('search-input').value.toLowerCase().trim();
  const rows = document.querySelectorAll('.cat-row');
  let visible = 0;
  rows.forEach(r => {
    const match = !q || r.dataset.name.includes(q);
    r.style.display = match ? '' : 'none';
    if (match) visible++;
  });
  const noResult = document.getElementById('no-result');
  if (noResult) noResult.style.display = visible === 0 ? '' : 'none';
}
</script>
@endpush

</x-outlet-layout>
